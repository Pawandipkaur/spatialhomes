( function ( $ ) {
	TVE.ResetStatsModal = require( './modals/reset-stats' );
	var ThriveAB = {
		domtoimage: null,
		selector: 'body',
		iframe_srcs: [],
		init: function () {
			var self = this;
			if ( TVE.CONST.ajax.thrive_ab.running_test ) {
				TVE.add_filter( 'validate_saved_content', $.proxy( this.validate_saved_content_filter, this ) );
			} else {
				TVE.main.on( 'tve.tve_save_post', $.proxy( this.on_save, this ) );
			}

			/* disable "CREATE NEW A/B TEST" button when an element enters edit mode */
			TVE.add_filter( 'tcb.edit_mode.disabled_buttons', function ( $buttons ) {
				return $buttons.add( TVE.main.$( '#thrive-ab-create-test' ) );
			} );
		},
		validate_saved_content_filter: function ( valid, callback ) {
			var self = this;
			self.save_callback = callback;
			if ( typeof TVE.CONST.reset_stats === 'undefined' ) {

				var ResetStatsModal = TVE.ResetStatsModal.get_instance( TVE.modal.get_element( 'reset-stats' ) );

				ResetStatsModal.open( {
					top: '20%'
				} );
				delete TVE.KEEP_OVERLAY;
				TVE.main.overlay( 'close' );
				ResetStatsModal.on( 'reset_stats', self.save_with_reset_stats, self );

				return false;
			}

			if ( ! TVE.CONST.reset_stats ) {
				delete TVE.CONST.reset_stats;
			}

			return true;
		},
		save_with_reset_stats: function ( running_test ) {
			TVE.main.overlay();
			TVE.CONST.reset_stats = running_test;
			this.on_save();
		},

		on_save: function () {
			this.get_image_source( _.bind( this.save, this ) );
		},


		get_image_source: function ( _then_callback ) {

			if ( ! this.domtoimage ) {
				return null;
			}

			var $element = TVE.inner_$( this.selector );

			if ( ! $element.length ) {
				return null;
			}

			$element.find( '#fb-root' ).remove();
			$element.find( 'img' ).removeAttr( 'srcset' );

			/**
			 * For the yt videos there are some params which prevent generating the preview image
			 */
			$element.find( '.tcb-yt-bg' ).each( _.bind( function ( index, iframe ) {
				this.iframe_srcs[ iframe.getAttribute( 'id' ) ] = iframe.getAttribute( 'src' );
				iframe.removeAttribute( 'src' );
			}, this ) );

			this.domtoimage.toBlob( $element[ 0 ], {
				bgcolor: 'white',
				style: {
					padding: 0,
					margin: 0,
					outline: 'none',
					'overflow-y': 'hidden'
				},
				width: $element.width(),
				height: 1000
			} )
			    .then( typeof _then_callback === 'function' ? _then_callback : function ( data_source ) {
				    console.log( 'no data callback/promise provided for image source' );
			    } )
			    .catch( function () {
				    console.log( 'ops... something went wrong when getting image source' );
				    console.log( arguments );
				    _then_callback();
			    } );
		},

		save: function ( data_source ) {
			var $element = TVE.inner_$( this.selector );

			$element.find( '.tcb-yt-bg' ).each( _.bind( function ( index, iframe ) {
				iframe.setAttribute( 'src', this.iframe_srcs[ iframe.getAttribute( 'id' ) ] );
			}, this ) );

			var form = new FormData();

			if ( data_source ) {
				form.append( 'preview_file', data_source, TVE.CONST.post_id + '.png' );
			}

			form.append( 'custom', 'save_variation_thumb' );
			form.append( 'action', TVE.CONST.ajax.thrive_ab.action );
			form.append( 'post_id', TVE.CONST.post_id );

			if ( typeof TVE.CONST.reset_stats !== 'undefined' ) {
				form.append( 'reset_data', TVE.CONST.reset_stats );
			}

			$.ajax( {
				type: 'POST',
				url: TVE.CONST.ajax_url,
				data: form,
				processData: false,
				contentType: false,
				always: function () {
					$( '#tcb-template-clone-elem' ).remove();
				}
			} );

			if ( typeof TVE.CONST.reset_stats !== 'undefined' ) {
				TVE.main.editor_settings.save( null, null, this.save_callback );
			}
		}
	};

	function check_editor() {

		if ( TVE.inner === undefined ) {
			return setTimeout( check_editor, 100 );
		}

		ThriveAB.domtoimage = TVE.inner.window.domtoimage;
		ThriveAB.init();
	}

	check_editor();

} )( jQuery );
