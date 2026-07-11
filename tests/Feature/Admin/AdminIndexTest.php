<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIndexTest extends TestCase
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

    public function test_認証ユーザーは管理ダッシュボードを表示できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_未認証ユーザーはログイン画面へリダイレクトされる()
    {
        $this->get('/admin')
            ->assertRedirect('/login');
    }

    public function test_問い合わせ詳細を表示できる()
    {
        $user = User::factory()->create();

        $contact = $this->createContact();

        $response = $this
            ->actingAs($user)
            ->get("/admin/contacts/{$contact->id}");

        $response->assertOk();

        $response->assertViewHas(
            'contact',
            fn ($viewContact) => $viewContact->id === $contact->id
        );

        $response->assertSee(
            $this->category->content
        );
    }

    public function test_問い合わせを削除できる()
    {
        $user = User::factory()->create();

        $contact = $this->createContact();

        $response = $this
            ->actingAs($user)
            ->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}
