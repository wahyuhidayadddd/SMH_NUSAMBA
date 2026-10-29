(() => {
    const form = document.getElementById('page-editor');

    if (!form) {
        return;
    }

    const frame = document.getElementById('visual-frame');
    const preview = document.getElementById('preview-viewport');
    const content = document.getElementById('page-content');
    const saveButton = document.getElementById('save-page');
    const selectionStatus = document.getElementById('selection-status');
    const selectionLabel = document.getElementById('selection-label');
    const imageEditor = document.getElementById('image-editor');
    const imageInput = document.getElementById('image-input');
    const imageAlt = document.getElementById('image-alt');
    const editorStatus = document.getElementById('editor-status');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let dirty = false;
    let saving = false;
    let selectedImage = false;

    function showStatus(message, isError = false) {
        editorStatus.textContent = message;
        editorStatus.classList.add('visible');
        editorStatus.classList.toggle('error', isError);
    }

    function setDirty() {
        dirty = true;
        selectionStatus.classList.add('ready');
    }

    function send(message) {
        if (frame.contentWindow) {
            frame.contentWindow.postMessage(message, window.location.origin);
        }
    }

    function setFrameWidth(size) {
        preview.dataset.size = size;
        const widths = { mobile: 390, tablet: 768 };
        const previewStyle = window.getComputedStyle(preview);
        const availableWidth = preview.clientWidth
            - Number.parseFloat(previewStyle.paddingLeft)
            - Number.parseFloat(previewStyle.paddingRight);

        frame.style.width = widths[size] ? `${Math.min(widths[size], availableWidth)}px` : '100%';
    }

    window.addEventListener('resize', () => {
        if (preview.dataset.size !== 'desktop') {
            setFrameWidth(preview.dataset.size);
        }
    });

    document.querySelectorAll('.device').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.device').forEach((device) => {
                device.classList.toggle('active', device === button);
                device.setAttribute('aria-pressed', device === button ? 'true' : 'false');
            });
            setFrameWidth(button.dataset.size);
        });
    });

    frame.addEventListener('load', () => {
        selectionStatus.classList.add('ready');
        selectionLabel.textContent = 'Klik teks atau gambar di pratinjau untuk mengedit.';
    });

    document.querySelectorAll('[data-command]').forEach((button) => {
        button.addEventListener('click', () => {
            const value = button.dataset.value ?? null;

            if (button.dataset.command === 'createLink') {
                const enteredUrl = window.prompt('Masukkan alamat tautan:');

                if (!enteredUrl) {
                    return;
                }

                let url;

                try {
                    url = new URL(enteredUrl, window.location.origin);
                } catch {
                    showStatus('Alamat tautan tidak valid.', true);
                    return;
                }

                if (!['http:', 'https:', 'mailto:', 'tel:'].includes(url.protocol)) {
                    showStatus('Jenis alamat tautan tersebut tidak diizinkan.', true);
                    return;
                }

                send({ type: 'cms:command', command: 'createLink', value: enteredUrl });
                setDirty();

                return;
            }

            send({ type: 'cms:command', command: button.dataset.command, value });
            setDirty();
        });
    });

    document.getElementById('title').addEventListener('input', (event) => {
        send({ type: 'cms:title', value: event.target.value });
        setDirty();
    });

    document.getElementById('meta-description').addEventListener('input', setDirty);
    document.querySelector('[name="is_published"]').addEventListener('change', setDirty);

    document.getElementById('upload-image').addEventListener('click', () => imageInput.click());

    imageAlt.addEventListener('input', () => {
        if (selectedImage) {
            send({ type: 'cms:image-alt', value: imageAlt.value });
            setDirty();
        }
    });

    imageInput.addEventListener('change', async () => {
        const file = imageInput.files?.[0];

        if (!file) {
            return;
        }

        const upload = new FormData();
        upload.append('image', file);
        showStatus('Mengunggah gambar...');

        try {
            const response = await fetch(document.getElementById('media-url').value, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: upload,
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Gambar tidak dapat diunggah.');
            }

            send({
                type: 'cms:replace-image',
                src: result.url,
                alt: imageAlt.value.trim(),
            });
            showStatus('Gambar diperbarui. Simpan perubahan untuk menerapkannya.');
            setDirty();
        } catch (error) {
            showStatus(error.message || 'Gambar tidak dapat diunggah.', true);
        } finally {
            imageInput.value = '';
        }
    });

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || event.source !== frame.contentWindow) {
            return;
        }

        switch (event.data?.type) {
            case 'cms:ready':
                send({ type: 'cms:title', value: document.getElementById('title').value });
                selectionStatus.classList.add('ready');
                selectionLabel.textContent = 'Klik teks atau gambar di pratinjau untuk mengedit.';
                break;
            case 'cms:selection':
                selectedImage = event.data.selection.kind === 'image';
                imageEditor.hidden = !selectedImage;

                if (selectedImage) {
                    selectionLabel.textContent = 'Gambar dipilih. Ubah teks alternatif atau ganti gambar.';
                    imageAlt.value = event.data.selection.alt;
                } else {
                    selectionLabel.textContent = `Teks ${event.data.selection.tag} dipilih. Ketik langsung pada pratinjau.`;
                }

                editorStatus.classList.remove('visible', 'error');
                break;
            case 'cms:dirty':
                setDirty();
                selectionLabel.textContent = 'Ada perubahan yang belum disimpan.';
                editorStatus.classList.remove('visible', 'error');
                break;
            case 'cms:serialized':
                if (!saving) {
                    break;
                }

                content.value = event.data.content;
                HTMLFormElement.prototype.submit.call(form);
                break;
        }
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        if (saving) {
            return;
        }

        if (!frame.contentWindow) {
            showStatus('Pratinjau belum siap. Muat ulang halaman lalu coba kembali.', true);
            return;
        }

        saving = true;
        saveButton.disabled = true;
        saveButton.textContent = 'Menyimpan...';
        showStatus('Menyimpan perubahan...');
        send({ type: 'cms:serialize' });

        window.setTimeout(() => {
            if (!saving) {
                return;
            }

            saving = false;
            saveButton.disabled = false;
            saveButton.textContent = 'Simpan perubahan';
            showStatus('Pratinjau tidak merespons. Muat ulang halaman lalu coba kembali.', true);
        }, 5000);
    });

    document.addEventListener('click', (event) => {
        if (!dirty) {
            return;
        }

        const link = event.target.closest('a.page-link, a.tab, a.brand, a.site-link');

        if (link && !window.confirm('Perubahan belum disimpan. Tinggalkan halaman ini?')) {
            event.preventDefault();
        }
    }, true);

    window.addEventListener('beforeunload', (event) => {
        if (dirty && !saving) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
})();
