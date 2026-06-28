<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddPlanMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $plan = $this->route('plan');
        
        //     
        return $plan && $plan->canManageMembers($this->user());
    }

    public function rules(): array
    {
        $planId = $this->route('plan')->id;

        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                //       
                Rule::unique('plan_members')->where(function ($query) use ($planId) {
                    $query->where('plan_id', $planId);
                }),
            ],
            'role' => [
                'required',
                'string',
                Rule::in('admin', 'editor', 'viewer'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID ',
            'user_id.exists' => '  ',
            'user_id.unique' => '     ',
            'role.required' => ' ',
            'role.in' => '  : admin, editor  viewer',
        ];
    }

    protected function prepareForValidation(): void
    {
        // ,  user_id -  
        if ($this->has('user_id')) {
            $this->merge([
                'user_id' => (int) $this->user_id,
            ]);
        }
    }
}
