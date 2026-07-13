<?php

namespace Tests\Unit;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiIndexRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validator(array $data)
    {
        $request = new IndexContactRequest;

        return Validator::make(
            $data,
            $request->rules()
        );
    }

    public function test_キーワードが255文字以内なら検索できる(): void
    {
        $validator = $this->validator([
            'keyword' => str_repeat('あ', 255),
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_キーワードが256文字以上ならエラーになる(): void
    {
        $validator = $this->validator([
            'keyword' => str_repeat('あ', 256),
        ]);

        $this->assertFalse($validator->passes());

        $this->assertArrayHasKey(
            'keyword',
            $validator->errors()->toArray()
        );
    }

    public function test_性別で検索できる(): void
    {
        $validator = $this->validator([
            'gender' => 1,
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_不正な性別値を拒否する(): void
    {
        $validator = $this->validator([
            'gender' => 99,
        ]);

        $this->assertFalse($validator->passes());

        $this->assertArrayHasKey(
            'gender',
            $validator->errors()->toArray()
        );
    }

    public function test_存在するカテゴリで検索できる(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $validator = $this->validator([
            'category_id' => $category->id,
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_存在しないカテゴリを拒否する(): void
    {
        Category::create([
            'content' => '商品のお届けについて',
        ]);

        $validator = $this->validator([
            'category_id' => 999999,
        ]);

        $this->assertFalse($validator->passes());

        $this->assertArrayHasKey(
            'category_id',
            $validator->errors()->toArray()
        );
    }

    public function test_日付で検索できる(): void
    {
        $validator = $this->validator([
            'date' => now()->format('Y-m-d'),
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_不正な日付を拒否する(): void
    {
        $validator = $this->validator([
            'date' => '2026/99/99',
        ]);

        $this->assertFalse($validator->passes());

        $this->assertArrayHasKey(
            'date',
            $validator->errors()->toArray()
        );
    }

    public function test_per_pageフィルタが有効である(): void
    {
        $validator = $this->validator([
            'per_page' => 50,
        ]);

        $this->assertTrue($validator->passes());

        $this->assertArrayNotHasKey(
            'per_page',
            $validator->errors()->toArray()
        );
    }

    public function test_per_pageが101以上の場合拒否する(): void
    {
        $validator = $this->validator([
            'per_page' => 101,
        ]);

        $this->assertFalse($validator->passes());

        $this->assertArrayHasKey(
            'per_page',
            $validator->errors()->toArray()
        );
    }
}
