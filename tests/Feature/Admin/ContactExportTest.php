<?php

namespace Tests\Feature\Contact;

use App\Exports\ContactsExport;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);

        $this->category = Category::first();
    }

    public function test_ログイン済み管理者は_csvをダウンロードできる(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertStatus(200);

        Excel::assertDownloaded(
            'contacts.csv',
            function (ContactsExport $export) {
                return true;
            }
        );
    }

    public function test_フィルター条件付きで_csvを出力できる(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export?keyword=山田');

        $response->assertStatus(200);

        Excel::assertDownloaded(
            'contacts.csv',
            function (ContactsExport $export) {

                return $export->getFilters()['keyword'] === '山田';

            }
        );
    }
}
