(() => {
  const gallery = document.querySelector('.property-gallery');
  if (!gallery) return;

  const slides = [...gallery.querySelectorAll('[data-gallery-slide]')];
  const track = gallery.querySelector('[data-gallery-track]');
  const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];
  const counter = gallery.querySelector('[data-gallery-count]');
  const toggle = gallery.querySelector('[data-gallery-toggle]');
  let current = 0;
  let timer = null;
  let paused = false;

  const show = (index) => {
    current = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => {
      slide.classList.toggle('is-active', i === current);
      slide.setAttribute('aria-hidden', i === current ? 'false' : 'true');
    });
    track.style.transform = `translateX(-${current * 100}%)`;
    thumbs.forEach((thumb, i) => {
      thumb.classList.toggle('is-active', i === current);
      thumb.setAttribute('aria-current', i === current ? 'true' : 'false');
    });
    counter.textContent = `${current + 1} / ${slides.length}`;
  };

  const updateToggle = () => {
    toggle.textContent = paused ? '自動再生を開始' : '自動再生を停止';
    toggle.setAttribute('aria-pressed', paused ? 'true' : 'false');
  };
  const stopTimer = () => {
    window.clearInterval(timer);
    timer = null;
  };
  const startTimer = () => {
    stopTimer();
    if (!paused && slides.length > 1) timer = window.setInterval(() => show(current + 1), 4000);
  };
  const resetTimer = () => {
    if (!paused) startTimer();
  };

  gallery.querySelector('.gallery-prev').addEventListener('click', () => { show(current - 1); resetTimer(); });
  gallery.querySelector('.gallery-next').addEventListener('click', () => { show(current + 1); resetTimer(); });
  thumbs.forEach((thumb) => thumb.addEventListener('click', () => { show(Number(thumb.dataset.galleryThumb)); resetTimer(); }));
  toggle.addEventListener('click', () => {
    paused = !paused;
    updateToggle();
    startTimer();
  });
  updateToggle();
  startTimer();
})();
