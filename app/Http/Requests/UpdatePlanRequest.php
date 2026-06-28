<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $plan = $this->route('plan');
        
        // ,     
        return $plan && $plan->canEdit($this->user());
    }

    public function rules(): array
    {
        $planId = $this->route('plan')->id;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:10',
            ],
            'status' => [
                'nullable',
                'string',
                'in:active,inactive,archived',
            ],
            'is_public' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '  ',
            'name.min' => '    3 ',
            'name.max' => '    255 ',
            'description.max' => '    1000 ',
            'color.regex' => '     #RRGGBB',
            'status.in' => '  : active, inactive  archived',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_public')) {
            $this->merge([
                'is_public' => filter_var($this->is_public, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        $this->merge([
            'name' => trim($this->name ?? ''),
            'description' => trim($this->description ?? ''),
        ]);
    }
}
