<x-auth-session-status class="mb-4" :status="session('status')" />

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
        <input id="email"
               type="email"
               name="email"
               value="{{ old('email') }}"
               required
               autofocus
               autocomplete="username"
               inputmode="email"
               autocorrect="off"
               autocapitalize="none"
               spellcheck="false"
               enterkeyhint="next"
               @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
               class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
        <x-input-error id="email-error" :messages="$errors->get('email')" class="mt-1" />
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
            <button type="button"
                    data-password-toggle="password"
                    aria-pressed="false"
                    aria-label="Tampilkan kata sandi"
                    class="text-xs font-medium text-emerald-700 hover:text-emerald-800">
                Tampilkan
            </button>
        </div>
        <input id="password"
               type="password"
               name="password"
               required
               autocomplete="current-password"
               enterkeyhint="done"
               @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
               class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
        <x-input-error id="password-error" :messages="$errors->get('password')" class="mt-1" />
    </div>

    <div class="flex items-center justify-between text-sm">
        <label for="remember_me" class="inline-flex items-center text-gray-600">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-600">
            <span class="ms-2">{{ __('Remember me') }}</span>
        </label>

        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-gray-600 hover:text-gray-900 underline">
                {{ __('Forgot your password?') }}
            </a>
        @endif
    </div>

    <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-emerald-700 rounded-md hover:bg-emerald-800">
        {{ __('Log in') }}
    </button>
</form>
