<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Role Selection -->
        <div class="mt-6 p-4 bg-white/5 border border-white/10 rounded-lg">
            <x-input-label for="role" :value="__('Saya ingin sebagai:')" class="mb-4" />
            <div class="space-y-3">
                <label class="flex items-center p-3 border-2 border-white/20 rounded-lg cursor-pointer hover:border-purple-500 transition" id="role-buyer">
                    <input type="radio" name="role" value="customer" {{ old('role') === 'customer' ? 'checked' : '' }} class="w-4 h-4" required>
                    <span class="ms-3">
                        <span class="block font-semibold text-white">🛒 Pembeli (Buyer)</span>
                        <span class="text-sm text-gray-400">Cari dan beli foto dari fotografer</span>
                    </span>
                </label>

                <label class="flex items-center p-3 border-2 border-white/20 rounded-lg cursor-pointer hover:border-purple-500 transition" id="role-photographer">
                    <input type="radio" name="role" value="photographer" {{ old('role') === 'photographer' ? 'checked' : '' }} class="w-4 h-4" required>
                    <span class="ms-3">
                        <span class="block font-semibold text-white">📸 Fotografer (Seller)</span>
                        <span class="text-sm text-gray-400">Jual foto dan kelola album Anda</span>
                    </span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
