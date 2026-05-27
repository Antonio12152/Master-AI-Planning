<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIdeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $group = $this->route('group');
        
        // Проверить, может ли пользователь создавать идеи в этой группе
        return $group && $group->plan->canEdit($this->user());
    }

    public function rules(): array
    {
        return [
            'text' => [
                'required',
                'string',
                'min:3',
                'max:500',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'priority' => [
                'nullable',
                'integer',
                'in:0,1,2,3', // 0=low, 1=medium, 2=high, 3=critical
            ],
            'status' => [
                'nullable',
                'string',
                'in:new,in_progress,completed,rejected',
            ],
            'tags' => [
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

    public function messages(): array
    {
        return [
            'text.required' => 'Текст идеи обязателен',
            'text.min' => 'Текст должен быть минимум 3 символа',
            'text.max' => 'Текст может быть максимум 500 символов',
            'description.max' => 'Описание может быть максимум 2000 символов',
            'priority.in' => 'Приоритет должен быть: 0 (low), 1 (medium), 2 (high), 3 (critical)',
            'status.in' => 'Статус должен быть: new, in_progress, completed или rejected',
            'tags.max' => 'Можно добавить максимум 10 тегов',
            'tags.*.max' => 'Каждый тег может быть максимум 50 символов',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Привести приоритет к числу
        if ($this->has('priority')) {
            $this->merge([
                'priority' => (int) $this->priority,
            ]);
        }

        // Привести теги в массив и удалить дубликаты
        if ($this->has('tags')) {
            $tags = is_array($this->tags) ? $this->tags : explode(',', $this->tags);
            $tags = array_unique(array_map('trim', $tags));
            
            $this->merge([
                'tags' => array_values($tags),
            ]);
        }

        // Убрать пробелы
        $this->merge([
            'text' => trim($this->text),
            'description' => trim($this->description ?? ''),
        ]);
    }
}
