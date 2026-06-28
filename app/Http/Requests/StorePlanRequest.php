<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    /**
     * ,      
     */
    public function authorize(): bool
    {
        //    
        // (  middleware 'auth:sanctum')
        return $this->user() !== null;
    }

    /**
     *  
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'not_regex:/^test$/i', //    
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/', // Hex color
            ],
            'icon' => [
                'nullable',
                'string',
                'max:10', //  emoji
            ],
            'is_public' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     *    ( )
     */
    public function messages(): array
    {
        return [
            'name.required' => '  ',
            'name.min' => '    3 ',
            'name.max' => '    255 ',
            'name.not_regex' => '  ,   "test"',
            'description.max' => '    1000 ',
            'color.regex' => '     #RRGGBB (, #FF5733)',
            'icon.max' => '    10 ',
            'is_public.boolean' => 'is_public   true  false',
        ];
    }

    /**
     *    
     */
    protected function prepareForValidation(): void
    {
        //  is_public  boolean
        if ($this->has('is_public')) {
            $this->merge([
                'is_public' => filter_var($this->is_public, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        //      
        $this->merge([
            'name' => trim($this->name),
            'description' => trim($this->description ?? ''),
        ]);
    }

    /**
     *   
     */
    public function validated($key = null, $default = null): mixed
    {
        return parent::validated($key, $default);
    }
}
