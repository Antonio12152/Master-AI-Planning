<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIdeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $idea = $this->route('idea');
        
        // ,      
            if (!$idea) {
                return false;
            }

            // Allow users who can view the plan to update only the sort_order (used for drag-reorder).
            $inputKeys = array_keys($this->all());
            $onlySortOrder = count($inputKeys) === 1 && in_array('sort_order', $inputKeys, true);

            if ($onlySortOrder) {
                return $idea->plan->canView($this->user());
            }

            // Otherwise require edit permission
            return $idea->plan->canEdit($this->user());
    }

    public function rules(): array
    {
        return [
            'text' => [
                'sometimes',
                'string',
                'min:1',
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
                'max:10', //  10 
            ],
            'tags.*' => [
                'string',
                'max:50',
            ],
            'sort_order' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}
