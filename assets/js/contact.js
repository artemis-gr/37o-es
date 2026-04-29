(function () {
  const mq = window.matchMedia("(min-width: 1367px)");
  if (!mq.matches) return; // desktop-only

  const layout = document.querySelector(".contact-layout");
  const map = document.querySelector(".contact-map");
  const overlay = document.querySelector(".contact-overlay");
  const line = document.querySelector(".contact-line");
  const label = document.querySelector(".contact-label");
  if (!layout || !map || !overlay || !line || !label) return;

  // create one <img> and one <span> and reuse them
  const labelImg = document.createElement("img");
  labelImg.className = "contact-label__img";
  const labelText = document.createElement("span");
  labelText.className = "contact-label__text";
  label.append(labelImg, labelText);

  // % from data attrs
  const p = (k) => parseFloat(map.dataset[k]);
  const d1 = { x: p("dot1X"), y: p("dot1Y") };
  const d2 = { x: p("dot2X"), y: p("dot2Y") };

  function rects() {
    return {
      layout: layout.getBoundingClientRect(),
      map: map.getBoundingClientRect(),
    };
  }
  function dotPixels(dot, R) {
    const mx = (R.map.width * dot.x) / 100;
    const my = (R.map.height * dot.y) / 100;
    return {
      x: R.map.left - R.layout.left + mx,
      y: R.map.top - R.layout.top + my,
    };
  }
  function overlayPointFromElement(
    el,
    R,
    { at = "bottom", xAlign = "center", yOffset = 4 } = {},
  ) {
    const r = el.getBoundingClientRect();

    let x;
    if (xAlign === "left") x = r.left;
    else if (xAlign === "right") x = r.right;
    else x = r.left + r.width / 2;

    const y = at === "bottom" ? r.bottom : r.top;

    return {
      x: x - R.layout.left,
      y: y - R.layout.top + yOffset,
    };
  }
  function setLineAttrs(x1, y1, x2, y2) {
    line.setAttribute("x1", x1);
    line.setAttribute("y1", y1);
    line.setAttribute("x2", x2);
    line.setAttribute("y2", y2);
  }

  function textPointFromElement(
    el,
    R,
    { at = "bottom", char = "first", yOffset = 0 } = {},
  ) {
    const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, {
      acceptNode(node) {
        return node.textContent.trim().length
          ? NodeFilter.FILTER_ACCEPT
          : NodeFilter.FILTER_SKIP;
      },
    });

    const textNode = walker.nextNode();
    if (!textNode) {
      return overlayPointFromElement(el, R, {
        at,
        xAlign: "left",
        yOffset,
      });
    }

    const text = textNode.textContent;
    const range = document.createRange();

    if (char === "first") {
      range.setStart(textNode, 0);
      range.setEnd(textNode, 1);
    } else {
      range.setStart(textNode, Math.max(0, text.length - 1));
      range.setEnd(textNode, text.length);
    }

    const rect = range.getBoundingClientRect();
    const x = char === "first" ? rect.left : rect.right;
    const y = at === "bottom" ? rect.bottom : rect.top;

    return {
      x: x - R.layout.left,
      y: y - R.layout.top + yOffset,
    };
  }

  function setInteractiveLine(which) {
    const R = rects();
    const A = dotPixels(d1, R);
    const B = dotPixels(d2, R);

    // unit vector along the map diagonal
    const vx = B.x - A.x;
    const vy = B.y - A.y;
    const L = Math.hypot(vx, vy) || 1;
    const ux = vx / L;
    const uy = vy / L;

    const activeName = document.querySelector(
      `.js-arch-trigger[data-target="${which}"] .architect-name`,
    );
    const otherName = document.querySelector(
      `.js-arch-trigger[data-target="${which === "left" ? "right" : "left"}"] .architect-name`,
    );

    if (!activeName || !otherName) return;

    // Start from the active architect name:
    // - left architect -> use the RIGHT edge of the name
    // - right architect -> use the LEFT edge of the name
    const startP =
      which === "left"
        ? overlayPointFromElement(activeName, R, {
            at: "bottom",
            xAlign: "left",
            yOffset: 2,
          })
        : overlayPointFromElement(activeName, R, {
            at: "bottom",
            xAlign: "right",
            yOffset: 2,
          });

    const endP =
      which === "left"
        ? overlayPointFromElement(otherName, R, {
            at: "bottom",
            xAlign: "right",
            yOffset: 2,
          })
        : overlayPointFromElement(otherName, R, {
            at: "bottom",
            xAlign: "left",
            yOffset: 2,
          });

    // Project both anchor points onto the same diagonal
    const tStart = (startP.x - A.x) * ux + (startP.y - A.y) * uy;
    const tEnd = (endP.x - A.x) * ux + (endP.y - A.y) * uy;

    // Small inset so the line doesn't crash into the names
    const endPad = Math.max(140, R.layout.width * 0.12);
    const t2 = which === "left" ? tEnd - endPad : tEnd + endPad;

    // Safety: if end is too close to start, bail
    const projectedEndX = A.x + ux * t2;
    const projectedEndY = A.y + uy * t2;
    const distance = Math.hypot(
      projectedEndX - startP.x,
      projectedEndY - startP.y,
    );
    if (distance < 20) return;

    // Get the role element (the second line under the name)
    const roleEl = activeName
      .closest(".architect-head")
      ?.querySelector(".architect-role");

    if (!roleEl) return;

    const roleRect = roleEl.getBoundingClientRect();

    const offset = Math.max(6, R.layout.width * 0.003);
    const yLine = roleRect.bottom - R.layout.top + offset;

    const x1 = startP.x;
    const y1 = yLine;

    const x2 = projectedEndX;
    const y2 = yLine;

    const dash = Math.hypot(x2 - x1, y2 - y1);
    setLineAttrs(x1, y1, x2, y2);
    line.style.strokeDasharray = `${dash} ${dash}`;
    line.style.transition = "none";

    // Always reveal from x1 -> x2
    line.style.strokeDashoffset = dash;

    void line.getBoundingClientRect();

    line.style.transition =
      "opacity .1s ease-out .02s, stroke-dashoffset .5s cubic-bezier(.4,0,.2,1) .02s";
    line.style.strokeDashoffset = "0";

    // Place label near the end of the line, a bit before it, and above it
    const endInset = 90; // how far before the end of the line
    const lift = 2; // how much above the line

    const lineLength = Math.hypot(x2 - x1, y2 - y1) || 1;
    const dx = (x2 - x1) / lineLength;
    const dy = (y2 - y1) / lineLength;

    // point near the end, pulled back along the line
    const lx = x2 - dx * endInset;
    const ly = y2 - dy * endInset;

    // always lift upward visually
    label.style.left = `${lx}px`;
    label.style.top = `${ly - lift}px`;
  }

  function show(which) {
    document
      .querySelectorAll(".js-arch-trigger")
      .forEach((t) =>
        t.classList.toggle("is-active", t.dataset.target === which),
      );

    const isLeft = which === "left";
    const src = isLeft ? map.dataset.dot1LabelSrc : map.dataset.dot2LabelSrc;
    const alt = isLeft
      ? map.dataset.dot1LabelAlt || ""
      : map.dataset.dot2LabelAlt || "";
    const fallback = isLeft
      ? map.dataset.dot1Label || ""
      : map.dataset.dot2Label || "";

    // make label renderable
    label.setAttribute("aria-hidden", "false");
    label.style.display = "block";
    label.style.opacity = "0";

    // toggle image vs text
    if (src) {
      labelImg.hidden = false;
      labelText.hidden = true;
      if (labelImg.src !== src) labelImg.src = src;
      labelImg.alt = alt;
    } else {
      labelImg.hidden = true;
      labelText.hidden = false;
      labelText.textContent = fallback;
    }

    map.classList.add("is-hot");
    setInteractiveLine(which);
    line.classList.add("is-visible");

    requestAnimationFrame(() => {
      label.style.opacity = "1";
    });
  }

  function hide() {
    document
      .querySelectorAll(".js-arch-trigger")
      .forEach((t) => t.classList.remove("is-active"));

    map.classList.remove("is-hot");
    line.classList.remove("is-visible");

    label.style.opacity = "0";
    label.setAttribute("aria-hidden", "true");
  }

  // events
  const triggers = document.querySelectorAll(".js-arch-trigger");

  let suppressHover = false;

  function resetInteractiveLine() {
    line.style.transition = "none";
    label.style.transition = "none";

    hide();

    line.classList.remove("is-visible");
    line.style.strokeDashoffset = "";
    line.style.strokeDasharray = "";
    line.setAttribute("x1", "0");
    line.setAttribute("y1", "0");
    line.setAttribute("x2", "0");
    line.setAttribute("y2", "0");

    label.style.opacity = "0";
    label.style.display = "none";
    label.setAttribute("aria-hidden", "true");

    requestAnimationFrame(() => {
      line.style.transition = "";
      label.style.transition = "";
    });
  }

  function onEnter(e) {
    if (suppressHover) return;
    show(e.currentTarget.dataset.target);
  }

  function onLeave() {
    suppressHover = false;
    resetInteractiveLine();
  }

  triggers.forEach((t) => {
    t.addEventListener("mouseenter", onEnter);
    t.addEventListener("focus", onEnter);
    t.addEventListener("mouseleave", onLeave);
    t.addEventListener("blur", onLeave);

    t.addEventListener(
      "click",
      () => {
        suppressHover = true;
        resetInteractiveLine();

        if (document.activeElement) {
          document.activeElement.blur();
        }
      },
      true
    );
  });

  document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "hidden") {
      suppressHover = true;
      resetInteractiveLine();
    }
  });

  window.addEventListener("pageshow", () => {
    suppressHover = false;
    resetInteractiveLine();
  });

  window.addEventListener("focus", () => {
    suppressHover = false;
    resetInteractiveLine();
  });

  // keep alignment crisp on resize
  window.addEventListener("resize", () => {
    if (!line.classList.contains("is-visible")) return;
    const active = document.querySelector(".js-arch-trigger.is-active");
    setInteractiveLine(active ? active.dataset.target : "left");
  });

  mq.addEventListener("change", (e) => {
    if (!e.matches) hide();
  });
})();

