export const setGroup = (state, group) => {
    state.group = group;
};

export const setItemIdInSequence = (state, itemIdInSequence) => {
    state.itemIdInSequence = itemIdInSequence;
};

export const setLastUpdated = (state, value) => {
    if (value != undefined)
        state.lastUpdated = value;
    else {
        let now = new Date();
        state.lastUpdated = now.toLocaleString();
    }
}