<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validator(array $data)
    {
        $request = new StoreTagRequest;

        return Validator::make(
            $data,
            $request->rules()
        );
    }

    public function test_タグ名が50文字ならバリデーションを通過する(): void
    {
        $validator = $this->validator([
            'name' => str_repeat('あ', 50),
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_タグ名が空の場合はバリデーションエラーになる(): void
    {
        $validator = $this->validator([
            'name' => '',
        ]);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_タグ名が51文字以上の場合はバリデーションエラーになる(): void
    {
        $validator = $this->validator([
            'name' => str_repeat('あ', 51),
        ]);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_タグ名が重複している場合はバリデーションエラーになる(): void
    {
        Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        $validator = $this->validator([
            'name' => 'Laravel',
        ]);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
