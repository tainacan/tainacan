export default function({ attributes, className }){
    const { content } = attributes;
    return <div data-module="terms-list" className={className}>{ content }</div>
};