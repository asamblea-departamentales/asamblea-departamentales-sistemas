<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactUsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:150'],
            'employees' => ['nullable', 'string', Rule::in(['1-10','11-50','51-200','201-500','501-1000','1000+'])],
            'title' => ['nullable', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'Please provide your first name.',
            'lastname.required' => 'Please provide your last name.',
            'email.required' => 'Please provide your email address.',
            'email.email' => 'Please provide a valid email address.',
            'email.dns' => 'The email domain appears to be invalid.',
            'subject.required' => 'Please provide a subject for your message.',
            'message.required' => 'Your message should not be empty.',
            'message.min' => 'Your message should be at least 10 characters long.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('title') && ! $this->has('subject') && $this->title) {
            $this->merge(['subject' => $this->title]);
        }
    }
}
