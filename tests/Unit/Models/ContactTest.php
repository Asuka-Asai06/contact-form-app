<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'content' => '商品のお届けについて',
        ]);
    }

    public function test_カテゴリから複数のお問い合わせを取得できる()
    {
        Contact::factory()
            ->count(3)
            ->create([
                'category_id' => $this->category->id,
            ]);

        $contacts = $this->category->contacts;

        $this->assertCount(3, $contacts);

        foreach ($contacts as $contact) {
            $this->assertEquals(
                $this->category->id,
                $contact->category_id
            );
        }
    }

    public function test_お問い合わせがカテゴリに属している()
    {
        $contact = Contact::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $this->assertEquals(
            $this->category->id,
            $contact->category->id
        );

        $this->assertEquals(
            $this->category->content,
            $contact->category->content
        );
    }

    public function test_お問い合わせに複数のタグを関連付けられる()
    {
        Tag::factory()->count(3)->create();

        $contact = Contact::factory()
            ->withTags(3)
            ->create([
                'category_id' => $this->category->id,
            ]);

        $this->assertCount(3, $contact->fresh()->tags);
    }

    public function test_タグから複数のお問い合わせを取得できる()
    {
        $tag = Tag::factory()->create();

        $contacts = Contact::factory()
            ->count(3)
            ->create([
                'category_id' => $this->category->id,
            ]);

        $contacts->each(
            fn ($contact) => $contact->tags()->attach($tag)
        );

        $this->assertCount(
            3,
            $tag->fresh()->contacts
        );
    }
}
