<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiStoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);

        $this->category = Category::first();
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
            'category_id' => $this->category->id,
            'detail' => '商品の配送について問い合わせます',
        ];
    }

    public function test_必須項目が全て入力されていればバリデーションを通過する(): void
    {
        $validator = $this->validator(
            $this->validData()
        );

        $this->assertTrue(
            $validator->passes()
        );
    }

    public function test_姓が未入力の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData();

        $data['first_name'] = '';

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );
        $this->assertArrayHasKey(
            'first_name',
            $validator->errors()->toArray()
        );
    }

    public function test_名前が未入力の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData();

        $data['last_name'] = '';

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );
        $this->assertArrayHasKey(
            'last_name',
            $validator->errors()->toArray()
        );
    }

    public function test_不正な性別値の場合はバリデーションエラーになる(): void
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

    public function test_不正なemail形式の場合はバリデーションエラーになる(): void
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

    public function test_emailが未入力の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData();

        $data['email'] = '';

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'email',
            $validator->errors()->toArray()
        );
    }

    public function test_不正な電話番号形式の場合はバリデーションエラーになる(): void
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

    public function test_電話番号が未入力の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData();

        $data['tel'] = '';

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'tel',
            $validator->errors()->toArray()
        );
    }

    public function test_住所が未入力の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData();

        $data['address'] = '';

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );
        $this->assertArrayHasKey(
            'address',
            $validator->errors()->toArray()
        );
    }

    public function test_建物名が256文字以上の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData();

        $data['building'] = str_repeat('あ', 256);

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'building',
            $validator->errors()->toArray()
        );
    }

    public function test_存在しないカテゴリの場合はバリデーションエラーになる(): void
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

    public function test_問い合わせ内容が121文字以上の場合はバリデーションエラーになる(): void
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

    public function test_問い合わせ内容が未入力の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData();

        $data['detail'] = '';

        $validator = $this->validator($data);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertArrayHasKey(
            'detail',
            $validator->errors()->toArray()
        );
    }

    public function test_存在するタグの場合はバリデーションを通過する(): void
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

    public function test_存在しないタグの場合はバリデーションエラーになる(): void
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
}
