(() => {
  const loader = document.querySelector('[data-page-loader]');

  if (!loader) {
    return;
  }

  let removed = false;
  const removeLoader = () => {
    if (removed) {
      return;
    }

    removed = true;
    window.clearTimeout(fallback);
    loader.classList.add('is-hidden');
    loader.setAttribute('aria-hidden', 'true');
    window.setTimeout(() => loader.remove(), 400);
  };
  const fallback = window.setTimeout(removeLoader, 6000);

  if (document.readyState === 'complete') {
    removeLoader();
  } else if (window.matchMedia('(max-width: 1024px), (pointer: coarse)').matches) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', removeLoader, { once: true });
    } else {
      removeLoader();
    }
  } else {
    window.addEventListener('load', removeLoader, { once: true });
  }
})();
