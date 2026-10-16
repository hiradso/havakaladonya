<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessage extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Persian/Arabic digits typed on a phone keyboard become 0-9 before validation. */
    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('phone'))) {
            return;
        }

        $phone = strtr(trim($this->input('phone')), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $this->merge(['phone' => preg_replace('/[\s\-()]/', '', $phone)]);
    }

    public function rules(): array
    {
        return [
            'website' => ['prohibited'], // honeypot
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'message' => ['required', 'string', 'min:5', 'max:3000'],
        ];
    }

    public function attributes(): array
    {
        return app()->getLocale() === 'fa'
            ? ['name' => 'نام', 'phone' => 'شماره تماس', 'email' => 'ایمیل', 'message' => 'پیام']
            : [];
    }

    public function messages(): array
    {
        return app()->getLocale() === 'fa'
            ? ['phone.regex' => 'شماره تماس معتبر نیست. فقط عدد وارد کنید، مثلاً ۰۹۱۲۱۲۳۴۵۶۷.']
            : ['phone.regex' => 'Please enter a valid phone number (digits only).'];
    }
}
