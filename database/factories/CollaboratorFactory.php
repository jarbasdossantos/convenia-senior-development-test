<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollaboratorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'cpf' => $this->generateValidCpf(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
        ];
    }

    private function generateValidCpf(): string
    {
        $digits = [];

        for ($i = 0; $i < 9; $i++) {
            $digits[] = fake()->numberBetween(0, 9);
        }

        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += $digits[$i] * (10 - $i);
        }

        $firstDigit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);
        $digits[] = $firstDigit;

        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += $digits[$i] * (11 - $i);
        }

        $secondDigit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);
        $digits[] = $secondDigit;

        return sprintf(
            '%s%s%s.%s%s%s.%s%s%s-%s%s',
            $digits[0],
            $digits[1],
            $digits[2],
            $digits[3],
            $digits[4],
            $digits[5],
            $digits[6],
            $digits[7],
            $digits[8],
            $digits[9],
            $digits[10]
        );
    }
}
