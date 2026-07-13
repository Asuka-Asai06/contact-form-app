<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = Category::pluck('id');

        Contact::factory()
            ->count(20)
            ->state(fn () => [
                'category_id' => $categoryIds->random(),
            ])
            ->create()
            ->each(function (Contact $contact) {

                $tagIds = Tag::query()
                    ->inRandomOrder()
                    ->limit(rand(1, 3))
                    ->pluck('id');

                $contact->tags()->attach($tagIds);
            });
    }
}
