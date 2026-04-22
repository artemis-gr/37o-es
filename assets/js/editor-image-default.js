( function( wp ) {
	if ( !wp || !wp.data || !wp.domReady ) return;

	const STYLE_CLASS = 'is-style-proj-img-small';
	const { select, dispatch, subscribe } = wp.data;

	function ensureSmallStyle( block ) {
		if ( !block || block.name !== 'core/image' ) return;

		// If no className, or it has no is-style-*, set Small
		const cls = block.attributes.className || '';
		if ( !/(\s|^)is-style-/.test( cls ) ) {
			const next = (cls ? (cls + ' ') : '') + STYLE_CLASS;
			dispatch( 'core/block-editor' ).updateBlockAttributes( block.clientId, {
				className: next
			} );
		}
	}

	// Apply when a block becomes selected
	subscribe( function() {
		const b = select( 'core/block-editor' ).getSelectedBlock();
		ensureSmallStyle( b );
	} );

	// Also sweep all blocks on editor ready (new / existing content)
	wp.domReady( function() {
		const blocks = select( 'core/block-editor' ).getBlocks();
		blocks.forEach( ensureSmallStyle );
	} );
} )( window.wp );
