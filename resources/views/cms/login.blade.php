<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk CMS | NUSAMBA</title>
    <link rel="stylesheet" href="{{ asset('assets/cms-admin/libs/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/cms-admin/libs/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/cms-admin/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/cms-admin/css/cms-admin.css') }}">
</head>
<body class="cms-login">
<main class="login-wrapper">
    <div class="login-bg-shape login-bg-shape-1" aria-hidden="true"></div>
    <div class="login-bg-shape login-bg-shape-2" aria-hidden="true"></div>
    <section class="login-card">
        <a class="login-brand text-decoration-none" href="{{ route('cms.login') }}">
            <i class="bi bi-bank2" aria-hidden="true"></i>
            <span>NUSAMBA CMS</span>
        </a>
        <p class="login-subtitle">Masuk untuk mengelola konten website.</p>

        @if (! $isConfigured)
            <div class="alert alert-warning cms-login-notice" role="alert">
                Akun admin belum dikonfigurasi. Atur kredensial CMS pada konfigurasi aplikasi.
            </div>
        @endif

        <form method="post" action="{{ route('cms.login.submit') }}">
            @csrf
            <div class="login-form-group">
                <label class="login-form-label" for="email">Username atau email admin</label>
                <div class="login-input-group">
                    <i class="bi bi-person input-icon" aria-hidden="true"></i>
                    <input
                        class="login-input"
                        id="email"
                        name="email"
                        type="text"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        placeholder="Masukkan username atau email"
                        required
                        autofocus
                    >
                </div>
                @error('email')
                    <p class="cms-login-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="login-form-group">
                <label class="login-form-label" for="password">Kata sandi</label>
                <div class="login-input-group">
                    <i class="bi bi-shield-lock input-icon" aria-hidden="true"></i>
                    <input
                        class="login-input"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi"
                        required
                    >
                </div>
                @error('password')
                    <p class="cms-login-error">{{ $message }}</p>
                @enderror
            </div>
            <button class="btn-login" type="submit" @disabled(! $isConfigured)>
                <span>Masuk ke CMS</span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </button>
        </form>
        <a class="cms-login-home" href="{{ route('site.home') }}">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Kembali ke website
        </a>
    </section>
</main>
</body>
</html>
