/**
 * Reads derived state from `report/getSummary`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getSummary = state => {
  return state.summary;
};

/**
 * Reads derived state from `report/getMetadata`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getMetadata = state => {
  return state.metadata;
};

/**
 * Reads derived state from `report/getMetadataList`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getMetadataList = state => {
  return state.metadataList;
};

/**
 * Reads derived state from `report/getCollectionsList`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getCollectionsList = state => {
  return state.collectionsList;
};

/**
 * Reads derived state from `report/getTaxonomiesList`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTaxonomiesList = state => {
  return state.taxonomiesList;
};

/**
 * Reads derived state from `report/getTaxonomyTerms`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTaxonomyTerms = state => {
  return state.taxonomyTerms;
};

/**
 * Reads derived state from `report/getTaxonomyChildTerms`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTaxonomyChildTerms = state => {
  return state.taxonomyChildTerms;
};

/**
 * Reads derived state from `report/getActivities`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getActivities = state => {
  return state.activities;
};

/**
 * Reads derived state from `report/getStartDate`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getStartDate = state => {
  return state.startDate;
};

/**
 * Reads derived state from `report/getStackedBarChartOptions`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getStackedBarChartOptions = state => {
  return state.stackedBarChartOptions;
};

/**
 * Reads derived state from `report/getHorizontalBarChartOptions`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getHorizontalBarChartOptions = state => {
  return state.horizontalBarChartOptions;
};

/**
 * Reads derived state from `report/getVisibilityHorizontalBarChartOptions`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getVisibilityHorizontalBarChartOptions = state => {
  return state.visibilityHorizontalBarChartOptions;
};

/**
 * Reads derived state from `report/getDonutChartOptions`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getDonutChartOptions = state => {
  return state.donutChartOptions;
};

/**
 * Reads derived state from `report/getHeatMapChartOptions`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getHeatMapChartOptions = state => {
  return state.heatMapChartOptions;
};

/**
 * Reads derived state from `report/getAreaChartOptions`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getAreaChartOptions = state => {
  return state.areaChartOptions;
};

/**
 * Reads derived state from `report/getTreeMapChartOptions`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTreeMapChartOptions = state => {
  return state.treeMapChartOptions;
};

/**
 * Reads derived state from `report/getReportsLatestCachedOn`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getReportsLatestCachedOn = state => {
  return state.reportsLatestCachedOn;
};