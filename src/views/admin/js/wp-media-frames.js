export default {
	customAttachmentsControl: class customAttachmentsControl {

		constructor(props) {
			this.props = props;

			wp.media.view.settings.post = {
				id: this.props.relatedPostId,
				wp_customize: 'off'
			}

			this.buildAndSetGalleryFrame();
		}

		getGalleryDetailsMediaFrame() {
			/**
			 * Custom gallery details frame.
			 *
			 * @see https://github.com/xwp/wp-core-media-widgets/blob/905edbccfc2a623b73a93dac803c5335519d7837/wp-admin/js/widgets/media-gallery-widget.js
			 * @class GalleryDetailsMediaFrame
			 * @class
			 */
			return wp.media.view.MediaFrame.Post.extend( {
				/**
				 * Set up gallery toolbar.
				 *
				 * @return {void}
				 */
				galleryToolbar() {
					const editing = this.state().get( 'editing' );
					this.toolbar.set(
						new wp.media.view.Toolbar( {
							controller: this,
							items: {
								insert: {
									style: 'primary',
									text: editing
										? wp.media.view.l10n.updateGallery
										: wp.media.view.l10n.insertGallery,
									priority: 80,
									requires: { library: true },
		
									/**
									 * @fires wp.media.controller.State#update
									 */
									click() {
										const controller = this.controller,
											state = controller.state();
										
										controller.close();
										state.trigger(
											'update',
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
				 * Create the default states.
				 *
				 * @return {void}
				 */
				createStates: function createStates() {
					this.on( 'toolbar:create:main-gallery', this.galleryToolbar, this );
		
					this.states.add( [
						new wp.media.controller.GalleryEdit( {
							library: this.options.selection,
							editing: this.options.editing,
							menu: 'gallery',
							displaySettings: false,
							multiple: true,
							filterable: false,
							searchable: false
						} ),
						new wp.media.controller.GalleryAdd({
							library: wp.media.query( {
								uploadedTo: wp.media.view.settings.post.id,
								orderby: 'menuOrder',
								order: 'DESC',
								posts_per_page: -1,
								query: true
							} ),
							multiple: true,
							filterable: false,
							searchable: false
						}),
					] );
				},
			} );
		};

		getAttachmentsCollection() {
			return wp.media.query( {
				uploadedTo: wp.media.view.settings.post.id,
				orderby: 'menuOrder',
				order: 'DESC',
				posts_per_page: -1,
				query: true
			} );
		};

		/**
		 * Sets the Gallery frame and initializes listeners.
		 *
		 * @return {void}
		 */
		buildAndSetGalleryFrame() {

			// If a frame already existed remove it.
			if ( this.frame )
				this.frame.remove();
			
			if ( ! this.GalleryDetailsMediaFrame )
				this.GalleryDetailsMediaFrame = this.getGalleryDetailsMediaFrame();
			
			const attachments = this.getAttachmentsCollection( );
			const selection = new wp.media.model.Selection( attachments.models, {
				props: attachments.props.toJSON(),
				multiple: true,
			} );

			this.frame = new this.GalleryDetailsMediaFrame( {
				state: this.props.existingAttachments && this.props.existingAttachments.length ? 'gallery-edit' : 'gallery-library',
				multiple: true,
				selection: selection,
				editing: this.props.existingAttachments && this.props.existingAttachments.length ? true : false,
			} );
			wp.media.frame = this.frame;

			this.frame.on( 'select', (selected) => this.onSelect(selected));
			this.frame.on( 'update', (selected) => this.onUpdate(selected));
			this.frame.on( 'onclose', () => this.onClose() );
		}

		onUpdate( selections ) {
			const state = this.frame.state();
			const selectedImages = selections || state.get( 'selection' );

			if ( ! selectedImages || ! selectedImages.models.length )
				return;

			this.props.onSelect( selectedImages.models.map( ( model ) => model.toJSON() ));
		}

		onSelect() {
			// Get media attachment details from the frame state
			const attachments = this.frame.state().get( 'selection' ).toJSON();
			this.props.onSelect( attachments );
		}

		onClose() {
			const { onClose } = this.props;
	
			if ( onClose )
				onClose();
		}

		openModal() {
			this.buildAndSetGalleryFrame();
			
			const frameContent = this.frame.content.get();
			if ( frameContent && frameContent.collection ) {
				const collection = frameContent.collection;

				// clean all attachments we have in memory.
				collection
					.toArray()
					.forEach( ( model ) => model.trigger( 'destroy', model ) );

				// reset has more flag, if library had small amount of items all items may have been loaded before.
				collection.mirroring._hasMore = true;

				// request items
				collection.more();
			}
			// load the images so they are available in the media modal.
			this.getAttachmentsCollection().more();

			this.frame.open();
		}
	},  
	
    attachmentControl: wp.customize.MediaControl.extend({
        /**
		 * Create a media modal select frame, and store it so the instance can be reused when needed.
		 */
		initFrame: function() {

			wp.media.view.settings.post = {
				id: this.params.relatedPostId,
				wp_customize: 'off'
			}
			
			this.frame = wp.media({
				states: [
					new wp.media.controller.Library({
						title: this.params.button_labels.frame_title,
						library: wp.media.query({
							uploadedTo: wp.media.view.settings.post.id,
							orderby: 'menuOrder',
							order: 'DESC'
						}),
						multiple:  true,
						sortable: true
					})
				]
			});

			this.frame.on( 'select', () => {
                 // Get the attachment from the modal frame.
                var attachments = this.frame.state().get( 'selection' ).toJSON();
				
				wp.media.view.settings.post = {
					id: this.params.relatedPostId
				}
																																																
                this.params.attachments = attachments;
				this.params.onSave(attachments);
			});
			
			this.frame.on( 'close', () => { this.frame.remove() });
		}
	}),	

	// CroppedImageControl, with presets for thumbnail dimensions
	thumbnailControl: wp.customize.CroppedImageControl.extend({

		initFrame: function() {

			var l10n = _wpMediaViewsL10n;

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
							uploadedTo: null
						}),
						multiple: false,
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

			this.frame.on( 'select', this.onSelect, this );
			this.frame.on( 'cropped', this.onCropped, this );
			this.frame.on( 'skippedcrop', this.onSkippedCrop, this );
		},
		// Called on both skippedcrop and cropped states
		setImageFromAttachment: function( attachment ) {
			wp.media.view.settings.post = {
				id: this.params.relatedPostId
			}
			this.params.attachment = attachment;
			this.params.onSave(attachment);
		}

	}),
	// CroppedImageControl, with presets for header dimensions
	headerImageControl: wp.customize.CroppedImageControl.extend({
		
		initFrame: function() {

			var l10n = _wpMediaViewsL10n;	
			
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
				id: this.params.relatedPostId
			}

			this.frame = wp.media({
				button: {
					text: this.params.button_labels.frame_button
				},
				content: 'upload', // First view that is opened
				states: [
					new wp.media.controller.Library({
						title:     this.params.button_labels.frame_title,
						library:   wp.media.query({ 	
							uploadedTo: null
						}),
						multiple:  false,
						date:      false,
						uploadedTo: this.params.relatedPostId
					})
				]
			});

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