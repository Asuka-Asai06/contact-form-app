<?php

namespace Tests\Feature\Admin;

use App\Exports\ContactsExport;
use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ContactExportTest extends TestCase
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

    public function test_未ログインユーザーはcsv出力できない(): void
    {
        $response = $this->get(
            '/contacts/export'
        );

        $response->assertRedirect(
            '/login'
        );
    }

    public function test_ログイン済みユーザーはcsv出力できる(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertStatus(200);

        Excel::assertDownloaded('contacts.csv');
    }

    public function test_フィルター条件付きでcsv出力できる(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(
                '/contacts/export?keyword=山田'
            );

        $response->assertStatus(200);

        Excel::assertDownloaded(
            'contacts.csv',
            function (ContactsExport $export) {

                return $export->getFilters()['keyword']
                    === '山田';

            }
        );
    }

    public function test_カテゴリを指定してcsv出力できる(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(
                "/contacts/export?category_id={$this->category->id}"
            );

        $response->assertStatus(200);

        Excel::assertDownloaded(
            'contacts.csv',
            function (ContactsExport $export) {

                return $export->getFilters()['category_id']
                    === (string) $this->category->id;

            }
        );
    }

    public function test_未指定時は新着順でcsv出力できる(): void
    {
        $new = Contact::factory()->create([
            'first_name' => '新しい',
            'category_id' => $this->category->id,
            'created_at' => now(),
        ]);

        $old = Contact::factory()->create([
            'first_name' => '古い',
            'category_id' => $this->category->id,
            'created_at' => now()->subDay(),
        ]);

        $export = new ContactsExport([]);

        $contacts = $export->collection();

        $this->assertSame(
            [$new->id, $old->id],
            $contacts->pluck(0)->toArray()
        );
    }
}
