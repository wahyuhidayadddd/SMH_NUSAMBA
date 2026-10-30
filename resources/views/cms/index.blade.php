<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Konten | CMS NUSAMBA</title>
    <link rel="stylesheet" href="{{ asset('assets/cms-admin/libs/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/cms-admin/libs/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/cms-admin/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/cms-admin/css/cms-admin.css') }}">
</head>
<body class="cms-admin">
<aside class="sidebar-wrapper cms-sidebar" id="sidebar">
    <a class="sidebar-brand brand" href="{{ route('cms.index') }}">
        <i class="bi bi-bank2" aria-hidden="true"></i>
        <span>NUSAMBA <small>/ CMS</small></span>
    </a>

    <div class="flex-grow-1 overflow-y-auto">
        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Menu</div>
            <ul class="sidebar-menu-list">
                <li class="sidebar-menu-item">
                    <a class="sidebar-menu-link" href="{{ route('cms.index') }}">
                        <i class="bi bi-grid-fill" aria-hidden="true"></i>
                        <span>Kelola konten</span>
                        <span class="sidebar-menu-badge">{{ $pagesBySection->flatten()->count() }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Kategori halaman</div>
            <ul class="sidebar-menu-list">
                @foreach ($pagesBySection as $section => $pages)
                    @php($sectionPage = $pages->first())
                    <li class="sidebar-menu-item">
                        <a
                            class="sidebar-menu-link tab {{ $activeSection === $section ? 'active' : '' }}"
                            href="{{ route('cms.index', ['tab' => $section, 'page' => $sectionPage->id]) }}"
                            title="{{ $section }}"
                        >
                            <i class="bi bi-folder2-open" aria-hidden="true"></i>
                            <span>{{ $section }}</span>
                            <span class="sidebar-menu-badge">{{ $pages->count() }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        @if ($selectedPage)
            <div class="sidebar-menu-section cms-page-navigation">
                <div class="sidebar-menu-title">Halaman {{ $activeSection }}</div>
                <ul class="sidebar-menu-list">
                    @foreach ($pagesBySection->get($activeSection, collect()) as $page)
                        <li class="sidebar-menu-item">
                            <a
                                class="sidebar-menu-link page-link cms-page-link {{ $selectedPage->id === $page->id ? 'active' : '' }}"
                                href="{{ route('cms.index', ['tab' => $activeSection, 'page' => $page->id]) }}"
                                title="{{ $page->path }}"
                            >
                                <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                                <span>
                                    <span class="cms-page-title">{{ $page->title }}</span>
                                    <span class="cms-page-path">{{ $page->path }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="sidebar-profile">
        <img
            src="{{ asset('assets/cms-admin/images/avatar.png') }}"
            alt=""
            class="sidebar-profile-img"
        >
        <div class="sidebar-profile-info">
            <div class="sidebar-profile-name">Administrator</div>
            <div class="sidebar-profile-email">{{ config('cms.admin_email') }}</div>
        </div>
    </div>
</aside>

<div class="main-wrapper">
    <header class="navbar-custom">
        <div class="navbar-left">
            <button
                class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center"
                id="desktop-sidebar-toggle"
                type="button"
                aria-label="Ciutkan navigasi"
            >
                <i class="bi bi-chevron-bar-left" aria-hidden="true"></i>
            </button>
            <button class="sidebar-toggle-btn" id="sidebar-toggle" type="button" aria-label="Buka navigasi">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
        </div>

        <div class="navbar-search-wrapper cms-navbar-center">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
            <span>Content Management System</span>
        </div>

        <div class="navbar-actions cms-navbar-actions">
            <a class="site-link" href="{{ route('site.home') }}" target="_blank" rel="noopener">
                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                <span>Lihat website</span>
            </a>
            <span class="cms-admin-email">{{ config('cms.admin_email') }}</span>
            <form method="post" action="{{ route('cms.logout') }}">
                @csrf
                <button class="btn-custom btn-custom-outline-secondary cms-logout" type="submit">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </header>

    <main class="cms-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Kelola konten</h1>
                <p class="page-subtitle">Pilih halaman untuk melihat pratinjau atau mengubah kontennya.</p>
            </div>
            <div class="cms-page-count">
                <i class="bi bi-files" aria-hidden="true"></i>
                {{ $pagesBySection->flatten()->count() }} halaman
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success cms-notice" role="status">{{ session('status') }}</div>
        @endif

        @if ($selectedPage)
            @php($sourcePath = \App\Support\CmsPageSource::sourcePathForPage($selectedPage->path))
            <section class="card cms-editor-card" aria-label="Editor konten">
                @if ($sourcePath !== null)
                    <div class="card-header cms-editor-header">
                        <div>
                            <h2 class="card-title">Halaman dari file sumber</h2>
                            <p class="cms-editor-path">{{ $sourcePath }}</p>
                        </div>
                        <span class="cms-state-badge">
                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                            File kode menjadi sumber utama halaman
                        </span>
                    </div>
                    <div class="cms-editor-help">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        <span>
                            <strong>Halaman dibaca langsung dari file di atas, bukan dari salinan CMS.</strong>
                            Edit atau hapus file HTML di folder <code>resources\content\pages</code> untuk mengubah atau menghapus halaman.
                            Jika halaman memiliki stylesheet sendiri, edit file CSS terkait di <code>public\assets\css</code>.
                        </span>
                    </div>
                    <div class="preview-viewport cms-preview-viewport" id="preview-viewport" data-size="desktop">
                        <iframe
                            class="visual-frame"
                            id="visual-frame"
                            title="Pratinjau langsung {{ $selectedPage->title }}"
                            src="{{ route('cms.pages.preview', $selectedPage) }}"
                        ></iframe>
                    </div>
                    <div class="cms-editor-footer">
                        <a class="cms-preview-link" href="{{ url('/'.$selectedPage->path) }}" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                            Lihat halaman langsung
                        </a>
                    </div>
                @else
                    <div class="card-header cms-editor-header">
                        <div>
                            <h2 class="card-title">Edit halaman</h2>
                            <p class="cms-editor-path">{{ $selectedPage->path }}</p>
                        </div>
                        <label class="form-check cms-published-toggle">
                            <input
                                class="form-check-input"
                                form="page-editor"
                                type="checkbox"
                                name="is_published"
                                value="1"
                                @checked($selectedPage->is_published)
                            >
                            <span class="form-check-label">Tampilkan di website</span>
                        </label>
                    </div>
                    @if ($sourcePath !== null)
                        <form
                            class="cms-source-sync"
                            method="post"
                            action="{{ route('cms.pages.sync-source', $selectedPage) }}"
                            onsubmit="return confirm('Konten CMS halaman ini akan diganti dengan isi {{ $sourcePath }}. Lanjutkan?')"
                        >
                            @csrf
                            <span><strong>Sumber asli:</strong> {{ $sourcePath }}. Sinkronisasi hanya memperbarui halaman ini.</span>
                            <button class="btn-custom btn-custom-outline-secondary" type="submit">
                                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                                Muat ulang dari sumber
                            </button>
                        </form>
                    @endif

                    <form id="page-editor" class="cms-form" method="post" action="{{ route('cms.pages.update', $selectedPage) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" id="page-content" name="content">
                        <p class="cms-editor-help">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span><strong>Konten halaman.</strong> Pilih teks atau gambar langsung di bawah untuk mengeditnya. Gunakan tombol format, lalu simpan untuk menerbitkan perubahan ke website.</span>
                        </p>
                        @error('content')
                            <p class="cms-validation-error">{{ $message }}</p>
                        @enderror
                        <div class="cms-form-fields">
                            <div class="cms-field">
                                <label class="form-label-custom" for="title">Judul halaman / judul tab browser</label>
                                <input
                                    class="form-control-custom"
                                    id="title"
                                    name="title"
                                    type="text"
                                    value="{{ old('title', $selectedPage->title) }}"
                                    maxlength="160"
                                    required
                                >
                                @error('title')
                                    <p class="cms-validation-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="cms-field">
                                <label class="form-label-custom" for="meta-description">
                                    Deskripsi halaman <span class="cms-field-hint">Untuk hasil pencarian · opsional</span>
                                </label>
                                <input
                                    class="form-control-custom"
                                    id="meta-description"
                                    name="meta_description"
                                    type="text"
                                    value="{{ old('meta_description', $selectedPage->meta_description) }}"
                                    maxlength="500"
                                >
                            </div>
                        </div>

                        <div class="toolbar-wrap cms-toolbar-wrap">
                            <div class="toolbar cms-toolbar" role="toolbar" aria-label="Format teks">
                                <button class="tool cms-tool" type="button" data-command="bold" title="Tebal" aria-label="Tebal">B</button>
                                <button class="tool cms-tool" type="button" data-command="italic" title="Miring" aria-label="Miring"><em>I</em></button>
                                <button class="tool cms-tool" type="button" data-command="underline" title="Garis bawah" aria-label="Garis bawah"><u>U</u></button>
                                <span class="toolbar-divider" aria-hidden="true"></span>
                                <button class="tool cms-tool" type="button" data-command="formatBlock" data-value="h2" title="Judul bagian">H2</button>
                                <button class="tool cms-tool" type="button" data-command="formatBlock" data-value="p" title="Paragraf">Paragraf</button>
                                <button class="tool cms-tool" type="button" data-command="insertUnorderedList" title="Daftar poin">• Daftar</button>
                                <button class="tool cms-tool" type="button" data-command="insertOrderedList" title="Daftar bernomor">1. Daftar</button>
                                <span class="toolbar-divider" aria-hidden="true"></span>
                                <button class="tool cms-tool" type="button" data-command="undo" title="Urungkan">↶</button>
                                <button class="tool cms-tool" type="button" data-command="redo" title="Ulangi">↷</button>
                            </div>
                            <div class="devices cms-devices" role="group" aria-label="Ukuran pratinjau">
                                <button class="device active" type="button" data-size="desktop" aria-pressed="true">Desktop</button>
                                <button class="device" type="button" data-size="tablet" aria-pressed="false">Tablet</button>
                                <button class="device" type="button" data-size="mobile" aria-pressed="false">Mobile</button>
                            </div>
                        </div>

                        <div class="selection-status cms-selection-status" id="selection-status" role="status" aria-live="polite">
                            <span class="selection-dot" aria-hidden="true"></span>
                            <span id="selection-label">Muat pratinjau halaman untuk mulai mengedit.</span>
                        </div>
                        <div class="image-editor cms-image-editor" id="image-editor" hidden>
                            <div class="image-alt-wrap">
                                <label class="image-alt-label" for="image-alt">Teks alternatif gambar</label>
                                <input class="image-alt" id="image-alt" type="text" maxlength="250" placeholder="Jelaskan gambar secara singkat">
                            </div>
                            <input id="image-input" type="file" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                            <button class="upload-button" id="upload-image" type="button">
                                <i class="bi bi-image" aria-hidden="true"></i>
                                Ganti gambar
                            </button>
                        </div>
                        <p class="editor-status cms-editor-status" id="editor-status" role="status" aria-live="polite"></p>
                        <div class="preview-viewport cms-preview-viewport" id="preview-viewport" data-size="desktop">
                            <iframe
                                class="visual-frame"
                                id="visual-frame"
                                title="Pratinjau visual {{ $selectedPage->title }}"
                                src="{{ route('cms.pages.preview', $selectedPage) }}"
                            ></iframe>
                        </div>
                        <input id="preview-url" type="hidden" value="{{ route('cms.pages.preview', $selectedPage) }}">
                        <input id="media-url" type="hidden" value="{{ route('cms.media.store') }}">
                        <div class="cms-editor-footer">
                            <a class="cms-preview-link" href="{{ url('/'.$selectedPage->path) }}" target="_blank" rel="noopener">
                                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                Pratinjau halaman
                            </a>
                            <button class="btn-custom btn-custom-primary cms-save" id="save-page" type="submit">
                                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                Simpan perubahan
                            </button>
                        </div>
                    </form>
                @endif
            </section>
        @else
            <div class="card cms-empty">
                <i class="bi bi-file-earmark-x" aria-hidden="true"></i>
                <strong>Konten belum tersedia</strong>
                Jalankan <code>php artisan migrate --seed</code> untuk memuat halaman awal.
            </div>
        @endif
    </main>
</div>
<script src="{{ asset('assets/cms-admin/js/dashboard.js') }}" defer></script>
<script src="{{ asset('assets/js/cms-editor.js') }}?v={{ filemtime(public_path('assets/js/cms-editor.js')) }}" defer></script>
</body>
</html>
