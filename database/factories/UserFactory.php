<?php

namespace Database\Factories;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\User\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $middleInitial = strtoupper(fake()->randomLetter());

        return [
            'name' => "{$firstName} {$lastName}",
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),

            // Personal information
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_initial' => fake()->boolean(30) ? $middleInitial : null,
            'date_of_birth' => fake()->date('Y-m-d', '-18 years'),
            'gender' => fake()->randomElement(['Male', 'Female', 'Non-binary', 'Prefer not to say']),

            // Contact information
            'phone' => fake()->phoneNumber(),
            'secondary_phone' => fake()->boolean(30) ? fake()->phoneNumber() : null,
            'secondary_email' => fake()->boolean(20) ? fake()->unique()->safeEmail() : null,

            // Address
            'street_address' => fake()->streetAddress(),
            'unit' => fake()->boolean(20) ? fake()->randomNumber(3) : null,
            'city' => fake()->city(),
            'province_state' => fake()->randomElement(['ON', 'QC', 'BC', 'AB', 'MB', 'SK', 'NS', 'NB', 'NL', 'PE']),
            'postal_zip_code' => strtoupper(fake()->bothify('?#? #?#')),

            // Emergency contact
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),

            // Privacy
            'show_contact_info' => fake()->boolean(40),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
