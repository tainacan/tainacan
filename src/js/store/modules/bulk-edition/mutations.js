export const setGroup = (state, group) => {
    state.group = group;
};

export const setActionResult = (state, actionResult) => {
    state.actionResult = actionResult;
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

export const setBulkAddItems = (state, items) => {
    return state.bulkAddItems = items;
}