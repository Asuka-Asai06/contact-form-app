<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStoreTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected $tags;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);

        $this->category = Category::firstWhere(
            'content',
            '商品のお届けについて'
        );

        $this->tags = Tag::factory()
            ->count(3)
            ->create();
    }

    public function test_問い合わせを登録できる(): void
    {
        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => '新宿マンション101',
            'category_id' => $this->category->id,
            'detail' => '商品の配送について問い合わせます',
            'tag_ids' => $this->tags->pluck('id')->toArray(),
        ];

        $response = $this->postJson(
            '/api/v1/contacts',
            $data
        );

        $response->assertStatus(201);

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
            ],
        ]);

        $this->assertDatabaseHas('contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'category_id' => $this->category->id,
        ]);

        $contact = Contact::where(
            'email',
            'yamada@example.com'
        )->first();

        $this->assertNotNull($contact);

        foreach ($this->tags as $tag) {
            $this->assertDatabaseHas('contact_tag', [
                'contact_id' => $contact->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    public function test_必須項目が未入力の場合は422エラーになる(): void
    {
        $data = [
            'first_name' => '',
            'last_name' => '',
            'gender' => '',
            'email' => '',
            'tel' => '',
            'address' => '',
            'category_id' => '',
            'detail' => '',
        ];

        $response = $this->postJson(
            '/api/v1/contacts',
            $data
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    public function test_email形式が不正の場合は422エラーになる(): void
    {
        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'invalid-email',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'category_id' => $this->category->id,
            'detail' => 'お問い合わせ内容',
        ];

        $response = $this->postJson(
            '/api/v1/contacts',
            $data
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }

    public function test_存在しないカテゴリの場合は422エラーになる(): void
    {
        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'category_id' => 99999,
            'detail' => 'お問い合わせ内容',
        ];

        $response = $this->postJson(
            '/api/v1/contacts',
            $data
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'category_id',
        ]);
    }

    public function test_存在しないタグの場合は422エラーになる(): void
    {
        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'category_id' => $this->category->id,
            'detail' => 'お問い合わせ内容',
            'tag_ids' => [99999],
        ];

        $response = $this->postJson(
            '/api/v1/contacts',
            $data
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'tag_ids.0',
        ]);
    }
}
