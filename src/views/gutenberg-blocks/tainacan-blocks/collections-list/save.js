export default function({ attributes, className }) {
    const { content } = attributes;
    return <div data-module="collections-list" className={ className }>{ content }</div>
};