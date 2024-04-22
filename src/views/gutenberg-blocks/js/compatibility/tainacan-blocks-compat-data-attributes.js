// Checks if we have legacy data attributes and returns the correct ones
export default function getDataAttribute(block, key, defaultValue) {
    if (block.getAttribute('data-' + key) != undefined)
        return block.getAttribute('data-' + key);
    else if ( block.attributes[key] != undefined )
        return block.attributes[key].value;
    else
        return defaultValue;
};