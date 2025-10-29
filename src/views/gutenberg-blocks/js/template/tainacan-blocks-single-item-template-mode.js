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
const { select } = wp.data;

/**
 * Detects whether the current editing context is a single collection item template.
 * Returns the collection ID (number) or false.
 */
export default function getCollectionIdFromPossibleTemplateEdition() {
    let templateSlug = null;

    // Prefer Gutenberg's editor state
    try {
        // Try new editor store first (used in WP 6.1+)
        const currentPost = select('core/editor')?.getCurrentPost();
        if (currentPost?.type === 'wp_template' && typeof currentPost?.slug === 'string') {
            templateSlug = currentPost.slug;
            console.log('templateSlug: ' + templateSlug);
        }
        // Fallback for WP 5.9–6.0 (Site Editor used core/edit-site)
        if (!templateSlug) {
            const editSitePost = select('core/edit-site')?.getEditedPostTemplate();
            if (editSitePost?.slug) {
                templateSlug = editSitePost.slug;
            }
        }
    } catch (e) {
        // Editor store not available (frontend usage, etc.)
        console.error(e);
    }

    // Fallback: check URL query (for older WP or before editor state is ready)
    if (!templateSlug) {
        const queryParams = new URLSearchParams(window.location.search);
        const postType = queryParams.get('postType');
        const postId = queryParams.get('postId') || queryParams.get('p');

        if (postType === 'wp_template' && postId) {
            templateSlug = postId;
        } else if (typeof postId === 'string' && postId.includes('/wp_template/')) {
            templateSlug = postId;
        }
    }

    // Extract collection ID pattern: single-tnc_col_###_item
    if (templateSlug && templateSlug.includes('single-tnc_col_')) {
        const match = templateSlug.match(/single-tnc_col_(\d+)_item/);
        if (match && match[1]) {
            return Number(match[1]);
        }
    }

    return false;
}
