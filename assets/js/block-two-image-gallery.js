/* 2-Image Gallery — custom block that always has exactly 2 images */
(function () {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { InnerBlocks, InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
  const { PanelBody, RadioControl, Notice } = wp.components;
  const { createElement: el } = wp.element;

  const ALLOWED = ['core/image'];
  const TEMPLATE = [
    ['core/image', {}],
    ['core/image', {}],
  ];

  registerBlockType('thirtyseven/two-image-gallery', {
    apiVersion: 3,
    title: __('2-Image Gallery', '37o-es'),
    description: __('Exactly two images side-by-side (Left / Right).', '37o-es'),
    icon: 'images-alt2',
    category: 'media',
    supports: {
      className: true,
      align: false,
      html: false,
      inserter: true,
    },
    attributes: {
      aspect: { type: 'string', default: 'vertical' }, // 'vertical' | 'square'
    },

    edit({ attributes, setAttributes }) {
      const aspectClass = attributes.aspect === 'square' ? 'is-ar-square' : 'is-ar-vertical';
      const blockProps = useBlockProps({ className: `two-image-gallery-editor ${aspectClass}` });

      return el(
        'div',
        blockProps,
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: __('Layout', '37o-es'), initialOpen: true },
            el(RadioControl, {
              label: __('Aspect', '37o-es'),
              selected: attributes.aspect,
              options: [
                { label: __('Vertical (2:3)', '37o-es'), value: 'vertical' },
                { label: __('Square (1:1)', '37o-es'),   value: 'square' },
              ],
              onChange: (val) => setAttributes({ aspect: val }),
            }),
          ),
          el(
            PanelBody,
            { title: __('Info', '37o-es'), initialOpen: false },
            el(Notice, { status: 'info', isDismissible: false },
              __('Tip: To avoid cropping, upload images already matching the chosen aspect.', '37o-es')
            )
          )
        ),
        el(
          'div',
          { className: 'two-image-gallery-grid' },
          el(InnerBlocks, {
            allowedBlocks: ALLOWED,
            template: TEMPLATE,
            templateLock: 'all',
            renderAppender: false,
          })
        )
      );
    },

    save({ attributes }) {
      const aspectClass = attributes.aspect === 'square' ? 'is-ar-square' : 'is-ar-vertical';
      const blockProps = (useBlockProps.save || wp.blockEditor.useBlockProps.save)({
        className: `two-image-gallery ${aspectClass}`,
      });

      return wp.element.createElement(
        'div',
        blockProps,
        wp.element.createElement(InnerBlocks.Content, null)
      );
    },
  });
})();
