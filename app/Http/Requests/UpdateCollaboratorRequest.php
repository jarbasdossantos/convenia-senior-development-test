<?php

namespace App\Http\Requests;

use App\Rules\CpfValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCollaboratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','required','string'],
            'email' => ['sometimes','required','email'],
            'cpf' => ['sometimes','required', new CpfValidationRule],
            'city' => ['sometimes','required','string'],
            'state' => ['sometimes','required','string','size:2'],
        ];
    }
}
