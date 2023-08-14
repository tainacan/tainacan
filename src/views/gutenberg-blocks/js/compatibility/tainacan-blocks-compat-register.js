const { registerBlockType } = wp.blocks;

// Register the block type according to WP version
export default function({ metadata, icon, edit, save, deprecated, transforms }) {
    
    let attributes = {
        icon: {
            src: icon,
            foreground: '#187181',
        },
        edit,
        deprecated,
        transforms
    }
    if (save)
        attributes['save'] = save;

    // Registers block type using new strategy from WP 5.8
    registerBlockType( metadata, attributes);  
};