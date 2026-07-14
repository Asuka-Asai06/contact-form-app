<?php

namespace Tests\Feature\Contact;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
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

    public function test_問い合わせ送信時に問い合わせとタグが保存される(): void
    {
        $tag = Tag::factory()->create();

        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
        ]);

        $data = [
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'building' => $contact->building,
            'category_id' => $contact->category_id,
            'detail' => $contact->detail,
            'tag_ids' => [$tag->id],
        ];

        $response = $this->post(
            route('contacts.store'),
            $data
        );

        $response->assertRedirect(
            route('contacts.thanks')
        );

        $this->assertDatabaseHas('contacts', [
            'category_id' => $contact->category_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'building' => $contact->building,
            'detail' => $contact->detail,
        ]);

        $savedContact = Contact::first();

        $this->assertNotNull($savedContact);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $savedContact->id,
            'tag_id' => $tag->id,
        ]);
        $this->assertTrue(
            $savedContact->tags->contains('id', $tag->id)
        );
    }

    public function test_入力値が不正な場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'first_name' => str_repeat('あ', 256),
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertRedirect();

        $response->assertSessionHasErrors([
            'first_name',
        ]);
    }
}
