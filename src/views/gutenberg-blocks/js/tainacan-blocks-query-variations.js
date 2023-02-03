const { registerBlockVariation } = wp.blocks;
const { __ } = wp.i18n;
import icon from '../blocks/items-list/icon';

const POST_TYPES = tainacan_blocks.collections_post_types;

Object.keys(POST_TYPES).forEach((postType) => {
    const postName = POST_TYPES[postType];
    const VARIATION_NAME = 'tainacan-items-' + postType;

    registerBlockVariation( 'core/query', {
        name: VARIATION_NAME,
        title: postName,
        icon: icon,
        category: 'tainacan-blocks-variations',
        description: __('Displays a list of Tainacan itens', 'tainacan'),
        isActive: ( { namespace, query } ) => {
                return (
                    namespace === VARIATION_NAME
                    && query.postType === postType
                );
        },
        attributes: {
            namespace: VARIATION_NAME,
            query: {
                postType: postType,
                perPage: 12,
                offset: 0
            },
            align: 'wide',
            displayLayout: {
                type: 'flex',
                columns: 4
            }
        },
        allowedControls: [ 'inherit', 'order', 'taxQuery', 'search' ],
        innerBlocks: [
            [
                'core/post-template',
                {},
                [
                    [ 'core/post-featured-image' ],
                    [ 'core/post-title' ]
                ],
            ]
        ]
    } );
});
