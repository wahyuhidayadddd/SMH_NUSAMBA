<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk CMS | NUSAMBA</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:#14243a;background:#f3f6fa;font-synthesis:none}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px}
        .card{width:min(100%,420px);padding:36px;border:1px solid #e6ebf2;border-radius:20px;background:#fff;box-shadow:0 20px 60px #19345212}
        .brand{display:flex;align-items:center;gap:12px;margin-bottom:32px}.mark{display:grid;place-items:center;width:42px;height:42px;border-radius:13px;background:#123d66;color:#fff;font-weight:800}
        h1{margin:0;font-size:23px;letter-spacing:-.04em}.muted{margin:5px 0 0;color:#77869a;font-size:13px}
        label{display:block;margin:20px 0 8px;font-size:13px;font-weight:650}input{width:100%;height:46px;padding:0 13px;border:1px solid #dfe6ef;border-radius:10px;font:inherit;outline:none}
        input:focus{border-color:#2678bd;box-shadow:0 0 0 3px #2678bd1c}.button{width:100%;height:47px;margin-top:24px;border:0;border-radius:10px;background:#14578c;color:#fff;font:inherit;font-weight:700;cursor:pointer}
        .button:hover{background:#104a79}.error,.notice{margin:8px 0 0;color:#b42318;font-size:13px}.notice{padding:12px;border-radius:10px;background:#fff5e8;color:#8f4c00;line-height:1.55}
    </style>
</head>
<body>
<main class="card">
    <div class="brand"><div class="mark">N</div><div><h1>CMS NUSAMBA</h1><p class="muted">Masuk untuk mengelola konten website</p></div></div>
    <form method="post" action="{{ route('cms.login.submit') }}">
        @csrf
        <label for="email">Username atau email admin</label>
        <input id="email" name="email" type="text" value="{{ old('email') }}" autocomplete="username" required autofocus>
        @error('email')<p class="error">{{ $message }}</p>@enderror
        <label for="password">Kata sandi</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        @error('password')<p class="error">{{ $message }}</p>@enderror
        <button class="button" type="submit" @disabled(! $isConfigured)>Masuk ke CMS</button>
    </form>
</main>
</body>
</html>
