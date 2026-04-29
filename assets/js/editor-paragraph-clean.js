( function( wp ) {
  const { addFilter } = wp.hooks;
  const { assign } = lodash;

  // 1) Tighten supports for core/paragraph at registration time
  addFilter(
    'blocks.registerBlockType',
    'theme/paragraph-supports',
    ( settings, name ) => {
      if ( name !== 'core/paragraph' ) return settings;

      settings.supports = assign( {}, settings.supports, {
        // Remove text alignment UI
        align: false,             // paragraph text alignment (left/center/right)
        textAlign: false,         // belt & braces for older builds

        // Typography: keep only fontSize
        typography: assign( {}, settings.supports?.typography, {
          fontSize: true,
          lineHeight: false,
          __experimentalFontStyle: false,
          __experimentalFontWeight: false,
          __experimentalTextTransform: false,
          __experimentalLetterSpacing: false,
          __experimentalDefaultControls: { fontSize: true }
        })
      });

      // Default text-align to center (attribute is "align")
      settings.attributes = assign( {}, settings.attributes, {
        align:    { type: 'string', default: 'center' },
        fontSize: { type: 'string', default: 'proj-normal' }
      });

      return settings;
    }
  );

  // 2) Runtime hardening + limit font sizes to just two presets
  wp.domReady( () => {
    // Remove alignment supports in case another plugin re-enables them
    try { wp.blocks.unregisterBlockSupport( 'core/paragraph', 'align' ); } catch(e){}
    try { wp.blocks.unregisterBlockSupport( 'core/paragraph', 'textAlign' ); } catch(e){}

    // Remove Bold/Italic formats globally (safe in your setup)
    //try { wp.richText.unregisterFormatType('core/bold'); } catch(e){}
    //try { wp.richText.unregisterFormatType('core/italic'); } catch(e){}

    // Optional CSS fallback: hide stray alignment UI (left/right/justify)
    const style = document.createElement('style');
    style.textContent = `
      .block-editor-block-toolbar [aria-label="Align text left"],
      .block-editor-block-toolbar [aria-label="Align text right"],
      .block-editor-block-toolbar [aria-label="Align text justify"],
      .components-popover .components-menu-item__button[aria-label="Align text left"],
      .components-popover .components-menu-item__button[aria-label="Align text right"],
      .components-popover .components-menu-item__button[aria-label="Align text justify"]{
        display: none !important;
      }
    `;
    document.head.appendChild(style);

    // Lock the Typography size presets to just Normal/Small
    try {
      const { select, dispatch } = wp.data;
      const store = 'core/block-editor';
      const current = select(store).getSettings();
      const onlyTwo = [
        { name: 'Medium', slug: 'proj-normal', size: '1.3rem' },
        { name: 'Small',  slug: 'proj-small',  size: '1.05rem' },
      ];
      dispatch(store).updateSettings({
        ...current,
        fontSizes: onlyTwo,
        disableCustomFontSizes: true,
      });
    } catch (e) {}
  } );

} )( window.wp );

// === Paragraph toolbar: S / M font-size buttons ===
(function (wp) {
  const { createElement: el, Fragment } = wp.element;
  const blockEditor = wp.blockEditor || wp.editor; // compat
  const { BlockControls } = blockEditor;
  const { ToolbarGroup, ToolbarButton } = wp.components;
  const { createHigherOrderComponent } = wp.compose;

  const ParagraphFontsizeToolbar = createHigherOrderComponent(
    (BlockEdit) => (props) => {
      if (props.name !== 'core/paragraph') return el(BlockEdit, props);

      const { attributes, setAttributes } = props;
      const current = attributes.fontSize; // slug when using presets
      const isSmall = current === 'proj-small';
      const isNormal = current === 'proj-normal' || !current; // default

      return el(
        Fragment,
        null,
        el(
          BlockControls,
          { group: 'inline' }, // inline row near text controls
          el(
            ToolbarGroup,
            null,
            el(
              ToolbarButton,
              {
                isPressed: isSmall,
                onClick: () => setAttributes({ fontSize: 'proj-small' }),
                'aria-label': 'Font size: Small',
              },
              'S'
            ),
            el(
              ToolbarButton,
              {
                isPressed: isNormal,
                onClick: () => setAttributes({ fontSize: 'proj-normal' }),
                'aria-label': 'Font size: Normal',
              },
              'M'
            )
          )
        ),
        el(BlockEdit, props)
      );
    },
    'ParagraphFontsizeToolbar'
  );

  wp.hooks.addFilter(
    'editor.BlockEdit',
    'theme/paragraph-fontsize-toolbar',
    ParagraphFontsizeToolbar
  );
})(window.wp);

