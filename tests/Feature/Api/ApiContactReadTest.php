<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactReadTest extends TestCase
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
        Tag::factory()
            ->count(3)
            ->create();
    }

    public function test_問い合わせ一覧を_json形式で取得できる(): void
    {
        Contact::factory()->count(3)->withTags()
            ->create([
                'category_id' => $this->category->id,
            ]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                    'gender',
                    'email',
                    'category',
                    'tags',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }

    public function test_問い合わせ詳細を_json形式で取得できる(): void
    {
        $contact = Contact::factory()
            ->withTags()
            ->create([
                'category_id' => $this->category->id,
            ]);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'building',
                'category',
                'tags',
                'detail',
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertJsonPath(
            'data.id',
            $contact->id
        );
    }

    public function test_キーワード検索ができる()
    {
        Contact::factory()->create([
            'category_id' => $this->category->id,
            'first_name' => '山田',
        ]);

        Contact::factory()->create([
            'category_id' => $this->category->id,
            'first_name' => '鈴木',
        ]);
        $response = $this->getJson(
            '/api/v1/contacts?keyword=山田'
        );

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.first_name',
            '山田'
        );
    }

    public function 存在しない問い合わせで404エラーを返す(): void
    {
        $response = $this->getJson('/api/v1/contacts/99999');

        $response->assertNotFound();
    }

    public function test_検索キーワードが255文字を超える場合422を返す(): void
    {
        $keyword = str_repeat('a', 256);

        $response = $this->getJson(
            "/api/v1/contacts?keyword={$keyword}"
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'keyword',
        ]);
    }

    public function test_ページネーションが機能する(): void
    {
        Contact::factory()
            ->count(25)
            ->create([
                'category_id' => $this->category->id,
            ]);

        $response = $this->getJson(
            '/api/v1/contacts?per_page=10'
        );

        $response->assertOk();

        $response->assertJsonCount(10, 'data');

        $response->assertJsonPath(
            'meta.current_page',
            1
        );

        $response->assertJsonPath(
            'meta.per_page',
            10
        );

        $response->assertJsonPath(
            'meta.total',
            25
        );

        $response->assertJsonPath(
            'meta.last_page',
            3
        );
    }
}
