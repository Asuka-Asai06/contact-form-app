<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ユーザーが登録できる(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticated();
    }

    public function test_名前が未入力の場合はエラーになる(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_名前が256文字以上の場合はエラーになる(): void
    {
        $data = [
            'name' => str_repeat('あ', 256),
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_email形式が不正な場合はエラーになる(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_emailが256文字以上の場合はエラーになる(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => str_repeat('a', 256).'@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_emailが重複している場合はエラーになる(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_パスワードと確認用パスワードが不一致の場合はエラーになる(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors(['password']);
    }
}
