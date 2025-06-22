<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locales = ['en', 'fr', 'es'];
        $tags = ['mobile', 'web', 'desktop'];

        return [
            'key' => fn() => 'key_' . \Str::random(10),
            'locale' => $this->faker->randomElement($locales),
            'value' => $this->faker->sentence,
            'tag' => $this->faker->randomElement($tags),
        ];
    }
}
