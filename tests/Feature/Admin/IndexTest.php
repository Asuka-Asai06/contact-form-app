<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
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

    public function test_認証ユーザーは管理ダッシュボードを表示できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_未認証ユーザーはログイン画面へリダイレクトされる(): void
    {
        $this->get('/admin')
            ->assertRedirect('/login');
    }
}
