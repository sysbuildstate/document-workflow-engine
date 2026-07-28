<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'state' => 'required|string|in:Draft,Pending Legal Review,Manager Approved,Executed',
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ];
    }
}
