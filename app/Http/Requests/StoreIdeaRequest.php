<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIdeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $group = $this->route('group');

        return $group && $group->plan->canView($this->user());
    }

    public function rules(): array
    {
        return [
            'text' => [
                'required',
                'string',
                'min:1',
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
                'in:0,1,2,3',
            ],
            'status' => [
                'nullable',
                'string',
                'in:new,in_progress,completed,rejected',
            ],
            'tags' => [
                'nullable',
                'array',
                'max:10',
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
            'text.required' => 'Idea text is required',
            'text.min' => 'Text must be at least 1 character',
            'text.max' => 'Text may be at most 500 characters',
            'description.max' => 'Description may be at most 2000 characters',
            'priority.in' => 'Priority must be one of: 0 (low), 1 (medium), 2 (high), 3 (critical)',
            'status.in' => 'Status must be one of: new, in_progress, completed or rejected',
            'tags.max' => 'You can add up to 10 tags',
            'tags.*.max' => 'Each tag may be at most 50 characters',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('priority')) {
            $this->merge([
                'priority' => (int) $this->priority,
            ]);
        }

        if ($this->has('tags')) {
            $tags = is_array($this->tags) ? $this->tags : explode(',', $this->tags);
            $tags = array_unique(array_map('trim', $tags));

            $this->merge([
                'tags' => array_values($tags),
            ]);
        }

        $this->merge([
            'text' => trim($this->text),
            'description' => trim($this->description ?? ''),
        ]);
    }
}