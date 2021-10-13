const { InnerBlocks} = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function({ className }) {
    return <div data-module="related-items-list" className={ className }><InnerBlocks.Content /></div>
};