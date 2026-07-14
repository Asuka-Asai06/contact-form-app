<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowContactTest extends TestCase
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

    public function test_認証ユーザーは問い合わせ詳細を表示できる(): void
    {
        $user = User::factory()->create();

        $contact = $this->createContact();

        $response = $this
            ->actingAs($user)
            ->get(
                route(
                    'admin.contacts.show',
                    $contact
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'contact',
            fn ($viewContact) => $viewContact->id === $contact->id
        );

        $response->assertSee(
            $this->category->content
        );
    }

    public function test_未認証ユーザーは問い合わせ詳細を表示できない(): void
    {
        $contact = $this->createContact();

        $response = $this->get(
            route(
                'admin.contacts.show',
                $contact
            )
        );

        $response->assertRedirect('/login');
    }

    public function test_存在しない問い合わせの場合404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(
                route(
                    'admin.contacts.show',
                    99999
                )
            );

        $response->assertNotFound();
    }
}
