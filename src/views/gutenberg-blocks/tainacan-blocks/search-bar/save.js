export default function({ attributes, className }) {
    const { content } = attributes;
    return <div data-module="search-bar" className={ className }>{ content }</div>
}