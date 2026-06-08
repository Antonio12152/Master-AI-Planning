<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIdeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $idea = $this->route('idea');
        
        // Проверить, может ли пользователь редактировать эту идею
        return $idea && $idea->plan->canEdit($this->user());
    }

    public function rules(): array
    {
        return [
            'text' => [
                'sometimes',
                'string',
                'min:3',
                'max:500',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],
            'priority' => [
                'sometimes',
                'nullable',
                'integer',
                'in:0,1,2,3', // 0=low, 1=medium, 2=high, 3=critical
            ],
            'status' => [
                'sometimes',
                'nullable',
                'string',
                'in:new,in_progress,completed,rejected',
            ],
            'tags' => [
                'sometimes',
                'nullable',
                'array',
                'max:10', // Максимум 10 тегов
            ],
            'tags.*' => [
                'string',
                'max:50',
            ],
        ];
    }
}
