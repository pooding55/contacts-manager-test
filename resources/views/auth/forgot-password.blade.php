<x-layouts.app>
    <div class="container">

        <p class="text-center">Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.</p>

        <x-auth-validation-errors class="mb-4" :errors="$errors" />
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
            </div>

            <button type="submit" class="btn btn-primary">Email Password Reset Link</button>
        </form>
    </div>
</x-layouts.app>
