/**
 * Commits `taxonomy/setRepositoryTotalTaxonomies` state changes.
 * @param {Object} state - Module state.
 * @param {*} repositoryTotalTaxonomies - Mutation payload.
 * @returns {void} No return value.
 */
export const setRepositoryTotalTaxonomies = (state, repositoryTotalTaxonomies) => {
    state.repositoryTotalTaxonomies = repositoryTotalTaxonomies;
};

// TAXONOMIES
/**
 * Commits `taxonomy/setTaxonomy` state changes.
 * @param {Object} state - Module state.
 * @param {*} taxonomy - Mutation payload.
 * @returns {void} No return value.
 */
export const setTaxonomy = (state, taxonomy) => {
    state.taxonomy = taxonomy;
};

/**
 * Commits `taxonomy/set` state changes.
 * @param {Object} state - Module state.
 * @param {*} taxonomies - Mutation payload.
 * @returns {void} No return value.
 */
export const set = (state, taxonomies) => {
    state.taxonomies = taxonomies;
};

/**
 * Commits `taxonomy/setTaxonomyName` state changes.
 * @param {Object} state - Module state.
 * @param {*} name - Mutation payload.
 * @returns {void} No return value.
 */
export const setTaxonomyName = (state, name) => {
    state.taxonomyName = name;
};

/**
 * Commits `taxonomy/deleteTaxonomy` state changes.
 * @param {Object} state - Module state.
 * @param {*} taxonomy - Mutation payload.
 * @returns {void} No return value.
 */
export const deleteTaxonomy = ( state, taxonomy ) => {
    let index = state.taxonomies.findIndex(deletedTaxonomy => deletedTaxonomy.id === taxonomy.id);

    if (index >= 0) {
        state.taxonomies.splice(index, 1);
    }
};
