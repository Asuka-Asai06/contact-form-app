<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiStoreRequestTest extends TestCase
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

    private function validData(): array
    {
        return [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'category_id' => $this->categoryId,
            'detail' => '商品の配送について問い合わせます',
        ];
    }

    public function test_必須項目が全て入力されていれば登録できる(): void
    {
        $validator = $this->validator(
            $this->validData()
        );

        $this->assertTrue(
            $validator->passes()
        );
    }

    public function test_タグ入力を受け付ける(): void
    {
        $tags = Tag::factory()
            ->count(2)
            ->create();

        $data = $this->validData();

        $data['tag_ids'] = $tags
            ->pluck('id')
            ->toArray();

        $validator = $this->validator($data);

        $this->assertTrue(
            $validator->passes()
        );
    }

    public function test_存在しないタグ_idは拒否する(): void
    {
        $data = $this->validData();

        $data['tag_ids'] = [99999];

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'tag_ids.0',
            $validator->errors()->toArray()
        );
    }

    public function test_不正なメール形式は拒否する(): void
    {
        $data = $this->validData();

        $data['email'] = 'invalid-email';

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'email',
            $validator->errors()->toArray()
        );
    }

    public function test_メール未入力は拒否する(): void
    {
        $data = $this->validData();

        $data['email'] = null;

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'email',
            $validator->errors()->toArray()
        );
    }

    public function test_不正な性別値は拒否する(): void
    {
        $data = $this->validData();

        $data['gender'] = 99;

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'gender',
            $validator->errors()->toArray()
        );
    }

    public function test_不正な電話番号形式は拒否する(): void
    {
        $data = $this->validData();

        $data['tel'] = '090-1234-5678';

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'tel',
            $validator->errors()->toArray()
        );
    }

    public function test_存在しないカテゴリーidは拒否する(): void
    {
        $data = $this->validData();

        $data['category_id'] = 99999;

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'category_id',
            $validator->errors()->toArray()
        );
    }

    public function test_問い合わせ内容が120文字を超える場合は拒否する(): void
    {
        $data = $this->validData();

        $data['detail'] = str_repeat('あ', 121);

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'detail',
            $validator->errors()->toArray()
        );
    }
}
