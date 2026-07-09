<?php

namespace Tests\Unit;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactSearchRequestTest extends TestCase
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

        $this->assertFalse($validator->fails());
    }

    public function test_キーワードが256文字以上ならエラーになる(): void
    {
        $validator = $this->validator([
            'keyword' => str_repeat('あ', 256),
        ]);

        $this->assertTrue($validator->fails());

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

        $this->assertFalse($validator->fails());
    }

    public function test_不正な性別値を拒否する(): void
    {
        $validator = $this->validator([
            'gender' => 99,
        ]);

        $this->assertTrue($validator->fails());

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

        $this->assertFalse($validator->fails());
    }

    public function test_存在しないカテゴリを拒否する(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $validator = $this->validator([
            'category_id' => 999999,
        ]);

        $this->assertTrue($validator->fails());

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

        $this->assertFalse($validator->fails());
    }

    public function test_不正な日付を拒否する(): void
    {
        $validator = $this->validator([
            'date' => '2026/99/99',
        ]);

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'date',
            $validator->errors()->toArray()
        );
    }
}
