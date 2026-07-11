<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);

        $this->categoryId = Category::first()->id;
    }

    private function validator(array $data)
    {
        $request = new StoreContactRequest;

        return Validator::make(
            $data,
            $request->rules()
        );
    }

    public function test_必須項目が全て入力されていれば登録できる(): void
    {
        $validator = $this->validator([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'category_id' => $this->categoryId,
            'detail' => '商品の配送について問い合わせます',
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_不正な電話番号はエラーになる(): void
    {
        $validator = $this->validator([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '090-1234-5678',
            'address' => '東京都新宿区',
            'category_id' => $this->categoryId,
            'detail' => '商品の配送について問い合わせます',
        ]);

        $this->assertFalse($validator->passes());

        $this->assertArrayHasKey(
            'tel',
            $validator->errors()->toArray()
        );
    }
}
