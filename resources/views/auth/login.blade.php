<x-layouts.app>
    <div class="container">
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" name="remember" for="remember">Check me out</label>
            </div>

            <div class="mb-3">
                <a href="{{ route('password.request') }}">
                    Forgot your password?
                </a>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</x-layouts.app>
