<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating Tag model instances.
 *
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Tag>
     */
    protected $model = Tag::class;

    /**
     * Predefined tag names for context tagging.
     *
     * @var array<string>
     */
    private const TAG_NAMES = [
        'mobile',
        'desktop',
        'web',
        'ios',
        'android',
        'api',
        'admin',
        'public',
        'email',
        'sms',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(self::TAG_NAMES),
        ];
    }

    /**
     * Set a specific name for the tag.
     */
    public function name(string $name): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => $name,
        ]);
    }
}
