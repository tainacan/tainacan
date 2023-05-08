import itemsIcon from '../blocks/items-list/icon';
import collectionsIcon from '../blocks/collections-list/icon';
import taxonomiesIcon from '../blocks/terms-list/icon';

const { registerBlockVariation } = wp.blocks;
const { __ } = wp.i18n;

/**
 * Adds Tainacan Collections as a query loop variation
 */
registerBlockVariation( 'core/query', {
    name: 'tainacan-collection',
    title: __( 'Tainacan collections', 'tainacan'),
    icon: collectionsIcon,
    category: 'tainacan-blocks-variations',
    description: __('Displays a list of Tainacan collections', 'tainacan'),
    isActive: ( { namespace, query } ) => {
            return (
                namespace === 'tainacan-collection'
                && query.postType === 'tainacan-collection'
            );
    },
    attributes: {
        namespace: 'tainacan-collection',
        query: {
            postType: 'tainacan-collection',
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

/**
 * Loops on Tainacan Collections post types to create items list variations
 */
const POST_TYPES = tainacan_blocks.collections_post_types;

Object.keys(POST_TYPES).forEach((postType) => {
    const postName = POST_TYPES[postType];
    const VARIATION_NAME = 'tainacan-items-' + postType;

    registerBlockVariation( 'core/query', {
        name: VARIATION_NAME,
        title: postName,
        icon: itemsIcon,
        category: 'tainacan-blocks-variations',
        description: __('Displays a list of Tainacan itens from a collection', 'tainacan'),
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


/**
 * Adds Tainacan Taxonomies as a query loop variation
 */
registerBlockVariation( 'core/query', {
    name: 'tainacan-taxonomies',
    title: __( 'Tainacan taxonomies', 'tainacan'),
    icon: taxonomiesIcon,
    category: 'tainacan-blocks-variations',
    description: __('Displays a list of Tainacan taxonomies', 'tainacan'),
    isActive: ( { namespace, query } ) => {
            return (
                namespace === 'tainacan-taxonomy'
                && query.postType === 'tainacan-taxonomy'
            );
    },
    attributes: {
        namespace: 'tainacan-taxonomy',
        query: {
            postType: 'tainacan-taxonomy',
            perPage: 12,
            offset: 0
        },
        align: 'wide',
        displayLayout: {
            type: 'flex',
            columns: 4
        }
    },
    allowedControls: [ 'inherit', 'order', 'search' ],
    innerBlocks: [
        [
            'core/post-template',
            {},
            [
                // [ 'core/post-featured-image' ],
                [ 'core/post-title' ]
            ],
        ]
    ]
} );

