export default { 
    mediaControl: wp.customize.MediaControl.extend({
        /**
		 * Create a media modal select frame, and store it so the instance can be reused when needed.
		 */
		initFrame: function() {
			'use strict';
			this.frame = wp.media({
				button: {
					text: this.params.button_labels.frame_button
				},
				states: [
					new wp.media.controller.Library({
						title:     this.params.button_labels.frame_title,
						library:   wp.media.query({ type: this.params.mime_type }),
						multiple:  false,
						date:      false
					})
				]
			});

			// When a file is selected, run a callback.
			this.frame.on( 'select', () => {
                 // Get the attachment from the modal frame.
                var node,
                attachment = this.frame.state().get( 'selection' ).first().toJSON(),
                mejsSettings = window._wpmejsSettings || {};

                this.params.attachment = attachment;
				this.params.onSave(attachment.id);
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
	// CroppedImageControl, with presets for thumbnail dimensions
	thumbnailControl: wp.customize.CroppedImageControl.extend({

		initFrame: function() {
			var l10n = _wpMediaViewsL10n;

			wp.media.view.settings.post = {
                id: this.params.relatedPostId
			}

			this.params.flex_width = 0;
			this.params.flex_height = 0;
			this.params.width = 220;
			this.params.height = 220;

			this.frame = wp.media({
				frame: 'select',
				button: {
					text: l10n.select,
					close: false,
					library: {
						type: 'image',
						uploadedTo: this.params.relatedPostId
					},
				},
				uploader: true,
				states: [
					new wp.media.controller.Library({
						title: this.params.button_labels.frame_title,
						library: wp.media.query({ type: 'image' }),
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

			this.frame.on( 'select', this.onSelect, this );
			this.frame.on( 'cropped', this.onCropped, this );
			this.frame.on( 'skippedcrop', this.onSkippedCrop, this );
		},
		// Called on both skippedcrop and cropped states
		setImageFromAttachment: function( attachment ) {
			this.params.attachment = attachment;
			this.params.onSave(attachment.id);
		}

	}),
	// CroppedImageControl, with presets for thumbnail dimensions
	headerImageControl: wp.customize.CroppedImageControl.extend({

		initFrame: function() {
			var l10n = _wpMediaViewsL10n;

			wp.media.view.settings.post = {
                id: this.params.relatedPostId
			}
			this.params.flex_width = 1;
			this.params.flex_height = 1;
			this.params.width = 900;
			this.params.height = 200;

			this.frame = wp.media({
				frame: 'select',
				button: {
					text: l10n.select,
					close: false,
					library: {
						type: 'image',
						uploadedTo: this.params.relatedPostId
					},
				},
				uploader: true,
				states: [
					new wp.media.controller.Library({
						title: this.params.button_labels.frame_title,
						library: wp.media.query({ type: 'image' }),
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
			this.params.onSave(attachment.id);
		}

	})
}