(() => {
  const section = document.querySelector('.u-section-11');
  const row = section?.querySelector('.u-layout-row');
  const cards = Array.from(row?.querySelectorAll(':scope > .u-layout-cell') ?? []);

  if (!row || cards.length < 3) {
    return;
  }

  const track = document.createElement('div');
  track.className = 'partners-carousel__track';
  track.setAttribute('aria-live', 'polite');
  const slides = [];

  for (let index = 0; index < cards.length; index += 2) {
    const slide = document.createElement('div');
    slide.className = 'partners-carousel__slide';
    slide.setAttribute('role', 'group');
    slide.setAttribute('aria-roledescription', 'slide');
    slide.setAttribute('aria-label', `${slides.length + 1}`);
    slide.append(...cards.slice(index, index + 2));
    track.append(slide);
    slides.push(slide);
  }

  row.append(track);

  const controls = document.createElement('div');
  controls.className = 'partners-carousel__controls';
  controls.setAttribute('role', 'group');
  controls.setAttribute('aria-label', 'Navigasi mitra');

  const previous = document.createElement('button');
  previous.className = 'partners-carousel__button';
  previous.type = 'button';
  previous.setAttribute('aria-label', 'Mitra sebelumnya');
  previous.textContent = '\u2039';

  const position = document.createElement('span');
  position.className = 'partners-carousel__position';
  position.setAttribute('aria-live', 'polite');

  const next = document.createElement('button');
  next.className = 'partners-carousel__button';
  next.type = 'button';
  next.setAttribute('aria-label', 'Mitra berikutnya');
  next.textContent = '\u203a';

  controls.append(previous, position, next);
  row.parentElement?.append(controls);

  let activeSlide = 0;
  let startX = null;

  const update = () => {
    track.style.transform = window.matchMedia('(max-width: 991px)').matches
      ? `translateX(-${activeSlide * 100}%)`
      : '';
    position.textContent = `${activeSlide + 1}/${slides.length}`;
    previous.disabled = activeSlide === 0;
    next.disabled = activeSlide === slides.length - 1;
    slides.forEach((slide, index) => {
      slide.setAttribute('aria-hidden', String(index !== activeSlide && window.matchMedia('(max-width: 991px)').matches));
    });
  };

  previous.addEventListener('click', () => {
    activeSlide = Math.max(0, activeSlide - 1);
    update();
  });
  next.addEventListener('click', () => {
    activeSlide = Math.min(slides.length - 1, activeSlide + 1);
    update();
  });
  row.addEventListener('touchstart', event => {
    startX = event.changedTouches[0]?.clientX ?? null;
  }, { passive: true });
  row.addEventListener('touchend', event => {
    const endX = event.changedTouches[0]?.clientX;

    if (startX === null || endX === undefined || Math.abs(endX - startX) < 40) {
      return;
    }

    activeSlide = Math.max(0, Math.min(slides.length - 1, activeSlide + (endX < startX ? 1 : -1)));
    startX = null;
    update();
  }, { passive: true });
  window.addEventListener('resize', update, { passive: true });

  update();
})();
