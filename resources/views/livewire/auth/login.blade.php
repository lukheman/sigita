<div class="login-container">
    <div class="login-card">
        <!-- Brand Logo -->
        <div class="brand-logo">
            <div class="icon-wrapper">
                <i class="fas fa-heartbeat"></i>
            </div>
            <h1>Selamat Datang</h1>
            <p>Masuk untuk melanjutkan ke SIGITA</p>
        </div>

        <!-- Login Form -->
        <form wire:submit="submit">
            <!-- Email Field -->
            <div class="form-floating position-relative">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror"
                    id="email" placeholder="Alamat Email" autofocus>
                <label for="email">Alamat Email</label>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="form-floating position-relative">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" wire:model="password"
                    class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Kata Sandi">
                <label for="password">Kata Sandi</label>
                <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <script>
                function togglePassword() {
                    const input = document.getElementById('password');
                    const icon = document.getElementById('toggleIcon');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                }
            </script>

            <!-- Remember Me & Forgot Password -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" wire:model="remember" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <a href="#" class="forgot-password">Lupa Kata Sandi?</a>
            </div>

            <!-- Login Button -->
            <button type="submit" class="btn btn-login" wire:loading.attr="disabled">
                <span wire:loading.remove>Masuk <i class="fas fa-arrow-right"></i></span>
                <span wire:loading>
                    <i class="fas fa-spinner fa-spin me-2"></i> Memproses...
                </span>
            </button>
        </form>

        {{--

        <!-- Divider -->
        <div class="divider">
            <span>atau</span>
        </div>


        <!-- Sign Up Link -->
        <div class="signup-link">
            Belum punya akun? <a href="{{ route('register') }}">Daftar Sekarang</a>
        </div>
        --}}

    </div>
</div>
