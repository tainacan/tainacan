

/**
 * Commits `report/setSummary` state changes.
 * @param {Object} state - Module state.
 * @param {*} summary - Mutation payload.
 * @returns {void} No return value.
 */
export const setSummary = (state, summary) => {
  state.summary = summary;
};

/**
 * Commits `report/setMetadata` state changes.
 * @param {Object} state - Module state.
 * @param {*} metadata - Mutation payload.
 * @returns {void} No return value.
 */
export const setMetadata = (state, metadata) => {
  state.metadata = metadata;
};

/**
 * Commits `report/setMetadataList` state changes.
 * @param {Object} state - Module state.
 * @param {*} metadataList - Mutation payload.
 * @returns {void} No return value.
 */
export const setMetadataList = (state, metadataList) => {
  state.metadataList = metadataList;
};

/**
 * Commits `report/setCollectionsList` state changes.
 * @param {Object} state - Module state.
 * @param {*} collectionsList - Mutation payload.
 * @returns {void} No return value.
 */
export const setCollectionsList = (state, collectionsList) => {
  state.collectionsList = collectionsList;
};

/**
 * Commits `report/setTaxonomiesList` state changes.
 * @param {Object} state - Module state.
 * @param {*} taxonomiesList - Mutation payload.
 * @returns {void} No return value.
 */
export const setTaxonomiesList = (state, taxonomiesList) => {
  state.taxonomiesList = taxonomiesList;
};

/**
 * Commits `report/setTaxonomyTerms` state changes.
 * @param {Object} state - Module state.
 * @param {*} taxonomyTerms - Mutation payload.
 * @returns {void} No return value.
 */
export const setTaxonomyTerms = (state, taxonomyTerms) => {
  state.taxonomyTerms = taxonomyTerms;
};

/**
 * Commits `report/setTaxonomyChildTerms` state changes.
 * @param {Object} state - Module state.
 * @param {*} taxonomyTerms - Mutation payload.
 * @returns {void} No return value.
 */
export const setTaxonomyChildTerms = (state, taxonomyTerms) => {
  state.taxonomyChildTerms = taxonomyTerms;
};

/**
 * Commits `report/setActivities` state changes.
 * @param {Object} state - Module state.
 * @param {*} activities - Mutation payload.
 * @returns {void} No return value.
 */
export const setActivities = (state, activities) => {
  state.activities = activities;
};

/**
 * Commits `report/setStartDate` state changes.
 * @param {Object} state - Module state.
 * @param {*} startDate - Mutation payload.
 * @returns {void} No return value.
 */
export const setStartDate = (state, startDate) => {
  state.startDate = startDate;
};

/**
 * Commits `report/setStackedBarChartOptions` state changes.
 * @param {Object} state - Module state.
 * @param {*} stackedBarChartOptions - Mutation payload.
 * @returns {void} No return value.
 */
export const setStackedBarChartOptions = (state, stackedBarChartOptions) => {
  state.stackedBarChartOptions = stackedBarChartOptions;
};

/**
 * Commits `report/setHorizontalBarChartOptions` state changes.
 * @param {Object} state - Module state.
 * @param {*} horizontalBarChartOptions - Mutation payload.
 * @returns {void} No return value.
 */
export const setHorizontalBarChartOptions = (state, horizontalBarChartOptions) => {
  state.horizontalBarChartOptions = horizontalBarChartOptions;
};

/**
 * Commits `report/setVisibilityHorizontalBarChartOptions` state changes.
 * @param {Object} state - Module state.
 * @param {*} visibilityHorizontalBarChartOptions - Mutation payload.
 * @returns {void} No return value.
 */
export const setVisibilityHorizontalBarChartOptions = (state, visibilityHorizontalBarChartOptions) => {
  state.visibilityHorizontalBarChartOptions = visibilityHorizontalBarChartOptions;
};

/**
 * Commits `report/setDonutChartOptions` state changes.
 * @param {Object} state - Module state.
 * @param {*} donutChartOptions - Mutation payload.
 * @returns {void} No return value.
 */
export const setDonutChartOptions = (state, donutChartOptions) => {
  state.donutChartOptions = donutChartOptions;
};

/**
 * Commits `report/setHeatMapChartOptions` state changes.
 * @param {Object} state - Module state.
 * @param {*} heatMapChartOptions - Mutation payload.
 * @returns {void} No return value.
 */
export const setHeatMapChartOptions = (state, heatMapChartOptions) => {
  state.heatMapChartOptions = heatMapChartOptions;
};

/**
 * Commits `report/setAreaChartOptions` state changes.
 * @param {Object} state - Module state.
 * @param {*} areaChartOptions - Mutation payload.
 * @returns {void} No return value.
 */
export const setAreaChartOptions = (state, areaChartOptions) => {
  state.areaChartOptions = areaChartOptions;
};

/**
 * Commits `report/setTreeMapChartOptions` state changes.
 * @param {Object} state - Module state.
 * @param {*} areaChartOptions - Mutation payload.
 * @returns {void} No return value.
 */
export const setTreeMapChartOptions = (state, areaChartOptions) => {
  state.threeMapChartOptions = areaChartOptions;
};

/**
 * Commits `report/setReportLatestCachedOn` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const setReportLatestCachedOn = (state, { report, reportLatestCachedOn }) => {
  Object.assign(state.reportsLatestCachedOn, { [report]: reportLatestCachedOn });
};
