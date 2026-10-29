(() => {
  const section = document.querySelector('.u-section-9');
  const sheet = section?.querySelector('.u-sheet-1');

  if (!sheet) {
    return;
  }

  const originalContent = Array.from(sheet.childNodes, node => node.cloneNode(true));
  const mobileViewport = window.matchMedia('(max-width: 767px)');
  let cleanupCarousel = null;

  const mountCarousel = () => {
    const cards = Array.from(section.querySelectorAll('.u-layout-cell'))
      .filter(card => card.querySelector('img'));
    const heading = section.querySelector('.u-text-9');
    const description = section.querySelector('.u-text-10');

    if (cards.length < 2 || !heading || !description) {
      return null;
    }

    section.classList.add('promo-carousel-ready');
    const layout = document.createElement('div');
    layout.className = 'promo-layout';

    const carousel = document.createElement('div');
    carousel.className = 'promo-carousel';
    carousel.setAttribute('role', 'region');
    carousel.setAttribute('aria-label', 'Promo hari ini');

    const viewport = document.createElement('div');
    viewport.className = 'promo-carousel__viewport';

    const track = document.createElement('div');
    track.className = 'promo-carousel__track';
    track.style.setProperty('--slide-count', String(cards.length));

    cards.forEach((card, index) => {
      card.classList.add('promo-carousel__card');
      card.setAttribute('role', 'group');
      card.setAttribute('aria-roledescription', 'slide');
      card.setAttribute('aria-label', `${index + 1} dari ${cards.length}`);
      track.append(card);
    });

    viewport.append(track);
    carousel.append(viewport);

    const controls = document.createElement('div');
    controls.className = 'promo-carousel__controls';
    controls.setAttribute('role', 'group');
    controls.setAttribute('aria-label', 'Navigasi promo');

    const previous = document.createElement('button');
    previous.className = 'promo-carousel__button';
    previous.type = 'button';
    previous.setAttribute('aria-label', 'Promo sebelumnya');
    previous.textContent = '\u2039';

    const position = document.createElement('span');
    position.className = 'promo-carousel__position';
    position.setAttribute('aria-live', 'polite');

    const next = document.createElement('button');
    next.className = 'promo-carousel__button';
    next.type = 'button';
    next.setAttribute('aria-label', 'Promo berikutnya');
    next.textContent = '\u203a';

    controls.append(previous, position, next);
    carousel.append(controls);

    const copy = document.createElement('div');
    copy.className = 'promo-copy';
    copy.append(heading, description);
    layout.append(carousel, copy);
    sheet.replaceChildren(layout);

    let activeSlide = 0;
    let timer;
    let pointerStartX = null;
    let pointerId = null;
    let isDragging = false;
    const autoAdvanceDelay = 5000;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    const update = () => {
      track.style.transform = `translateX(-${activeSlide * (100 / cards.length)}%)`;
      position.textContent = `${activeSlide + 1}/${cards.length}`;
    };

    const resetDrag = () => {
      pointerStartX = null;
      pointerId = null;
      isDragging = false;
      viewport.classList.remove('is-dragging');
      update();
    };

    const startTimer = () => {
      window.clearTimeout(timer);

      if (!reducedMotion.matches && !document.hidden) {
        timer = window.setTimeout(() => {
          activeSlide = (activeSlide + 1) % cards.length;
          update();
          startTimer();
        }, autoAdvanceDelay);
      }
    };

    const goToSlide = index => {
      activeSlide = (index + cards.length) % cards.length;
      update();
      startTimer();
    };

    const onPrevious = () => goToSlide(activeSlide - 1);
    const onNext = () => goToSlide(activeSlide + 1);
    const onPointerDown = event => {
      if (event.pointerType === 'mouse' && event.button !== 0) {
        return;
      }
      pointerStartX = event.clientX;
      pointerId = event.pointerId;
      isDragging = true;
      viewport.classList.add('is-dragging');
      viewport.setPointerCapture(event.pointerId);
      window.clearTimeout(timer);
    };
    const onPointerMove = event => {
      if (!isDragging || event.pointerId !== pointerId) {
        return;
      }

      const distance = event.clientX - pointerStartX;
      const offset = activeSlide * (100 / cards.length);
      track.style.transform = `translateX(calc(-${offset}% + ${distance}px))`;
    };
    const onPointerUp = event => {
      if (!isDragging || event.pointerId !== pointerId) {
        return;
      }

      const distance = event.clientX - pointerStartX;
      const dragged = Math.abs(distance) >= 40;
      resetDrag();

      if (dragged) {
        goToSlide(activeSlide + (distance < 0 ? 1 : -1));
      } else {
        startTimer();
      }
    };
    const onVisibilityChange = () => startTimer();
    const onReducedMotionChange = () => startTimer();

    previous.addEventListener('click', onPrevious);
    next.addEventListener('click', onNext);
    viewport.addEventListener('pointerdown', onPointerDown);
    viewport.addEventListener('pointermove', onPointerMove);
    viewport.addEventListener('pointerup', onPointerUp);
    viewport.addEventListener('pointercancel', resetDrag);
    viewport.addEventListener('lostpointercapture', resetDrag);
    viewport.addEventListener('dragstart', event => event.preventDefault());
    carousel.addEventListener('mouseenter', () => window.clearTimeout(timer));
    carousel.addEventListener('mouseleave', startTimer);
    document.addEventListener('visibilitychange', onVisibilityChange);
    reducedMotion.addEventListener('change', onReducedMotionChange);

    update();
    startTimer();

    return () => {
      window.clearTimeout(timer);
      document.removeEventListener('visibilitychange', onVisibilityChange);
      reducedMotion.removeEventListener('change', onReducedMotionChange);
    };
  };

  const updateLayout = () => {
    if (mobileViewport.matches && !cleanupCarousel) {
      cleanupCarousel = mountCarousel();
    } else if (!mobileViewport.matches && cleanupCarousel) {
      cleanupCarousel();
      cleanupCarousel = null;
      section.classList.remove('promo-carousel-ready');
      sheet.replaceChildren(...originalContent.map(node => node.cloneNode(true)));
    }
  };

  mobileViewport.addEventListener('change', updateLayout);
  updateLayout();
})();
