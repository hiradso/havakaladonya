<?php

// Persian validation messages (only the rules this site uses; others fall back to English).
return [
    'boolean' => 'مقدار :attribute معتبر نیست.',
    'email' => ':attribute باید یک ایمیل معتبر باشد.',
    'file' => ':attribute باید یک فایل باشد.',
    'in' => ':attribute انتخاب‌شده معتبر نیست.',
    'max' => [
        'numeric' => ':attribute نباید بیشتر از :max باشد.',
        'file' => 'حجم :attribute نباید بیشتر از :max کیلوبایت باشد.',
        'string' => ':attribute نباید بیشتر از :max حرف باشد.',
    ],
    'mimes' => 'نوع فایل :attribute باید یکی از این‌ها باشد: :values.',
    'min' => [
        'numeric' => ':attribute نباید کمتر از :min باشد.',
        'string' => ':attribute نباید کمتر از :min حرف باشد.',
    ],
    'numeric' => ':attribute باید عدد باشد.',
    'prohibited' => 'درخواست نامعتبر است.',
    'regex' => 'قالب :attribute معتبر نیست.',
    'required' => 'وارد کردن :attribute الزامی است.',
    'string' => ':attribute باید متن باشد.',
    'uploaded' => 'بارگذاری :attribute انجام نشد.',

    'password' => [
        'letters' => ':attribute باید حداقل یک حرف داشته باشد.',
        'mixed' => ':attribute باید حداقل یک حرف بزرگ و یک حرف کوچک انگلیسی داشته باشد.',
        'numbers' => ':attribute باید حداقل یک عدد داشته باشد.',
        'symbols' => ':attribute باید حداقل یک نماد (مثل ! یا @) داشته باشد.',
        'uncompromised' => 'این :attribute در نشت‌های اطلاعاتی دیده شده است؛ رمز دیگری انتخاب کنید.',
    ],

    'attributes' => [],
];
