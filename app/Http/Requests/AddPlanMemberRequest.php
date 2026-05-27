<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddPlanMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $plan = $this->route('plan');
        
        // Только админы могут добавлять членов
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
                // Не может быть уже членом этого плана
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
            'user_id.required' => 'User ID обязателен',
            'user_id.exists' => 'Пользователь не найден',
            'user_id.unique' => 'Этот пользователь уже добавлен в план',
            'role.required' => 'Роль обязательна',
            'role.in' => 'Роль должна быть: admin, editor или viewer',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Убедиться, что user_id - это число
        if ($this->has('user_id')) {
            $this->merge([
                'user_id' => (int) $this->user_id,
            ]);
        }
    }
}
