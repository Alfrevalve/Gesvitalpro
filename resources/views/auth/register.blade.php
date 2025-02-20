<x-guest-layout>
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center">
            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="#0097CD">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <span class="ml-2 text-xl font-semibold text-[#0F3061]">GESBIO</span>
        </div>
        <div>
            <span class="text-gray-500 text-sm">Already have an account?</span>
            <a href="{{ route('login') }}" class="ml-2 text-[#0097CD] font-medium hover:text-[#0F3061]">SIGN IN</a>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-[#0F3061]">Welcome to GesBio!</h2>
        <p class="text-gray-500 mt-1">Register your account</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" id="name" required
                   class="input-field @error('name') border-red-500 @enderror"
                   value="{{ old('name') }}" autofocus>
            @error('name')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" required
                   class="input-field @error('email') border-red-500 @enderror"
                   value="{{ old('email') }}">
            @error('email')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <div class="relative">
                <input type="password" name="password" id="password" required
                       class="input-field @error('password') border-red-500 @enderror">
                <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full py-2 px-4 rounded-md bg-[#0097CD] text-white font-medium hover:bg-[#0F3061] transition-colors duration-200">
            Register
        </button>

        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Create account with</span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-3">
                <a href="#" class="social-login flex justify-center items-center">
                    <svg class="h-5 w-5" fill="#0097CD" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="#" class="social-login flex justify-center items-center">
                    <svg class="h-5 w-5" fill="#0097CD" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                </a>
                <a href="#" class="social-login flex justify-center items-center">
                    <svg class="h-5 w-5" fill="#0097CD" viewBox="0 0 24 24">
                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.016 18.124c-.213.042-.427.074-.643.095a.922.922 0 01-.117.008.97.97 0 01-.67-.286 1.03 1.03 0 01-.257-.442 6.134 6.134 0 01-.105-.445 5.608 5.608 0 01-.079-.56c-.01-.126-.016-.243-.016-.343 0-.126.005-.243.016-.343.016-.19.042-.369.079-.56.031-.158.068-.306.105-.445a1.02 1.02 0 01.257-.442.97.97 0 01.67-.286c.039 0 .078.003.117.008.216.021.43.053.643.095.474.095.89.253 1.248.475.474.285.664.633.664 1.03 0 .396-.19.744-.664 1.03-.358.222-.774.38-1.248.475zM12 6.5c-3.038 0-5.5 2.462-5.5 5.5s2.462 5.5 5.5 5.5 5.5-2.462 5.5-5.5-2.462-5.5-5.5-5.5z"/>
                    </svg>
                </a>
            </div>
        </div>
    </form>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            password.type = password.type === 'password' ? 'text' : 'password';
        }
    </script>
</x-guest-layout>
