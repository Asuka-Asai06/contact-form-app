<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactShowTest extends TestCase
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

    public function test_問い合わせ詳細をjson形式で取得できる(): void
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

    public function 存在しない問い合わせの場合は404エラーを返す(): void
    {
        $response = $this->getJson('/api/v1/contacts/99999');

        $response->assertNotFound();
    }
}
