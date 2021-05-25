import Vue from 'vue';

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

export const setTaxonomyTerms = (state, taxonomyTerms) => {
  state.taxonomyTerms = taxonomyTerms;
};

export const setTaxonomyChildTerms = (state, taxonomyTerms) => {
  state.taxonomyChildTerms = taxonomyTerms;
};

export const setActivities = (state, activities) => {
  state.activities = activities;
};

export const setStartDate = (state, startDate) => {
  state.startDate = startDate;
};

export const setStackedBarChartOptions = (state, stackedBarChartOptions) => {
  state.stackedBarChartOptions = stackedBarChartOptions;
};

export const setHorizontalBarChartOptions = (state, horizontalBarChartOptions) => {
  state.horizontalBarChartOptions = horizontalBarChartOptions;
};

export const setVisibilityHorizontalBarChartOptions = (state, visibilityHorizontalBarChartOptions) => {
  state.visibilityHorizontalBarChartOptions = visibilityHorizontalBarChartOptions;
};

export const setDonutChartOptions = (state, donutChartOptions) => {
  state.donutChartOptions = donutChartOptions;
};

export const setHeatMapChartOptions = (state, heatMapChartOptions) => {
  state.heatMapChartOptions = heatMapChartOptions;
};

export const setAreaChartOptions = (state, areaChartOptions) => {
  state.areaChartOptions = areaChartOptions;
};

export const setTreeMapChartOptions = (state, areaChartOptions) => {
  state.threeMapChartOptions = areaChartOptions;
};

export const setReportLatestCachedOn = (state, { report, reportLatestCachedOn }) => {
  Vue.set(state.reportsLatestCachedOn, report, reportLatestCachedOn);
};