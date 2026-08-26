<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'=>['sometimes','string','max:255'],
            'description'=>['sometimes','nullable','string']
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->hasAny(['title', 'description'])) {
                $validator->errors()->add(
                    'project',
                    'At least one field must be provided.'
                );
            }
        });
    }


}
