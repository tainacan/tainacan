export default function({ attributes, className }){
    const { content } = attributes;
    return <div className={className}>{ content }</div>
};