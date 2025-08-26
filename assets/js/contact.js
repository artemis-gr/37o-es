(function () {
  const mq = window.matchMedia('(min-width: 900px)');
  if (!mq.matches) return; // 🚫 bail on mobile/tablet

  const layout  = document.querySelector('.contact-layout');
  const map     = document.querySelector('.contact-map');
  const overlay = document.querySelector('.contact-overlay');
  const line    = document.querySelector('.contact-line');
  const label   = document.querySelector('.contact-label');
  if (!layout || !map || !overlay || !line || !label) return;

  // % from data attrs
  const p  = (k) => parseFloat(map.dataset[k]);
  const d1 = { x: p('dot1X'), y: p('dot1Y') };
  const d2 = { x: p('dot2X'), y: p('dot2Y') };

  const LONG_TAIL  = 90;
  const SHORT_TAIL = 1050;

  function rects() {
    return { layout: layout.getBoundingClientRect(), map: map.getBoundingClientRect() };
  }
  function dotPixels(dot, R) {
    const mx = R.map.width  * dot.x / 100;
    const my = R.map.height * dot.y / 100;
    return { x: (R.map.left - R.layout.left) + mx, y: (R.map.top - R.layout.top) + my };
  }
  function overlayPointFromElement(el, R, { at='bottom', xAlign='center', yOffset=4 } = {}) {
    const r = el.getBoundingClientRect();
    const x = (xAlign === 'center') ? (r.left + r.width / 2) : r.left;
    const y = (at === 'bottom') ? r.bottom : r.top;
    return { x: x - R.layout.left, y: y - R.layout.top + yOffset };
  }
  function setLineAttrs(x1, y1, x2, y2) {
    line.setAttribute('x1', x1); line.setAttribute('y1', y1);
    line.setAttribute('x2', x2); line.setAttribute('y2', y2);
  }

  function setInteractiveLine(which) {
    const R = rects();
    const A = dotPixels(d1, R);
    const B = dotPixels(d2, R);

    const vx = B.x - A.x, vy = B.y - A.y;
    const L  = Math.hypot(vx, vy) || 1;
    const ux = vx / L,  uy = vy / L;

    const activeEl = document.querySelector(`.js-arch-trigger[data-target="${which}"]`);
    if (!activeEl) return;

    const P  = overlayPointFromElement(activeEl, R, { at: 'bottom', xAlign: 'center', yOffset: 2 });
    const t  = ((P.x - A.x) * ux + (P.y - A.y) * uy);
    const Sx = A.x + ux * t;
    const Sy = A.y + uy * t;

    const longDir  = (which === 'left') ? -1 : 1;
    const shortDir = -longDir;

    const x1 = Sx + ux * LONG_TAIL  * longDir;
    const y1 = Sy + uy * LONG_TAIL  * longDir;
    const x2 = Sx + ux * SHORT_TAIL * shortDir;
    const y2 = Sy + uy * SHORT_TAIL * shortDir;

    const dash = Math.hypot(x2 - x1, y2 - y1);
    setLineAttrs(x1, y1, x2, y2);
    line.style.strokeDasharray = `${dash} ${dash}`;
    line.style.transition = 'none';

    // draw direction: left name = left→right, right name = right→left
    const revealFromStart = (which === 'left') ? (x1 <= x2) : (x1 > x2);
    line.style.strokeDashoffset = revealFromStart ? dash : -dash;
    void line.getBoundingClientRect();
    line.style.transition = 'opacity .1s ease-out .02s, stroke-dashoffset .5s cubic-bezier(.4,0,.2,1) .02s';
    line.style.strokeDashoffset = '0';

    // label near short side, slightly off the stroke
    const along = 0.95;
    const lx = x1 + (x2 - x1) * along;
    const ly = y1 + (y2 - y1) * along;
    const nx = -uy, ny = ux;
    label.style.left = `${lx + nx * 3}px`;
    label.style.top  = `${ly + ny * 3}px`;
  }

  function show(which) {
    document.querySelectorAll('.js-arch-trigger')
      .forEach(t => t.classList.toggle('is-active', t.dataset.target === which));

    label.textContent = (which === 'left')
      ? (map.dataset.dot1Label || 'Spain')
      : (map.dataset.dot2Label || 'Greece');

    setInteractiveLine(which);
    line.classList.add('is-visible');
    label.style.opacity = '1';
  }
  function hide() {
    document.querySelectorAll('.js-arch-trigger').forEach(t => t.classList.remove('is-active'));
    line.classList.remove('is-visible');
    label.style.opacity = '0';
  }

  // bind desktop-only events
  const triggers = document.querySelectorAll('.js-arch-trigger');
  function onEnter(e){ show(e.currentTarget.dataset.target); }
  function onLeave(){ hide(); }
  triggers.forEach(t => {
    t.addEventListener('mouseenter', onEnter);
    t.addEventListener('focus',      onEnter);
    t.addEventListener('mouseleave', onLeave);
    t.addEventListener('blur',       onLeave);
    t.addEventListener('click',      onEnter);
  });

  // keep alignment crisp on resize
  window.addEventListener('resize', () => {
    if (!line.classList.contains('is-visible')) return;
    const active = document.querySelector('.js-arch-trigger.is-active');
    setInteractiveLine(active ? active.dataset.target : 'left');
  });

  // if the viewport shrinks below 900px while on this page, clean up
  mq.addEventListener('change', (e) => {
    if (!e.matches) {
      hide();
      // (we don’t unbind; page will likely reload styles/JS on nav anyway)
    }
  });
})();
