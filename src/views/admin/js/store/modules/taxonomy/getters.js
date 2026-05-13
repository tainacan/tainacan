/**
 * Reads derived state from `taxonomy/getTaxonomy`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTaxonomy = state => {
  return state.taxonomy;
};

/**
 * Reads derived state from `taxonomy/get`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const get = state => {
  return state.taxonomies;
};

/**
 * Reads derived state from `taxonomy/getTaxonomyName`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTaxonomyName = state => {
  return state.taxonomyName;
};

/**
 * Reads derived state from `taxonomy/getRepositoryTotalTaxonomies`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getRepositoryTotalTaxonomies = state => {
  return state.repositoryTotalTaxonomies;
};