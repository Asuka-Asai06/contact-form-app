<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validator(array $data, ?Tag $tag = null)
    {
        $request = new UpdateTagRequest;

        $request->tag = $tag;

        return Validator::make(
            $data,
            $request->rules()
        );
    }

    public function test_タグ名が50文字の場合はバリデーションを通過する(): void
    {
        $tag = Tag::factory()->create();

        $validator = $this->validator([
            'name' => str_repeat('あ', 50),
        ], $tag);

        $this->assertTrue($validator->passes());
    }

    public function test_タグ名が未入力の場合はバリデーションエラーになる(): void
    {
        $tag = Tag::factory()->create();

        $validator = $this->validator([
            'name' => '',
        ], $tag);

        $this->assertFalse($validator->passes());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    public function test_タグ名が51文字以上の場合はバリデーションエラーになる(): void
    {
        $tag = Tag::factory()->create();

        $validator = $this->validator([
            'name' => str_repeat('あ', 51),
        ], $tag);

        $this->assertFalse($validator->passes());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    public function test_他のタグ名と重複している場合はバリデーションエラーになる(): void
    {
        $currentTag = Tag::factory()->create([
            'name' => 'PHP',
        ]);

        Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        $validator = $this->validator([
            'name' => 'Laravel',
        ], $currentTag);

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('name')
        );
    }

    public function test_自身のタグ名と同じ場合はバリデーションを通過する(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        $validator = $this->validator([
            'name' => 'Laravel',
        ], $tag);

        $this->assertTrue($validator->passes());
    }
}
