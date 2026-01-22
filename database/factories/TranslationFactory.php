<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating Translation model instances.
 *
 * @extends Factory<Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Translation>
     */
    protected $model = Translation::class;

    /**
     * Available locales for translation generation.
     *
     * @var array<string>
     */
    private const LOCALES = ['en', 'fr', 'es', 'de', 'it', 'pt', 'nl', 'ru', 'zh', 'ja'];

    /**
     * Common translation key prefixes for realistic data.
     *
     * @var array<string>
     */
    private const KEY_PREFIXES = [
        'common',
        'auth',
        'validation',
        'messages',
        'errors',
        'buttons',
        'labels',
        'placeholders',
        'notifications',
        'pages',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prefix = $this->faker->randomElement(self::KEY_PREFIXES);
        $suffix = $this->faker->unique()->words($this->faker->numberBetween(1, 3), true);

        return [
            'key' => $prefix . '.' . str_replace(' ', '_', strtolower($suffix)),
            'locale' => $this->faker->randomElement(self::LOCALES),
            'value' => $this->faker->sentence($this->faker->numberBetween(3, 15)),
        ];
    }

    /**
     * Set a specific locale for the translation.
     */
    public function locale(string $locale): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => $locale,
        ]);
    }

    /**
     * Set a specific key for the translation.
     */
    public function key(string $key): static
    {
        return $this->state(fn(array $attributes) => [
            'key' => $key,
        ]);
    }
}
