/* Multi-Image Gallery — flexible block for 3 to 5 images */
(function () {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { InnerBlocks, InspectorControls, useBlockProps } =
    wp.blockEditor || wp.editor;
  const { PanelBody, Notice } = wp.components;
  const { createElement: el } = wp.element;

  const ALLOWED = ['core/image'];

  const TEMPLATE = [
    ['core/image', {}],
    ['core/image', {}],
    ['core/image', {}],
  ];

  registerBlockType('thirtyseven/multi-image-gallery', {
    apiVersion: 3,
    title: __('Multi-Image Gallery', '37o-es'),
    description: __('Flexible gallery for 3 to 5 images.', '37o-es'),
    icon: 'format-gallery',
    category: 'media',

    supports: {
      className: true,
      align: false,
      html: false,
      inserter: true,
    },

    edit() {
      const blockProps = useBlockProps({
        className: 'multi-image-gallery-editor',
      });

      return el(
        'div',
        blockProps,

        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: __('Info', '37o-es'), initialOpen: true },
            el(
              Notice,
              { status: 'info', isDismissible: false },
              __('Use 3 to 5 images. On desktop they fit inside the page width; on mobile they stack vertically.', '37o-es')
            )
          )
        ),

        el(
          'div',
          { className: 'multi-image-gallery-grid' },
          el(InnerBlocks, {
            allowedBlocks: ALLOWED,
            template: TEMPLATE,
            templateLock: false,
            renderAppender: InnerBlocks.ButtonBlockAppender,
          })
        )
      );
    },

    save() {
      const blockProps = useBlockProps.save({
        className: 'multi-image-gallery',
      });

      return el(
        'div',
        blockProps,
        el(InnerBlocks.Content, null)
      );
    },
  });
})();