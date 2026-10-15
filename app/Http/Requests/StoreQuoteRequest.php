<?php

namespace App\Http\Requests;

use App\Models\QuoteRequest;
use App\Support\QuoteForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Persian/Arabic digits typed on a phone keyboard become 0-9 before validation. */
    protected function prepareForValidation(): void
    {
        $map = [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ];

        $clean = [];
        foreach (['phone', 'weight_kg', 'volume_cbm'] as $field) {
            if (is_string($this->input($field))) {
                $clean[$field] = strtr(trim($this->input($field)), $map);
            }
        }
        if (isset($clean['phone'])) {
            $clean['phone'] = preg_replace('/[\s\-()]/', '', $clean['phone']);
        }

        $this->merge($clean);
    }

    public function rules(): array
    {
        $form = QuoteForm::config();
        $rule = fn (string $key, array $rules): array => [
            $form[$key]['required'] ? 'required' : 'nullable',
            ...$rules,
        ];

        $rules = [
            // Honeypot: real visitors never see or fill this field.
            'website' => ['prohibited'],

            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'transport_mode' => ['required', Rule::in(array_keys(QuoteRequest::TRANSPORT_MODES))],
            'origin' => ['required', 'string', 'max:150'],
            'destination' => ['required', 'string', 'max:150'],
        ];

        $optional = [
            'company' => ['string', 'max:150'],
            'email' => ['email', 'max:150'],
            'cargo_type' => ['string', 'max:200'],
            'weight_kg' => ['numeric', 'min:0', 'max:100000000'],
            'volume_cbm' => ['numeric', 'min:0', 'max:1000000'],
            'load_type' => [Rule::in(array_keys(QuoteRequest::LOAD_TYPES))],
            'description' => ['string', 'max:3000'],
            'attachment' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
        ];

        foreach ($optional as $key => $fieldRules) {
            if ($form[$key]['enabled']) {
                $rules[$key] = $rule($key, $fieldRules);
            }
        }

        if ($form['services']['enabled']) {
            $rules['needs_clearance'] = ['boolean'];
            $rules['needs_insurance'] = ['boolean'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        $fa = [
            'name' => 'نام', 'phone' => 'شماره تماس', 'transport_mode' => 'روش حمل',
            'origin' => 'مبدأ', 'destination' => 'مقصد', 'company' => 'نام شرکت', 'email' => 'ایمیل',
            'cargo_type' => 'نوع کالا', 'weight_kg' => 'وزن', 'volume_cbm' => 'حجم', 'load_type' => 'نوع بار',
            'description' => 'توضیحات', 'attachment' => 'فایل پیوست',
        ];

        return app()->getLocale() === 'fa' ? $fa : [];
    }

    public function messages(): array
    {
        return app()->getLocale() === 'fa'
            ? ['phone.regex' => 'شماره تماس معتبر نیست. فقط عدد وارد کنید، مثلاً ۰۹۱۲۱۲۳۴۵۶۷.']
            : ['phone.regex' => 'Please enter a valid phone number (digits only).'];
    }
}
