(() => {
  const carousels = document.querySelectorAll('#carousel-0900, #carousel-32d8');

  carousels.forEach(carousel => {
    const viewport = carousel.querySelector('.u-carousel-inner');

    if (!viewport) {
      return;
    }

    let start = null;
    let suppressClick = false;

    carousel.querySelectorAll('.u-carousel-control').forEach(control => {
      control.addEventListener('click', event => {
        event.preventDefault();
      });
    });

    viewport.addEventListener('pointerdown', event => {
      if (event.pointerType === 'mouse' && event.button !== 0) {
        return;
      }

      if (event.target.closest('a, button, input, select, textarea')) {
        return;
      }

      start = {
        x: event.clientX,
        y: event.clientY,
        pointerId: event.pointerId,
      };
    });

    viewport.addEventListener('pointerup', event => {
      if (!start || event.pointerId !== start.pointerId) {
        return;
      }

      const deltaX = event.clientX - start.x;
      const deltaY = event.clientY - start.y;
      start = null;

      if (Math.abs(deltaX) < 45 || Math.abs(deltaX) <= Math.abs(deltaY) * 1.25) {
        return;
      }

      const direction = deltaX < 0 ? 'next' : 'prev';
      const control = carousel.querySelector(`[data-u-slide="${direction}"]`);

      if (!control) {
        return;
      }

      suppressClick = true;
      control.click();
      window.setTimeout(() => {
        suppressClick = false;
      }, 0);
    });

    viewport.addEventListener('pointercancel', () => {
      start = null;
    });

    viewport.addEventListener('click', event => {
      if (suppressClick) {
        event.preventDefault();
        event.stopPropagation();
        suppressClick = false;
      }
    }, true);
  });
})();
