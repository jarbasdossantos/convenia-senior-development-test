<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfValidationRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/[^0-9]/', '', $value);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            $fail('O campo :attribute não é um CPF válido.');
            return;
        }

        for ($i = 9; $i < 11; $i++) {
            $sum = 0;

            for ($j = 0; $j < $i; $j++) {
                $sum += $cpf[$j] * (($i + 1) - $j);
            }

            $digit = $sum % 11;
            $digit = ($digit < 2) ? 0 : 11 - $digit;

            if ($cpf[$i] != $digit) {
                $fail('O campo :attribute não é um CPF válido.');
                return;
            }
        }
    }
}
