{{--
    Live strength bar under the password field of the user form.
    Same checks as App\Support\PasswordPolicy (the server rejects anything that fails them).
--}}
@php($min = \App\Support\PasswordPolicy::MIN)
<div x-data="{ pw: '' }" x-init="
    const input = $el.closest('[data-field-wrapper]')?.querySelector('input');
    if (input) input.addEventListener('input', (e) => (pw = e.target.value));
">
@if ($editing)
    <p class="hkd-pw-edit-hint" x-show="! pw">برای تغییر رمز پر کنید؛ خالی بماند رمز قبلی می‌ماند.</p>
@endif
<div
    class="hkd-pw"
    x-data="{
        pw: '',
        init() {
            const input = $el.closest('[data-field-wrapper]')?.querySelector('input');
            if (! input) return;
            this.pw = input.value;
            input.addEventListener('input', (e) => (this.pw = e.target.value));
        },
        get checks() {
            return {
                length: [...this.pw].length >= {{ $min }},
                lower: /\p{Ll}/u.test(this.pw),
                upper: /\p{Lu}/u.test(this.pw),
                number: /\p{N}/u.test(this.pw),
            };
        },
        get score() {
            return Object.values(this.checks).filter(Boolean).length;
        },
        get strong() {
            return this.score === 4 && ([...this.pw].length >= 12 || /[^\p{L}\p{N}]/u.test(this.pw));
        },
        get level() {
            if (! this.pw) return 0;
            return this.strong ? 5 : this.score;
        },
        get label() {
            return ['', 'خیلی ضعیف', 'ضعیف', 'متوسط', 'قابل قبول', 'قوی'][this.level];
        },
    }"
    x-show="{{ $editing ? 'pw.length > 0' : 'true' }}"
    x-cloak
    :data-level="level"
>
    <div class="hkd-pw-head">
        <div class="hkd-pw-bar" role="progressbar" aria-label="قدرت رمز عبور" aria-valuemin="0" aria-valuemax="5" :aria-valuenow="level">
            <template x-for="i in 5" :key="i">
                <span :class="{ 'is-on': i <= level }"></span>
            </template>
        </div>
        <span class="hkd-pw-label" x-text="label" aria-live="polite"></span>
    </div>

    <ul class="hkd-pw-checks">
        <li :class="{ 'is-ok': checks.length }">حداقل {{ \App\Support\Digits::toPersian((string) $min) }} کاراکتر</li>
        <li :class="{ 'is-ok': checks.upper }">یک حرف بزرگ انگلیسی (A-Z)</li>
        <li :class="{ 'is-ok': checks.lower }">یک حرف کوچک انگلیسی (a-z)</li>
        <li :class="{ 'is-ok': checks.number }">یک عدد (0-9)</li>
    </ul>

    <p class="hkd-pw-hint" x-show="score === 4 && ! strong">برای رمز قوی‌تر: ۱۲ کاراکتر یا بیشتر، یا یک نماد مثل ! @ #</p>
</div>
</div>

@once
    <style>
        .hkd-pw { --pw-off: rgb(229 231 235); --pw-c: rgb(156 163 175); margin-top: .5rem; display: flex; flex-direction: column; gap: .6rem; }
        .dark .hkd-pw { --pw-off: rgb(255 255 255 / .1); }
        .hkd-pw[data-level='1'] { --pw-c: #ef4444; }
        .hkd-pw[data-level='2'] { --pw-c: #f97316; }
        .hkd-pw[data-level='3'] { --pw-c: #eab308; }
        .hkd-pw[data-level='4'] { --pw-c: #22c55e; }
        .hkd-pw[data-level='5'] { --pw-c: #16a34a; }
        .hkd-pw-head { display: flex; align-items: center; gap: .75rem; }
        .hkd-pw-bar { flex: 1; display: grid; grid-template-columns: repeat(5, 1fr); gap: .3rem; }
        .hkd-pw-bar span { height: .375rem; border-radius: 9999px; background: var(--pw-off); transition: background-color .25s ease, transform .25s ease; }
        .hkd-pw-bar span.is-on { background: var(--pw-c); }
        .hkd-pw-label { min-width: 4.5rem; text-align: end; font-size: .8rem; font-weight: 700; color: var(--pw-c); transition: color .25s ease; }
        .hkd-pw-checks { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .35rem .75rem; font-size: .8rem; color: rgb(107 114 128); }
        .dark .hkd-pw-checks { color: rgb(156 163 175); }
        .hkd-pw-checks li { display: flex; align-items: center; gap: .4rem; transition: color .2s ease; }
        .hkd-pw-checks li::before {
            content: ''; flex: none; width: 1rem; height: 1rem; border-radius: 9999px;
            border: 1.5px solid currentColor; opacity: .55; transition: all .2s ease;
            background-position: center; background-repeat: no-repeat; background-size: .65rem;
        }
        .hkd-pw-checks li.is-ok { color: #16a34a; }
        .dark .hkd-pw-checks li.is-ok { color: #4ade80; }
        .hkd-pw-checks li.is-ok::before {
            opacity: 1; border-color: transparent; background-color: currentColor;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12l5 5L20 7'/%3E%3C/svg%3E");
        }
        .hkd-pw-edit-hint { font-size: .875rem; color: rgb(107 114 128); }
        .dark .hkd-pw-edit-hint { color: rgb(156 163 175); }
        .hkd-pw-hint { font-size: .75rem; color: rgb(107 114 128); }
        @media (max-width: 480px) { .hkd-pw-checks { grid-template-columns: 1fr; } }
    </style>
@endonce
