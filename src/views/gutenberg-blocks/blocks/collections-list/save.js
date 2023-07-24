const { useBlockProps } = wp.blockEditor;

export default function({ attributes, className }) {
    const { content } = attributes;
    
    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = useBlockProps.save();
    
    return <div { ...blockProps } data-module="collections-list">{ content }</div>
};