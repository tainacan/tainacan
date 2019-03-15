export default { 
    attachmentControl: wp.customize.MediaControl.extend({
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
				autoSelect: true,
				states: [
					new wp.media.controller.Library({
						title:     this.params.button_labels.frame_title,
						library:   wp.media.query({
							status:  null,
							type:    null,
							uploadedTo: wp.media.view.settings.post.id
						}),
						uploader: true,
						multiple:  true,
						date:      false,
						uploadedTo: wp.media.view.settings.post.id
					})
				]
			});

			// When a file is selected, run a callback.
			this.frame.on( 'select', () => {
				
                 // Get the attachment from the modal frame.
                var node,
                attachments,
				mejsSettings = window._wpmejsSettings || {};
				attachments = this.frame.state().get( 'selection' ).toJSON();

				wp.media.view.settings.post = {
					id: this.params.relatedPostId
				}
																																																
                this.params.attachments = attachments;
				this.params.onSave(attachments);
                // Set the Customizer setting; the callback takes care of rendering.
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
	// CroppedImageControl, with presets for thumbnail dimensions
	thumbnailControl: wp.customize.CroppedImageControl.extend({

		initFrame: function() {
			var l10n = _wpMediaViewsL10n;

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
					new wp.media.controller.CustomizeImageCropper({
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
					new wp.media.controller.CustomizeImageCropper({
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

			// We don't want document file to be listed as an attachment of item
			wp.media.view.settings.post = {
                id: null
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