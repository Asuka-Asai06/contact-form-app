<?php

namespace Tests\Feature\Admin;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーはタグ編集画面を表示できる()
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $this->actingAs($user)
            ->get(
                route('admin.tags.edit', $tag)
            )
            ->assertOk()
            ->assertViewHas('tag');
    }

    public function test_認証ユーザーはタグを作成できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(
                route('admin.tags.store'),
                [
                    'name' => 'Laravel',
                ]
            );

        $response->assertRedirect(
            route('admin.index')
        );

        $this->assertDatabaseHas('tags', [
            'name' => 'Laravel',
        ]);
    }

    public function test_認証ユーザーはタグを更新できる()
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => '更新前',
        ]);

        $response = $this->actingAs($user)
            ->put(
                route('admin.tags.update', $tag),
                [
                    'name' => '更新後',
                ]
            );

        $response->assertRedirect(
            route('admin.index')
        );

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '更新後',
        ]);
    }

    public function test_認証ユーザーはタグを削除できる()
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)
            ->delete(
                route('admin.tags.destroy', $tag)
            );

        $response->assertRedirect(
            route('admin.index')
        );

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_未認証ユーザーはタグ編集画面へアクセスできない()
    {
        $tag = Tag::factory()->create();

        $this->get(
            route('admin.tags.edit', $tag)
        )->assertRedirect('/login');
    }

    public function test_未認証ユーザーはタグを作成できない()
    {
        $this->post(
            route('admin.tags.store'),
            [
                'name' => 'Laravel',
            ]
        )->assertRedirect('/login');

        $this->assertDatabaseMissing('tags', [
            'name' => 'Laravel',
        ]);
    }

    public function test_未認証ユーザーはタグを更新できない()
    {
        $tag = Tag::factory()->create([
            'name' => '更新前',
        ]);

        $this->put(
            route('admin.tags.update', $tag),
            [
                'name' => '更新後',
            ]
        )->assertRedirect('/login');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '更新前',
        ]);
    }

    public function test_未認証ユーザーはタグを削除できない()
    {
        $tag = Tag::factory()->create();

        $this->delete(
            route('admin.tags.destroy', $tag)
        )->assertRedirect('/login');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
        ]);
    }
}