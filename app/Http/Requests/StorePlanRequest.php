<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    /**
     * Определить, авторизирован ли пользователь для этого запроса
     */
    public function authorize(): bool
    {
        // Пользователь должен быть авторизирован
        // (проверка через middleware 'auth:sanctum')
        return $this->user() !== null;
    }

    /**
     * Правила валидации
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'not_regex:/^test$/i', // Не разрешать тестовые названия
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
                'max:10', // Для emoji
            ],
            'is_public' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Сообщения об ошибках (на русском)
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название плана обязательно',
            'name.min' => 'Название должно быть минимум 3 символа',
            'name.max' => 'Название может быть максимум 255 символов',
            'name.not_regex' => 'Используйте реальное название, а не "test"',
            'description.max' => 'Описание может быть максимум 1000 символов',
            'color.regex' => 'Цвет должен быть в формате #RRGGBB (например, #FF5733)',
            'icon.max' => 'Иконка может быть максимум 10 символов',
            'is_public.boolean' => 'is_public должен быть true или false',
        ];
    }

    /**
     * Подготовить данные для валидации
     */
    protected function prepareForValidation(): void
    {
        // Привести is_public к boolean
        if ($this->has('is_public')) {
            $this->merge([
                'is_public' => filter_var($this->is_public, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Убрать пробелы в начале и конце
        $this->merge([
            'name' => trim($this->name),
            'description' => trim($this->description ?? ''),
        ]);
    }

    /**
     * Получить валидированные данные
     */
    public function validated($key = null, $default = null): mixed
    {
        return parent::validated($key, $default);
    }
}
