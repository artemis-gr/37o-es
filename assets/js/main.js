(function () {
  // 1) Helper: find the header/footer reliably
  function $(sel) { return document.querySelector(sel); }
  function firstExisting(selectors) {
    for (var i = 0; i < selectors.length; i++) {
      var el = $(selectors[i]);
      if (el) return el;
    }
    return null;
  }

  // 2) Helper: robust element height (handles abs-positioned children)
  function robustHeight(el) {
    if (!el) return 0;
    var rectH = el.getBoundingClientRect ? el.getBoundingClientRect().height : 0;
    var offH  = typeof el.offsetHeight === 'number' ? el.offsetHeight : 0;
    var cliH  = typeof el.clientHeight === 'number' ? el.clientHeight : 0;
    var scrH  = typeof el.scrollHeight === 'number' ? el.scrollHeight : 0;

    // computed height (may be 'auto'; use 0 then)
    var ch = 0;
    try {
      var cs = window.getComputedStyle(el);
      var parsed = parseFloat(cs && cs.height);
      ch = isNaN(parsed) ? 0 : parsed;
    } catch (e) {}

    // take the max of everything we can read
    return Math.max(rectH || 0, offH || 0, cliH || 0, scrH || 0, ch || 0);
  }

  // 3) Measure and write CSS vars
  function setFrameVars() {
    var root = document.documentElement;

    // Try several selectors; customize the array to your theme
    var header = firstExisting([
      'header.project-hero',
      'header.site-header',
      'header[role="banner"]',
      '.site-header',
      '.project-hero'   // in case it’s a div, not <header>
    ]);
    var footer = firstExisting([
      'footer.site-footer',
      'footer[role="contentinfo"]',
      '.site-footer'
    ]);
    var admin  = document.getElementById('wpadminbar');

    var hdr = robustHeight(header);
    var ftr = robustHeight(footer);
    var adm = robustHeight(admin);

    // If header still reads 0 but you know the hero image is inside,
    // try measuring the first child as a fallback:
    if (!hdr && header && header.firstElementChild) {
      hdr = robustHeight(header.firstElementChild);
    }

    root.style.setProperty('--hdr',   (Math.round(hdr) || 0) + 'px');
    root.style.setProperty('--ftr',   (Math.round(ftr) || 0) + 'px');
    root.style.setProperty('--admin', (Math.round(adm) || 0) + 'px');
  }

  // 4) Observe header/footer for size changes
  var ro;
  function observe(el) {
    if (!el || !window.ResizeObserver) return;
    if (!ro) {
      ro = new ResizeObserver(function () {
        // Debounce a bit
        clearTimeout(observe.t);
        observe.t = setTimeout(setFrameVars, 50);
      });
    }
    try { ro.observe(el); } catch (e) {}
  }

  function init() {
    setFrameVars();                 // initial
    window.addEventListener('resize', setFrameVars, { passive: true });
    window.addEventListener('load',   setFrameVars);

    // observe current header/footer (and admin bar)
    var header = document.querySelector('header.project-hero, header.site-header, header[role="banner"], .site-header, .project-hero');
    var footer = document.querySelector('footer.site-footer, footer[role="contentinfo"], .site-footer');
    var admin  = document.getElementById('wpadminbar');
    observe(header);
    observe(footer);
    observe(admin);

    // run again after fonts/images settle
    setTimeout(setFrameVars, 300);
    setTimeout(setFrameVars, 800);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
