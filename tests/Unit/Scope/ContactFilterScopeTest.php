<?php

namespace Tests\Unit\Scopes;

use App\Models\Category;
use App\Models\Contact;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFilterScopeTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);

        $this->category = Category::first();
    }

    public function test_名前を部分一致検索できる(): void
    {
        Contact::factory()->create([
            'category_id' => $this->category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
        ]);

        Contact::factory()->create([
            'category_id' => $this->category->id,
            'first_name' => '鈴木',
            'last_name' => '一郎',
        ]);

        $contacts = Contact::filter([
            'keyword' => '山田',
        ])->get();

        $this->assertCount(1, $contacts);

        $this->assertEquals(
            '山田',
            $contacts->first()->first_name
        );
    }

    public function test_メールアドレスを部分一致検索できる(): void
    {
        Contact::factory()->create([
            'category_id' => $this->category->id,
            'email' => 'yamada@example.com',
        ]);

        Contact::factory()->create([
            'category_id' => $this->category->id,
            'email' => 'suzuki@example.com',
        ]);

        $contacts = Contact::filter([
            'keyword' => 'yamada',
        ])->get();

        $this->assertCount(1, $contacts);

        $this->assertEquals(
            'yamada@example.com',
            $contacts->first()->email
        );
    }

    public function test_性別で絞り込みできる(): void
    {
        Contact::factory()->create([
            'category_id' => $this->category->id,
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'category_id' => $this->category->id,
            'gender' => 2,
        ]);

        $contacts = Contact::filter([
            'gender' => 1,
        ])->get();

        $this->assertCount(1, $contacts);

        $this->assertEquals(
            1,
            $contacts->first()->gender
        );
    }

    public function test_カテゴリで絞り込みできる(): void
    {
        $otherCategory = Category::where(
            'id',
            '!=',
            $this->category->id
        )->first();

        Contact::factory()->create([
            'category_id' => $this->category->id,
        ]);

        Contact::factory()->create([
            'category_id' => $otherCategory->id,
        ]);

        $contacts = Contact::filter([
            'category_id' => $this->category->id,
        ])->get();

        $this->assertCount(1, $contacts);

        $this->assertEquals(
            $this->category->id,
            $contacts->first()->category_id
        );
    }

    public function test_dateで登録日を絞り込みできる(): void
    {
        Contact::factory()->create([
            'category_id' => $this->category->id,
            'created_at' => '2026-07-01 10:00:00',
        ]);

        Contact::factory()->create([
            'category_id' => $this->category->id,
            'created_at' => '2026-07-02 10:00:00',
        ]);

        $contacts = Contact::filter([
            'date' => '2026-07-01',
        ])->get();

        $this->assertCount(1, $contacts);
    }

    public function test_条件指定なしの場合は全件取得できる(): void
    {
        Contact::factory()
            ->count(3)
            ->create([
                'category_id' => $this->category->id,
            ]);

        $contacts = Contact::filter([])->get();

        $this->assertCount(3, $contacts);
    }
}
