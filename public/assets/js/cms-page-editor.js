(() => {
    if (window.parent === window) {
        return;
    }

    const editableSelector = 'h1,h2,h3,h4,h5,h6,p,li,a,blockquote,figcaption,button,label,td,th,span';
    let activeElement = null;
    let lastRange = null;

    document.documentElement.classList.add('cms-preview');
    document.body.classList.add('cms-editing');
    document.addEventListener('submit', (event) => event.preventDefault(), true);
    document.addEventListener('selectionchange', () => {
        const selection = document.getSelection();

        if (selection?.rangeCount && activeElement?.isContentEditable) {
            lastRange = selection.getRangeAt(0).cloneRange();
        }
    });

    document.addEventListener('click', (event) => {
        const image = event.target.closest('img');

        if (image) {
            event.preventDefault();
            event.stopImmediatePropagation();
            document.querySelectorAll('[data-cms-selected]').forEach((element) => element.removeAttribute('data-cms-selected'));
            activeElement?.removeAttribute('data-cms-editable');
            activeElement = image;
            image.setAttribute('data-cms-selected', 'true');
            window.parent.postMessage({
                type: 'cms:selection',
                selection: { kind: 'image', src: image.currentSrc || image.src, alt: image.alt || '' },
            }, location.origin);

            return;
        }

        const target = event.target.closest(editableSelector);

        if (!target || target.isContentEditable || !target.textContent.trim()) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        document.querySelectorAll('[data-cms-selected]').forEach((element) => element.removeAttribute('data-cms-selected'));
        activeElement?.removeAttribute('data-cms-editable');
        activeElement = target;
        target.setAttribute('data-cms-editable', 'true');
        target.setAttribute('contenteditable', 'true');
        target.setAttribute('data-cms-selected', 'true');
        target.focus();

        window.parent.postMessage({
            type: 'cms:selection',
            selection: { kind: 'text', tag: target.tagName.toLowerCase(), text: target.textContent.trim().slice(0, 100) },
        }, location.origin);
    }, true);

    document.addEventListener('input', (event) => {
        if (!event.target.isContentEditable) {
            return;
        }

        window.parent.postMessage({
            type: 'cms:dirty',
            tag: event.target.tagName.toLowerCase(),
        }, location.origin);
    });

    function runCommand(command, value = null) {
        if (!activeElement?.isContentEditable) {
            return;
        }

        activeElement.focus();
        const selection = document.getSelection();

        if (lastRange && selection) {
            selection.removeAllRanges();
            selection.addRange(lastRange);
        }

        document.execCommand(command, false, value);
        lastRange = selection?.rangeCount ? selection.getRangeAt(0).cloneRange() : null;
        activeElement.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function serialize() {
        const copy = document.documentElement.cloneNode(true);
        copy.classList.remove('cms-preview');
        copy.querySelector('body')?.classList.remove('cms-editing');
        copy.querySelectorAll('[data-cms-preview]').forEach((element) => element.remove());
        copy.querySelectorAll('[contenteditable], [data-cms-editable], [data-cms-selected]').forEach((element) => {
            element.removeAttribute('contenteditable');
            element.removeAttribute('data-cms-editable');
            element.removeAttribute('data-cms-selected');
        });

        return `${document.doctype ? '<!doctype html>\n' : ''}${copy.outerHTML}`;
    }

    window.addEventListener('message', (event) => {
        if (event.origin !== location.origin || event.source !== window.parent) {
            return;
        }

        switch (event.data?.type) {
            case 'cms:command':
                runCommand(event.data.command, event.data.value);
                break;
            case 'cms:title':
                document.title = event.data.value;
                break;
            case 'cms:image-alt':
                if (activeElement?.tagName === 'IMG') {
                    activeElement.alt = event.data.value;
                    activeElement.dispatchEvent(new Event('input', { bubbles: true }));
                }
                break;
            case 'cms:replace-image':
                if (activeElement?.tagName !== 'IMG' || new URL(event.data.src, location.origin).origin !== location.origin) {
                    return;
                }

                activeElement.src = event.data.src;
                activeElement.alt = event.data.alt;
                activeElement.dispatchEvent(new Event('input', { bubbles: true }));
                break;
            case 'cms:serialize':
                event.source.postMessage({ type: 'cms:serialized', content: serialize() }, location.origin);
                break;
        }
    });

    window.parent.postMessage({ type: 'cms:ready' }, location.origin);
})();
