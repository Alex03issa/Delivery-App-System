@extends('layouts.main')

@section('container')
    @include('partials.navbar')

    <section class="register-photo" style="margin-top: 60px;">
        <div class="form-container">
            <div class="image-holder"></div>

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf

                {{-- === Role Tabs === --}}
                <div class="text-center mb-3">
                    <div class="btn-group" role="group" aria-label="Role toggle">
                    @php
                        $selectedRole = request()->query('role', 'client'); 
                    @endphp
                    <input type="hidden" name="role" id="selected-role" value="{{ $selectedRole }}">


                        <button type="button" id="btn-client" class="btn {{ $selectedRole === 'client' ? 'btn-warning' : 'btn-outline-dark' }}">
                            Client
                        </button>
                        <button type="button" id="btn-driver" class="btn {{ $selectedRole === 'driver' ? 'btn-warning' : 'btn-outline-dark' }}">
                            Driver
                        </button>

                    </div>
                </div>

                {{-- === Heading === --}}
                <h2 class="text-center" style="margin-top: -8px;"><strong>Create</strong> an account.</h2>
                <p class="text-center">Partner with us to drive your own livelihood and more.</p>

                {{-- Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success text-center" style="max-width: 600px; margin: 20px auto;">
                        <strong>{{ session('success') }}</strong>
                    </div>
                @endif


                {{-- Form Fields --}}
                <div class="mb-3">
                    <input type="email" name="email" placeholder="Email"
                           class="form-control @error('email') is-invalid @enderror" required value="{{ old('email') }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <input type="text" name="name" placeholder="Name"
                           class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <input type="text" name="phone" placeholder="Phone Number"
                        class="form-control @error('phone') is-invalid @enderror" required value="{{ old('phone') }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>


                <div class="mb-3 position-relative">
                    <div class="input-group">
                        <input type="password" name="password" id="password"
                            placeholder="Password"
                            class="form-control @error('password') is-invalid @enderror" required>
                        <span class="input-group-text toggle-password" style="cursor: pointer;">
                            <i class="fas fa-eye-slash" data-toggle="password"></i>
                        </span>
                    </div>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3 position-relative">
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            placeholder="Password (repeat)"
                            class="form-control @error('password_confirmation') is-invalid @enderror" required>
                        <span class="input-group-text toggle-password" style="cursor: pointer;">
                            <i class="fas fa-eye-slash" data-toggle="password_confirmation"></i>
                        </span>
                    </div>
                    @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>


                {{-- Terms --}}
                <div class="mb-3">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" required>
                            I agree to the license terms.
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <input class="btn btn-primary d-block w-100" type="submit"
                           style="background: rgb(254,209,54);" value="Sign Up">
                </div>

                <a class="already" href="/login">Already have an account? Login here.</a>


                
                {{-- Social Login (Only for Client) --}}
                <div id="social-buttons" class="text-center mb-3">
                    <div class="d-flex justify-content-center">
                        <a href="{{ route('google.redirect') }}" class="btn me-2"
                        style="background-color: #db4437; border:none; color:white; width:200px;">
                            <i class="fab fa-google me-2"></i> Google
                        </a>
                        <a href="{{ route('facebook.redirect') }}" class="btn"
                        style="background-color: #3b5998; border:none; color:white; width:200px;">
                            <i class="fab fa-facebook-f me-2"></i> Facebook
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- Toggle Script --}}
    <script>
        function updateTabUI(selected) {
            document.getElementById('selected-role').value = selected;

            const isClient = selected === 'client';
            document.getElementById('btn-client').classList.toggle('btn-warning', isClient);
            document.getElementById('btn-client').classList.toggle('btn-outline-dark', !isClient);
            document.getElementById('btn-driver').classList.toggle('btn-warning', !isClient);
            document.getElementById('btn-driver').classList.toggle('btn-outline-dark', isClient);
            document.getElementById('social-buttons').style.display = isClient ? 'block' : 'none';
        }

        document.getElementById('btn-client').addEventListener('click', function () {
            updateTabUI('client');
        });

        document.getElementById('btn-driver').addEventListener('click', function () {
            updateTabUI('driver');
        });
        
        window.addEventListener('DOMContentLoaded', () => {
            updateTabUI(document.getElementById('selected-role').value);
        });
    </script>

    <script>
        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function () {
                const icon = this.querySelector('i');
                const targetId = icon.getAttribute('data-toggle');
                const input = document.getElementById(targetId);

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });
    </script>

@endsection
