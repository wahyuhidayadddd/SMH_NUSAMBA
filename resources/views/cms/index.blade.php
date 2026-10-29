<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Konten | CMS NUSAMBA</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:#17283d;background:#f4f7fb;font-synthesis:none}
        *{box-sizing:border-box}body{margin:0;min-height:100vh}a{color:inherit;text-decoration:none}button,input,textarea{font:inherit}
        .topbar{height:70px;display:flex;align-items:center;justify-content:space-between;padding:0 34px;border-bottom:1px solid #e7edf4;background:#fff}
        .brand{display:flex;align-items:center;gap:12px;font-size:15px;font-weight:800;letter-spacing:-.02em}.mark{display:grid;place-items:center;width:38px;height:38px;border-radius:12px;background:#123d66;color:#fff}
        .top-actions{display:flex;align-items:center;gap:18px}.site-link{color:#60738b;font-size:13px;font-weight:600}.admin{color:#5f7085;font-size:13px}
        .logout{padding:9px 13px;border:1px solid #e1e8f0;border-radius:9px;background:#fff;color:#42556d;font-size:12px;font-weight:700;cursor:pointer}
        .shell{max-width:1480px;margin:0 auto;padding:34px}.heading{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:24px}
        .eyebrow{margin:0 0 8px;color:#2877ad;font-size:11px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}
        h1{margin:0;font-size:29px;letter-spacing:-.045em}.subheading{margin:7px 0 0;color:#718197;font-size:14px}
        .count{padding:9px 13px;border:1px solid #e2eaf2;border-radius:10px;background:#fff;color:#61738a;font-size:12px;font-weight:650}
        .notice{margin-bottom:18px;padding:12px 15px;border:1px solid #ccebdc;border-radius:10px;background:#f0fbf5;color:#18734a;font-size:13px}
        .workspace{display:grid;grid-template-columns:270px minmax(0,1fr);gap:20px;align-items:start;min-width:0}
        .workspace>*{min-width:0}
        .panel{border:1px solid #e5ebf2;border-radius:15px;background:#fff;box-shadow:0 4px 20px #1f3b5c08}
        .navigation{padding:18px}.panel-label{margin:0 0 13px;color:#9aa8b8;font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
        .tabs{display:grid;gap:5px;min-width:0}.tab{display:flex;justify-content:space-between;align-items:center;width:100%;padding:10px 11px;border:0;border-radius:9px;background:transparent;color:#60738b;text-align:left;font-size:13px;font-weight:650;cursor:pointer}
        .tab:hover{background:#f5f8fb;color:#18344e}.tab.active{background:#eaf3fa;color:#14578c}.badge{color:#92a0b0;font-size:11px}
        .divider{height:1px;margin:18px 0;background:#edf1f5}.page-list{display:grid;gap:3px;max-height:calc(100vh - 460px);min-height:110px;min-width:0;overflow:auto}
        .page-link{display:block;min-width:0;padding:9px 10px;border-radius:8px;color:#67788d;font-size:12px;line-height:1.4}.page-link:hover{background:#f6f8fb}
        .page-link.active{background:#f1f6fa;color:#14578c;font-weight:700}.page-title{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.page-path{display:block;margin-top:3px;color:#a0acba;font-size:10px}
        .editor{overflow:hidden}.editor-head{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:21px 24px;border-bottom:1px solid #edf1f5}
        .editor-head h2{margin:0;font-size:17px;letter-spacing:-.02em}.editor-head p{margin:5px 0 0;color:#8290a1;font-size:12px}.published{display:flex;align-items:center;gap:8px;color:#61738a;font-size:12px;white-space:nowrap}
        .published input{width:15px;height:15px;accent-color:#16734b}.form{padding:23px 24px}.field{margin-bottom:20px}.field label{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;color:#43566d;font-size:12px;font-weight:750}
        .hint{color:#97a4b3;font-size:11px;font-weight:500}.text-input{width:100%;height:45px;padding:0 13px;border:1px solid #dfe6ee;border-radius:9px;color:#20364d;outline:none}
        .text-input:focus,.visual-frame:focus{border-color:#4384b5;box-shadow:0 0 0 3px #4384b51b}
        .editor-help{margin:0;padding:14px 20px;border-bottom:1px solid #edf1f5;color:#687b91;font-size:12px;line-height:1.55}.editor-help strong{color:#29445f}
        .source-sync{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px 20px;border-bottom:1px solid #edf1f5;background:#f7fafc;color:#687b91;font-size:12px;line-height:1.5}
        .source-sync strong{color:#29445f}.source-sync button{flex:none;padding:9px 12px;border:1px solid #dce5ee;border-radius:8px;background:#fff;color:#38536c;font-size:11px;font-weight:700;cursor:pointer}
        .source-sync button:hover{background:#f3f8fc}
        .toolbar-wrap{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 17px;border-bottom:1px solid #edf1f5}
        .toolbar{display:flex;align-items:center;gap:5px;flex-wrap:wrap}.tool,.device{min-width:36px;height:35px;padding:0 10px;border:1px solid #e3eaf1;border-radius:8px;background:#fff;color:#40566d;font-size:12px;font-weight:700;cursor:pointer}
        .tool:hover,.device:hover{border-color:#aac6dc;background:#f3f8fc}.tool:disabled{opacity:.42;cursor:not-allowed}.toolbar-divider{width:1px;height:24px;margin:0 3px;background:#e6ebf1}
        .devices{display:flex;gap:5px}.device{min-width:auto;font-size:11px}.device.active{border-color:#c5dcee;background:#edf5fb;color:#14578c}
        .selection-status{min-height:38px;display:flex;align-items:center;gap:8px;padding:9px 18px;border-bottom:1px solid #edf1f5;color:#718197;font-size:12px}
        .selection-dot{width:7px;height:7px;flex:none;border-radius:50%;background:#c4ccd6}.selection-status.ready .selection-dot{background:#229b65}
        .preview-viewport{width:100%;overflow:auto;background:#edf1f5;padding:20px;overscroll-behavior:contain}
        .preview-viewport[data-size="mobile"],.preview-viewport[data-size="tablet"]{display:flex;justify-content:center}
        .visual-frame{display:block;width:100%;height:min(78vh,900px);min-height:620px;border:1px solid #dce4ec;border-radius:10px;background:#fff;box-shadow:0 8px 26px #1f3b5c12}
        .preview-viewport[data-size="mobile"] .visual-frame{width:min(390px,100%);height:740px;min-height:600px;flex:none}
        .preview-viewport[data-size="tablet"] .visual-frame{width:min(768px,100%);height:800px;min-height:600px;flex:none}
        .image-editor{display:flex;align-items:flex-end;gap:10px;padding:14px 18px;border-bottom:1px solid #edf1f5;background:#f7fafc}
        .image-editor[hidden]{display:none}.image-alt-wrap{flex:1;min-width:0}.image-alt-label{display:block;margin-bottom:6px;color:#52667c;font-size:11px;font-weight:700}
        .image-alt{width:100%;height:38px;padding:0 10px;border:1px solid #dfe6ee;border-radius:8px;font:inherit;font-size:12px}.upload-button{height:38px;padding:0 12px;border:1px solid #dce5ee;border-radius:8px;background:#fff;color:#38536c;font-size:12px;font-weight:700;cursor:pointer}.upload-button:hover{background:#f3f8fc}
        .editor-status{display:none;margin:0;padding:11px 18px;border-bottom:1px solid #edf1f5;color:#18734a;font-size:12px}
        .editor-status.visible{display:block}.editor-status.error{color:#b42318;background:#fff7f6}
        .editor-footer{display:flex;align-items:center;justify-content:space-between;gap:14px;padding-top:17px;border-top:1px solid #edf1f5}.preview{color:#2774a9;font-size:12px;font-weight:700}
        .save{padding:11px 17px;border:0;border-radius:9px;background:#14578c;color:#fff;font-size:12px;font-weight:750;cursor:pointer}.save:hover{background:#104a79}.save:disabled{opacity:.65;cursor:wait}
        .empty{padding:70px 20px;color:#8492a2;text-align:center}.empty strong{display:block;margin-bottom:7px;color:#40546c;font-size:15px}
        @media(max-width:850px){.shell{padding:22px 16px}.workspace{grid-template-columns:minmax(0,1fr)}.navigation{padding:14px}.tabs{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;max-height:180px;overflow:auto;padding-right:2px}.tab{width:100%;min-width:0;min-height:40px;gap:5px;padding:8px 7px;border:1px solid #edf1f5;border-radius:9px;font-size:12px}.badge{flex:none}.divider,.panel-label{display:none}.page-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(150px,100%),1fr));max-height:180px;min-height:0}.page-link{padding:8px}.editor-head,.form{padding-left:17px;padding-right:17px}.preview-viewport{padding:12px}.toolbar-wrap{align-items:flex-start;flex-direction:column}.visual-frame{height:75vh;min-height:560px}}
        @media(max-width:540px){.topbar{height:62px;padding:0 15px}.site-link,.admin{display:none}.heading{align-items:flex-start;flex-direction:column}.heading h1{font-size:25px}.editor-head{align-items:flex-start;flex-direction:column}.count{align-self:flex-start}.editor-footer{align-items:flex-start;flex-direction:column-reverse}.save{width:100%}.toolbar{flex-wrap:nowrap;max-width:100%;overflow-x:auto}.toolbar-wrap{gap:9px;padding:11px 12px}.devices{align-self:flex-end}.preview-viewport{padding:8px}.visual-frame{min-height:520px}.selection-status{padding-inline:13px}.image-editor{align-items:stretch;flex-direction:column;padding:12px}.upload-button{width:100%}}
    </style>
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('cms.index') }}"><span class="mark">N</span><span>NUSAMBA <span style="color:#8b9aaa;font-weight:600">/ CMS</span></span></a>
    <div class="top-actions">
        <a class="site-link" href="{{ route('site.home') }}" target="_blank" rel="noopener">Lihat website ↗</a>
        <span class="admin">{{ config('cms.admin_email') }}</span>
        <form method="post" action="{{ route('cms.logout') }}">@csrf<button class="logout" type="submit">Keluar</button></form>
    </div>
</header>
<main class="shell">
    <div class="heading">
        <div><p class="eyebrow">Manajemen website</p><h1>Kelola konten</h1><p class="subheading">Semua halaman website dalam satu tempat.</p></div>
        <div class="count">{{ $pagesBySection->flatten()->count() }} halaman</div>
    </div>
    @if (session('status'))<div class="notice">{{ session('status') }}</div>@endif
    <div class="workspace">
        <aside class="panel navigation">
            <p class="panel-label">Pilih kategori</p>
            <nav class="tabs" aria-label="Kategori konten">
                @foreach ($pagesBySection as $section => $pages)
                    @php($sectionPage = $pages->first())
                    <a class="tab {{ $activeSection === $section ? 'active' : '' }}" href="{{ route('cms.index', ['tab' => $section, 'page' => $sectionPage->id]) }}">
                        <span>{{ $section }}</span><span class="badge">{{ $pages->count() }}</span>
                    </a>
                @endforeach
            </nav>
            @if ($selectedPage)
                <div class="divider"></div>
                <p class="panel-label">Halaman {{ $activeSection }}</p>
                <div class="page-list">
                    @foreach ($pagesBySection->get($activeSection, collect()) as $page)
                        <a class="page-link {{ $selectedPage->id === $page->id ? 'active' : '' }}" href="{{ route('cms.index', ['tab' => $activeSection, 'page' => $page->id]) }}">
                            <span class="page-title">{{ $page->title }}</span><span class="page-path">{{ $page->path }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </aside>
        <section class="panel editor" aria-label="Editor konten">
            @if ($selectedPage)
                @php($sourcePath = \App\Support\CmsPageSource::sourcePathFor($selectedPage->path))
                @if (\App\Support\CmsPageSource::liveSourcePathFor($selectedPage->path) !== null)
                    <div class="editor-head">
                        <div><h2>Halaman dari file sumber</h2><p>{{ $sourcePath }}</p></div>
                        <span class="published">Perubahan file langsung tampil di website</span>
                    </div>
                    <p class="editor-help"><strong>Sumber desain halaman ini adalah file di atas.</strong> Edit HTML di folder <code>resources\content\pages</code>. CSS yang dipakai halaman beranda ada di <code>public\assets\css\index.css</code>. Halaman ini dibaca langsung dari sumber, bukan dari salinan CMS.</p>
                    <div class="preview-viewport" id="preview-viewport" data-size="desktop">
                        <iframe
                            class="visual-frame"
                            id="visual-frame"
                            title="Pratinjau langsung {{ $selectedPage->title }}"
                            src="{{ route('cms.pages.preview', $selectedPage) }}"
                        ></iframe>
                    </div>
                    <div class="editor-footer">
                        <a class="preview" href="{{ url('/'.$selectedPage->path) }}" target="_blank" rel="noopener">Lihat halaman langsung ↗</a>
                    </div>
                @else
                    <div class="editor-head">
                        <div><h2>Edit halaman dari pratinjau</h2><p>{{ $selectedPage->path }}</p></div>
                        <label class="published"><input form="page-editor" type="checkbox" name="is_published" value="1" @checked($selectedPage->is_published)> Tampilkan di website</label>
                    </div>
                    @if ($sourcePath !== null)
                    <form class="source-sync" method="post" action="{{ route('cms.pages.sync-source', $selectedPage) }}" onsubmit="return confirm('Konten CMS halaman ini akan diganti dengan isi {{ $sourcePath }}. Lanjutkan?')">
                        @csrf
                        <span><strong>Sumber asli:</strong> {{ $sourcePath }}. Sinkronisasi hanya memperbarui halaman ini.</span>
                        <button type="submit">Muat ulang dari sumber</button>
                    </form>
                    @endif
                    <form id="page-editor" class="form" method="post" action="{{ route('cms.pages.update', $selectedPage) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="page-content" name="content">
                    <p class="editor-help"><strong>Konten halaman.</strong> Pilih teks atau gambar langsung di bawah untuk mengeditnya. Gunakan tombol format, lalu simpan untuk menerbitkan perubahan ke website.</p>
                    @error('content')<p class="notice">{{ $message }}</p>@enderror
                    <div class="field">
                        <label for="title">Judul halaman / judul tab browser</label>
                        <input class="text-input" id="title" name="title" type="text" value="{{ old('title', $selectedPage->title) }}" maxlength="160" required>
                        @error('title')<p class="notice">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="meta-description">Deskripsi halaman <span class="hint">Untuk hasil pencarian · opsional</span></label>
                        <input class="text-input" id="meta-description" name="meta_description" type="text" value="{{ old('meta_description', $selectedPage->meta_description) }}" maxlength="500">
                    </div>
                    <div class="toolbar-wrap">
                        <div class="toolbar" role="toolbar" aria-label="Format teks">
                            <button class="tool" type="button" data-command="bold" title="Tebal" aria-label="Tebal">B</button>
                            <button class="tool" type="button" data-command="italic" title="Miring" aria-label="Miring"><em>I</em></button>
                            <button class="tool" type="button" data-command="underline" title="Garis bawah" aria-label="Garis bawah"><u>U</u></button>
                            <span class="toolbar-divider" aria-hidden="true"></span>
                            <button class="tool" type="button" data-command="formatBlock" data-value="h2" title="Judul bagian">H2</button>
                            <button class="tool" type="button" data-command="formatBlock" data-value="p" title="Paragraf">Paragraf</button>
                            <button class="tool" type="button" data-command="insertUnorderedList" title="Daftar poin">• Daftar</button>
                            <button class="tool" type="button" data-command="insertOrderedList" title="Daftar bernomor">1. Daftar</button>
                            <span class="toolbar-divider" aria-hidden="true"></span>
                            <button class="tool" type="button" data-command="undo" title="Urungkan">↶</button>
                            <button class="tool" type="button" data-command="redo" title="Ulangi">↷</button>
                        </div>
                        <div class="devices" role="group" aria-label="Ukuran pratinjau">
                            <button class="device active" type="button" data-size="desktop" aria-pressed="true">Desktop</button>
                            <button class="device" type="button" data-size="tablet" aria-pressed="false">Tablet</button>
                            <button class="device" type="button" data-size="mobile" aria-pressed="false">Mobile</button>
                        </div>
                    </div>
                    <div class="selection-status" id="selection-status" role="status" aria-live="polite">
                        <span class="selection-dot" aria-hidden="true"></span>
                        <span id="selection-label">Muat pratinjau halaman untuk mulai mengedit.</span>
                    </div>
                    <div class="image-editor" id="image-editor" hidden>
                        <div class="image-alt-wrap">
                            <label class="image-alt-label" for="image-alt">Teks alternatif gambar</label>
                            <input class="image-alt" id="image-alt" type="text" maxlength="250" placeholder="Jelaskan gambar secara singkat">
                        </div>
                        <input id="image-input" type="file" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                        <button class="upload-button" id="upload-image" type="button">Ganti gambar</button>
                    </div>
                    <p class="editor-status" id="editor-status" role="status" aria-live="polite"></p>
                    <div class="preview-viewport" id="preview-viewport" data-size="desktop">
                        <iframe
                            class="visual-frame"
                            id="visual-frame"
                            title="Pratinjau visual {{ $selectedPage->title }}"
                            src="{{ route('cms.pages.preview', $selectedPage) }}"
                        ></iframe>
                    </div>
                    <input id="preview-url" type="hidden" value="{{ route('cms.pages.preview', $selectedPage) }}">
                    <input id="media-url" type="hidden" value="{{ route('cms.media.store') }}">
                    <div class="editor-footer">
                        <a class="preview" href="{{ url('/'.$selectedPage->path) }}" target="_blank" rel="noopener">Pratinjau halaman ↗</a>
                        <button class="save" id="save-page" type="submit">Simpan perubahan</button>
                    </div>
                    </form>
                @endif
            @else
                <div class="empty"><strong>Konten belum tersedia</strong>Jalankan <code>php artisan migrate --seed</code> untuk memuat halaman awal.</div>
            @endif
        </section>
    </div>
</main>
<script src="{{ asset('assets/js/cms-editor.js') }}?v={{ filemtime(public_path('assets/js/cms-editor.js')) }}" defer></script>
</body>
</html>
