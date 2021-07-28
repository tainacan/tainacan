export default {  
	// AttachmentControl: requires upload of new files and accepts multiple files
    attachmentControl: wp.customize.MediaControl.extend({

		/**
		 * Set up gallery toolbar.
		 *
		 * @return {void}
		 */
		galleryToolbar() {
			this.toolbar.set(
				new wp.media.view.Toolbar( {
					controller: this,
					items: {
						insert: {
							style: 'primary',
							text: wp.media.view.l10n.update,
							priority: 80,
							requires: { library: true },

							/**
							 * @fires wp.media.controller.State#select
							 */
							click() {
								const controller = this.controller,
									state = controller.state();
								
								controller.close();
								state.trigger(
									'select',
									state.get( 'library' )
								);

								// Restore and reset the default state.
								controller.setState( controller.options.state );
								controller.reset();
							},
						},
					},
				} )
			);
		},

        /**
		 * Create a media modal select frame, and store it so the instance can be reused when needed.
		 */
		initFrame: function() {

			wp.media.view.settings.post = {
				id: parseInt(this.params.relatedPostId),
				wp_customize: 'off',
			}
			wp.media.model.settings.post.nonce = this.params.nonce;
			wp.media.model.settings.post.id = parseInt(this.params.relatedPostId);
			
			this.frame = wp.media({
				states: [
					new wp.media.controller.Library({
						title: this.params.button_labels.frame_title,
						library: wp.media.query({
							uploadedTo: wp.media.view.settings.post.id,
							orderby: 'menuOrder',
							order: 'ASC',
	 						posts_per_page: -1,
			 				query: true
						}),
						toolbar: 'main-gallery',
						autoSelect: true,
						sortable: true,
						filterable: 'unattached',
					})
				]
			});

			this.frame.on( 'toolbar:create:main-gallery', this.galleryToolbar, this.frame );

			this.frame.$el.addClass( 'tainacan-item-attachments-modal' );
			this.frame.$el['tainacan-document-id'] = this.params.document;
			this.frame.$el['tainacan-thumbnail-id'] = this.params.thumbnailId;

			wp.media.view.Attachment.Library = wp.media.view.Attachment.Library.extend({
                className: function() { 
					return 'attachment ' + 
						((this.controller.$el['tainacan-document-id'] && (this.model.get('id') == this.controller.$el['tainacan-document-id'])) ? 'tainacan-document-attachment ' : ' ') + 
						((this.controller.$el['tainacan-thumbnail-id'] && (this.model.get('id') == this.controller.$el['tainacan-thumbnail-id'])) ? 'tainacan-thumbnail-attachment ' : ' '); 
				}
            });

			this.frame.on( 'select', () => {
                 // Get the attachment from the modal frame.
                var attachments = this.frame.state().get( 'selection' ).toJSON();

				wp.media.view.settings.post.id = {
					id: this.params.relatedPostId
				}
					
                this.params.attachments = attachments;
				this.params.onSave(attachments);
			});
		}
	}),	

	// CroppedImageControl, with presets for thumbnail dimensions
	thumbnailControl: wp.customize.CroppedImageControl.extend({

		initFrame: function() {

			var l10n = wp.media.view.l10n = typeof _wpMediaViewsL10n === 'undefined' ? {} : _wpMediaViewsL10n;

			// Same of WordPress wp.media.controller.CustomizeImageCropper, but without `wp_customize: on`
			var customImageCropper = wp.media.controller.Cropper.extend({

				doCrop: function( attachment ) {
					var cropDetails = attachment.get( 'cropDetails' ),
						control = this.get( 'control' ),
						ratio = cropDetails.width / cropDetails.height;
			
					// Use crop measurements when flexible in both directions.
					if ( control.params.flex_width && control.params.flex_height ) {
						cropDetails.dst_width  = cropDetails.width;
						cropDetails.dst_height = cropDetails.height;
			
					// Constrain flexible side based on image ratio and size of the fixed side.
					} else {
						cropDetails.dst_width  = control.params.flex_width  ? control.params.height * ratio : control.params.width;
						cropDetails.dst_height = control.params.flex_height ? control.params.width  / ratio : control.params.height;
					}
			
					return wp.ajax.post( 'crop-image', {
						wp_customize: 'off',
						nonce: attachment.get( 'nonces' ).edit,
						id: attachment.get( 'id' ),
						context: control.id,
						cropDetails: cropDetails
					});
				}
			});

			wp.media.view.settings.post = {
				id: null
			}

			this.params.flex_width = 1;
			this.params.flex_height = 1;
			this.params.width = 220;
			this.params.height = 220;

			this.frame = wp.media({
				frame: 'select',
				button: {
					text: l10n.select,
					close: false
				},
				uploader: true,
				content: 'upload', // First view that is opened
				autoSelect: true,
				states: [
					new wp.media.controller.Library({
						title: this.params.button_labels.frame_title,
						library: wp.media.query({ 
							type: 'image', 
						}),
						multiple: false,
						autoSelect: true,
						date: false,
						priority: 20,
						suggestedWidth: this.params.width,
						suggestedHeight: this.params.height,
						uploadedTo: this.params.relatedPostId
					}),
					new customImageCropper({
						imgSelectOptions: this.calculateImageSelectOptions,
						control: this
					})
				]
			});
			this.frame.$el.addClass( 'tainacan-thumbnail-modal' );
			this.frame.on( 'select', this.onSelect, this );
			this.frame.on( 'cropped', this.onCropped, this );
			this.frame.on( 'skippedcrop', this.onSkippedCrop, this );
		},
		// Called on both skippedcrop and cropped states
		setImageFromAttachment: function( attachment ) {
			wp.media.view.settings.post.id = {
				id: this.params.relatedPostId
			}
			this.params.attachments = attachment;
			this.params.onSave(attachment);
		}

	}),

	// CroppedImageControl, with presets for header dimensions
	headerImageControl: wp.customize.CroppedImageControl.extend({
		
		initFrame: function() {

			var l10n = wp.media.view.l10n = typeof _wpMediaViewsL10n === 'undefined' ? {} : _wpMediaViewsL10n;
			
			// Same of WordPress wp.media.controller.CustomizeImageCropper, but without `wp_customize: on`
			var customImageCropper = wp.media.controller.Cropper.extend({

				doCrop: function( attachment ) {
					var cropDetails = attachment.get( 'cropDetails' ),
						control = this.get( 'control' ),
						ratio = cropDetails.width / cropDetails.height;
			
					// Use crop measurements when flexible in both directions.
					if ( control.params.flex_width && control.params.flex_height ) {
						cropDetails.dst_width  = cropDetails.width;
						cropDetails.dst_height = cropDetails.height;
			
					// Constrain flexible side based on image ratio and size of the fixed side.
					} else {
						cropDetails.dst_width  = control.params.flex_width  ? control.params.height * ratio : control.params.width;
						cropDetails.dst_height = control.params.flex_height ? control.params.width  / ratio : control.params.height;
					}
			
					return wp.ajax.post( 'crop-image', {
						wp_customize: 'off',
						nonce: attachment.get( 'nonces' ).edit,
						id: attachment.get( 'id' ),
						context: control.id,
						cropDetails: cropDetails
					});
				}
			});

			wp.media.view.settings.post = {
                id: null
			}
			
			if (tainacan_plugin.custom_header_support[0] != undefined) {
				this.params.flex_width = tainacan_plugin.custom_header_support[0].flex_width ? 1 : 0;
				this.params.flex_height = tainacan_plugin.custom_header_support[0].flex_height ? 1 : 0;
				this.params.width = tainacan_plugin.custom_header_support[0].width;
				this.params.height = tainacan_plugin.custom_header_support[0].height;
			} else {
				this.params.flex_width = true;
				this.params.flex_height = true;
				this.params.width = 2000;
				this.params.height = 625;
			}

			this.frame = wp.media({
				frame: 'select',
				button: {
					text: l10n.select,
					close: false
				},
				library: wp.media.query({ 
					type: 'image',
					uploadedTo: null
				}),
				uploader: true,
				content: 'upload', // First view that is opened
				states: [
					new wp.media.controller.Library({
						title: this.params.button_labels.frame_title,
						library: wp.media.query({ 
							type: 'image',
							uploadedTo: null
						}),
						multiple: false,
						date: false,
						priority: 20,
						suggestedWidth: this.params.width,
						suggestedHeight: this.params.height
					}),
					new customImageCropper({
						imgSelectOptions: this.calculateImageSelectOptions,
						control: this
					})
				]
			});

			//this.frame.state('cropper').set( 'canSkipCrop', true );
			this.frame.$el.addClass( 'tainacan-header-image-modal' );
			this.frame.on( 'select', this.onSelect, this );
			this.frame.on( 'cropped', this.onCropped, this );
			this.frame.on( 'skippedcrop', this.onSkippedCrop, this );
		},
		// Called on both skippedcrop and cropped states
		setImageFromAttachment: function( attachment ) {
			this.params.attachment = attachment;
			this.params.onSave(attachment);
		}

	}),

	// DocumentFileControl: similar to attachment, but used for items where the documents is of type file
	documentFileControl: wp.customize.MediaControl.extend({
        /**
		 * Create a media modal select frame, and store it so the instance can be reused when needed.
		 */
		initFrame: function() {

			wp.media.view.settings.post = {
				id: parseInt(this.params.relatedPostId),
				wp_customize: 'off'
			}
			wp.media.model.settings.post.nonce = this.params.nonce;
			wp.media.model.settings.post.id = parseInt(this.params.relatedPostId);

			this.frame = wp.media({
				button: {
					text: this.params.button_labels.frame_button
				},
				content: 'upload', // First view that is opened
				states: [
					new wp.media.controller.Library({
						title:     this.params.button_labels.frame_title,
						library: wp.media.query({
							uploadedTo: wp.media.view.settings.post.id,
							orderby: 'menuOrder',
							order: 'ASC',
	 						posts_per_page: -1,
			 				query: true
						}),
						autoSelect: true,
						multiple:  false,
						date:      false,
						sortable: true,
						filterable: 'unattached',
						uploadedTo: this.params.relatedPostId
					})
				]
			});
			this.frame.$el.addClass( 'tainacan-document-modal' );
			// When a file is selected, run a callback.
			this.frame.on( 'select', () => {
				
                 // Get the attachment from the modal frame.
                var node,
                attachment,
				mejsSettings = window._wpmejsSettings || {};
				attachment = this.frame.state().get( 'selection' ).first().toJSON();

                this.params.attachment = attachment;
				this.params.onSave(attachment);
                // Set the Customizer setting; the callback takes care of rendering.
                //this.setting( attachment.id );
                node = this.container.find( 'audio, video' ).get(0);

                // Initialize audio/video previews.
                if ( node ) {
                    this.player = new MediaElementPlayer( node, mejsSettings );
                } else {
                    this.cleanupPlayer();
                }
            });
		}
    }),
}