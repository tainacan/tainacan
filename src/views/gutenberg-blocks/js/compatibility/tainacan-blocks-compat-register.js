const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

// Converts non-localized block metadata info to localized ones, for WP versions older than 5.8
function tainacanBlocksLocalizeMetadata(metadata) {

    for (let metadatumKey in metadata) {
        if (metadatumKey == 'title' || metadatumKey == 'description') {
            metadata[metadatumKey] = __(metadata[metadatumKey], 'tainacan');
        } else if (metadatumKey == 'keywords') {
            metadata[metadatumKey].forEach(keyword => {
                keyword = __(keyword, 'tainacan');
            });
        } else if (metadatumKey == 'styles') {
            metadata[metadatumKey].forEach(style => {
                style.label = __(style.label, 'tainacan');
            });
        }
    }
    return metadata;
}

// Register the block type according to WP version
export default function({ metadata, icon, edit, save, deprecated, transforms }) {
    
    const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;
    let attributes = {
        icon: {
            src: icon,
            foreground: '#298596',
        },
        edit,
        deprecated,
        transforms
    }
    if (save)
        attributes['save'] = save;

    if (currentWPVersion >= '5.8-RC') {

        // Registers block type using new strategy from WP 5.8
        registerBlockType( metadata, attributes);
    
    } else {

        // Converts this array to a valid array previous to WP 5.8
        registerBlockType( metadata.name, {
            ...tainacanBlocksLocalizeMetadata(metadata),
            ...attributes
        });
    }
};