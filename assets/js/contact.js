(function(){
    const layout = document.querySelector('.contact-layout');
    const map    = document.querySelector('.contact-map');
    const overlay= document.querySelector('.contact-overlay');
    const line   = document.querySelector('.contact-line');
    const label  = document.querySelector('.contact-label');
    if (!layout || !map || !overlay || !line || !label) return;

    // dataset percentages from the map
    const p = (k) => parseFloat(map.dataset[k]);
    const d1 = { x: p('dot1X'), y: p('dot1Y') }; // left architect
    const d2 = { x: p('dot2X'), y: p('dot2Y') }; // right architect

    // Tail tuning: go wild 😎
    const LONG_TAIL     = 90;  // active side
    const SHORT_TAIL    = 1050;  // opposite side
    const DESKTOP_BOOST = 0;  // extra on desktop

    function rects() {
        return {
        layout: layout.getBoundingClientRect(),
        map:    map.getBoundingClientRect()
        };
    }

    // Convert % dot coords (within map) to overlay-space pixels
    function dotPixels(dot, R) {
        const mx = R.map.width  * dot.x / 100;
        const my = R.map.height * dot.y / 100;
        return {
        x: (R.map.left - R.layout.left) + mx,
        y: (R.map.top  - R.layout.top ) + my
        };
    }

    // turn an element’s point into overlay space (center-bottom by default)
    function overlayPointFromElement(el, R, { at = 'bottom', xAlign = 'center', yOffset = 4 } = {}) {
        const r = el.getBoundingClientRect();
        let x = (xAlign === 'center') ? (r.left + r.width / 2) : r.left;
        let y = (at === 'bottom') ? r.bottom : r.top;
        return { x: x - R.layout.left, y: y - R.layout.top + yOffset };
    }

    function setLine(which) {
        const R = rects();

        // Dot-derived line direction (unit)
        const A = dotPixels(d1, R);
        const B = dotPixels(d2, R);
        const vx = B.x - A.x, vy = B.y - A.y;
        const L  = Math.hypot(vx, vy) || 1;
        const ux = vx / L,  uy = vy / L;

        // Active architect's name element and its baseline
        const activeEl = document.querySelector(`.js-arch-trigger[data-target="${which}"]`);
        if (!activeEl) return;
        const P = overlayPointFromElement(activeEl, R, { at: 'bottom', xAlign: 'center', yOffset: 2 });

        // Project that point onto the A→B line
        const t  = ((P.x - A.x) * ux + (P.y - A.y) * uy);
        const Sx = A.x + ux * t;
        const Sy = A.y + uy * t;

        // Tail lengths
        const longT  = LONG_TAIL;
        const shortT = SHORT_TAIL;

        // Extend from anchor along line direction
        const longDir  = (which === 'left') ? -1 : 1;
        const shortDir = -longDir;

        const x1 = Sx + ux * longT  * longDir;
        const y1 = Sy + uy * longT  * longDir;
        const x2 = Sx + ux * shortT * shortDir;
        const y2 = Sy + uy * shortT * shortDir;

        // Apply endpoints
        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);

        // Animate draw from the active (long) side
        const dash = Math.hypot(x2 - x1, y2 - y1);
        if (!isFinite(dash) || dash <= 0) return;

        line.style.strokeDasharray = `${dash} ${dash}`;
        line.style.transition = 'none';

        // decide which endpoint to reveal from, by visual X direction
        const revealFromStart =
        (which === 'left') ? (x1 <= x2)   // left hover: start from the leftmost end
                            : (x1 >  x2);  // right hover: start from the rightmost end

        line.style.strokeDashoffset = revealFromStart ? dash : -dash;
        void line.getBoundingClientRect(); // reflow
        line.style.transition = 'opacity .1s ease-out .02s, stroke-dashoffset 0.5s cubic-bezier(.4,0,.2,1) .02s';
        line.style.strokeDashoffset = '0';

        // Label position (biased toward the short side)
        const labelAlong = 0.95; // 0..1 from long side to short side
        const Lx = x1 + (x2 - x1) * labelAlong;
        const Ly = y1 + (y2 - y1) * labelAlong;

        // Offset label off the stroke
        const nx = -uy, ny = ux;
        const normalOffset = 3;
        label.style.left = `${Lx + nx * normalOffset}px`;
        label.style.top  = `${Ly + ny * normalOffset}px`;
    }


    function show(which) {
        document.querySelectorAll('.js-arch-trigger')
        .forEach(t => t.classList.toggle('is-active', t.dataset.target === which));

        label.textContent = which === 'left'
        ? (map.dataset.dot1Label || 'Greece')
        : (map.dataset.dot2Label || 'Spain');

        setLine(which);
        line.classList.add('is-visible');
        label.style.opacity = '1';
    }

    function hide() {
        document.querySelectorAll('.js-arch-trigger')
        .forEach(t => t.classList.remove('is-active'));
        line.classList.remove('is-visible');
        label.style.opacity = '0';
    }

    // keep it correct on resize/load/font ready
    function relayout() {
        if (line.classList.contains('is-visible')) {
        const active = document.querySelector('.js-arch-trigger.is-active');
        setLine(active ? active.dataset.target : 'left');
        }
    }
    window.addEventListener('resize', () => requestAnimationFrame(relayout));
    window.addEventListener('load', relayout);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(relayout);

    // triggers
    const triggers = document.querySelectorAll('.js-arch-trigger');
    triggers.forEach(t => {
        t.addEventListener('mouseenter', () => show(t.dataset.target));
        t.addEventListener('focus',      () => show(t.dataset.target));
        t.addEventListener('mouseleave', hide);
        t.addEventListener('blur',       hide);
        // click still navigates to CV; we just flash the line
        t.addEventListener('click',      () => show(t.dataset.target));
    });
})();