(function () {
  if (window.__copyTipsInit) return;
  window.__copyTipsInit = true;

  const items = document.querySelectorAll(".js-copy");
  if (!items.length) return;

  items.forEach((el) => {
    if (el.dataset.copyInit) return;
    el.dataset.copyInit = "1";

    // 1) Neutralize navigation so the OS doesn’t open mail/phone apps
    const href = el.getAttribute("href");
    if (href) {
      el.dataset.href = href; // keep it, just in case you need it later
      el.removeAttribute("href"); // remove to fully disable navigation
    }
    el.setAttribute("role", "button");
    el.setAttribute("tabindex", "0"); // keep keyboard accessibility

    // 2) Click handler
    el.addEventListener("click", (e) => {
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return; // allow modifiers if you ever restore href
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      const text = (el.dataset.copy || el.textContent || "").trim();
      copy(text)
        .then(() => flashTip(el, "Copied"))
        .catch(() => flashTip(el, "Failed"));
      return false; // belt & suspenders
    });

    // 3) Keyboard: Enter / Space
    el.addEventListener("keydown", (e) => {
      const isEnter = e.key === "Enter" || e.keyCode === 13;
      const isSpace = e.key === " " || e.key === "Spacebar" || e.keyCode === 32;
      if (!isEnter && !isSpace) return;

      e.preventDefault();
      e.stopPropagation();
      const text = (el.dataset.copy || el.textContent || "").trim();
      copy(text)
        .then(() => flashTip(el, "Copied"))
        .catch(() => flashTip(el, "Failed"));
    });
  });

  function copy(text) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    }
    // Fallback (non-HTTPS/older browsers)
    return new Promise((resolve, reject) => {
      const ta = document.createElement("textarea");
      ta.value = text;
      ta.style.position = "fixed";
      ta.style.opacity = "0";
      document.body.appendChild(ta);
      ta.select();
      try {
        document.execCommand("copy") ? resolve() : reject();
      } catch (err) {
        reject(err);
      } finally {
        document.body.removeChild(ta);
      }
    });
  }

  function flashTip(el, msg) {
    let tip = el.querySelector(".copy-tip");
    if (!tip) {
      tip = document.createElement("span");
      tip.className = "copy-tip";
      el.appendChild(tip);
    }
    tip.textContent = msg;
    tip.classList.add("is-visible");

    clearTimeout(el._tipTimer);
    el._tipTimer = setTimeout(() => {
      tip.classList.remove("is-visible");
    }, 1200);
  }
})();
