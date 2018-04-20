
jQuery(document).ready(function() {

    //jQuery('#frame-uploader').on('click', (event) => {
        //event.preventDefault();

    // If the media frame already exists, reopen it.
    //    if ( this.frameUploader ) {
    //        this.frameUploader.open();
    //        return;
    //    }

    // Create a new media frame
    let frameUploader = wp.media.frames.frame_uploader = wp.media({
        title: 'Select or Upload Media Of Your Chosen Persuasion',
        button: {
            text: 'Use that media'
        },
        multiple: false,

    });

    

    frameUploader.on('select', () => {
        
        let media = frameUploader.state().get( 'selection' ).first().toJSON();

        console.log(media);

    });

    frameUploader.open();
    
    //})
    

});
