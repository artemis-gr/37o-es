(function (wp) {
  if (!wp || !wp.hooks || !wp.compose || !wp.element) return;

  var addFilter = wp.hooks.addFilter;
  var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
  var el = wp.element.createElement;

  // HOC: ensure core/spacer has a default style class (string)
  var withSpacerDefaultStyle = createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
      if (props.name !== 'core/spacer') {
        return el(BlockEdit, props);
      }

      var cls = props.attributes.className || '';

      // If no style class yet, add our small gap style
      if (cls.indexOf('is-style-gap-small') === -1 &&
          cls.indexOf('is-style-gap-medium') === -1) {
        props.setAttributes({
          className: (cls + ' is-style-gap-small').replace(/\s+/g, ' ').trim()
        });
      }

      // Do NOT touch height or style here
      return el(BlockEdit, props);
    };
  }, 'withSpacerDefaultStyle');

  addFilter('editor.BlockEdit', 'theme/with-spacer-default-style', withSpacerDefaultStyle);
})(window.wp);
