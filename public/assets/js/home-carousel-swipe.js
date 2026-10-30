(() => {
  const carousels = document.querySelectorAll('#carousel-0900, #carousel-32d8');

  carousels.forEach(carousel => {
    const mobileArrowNavigation = window.matchMedia('(max-width: 767px)').matches;

    if (mobileArrowNavigation) {
      carousel.removeAttribute('data-u-ride');

      const pauseAutoplay = () => {
        const $carousel = window.jQuery && window.jQuery(carousel);
        const instance = $carousel && $carousel.data('bs.uCarousel');

        if (instance) {
          instance._config.interval = false;
          instance.pause();
        }
      };

      pauseAutoplay();
      document.addEventListener('DOMContentLoaded', pauseAutoplay, { once: true });
      window.addEventListener('load', pauseAutoplay, { once: true });

      const items = [...carousel.querySelectorAll('.u-carousel-item')];
      const indicators = [...carousel.querySelectorAll('.u-carousel-indicators [data-u-slide-to]')];

      carousel.querySelectorAll('.u-carousel-control[data-u-slide]').forEach(control => {
        control.addEventListener('click', event => {
          event.preventDefault();
          event.stopImmediatePropagation();

          const currentIndex = items.findIndex(item => item.classList.contains('u-active'));
          if (currentIndex < 0 || items.length < 2) {
            return;
          }

          const direction = control.getAttribute('data-u-slide');
          const offset = direction === 'next' ? 1 : -1;
          const nextIndex = (currentIndex + offset + items.length) % items.length;

          items.forEach((item, index) => {
            item.classList.toggle('u-active', index === nextIndex);
          });
          indicators.forEach((indicator, index) => {
            indicator.classList.toggle('u-active', index === nextIndex);
          });
        });
      });
    }

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

    const isInteractiveTarget = target => target.closest('a, button, input, select, textarea');
    const startSwipe = (x, y, pointerId = null) => {
      start = { x, y, pointerId };
    };
    const finishSwipe = (x, y, pointerId = null) => {
      if (!start || (start.pointerId !== null && pointerId !== start.pointerId)) {
        return;
      }

      const deltaX = x - start.x;
      const deltaY = y - start.y;
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
    };

    const supportsTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    if (supportsTouch && !mobileArrowNavigation) {
      viewport.addEventListener('touchstart', event => {
        if (event.touches.length !== 1 || isInteractiveTarget(event.target)) {
          start = null;
          return;
        }

        const touch = event.touches[0];
        startSwipe(touch.clientX, touch.clientY);
      }, { passive: true });

      viewport.addEventListener('touchend', event => {
        const touch = event.changedTouches[0];

        if (touch) {
          finishSwipe(touch.clientX, touch.clientY);
        }
      }, { passive: true });

      viewport.addEventListener('touchcancel', () => {
        start = null;
      }, { passive: true });
    } else if (!supportsTouch && !mobileArrowNavigation) {
      viewport.addEventListener('pointerdown', event => {
        if ((event.pointerType === 'mouse' && event.button !== 0) || isInteractiveTarget(event.target)) {
          return;
        }

        startSwipe(event.clientX, event.clientY, event.pointerId);
      });

      viewport.addEventListener('pointerup', event => {
        finishSwipe(event.clientX, event.clientY, event.pointerId);
      });

      viewport.addEventListener('pointercancel', () => {
        start = null;
      });
    }

    viewport.addEventListener('click', event => {
      if (!suppressClick) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();
      suppressClick = false;
    }, true);

    if (carousel.id === 'carousel-32d8' && window.matchMedia('(max-width: 1024px), (pointer: coarse)').matches) {
      const pauseAutoplay = () => {
        const instance = window.jQuery && window.jQuery(carousel).data('bs.uCarousel');

        if (instance) {
          instance.pause();
        }
      };

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', pauseAutoplay, { once: true });
        window.addEventListener('load', pauseAutoplay, { once: true });
      } else if (document.readyState === 'complete') {
        pauseAutoplay();
      } else {
        document.addEventListener('DOMContentLoaded', pauseAutoplay, { once: true });
        window.addEventListener('load', pauseAutoplay, { once: true });
      }
    }
  });
})();
