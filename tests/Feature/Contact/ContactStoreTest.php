<?php

namespace Tests\Feature\Contact;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStoreTest extends TestCase
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

    public function test_問い合わせ送信時に問い合わせとタグが保存される()
    {
        $tag = Tag::factory()->create();

        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
        ]);

        $data = array_merge(
            $contact->toArray(),
            [
                'tags' => [$tag->id],
            ]
        );

        $response = $this->post(
            route('contacts.store'),
            $data
        );

        $response->assertRedirect();

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

        $savedContact = Contact::firstWhere(
            'email',
            $contact->email
        );

        $this->assertNotNull($savedContact);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $savedContact->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_姓が255文字を超える場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'first_name' => str_repeat('あ', 256),
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'first_name',
        ]);
    }

    public function test_名が255文字を超える場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'last_name' => str_repeat('あ', 256),
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'last_name',
        ]);
    }

    public function test_性別が不正な値の場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'gender' => 99,
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'gender',
        ]);
    }

    public function test_性別が未選択の場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'gender' => null,
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'gender',
        ]);
    }

    public function test_emailが255文字を超える場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'email' => str_repeat('a', 256).'@example.com',
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'email',
        ]);
    }

    public function test_email形式が不正な場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'email' => 'invalid-email',
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'email',
        ]);
    }

    public function test_emailが未入力の場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'email' => null,
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'email',
        ]);
    }

    public function test_電話番号の形式が不正な場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'tel' => '090-1234-5678',
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'tel',
        ]);
    }

    public function test_電話番号が未入力の場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'tel' => null,
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'tel',
        ]);
    }

    public function test_住所が255文字を超える場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'address' => str_repeat('あ', 256),
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'address',
        ]);
    }

    public function test_住所が未入力の場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'address' => null,
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'address',
        ]);
    }

    public function test_建物名が255文字を超える場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'building' => str_repeat('あ', 256),
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'building',
        ]);
    }

    public function test_カテゴリが存在しない場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => 9999,
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'category_id',
        ]);
    }

    public function test_問い合わせ内容が120文字を超える場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'detail' => str_repeat('あ', 121),
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'detail',
        ]);
    }

    public function test_問い合わせ内容が未入力の場合はバリデーションエラーになる()
    {
        $contact = Contact::factory()->make([
            'category_id' => $this->category->id,
            'detail' => null,
        ]);

        $response = $this->post(
            route('contacts.confirm'),
            $contact->toArray()
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'detail',
        ]);
    }
}
