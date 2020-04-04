(function (wp) {
	let el = wp.element.createElement;
	let __ = wp.i18n.__;

	let registerBlockType = wp.blocks.registerBlockType;
	let ServerSideRender = wp.serverSideRender;
	let TextControl = wp.components.TextControl;
	let TextareaControl = wp.components.TextareaControl;
	let InspectorControls = wp.blockEditor.InspectorControls;
	let icon = el("img", {
		src: dmck_audioplayer.site_url + "/wp-admin/images/generic.png",
		width: "50px",
		height: "50px"
	});
	registerBlockType('audio-player-cbhdmk/dmck-player-interface', {
		title: __('DMCK Player Interface', 'audio-player-cbhdmk'),
		icon: icon,
		attributes: {
			'mb_title': {
				type: 'string',
				default: "mb Editor content block"
			},
			'mb_text': {
				type: 'string',
				default: "Write here some text"
			},

			'mb_url': {
				type: 'string',
				default: "https://pupunzi.com"
			},
		},
		category: 'widgets',
		supports: {
			// Removes support for an HTML mode.
			html: false,
		},
		/**
		 * The edit function describes the structure of your block in the context of the editor.
		 * This represents what the editor will render when the block is used.
		 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#edit
		 *
		 * @param {Object} [props] Properties passed from the editor.
		 * @return {Element}       Element to render.
		 */
		edit: function (props) {

			if (props.isSelected) {
				// do something...
				//console.debug(props.attributes);
			};
			return [
				/**
				 * Server side render
				 */
				el("div", {
						className: props.className,
						style: {
							// textAlign: "center"
						}
					},
					el(ServerSideRender, {
						block: 'audio-player-cbhdmk/dmck-player-interface',
						attributes: props.attributes
					})
				),

				/**
				 * Inspector
				 */
				el(InspectorControls, {}, [
					el("hr", {
						style: {
							marginTop: 20
						}
					}),

					el(TextControl, {
						label: 'Title',
						value: props.attributes.mb_title,
						onChange: (value) => {
							props.setAttributes({
								mb_title: value
							});
						}
					}),

					el(TextareaControl, {
						style: {
							height: 250
						},
						label: 'Content',
						value: props.attributes.mb_text,
						onChange: (value) => {
							props.setAttributes({
								mb_text: value
							});
							console.debug(props.attributes)
						}
					}, props.attributes.mb_text),

					el(TextControl, {
						label: 'Url',
						value: props.attributes.mb_url,
						onChange: (value) => {
							props.setAttributes({
								mb_url: value
							});
						}
					}),
				])
			]
		},

		/**
		 * The save function defines the way in which the different attributes should be combined
		 * into the final markup, which is then serialized by Gutenberg into `post_content`.
		 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#save
		 *
		 * @return {Element}       Element to render.
		 */
		save: function () {
			return null;
			// return wp.element.RawHTML( {
			// 	children: player
			// } );				

			// return el(
			// 	'p',
			// 	{},
			// 	__( 'Hello from the saved content!', 'audio-player-cbhdmk' )
			// );

		}
	});
})(
	window.wp
);