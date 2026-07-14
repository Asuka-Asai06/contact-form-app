<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteContactTest extends TestCase
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

    public function test_問い合わせを削除できる(): void
    {
        $user = User::factory()->create();

        $contact = $this->createContact();

        $response = $this
            ->actingAs($user)
            ->delete(
                route(
                    'admin.contacts.destroy',
                    $contact
                )
            );

        $response->assertRedirectToRoute(
            'admin.index'
        );

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_未ログインユーザーは問い合わせを削除できない(): void
    {
        $contact = $this->createContact();

        $response = $this->delete(
            route(
                'admin.contacts.destroy',
                $contact
            )
        );

        $response->assertRedirect('/login');

        $this->assertDatabaseHas(
            'contacts',
            [
                'id' => $contact->id,
            ]
        );
    }
}
