export default function({ attributes, className }) {
    const { content } = attributes;
    return <div data-module="items-list" className={className}>{ content }</div>
};