<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfValidationRule;

class StoreCollaboratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string'],
            'email' => ['required','email'],
            'cpf' => ['required','string', new CpfValidationRule()],
            'city' => ['required','string'],
            'state' => ['required','string','size:2'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/\D+/', '', (string) $this->input('cpf')),
            ]);
        }
    }
}
