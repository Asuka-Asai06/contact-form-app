<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Contact;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactDeleteTest extends TestCase
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

    public function test_問い合わせを削除できる(): void
    {
        $contact = Contact::factory()
            ->withTags()
            ->create([
                'category_id' => $this->category->id,
            ]);

        $response = $this->deleteJson(
            "/api/v1/contacts/{$contact->id}"
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_存在しない問い合わせの場合は404エラーになる(): void
    {
        $response = $this->deleteJson(
            '/api/v1/contacts/9999'
        );

        $response->assertStatus(404);

    }
}
