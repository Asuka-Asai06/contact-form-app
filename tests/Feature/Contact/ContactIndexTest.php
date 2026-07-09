<?php

namespace Tests\Feature\Contact;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_お問い合わせフォームが表示される()
    {
        Category::create([
            'content' => '商品のお届けについて',
        ]);

        Tag::create([
            'name' => '質問',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee('商品のお届けについて');
        $response->assertSee('質問');
    }

    public function test_サンクスページが表示される()
    {
        $response = $this->get('/contact/thanks');

        $response->assertStatus(200);
        $response->assertViewIs('contact.thanks');
    }
}
