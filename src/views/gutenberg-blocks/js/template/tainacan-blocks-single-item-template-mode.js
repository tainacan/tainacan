/**
 * This function is used by all blocks that need to know if the 
 * the current editing context is an item single template edition or not
 * It looks in the editor url to see if a theme-slug//single-tnc_col_123_item
 * pattern is present. If so, it extracts the collection ID from it, to be used
 * for creating a block placeholder for any possible item of that collection.
 * 
 * To use this function, a block ideally have in its attributes a boolean
 * "templateMode" and a number "collectionId"
 */ 

export default function getCollectionIdFromPossibleTemplateEdition() {

    const queryParams = new URLSearchParams(window.location.search);
    if (queryParams.get('postType') == 'wp_template') {

        // Extracts collectionId from a string like theme-slug//single-tnc_col_123_item
        let postId = queryParams.get('postId');
        
        if (typeof postId == 'string') {
            postId = postId.split('single-tnc_col_');
            
            if (postId.length == 2) {
                postId = postId[1];

                if (typeof postId == 'string') {
                    postId = postId.split('_item');

                    if (postId.length == 2) {
                        postId = postId[0];

                        const collectionId = !isNaN(postId) ? Number(postId) : false;

                        return collectionId;
                    }
                }
            }
        }
    }

    return false;
}