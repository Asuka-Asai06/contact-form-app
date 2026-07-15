<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);

        $this->category = Category::firstWhere(
            'content',
            '商品のお届けについて'
        );
    }

    protected function createContact(array $attributes = []): Contact
    {
        return Contact::factory()->create(
            array_merge([
                'category_id' => $this->category->id,
            ], $attributes)
        );
    }

    public function test_キーワード検索ができる(): void
    {
        $user = User::factory()->create();

        $this->createContact([
            'first_name' => '山田',
        ]);

        $this->createContact([
            'first_name' => '鈴木',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?keyword=山田');

        $response->assertOk();

        $contacts = $response->viewData('contacts');

        $this->assertCount(1, $contacts);

        $this->assertEquals(
            '山田',
            $contacts->first()->first_name
        );
    }

    public function test_性別フィルタが機能する(): void
    {
        $user = User::factory()->create();

        $this->createContact([
            'gender' => 1,
        ]);

        $this->createContact([
            'gender' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?gender=1');

        $response->assertOk();

        $contacts = $response->viewData('contacts');

        $this->assertCount(1, $contacts);
        $this->assertEquals(
            1,
            $contacts->first()->gender
        );
    }

    public function test_カテゴリフィルタが機能する(): void
    {
        $user = User::factory()->create();

        $this->createContact();

        $otherCategory = Category::where(
            'id',
            '!=',
            $this->category->id
        )->first();

        $this->assertNotNull($otherCategory);

        Contact::factory()->create([
            'category_id' => $otherCategory->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?category_id='.$this->category->id);

        $response->assertOk();

        $contacts = $response->viewData('contacts');

        $this->assertCount(1, $contacts);

        $this->assertEquals(
            $this->category->id,
            $contacts->first()->category_id
        );
    }

    public function test_日付フィルタが機能する(): void
    {
        $this->createContact([
            'created_at' => now()->subDay(),
        ]);

        $todayContact = $this->createContact([
            'created_at' => now(),
        ]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin?date='.now()->toDateString());

        $response->assertOk();

        $contacts = $response->viewData('contacts');

        $this->assertCount(1, $contacts);

        $this->assertEquals(
            $todayContact->id,
            $contacts->first()->id
        );
    }

    public function test_一覧は7件ごとにページネーションされる(): void
    {
        $user = User::factory()->create();

        Contact::factory()
            ->count(8)
            ->create([
                'category_id' => $this->category->id,
            ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin');

        $response->assertOk();

        $contacts = $response->viewData('contacts');

        $this->assertEquals(8, $contacts->total());

        $this->assertEquals(
            7,
            $contacts->perPage()
        );

        $this->assertCount(7, $contacts);
    }
}
