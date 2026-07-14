<?php

namespace Tests\Unit\Exports;

use App\Exports\ContactsExport;
use App\Models\Category;
use App\Models\Contact;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactsExportTest extends TestCase
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

    public function test_問い合わせデータをexcelで文字列として扱えるcsv形式に変換できる(): void
    {
        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'category_id' => $this->category->id,
        ]);

        $export = new ContactsExport([]);

        $collection = $export->collection();

        $this->assertCount(
            1,
            $collection
        );

        $this->assertEquals(
            '山田太郎',
            $collection->first()[1]
        );

        $this->assertEquals(
            '="yamada@example.com"',
            $collection->first()[3]
        );
    }

    public function test_フィルター条件を保持できる(): void
    {
        $filters = [
            'keyword' => '山田',
            'category_id' => $this->category->id,
        ];

        $export = new ContactsExport($filters);

        $this->assertEquals(
            $filters,
            $export->getFilters()
        );
    }

    public function test_cs_v設定でutf8_bomが有効になっている(): void
    {
        $export = new ContactsExport([]);

        $settings = $export->getCsvSettings();

        $this->assertTrue(
            $settings['use_bom']
        );
    }

    public function test_ヘッダーが正しく設定されている(): void
    {
        $export = new ContactsExport([]);

        $this->assertSame(
            [
                'ID',
                '氏名',
                '性別',
                'メール',
                '電話',
                '住所',
                '建物',
                'カテゴリ',
                '内容',
                '作成日時',
            ],
            $export->headings()
        );
    }

    public function test_条件指定なしの場合は新着順でcsv出力される(): void
    {
        Contact::factory()->create([
            'first_name' => '新しい',
            'last_name' => '問い合わせ',
            'category_id' => $this->category->id,
            'created_at' => now(),
        ]);

        Contact::factory()->create([
            'first_name' => '古い',
            'last_name' => '問い合わせ',
            'category_id' => $this->category->id,
            'created_at' => now()->subDay(),
        ]);

        $export = new ContactsExport([]);

        $contacts = $export->collection();

        $this->assertEquals(
            '新しい問い合わせ',
            $contacts->first()[1]
        );

        $this->assertEquals(
            '古い問い合わせ',
            $contacts->last()[1]
        );
    }
}
