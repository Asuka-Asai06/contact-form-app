<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->lastName(),
            'last_name' => fake()->firstName(),
            'gender' => fake()->randomElement([1, 2, 3]),
            'email' => fake()->safeEmail(),
            'tel' => fake()->numerify('090########'),
            'address' => fake()->address(),
            'building' => fake()->optional()->secondaryAddress(),
            'detail' => fake()->realText(50),
        ];
    }

    public function withTags(int $count = 2): static
    {
        return $this->afterCreating(function (Contact $contact) use ($count) {

            $tagIds = Tag::query()
                ->limit($count)
                ->pluck('id');

            if ($tagIds->isNotEmpty()) {
                $contact->tags()->attach($tagIds);
            }
        });
    }
}
