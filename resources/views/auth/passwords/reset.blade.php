@extends('layouts.main')

@section('container')
    <section class="login-clean" style="padding-top: 160px;">
        <div class="form-container">
            <div class="text-center mb-4">
                <h1 class="mt-3" style="font-size: 28px; font-weight: 700;">Reset Password</h1>
                <p class="text-muted">Enter your email and choose a new password.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <input type="email"
                           name="email"
                           id="email"
                           placeholder="Email Address"
                           value="{{ $email ?? old('email') }}"
                           required
                           autocomplete="email"
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 position-relative">
                    <div class="input-group">
                        <input type="password"
                            name="password"
                            id="password"
                            placeholder="New Password"
                            required
                            autocomplete="new-password"
                            class="form-control @error('password') is-invalid @enderror">
                        <span class="input-group-text toggle-password" style="cursor: pointer;">
                            <i class="fas fa-eye-slash" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="password-strength-container mt-2 d-none" id="password-strength-container">
                        <p id="strength-message" class="mb-1 text-muted">Strength: Weak</p>
                        <ul class="list-unstyled small">
                            <li id="length"><i class="fas fa-times-circle text-danger me-1"></i> At least 8 characters</li>
                            <li id="uppercase"><i class="fas fa-times-circle text-danger me-1"></i> Contains uppercase</li>
                            <li id="number"><i class="fas fa-times-circle text-danger me-1"></i> Contains a number</li>
                            <li id="special"><i class="fas fa-times-circle text-danger me-1"></i> Contains a special character</li>
                        </ul>
                    </div>
                </div>


                <div class="mb-4">
                    <div class="input-group">
                        <input type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            placeholder="Confirm Password"
                            required
                            autocomplete="new-password"
                            class="form-control @error('password_confirmation') is-invalid @enderror">
                            <span class="input-group-text toggle-password" style="cursor: pointer;">
                                <i class="fas fa-eye-slash" id="togglePasswordIcon"></i>
                            </span>
                    </div>
                
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary d-block w-100" type="submit" style="background-color: #fed136; border: none; color: #000; font-weight: 600;">
                        Reset Password
                    </button>
                </div>

                
                <div class="text-center">
                    <a href="{{ route('login') }}" class="already">Back to Login</a>
                </div>
                
            </form>

            

            <div class="alert-container mt-3">
                @if (session('success'))
                    <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger"><i class="fas fa-times-circle me-1"></i> {{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li><i class="fas fa-times-circle me-1"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById("password");
            const confirmInput = document.getElementById("password_confirmation");
            const strengthContainer = document.getElementById("password-strength-container");

            const toggleIcons = document.querySelectorAll(".toggle-password");

            // Toggle show/hide for each field
            toggleIcons.forEach((toggle) => {
                toggle.addEventListener("click", () => {
                    const input = toggle.previousElementSibling;
                    const icon = toggle.querySelector("i");
                    const isHidden = input.type === "password";
                    input.type = isHidden ? "text" : "password";
                    icon.classList.toggle("fa-eye");
                    icon.classList.toggle("fa-eye-slash");
                });
            });

            // Show password strength on password field only
            passwordInput.addEventListener("focus", () => {
                strengthContainer.classList.remove("d-none");
            });

            passwordInput.addEventListener("input", () => {
                const val = passwordInput.value;
                const length = document.getElementById("length");
                const upper = document.getElementById("uppercase");
                const number = document.getElementById("number");
                const special = document.getElementById("special");

                const updateIcon = (condition, el) => {
                    el.querySelector("i").className = condition
                        ? "fas fa-check-circle text-success me-1"
                        : "fas fa-times-circle text-danger me-1";
                };

                updateIcon(val.length >= 8, length);
                updateIcon(/[A-Z]/.test(val), upper);
                updateIcon(/[0-9]/.test(val), number);
                updateIcon(/[!@#$%^&*(),.?":{}|<>]/.test(val), special);

                let score = 0;
                if (val.length >= 8) score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[!@#$%^&*(),.?":{}|<>]/.test(val)) score++;

                document.getElementById("strength-message").textContent =
                    "Strength: " + (["Weak", "Fair", "Good", "Strong"][score] || "Very Weak");
            });
        });
    </script>
@endsection



