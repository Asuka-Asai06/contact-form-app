<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactUpdateTest extends TestCase
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

    public function test_問い合わせを更新できる(): void
    {
        $contact = Contact::factory()
            ->create([
                'category_id' => $this->category->id,
            ]);

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
        ];

        $response = $this->putJson(
            "/api/v1/contacts/{$contact->id}",
            $data
        );

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
            ],
        ]);

        $this->assertDatabaseHas('contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'category_id' => $this->category->id,
        ]);

    }

    public function test_存在しない問い合わせを更新すると404エラーになる(): void
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
        ];

        $response = $this->putJson(
            '/api/v1/contacts/9999',
            $data
        );

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'お問い合わせが見つかりませんでした',
        ]);
    }

    public function test_バリデーションエラー時は422エラーになる(): void
    {
        $contact = Contact::factory()
            ->create([
                'category_id' => $this->category->id,
                'first_name' => '山田',
                'email' => 'yamada@example.com',
            ]);

        $data = [
            'first_name' => '',
            'last_name' => '',
            'gender' => 99,
            'email' => '',
            'tel' => '',
            'address' => '',
            'building' => '',
            'category_id' => '',
            'detail' => '',
        ];

        $response = $this->putJson(
            "/api/v1/contacts/{$contact->id}",
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

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '山田',
            'email' => 'yamada@example.com',
        ]);

    }
}
