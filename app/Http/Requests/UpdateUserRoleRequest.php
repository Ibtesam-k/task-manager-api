<?php

namespace App\Http\Requests;

use App\Enums\ProjectRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRoleRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role'=>['required',new Enum(ProjectRole::class)],
        ];
    }
}
