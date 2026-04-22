(function (wp) {
  const { addFilter } = wp.hooks;

  // --- keep: remove Gallery transforms helper ---
  function pruneGalleryTransforms(settings) {
    if (!settings.transforms) return settings;
    ['from','to'].forEach((dir) => {
      const list = settings.transforms[dir];
      if (Array.isArray(list)) {
        settings.transforms[dir] = list.filter((tr) => {
          if (tr.type !== 'block') return true;
          const b = tr.blocks ?? tr.block ?? [];
          const arr = Array.isArray(b) ? b : [b];
          return !arr.includes('core/gallery');
        });
      }
    });
    return settings;
  }

  // --- keep: rewrite Image block styles (Small/Medium/Large) ---
  addFilter(
    'blocks.registerBlockType',
    'thirtyseven/imageStyles',
    function (settings, name) {
      if (name !== 'core/image') return settings;

      settings.styles = [
        { name: 'proj-img-small',  label: 'Small',  isDefault: true },
        { name: 'proj-img-medium', label: 'Medium' },
        { name: 'proj-img-large',  label: 'Large'  },
      ];

      return pruneGalleryTransforms(settings);
    }
  );

  // === NEW: ensure "Small" is applied to any image with no style ===
  const { select, dispatch, subscribe } = wp.data;

  function hasOneOfOurStyles(cls = '') {
    return /(?:^|\s)is-style-proj-img-(?:small|medium|large)(?:\s|$)/.test(cls);
  }

  function ensureSmallStyle(block) {
    if (!block || block.name !== 'core/image') return;
    const cls = block.attributes.className || '';
    if (!hasOneOfOurStyles(cls)) {
      const newCls = (cls ? cls + ' ' : '') + 'is-style-proj-img-small';
      dispatch('core/block-editor').updateBlockAttributes(block.clientId, { className: newCls });
    }
  }

  // Run on newly inserted images, and also when selecting one without a style
  let seen = new Set();
  subscribe(() => {
    const blocks = select('core/block-editor').getBlocks();

    // Newly inserted blocks
    blocks.forEach((b) => {
      if (!seen.has(b.clientId)) {
        seen.add(b.clientId);
        ensureSmallStyle(b);
      }
    });

    // If user selects an image that lacks a style, fix it too
    const sel = select('core/block-editor').getSelectedBlock();
    ensureSmallStyle(sel);
  });
})(window.wp);
