export const setSummary = (state, summary) => {
  state.summary = summary;
};

export const setMetadata = (state, metadata) => {
  state.metadata = metadata;
};

export const setMetadataList = (state, metadataList) => {
  state.metadataList = metadataList;
};

export const setCollectionsList = (state, collectionsList) => {
  state.collectionsList = collectionsList;
};

export const setTaxonomiesList = (state, taxonomiesList) => {
  state.taxonomiesList = taxonomiesList;
};

export const setIsFetchingTaxonomiesList = (state, isFetchingTaxomiesList) => {
  return state.isFetchingTaxomiesList = isFetchingTaxomiesList;
};

export const setTaxonomyTerms = (state, taxonomyTerms) => {
  state.taxonomyTerms = taxonomyTerms;
};

export const setActivities = (state, activities) => {
  state.activities = activities;
};

export const setStackedBarChartOptions = (state, stackedBarChartOptions) => {
  state.stackedBarChartOptions = stackedBarChartOptions;
};

export const setHorizontalBarChartOptions = (state, horizontalBarChartOptions) => {
  state.horizontalBarChartOptions = horizontalBarChartOptions;
};

export const setDonutChartOptions = (state, donutChartOptions) => {
  state.donutChartOptions = donutChartOptions;
};

export const setHeatMapChartOptions = (state, heatMapChartOptions) => {
  state.heatMapChartOptions = heatMapChartOptions;
};