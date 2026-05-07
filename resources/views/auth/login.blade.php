<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="login-header text-center mb-4">
        <div class="brand-icon mb-2">
            <i class="bi bi-airplane"></i>
        </div>
        <h1>Aircraft Component Tracking</h1>
        <p>Login untuk mengakses sistem</p>
    </div>
    <div class="login-body px-4 py-3">
        <ul class="nav nav-tabs justify-content-center mb-4" id="loginTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="superadmin-tab" data-bs-toggle="tab" data-bs-target="#superadmin"
                    type="button" role="tab" aria-controls="superadmin" aria-selected="true">
                    M.PPC & PPC
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#user" type="button"
                    role="tab" aria-controls="user" aria-selected="false">
                    Q.CVDR / Q.Inspector / Mekanik
                </button>
            </li>
        </ul>
        <div class="tab-content" id="loginTabContent">
            <!-- Superadmin Login -->
            <div class="tab-pane fade show active" id="superadmin" role="tabpanel" aria-labelledby="superadmin-tab">
                @if (session('error') && old('login_type') === ['superadmin','admin'])
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <form action="{{ route('login') }}" method="POST" id="superadminForm">
                    @csrf
                    <input type="hidden" name="login_type" value="superadmin">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" placeholder="Email" value="{{ old('email') }}" required autofocus>
                        <label for="email"><i class="bi bi-envelope me-2"></i>Email</label>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" placeholder="Password" required>
                        <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-login w-100" id="superadminBtn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>
            </div>
            <!-- User Login -->
            <div class="tab-pane fade" id="user" role="tabpanel" aria-labelledby="user-tab">
                @if (session('error') && old('login_type') === ['mechanic', 'quality1', 'quality2', 'customer'])
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <form action="{{ route('userAuth') }}" method="POST" id="userForm">
                    @csrf
                    <input type="hidden" name="login_type" value="user">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik"
                            name="nik" placeholder="NIK" value="{{ old('nik') }}" required minlength="4" maxlength="16">
                        <label for="nik"><i class="bi bi-person-badge me-2"></i>NIK</label>
                        @error('nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-login w-100" id="userBtn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>
            </div>
        </div>
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-shield-check me-1"></i>
                Sistem terjamin keamanannya
            </small>
        </div>
    </div>
</x-guest-layout>

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab persistence on error
            @if (old('login_type') === 'user')
                var userTab = new bootstrap.Tab(document.getElementById('user-tab'));
                userTab.show();
            @endif
            // Loading animation
            const superadminForm = document.getElementById('superadminForm');
            const superadminBtn = document.getElementById('superadminBtn');
            if (superadminForm) {
                superadminForm.addEventListener('submit', function() {
                    const originalText = superadminBtn.innerHTML;
                    superadminBtn.innerHTML = '<span class="loading"></span> Logging in...';
                    superadminBtn.disabled = true;
                    setTimeout(() => {
                        superadminBtn.innerHTML = originalText;
                        superadminBtn.disabled = false;
                    }, 10000);
                });
            }
            const userForm = document.getElementById('userForm');
            const userBtn = document.getElementById('userBtn');
            if (userForm) {
                userForm.addEventListener('submit', function() {
                    const originalText = userBtn.innerHTML;
                    userBtn.innerHTML = '<span class="loading"></span> Logging in...';
                    userBtn.disabled = true;
                    setTimeout(() => {
                        userBtn.innerHTML = originalText;
                        userBtn.disabled = false;
                    }, 10000);
                });
            }
            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
@endpush
