<?php

namespace Tests\Feature\Contact;

use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);

        $this->category = Category::firstWhere(
            'content',
            '商品のお届けについて'
        );
    }

    public function test_お問い合わせフォームが表示される(): void
    {

        Tag::create([
            'name' => '質問',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee('商品のお届けについて');
        $response->assertSee('質問');
    }

    public function test_問い合わせ確認ページを表示できる(): void
    {

        $data = [
            'category_id' => $this->category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => '1',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都大田区',
            'building' => 'サンプルビル101',
            'detail' => '商品の配送について質問があります',
        ];

        $response = $this->post(
            route('contacts.confirm'),
            $data
        );

        $response->assertOk();

        $response->assertViewIs('contact.confirm');
        $contactInformation = [
            '山田',
            '太郎',
            '男性',
            'yamada@example.com',
            '09012345678',
            '東京都大田区',
            'サンプルビル101',
            '商品の配送について質問があります',
            '商品のお届けについて',
        ];

        foreach ($contactInformation as $text) {
            $response->assertSee($text);
        }
    }

    public function test_必須項目不足の場合は確認画面へ進めない(): void
    {
        $response = $this->post(
            route('contacts.confirm'),
            []
        );

        $response->assertRedirect();

        $response->assertSessionHasErrors([
            'category_id',
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'detail',
        ]);
    }

    public function test_サンクスページが表示される(): void
    {
        $response = $this->get('/contact/thanks');

        $response->assertOk();
        $response->assertViewIs('contact.thanks');
    }
}
