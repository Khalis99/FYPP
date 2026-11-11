<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Basic account details. Used directly for admin accounts and inherited by TeacherRequest.
 */
class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->editedUserId())],
            'staff_id' => ['required', 'digits:6', Rule::unique('users')->ignore($this->editedUserId())],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => __('The phone number may only contain digits, spaces, + and -.'),
            'email.unique' => __('Another account already uses this email address.'),
            'staff_id.digits' => __('The staff ID must be exactly 6 digits, for example 260005.'),
            'staff_id.unique' => __('Another account already uses this staff ID.'),
        ];
    }

    /**
     * Account fields ready to save; the password is only included when given.
     *
     * @return array<string, mixed>
     */
    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['staff_id' => __('staff ID')];
    }

    public function accountData(): array
    {
        return array_filter(
            $this->safe()->only(['name', 'email', 'staff_id', 'phone', 'password']),
            fn (mixed $value, string $key): bool => $key !== 'password' || filled($value),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    protected function editedUserId(): ?int
    {
        return $this->route('user')?->id;
    }
}
