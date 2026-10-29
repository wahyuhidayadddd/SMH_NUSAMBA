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
  } else {
    window.addEventListener('load', removeLoader, { once: true });
  }
})();
