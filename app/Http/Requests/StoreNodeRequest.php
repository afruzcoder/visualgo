<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id'   => 'nullable|exists:data_nodes,id', // Проверка, что родитель существует
            'name'        => 'required|string|max:255',
            'value'       => 'required|integer|min:0',
            'type'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ];
    }
}
