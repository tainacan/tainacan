const { InnerBlocks} = wp.blockEditor;

export default function({ className }) {
    return <div data-module="related-items-list" className={ className }><InnerBlocks.Content /></div>
};