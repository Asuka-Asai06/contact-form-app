<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ユーザーがログインできる(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $response = $this->post('/login', $data);

        $response->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
    }

    public function test_パスワードが異なるとログインに失敗する(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ];

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_emailが存在しないとログインに失敗する(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $data = [
            'email' => 'wrong-email@example.com',
            'password' => 'password',
        ];

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_ログアウトができる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/logout');

        $response->assertRedirect('/');

        $this->assertGuest();
    }
}
