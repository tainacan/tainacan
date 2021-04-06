<template>
     <div v-if="metadataList != undefined">
        <div 
                :class="{ 'skeleton': isFetchingData || isBuildingChart || isFetchingMetadatumTerms || !selectedMetadatum || !selectedMetadatum.id }"
                class="postbox">
            <div 
                    :style="selectedParentTerm ? 'margin-left: 0px;' : ''"
                    :class="selectedParentTerm ? 'columns' : ''">
                <div :class="selectedParentTerm ? 'column is-half' : ''">
                    <div class="box-header">
                        <div class="box-header__item">
                            <label 
                                    v-if="!isFetchingData"
                                    for="select_metadata_for_terms">
                                {{ $i18n.get('label_items_per_term_from_taxonomy_metadatum') }}&nbsp;
                            </label>
                            <select
                                    v-if="!isFetchingData"
                                    name="select_metadata_for_terms"
                                    id="select_metadata_for_terms"
                                    :placeholder="$i18n.get('label_select_a_taxonomy_metadatum')"
                                    v-model="selectedMetadatum">
                                <option 
                                        v-for="(metadatum, index) of metadataListArray"
                                        :key="index"
                                        :value="metadatum">
                                    {{ metadatum.name }} 
                                </option>
                            </select>
                        </div>
                        <div 
                                v-if="selectedMetadatum && selectedMetadatum.id && currentTotalTerms >= 56"
                                class="box-header__item">
                            <label for="max_terms">{{ $i18n.get('label_terms_per_page') }}</label>
                            <input
                                    type="number"
                                    step="1"
                                    min="1"
                                    max="999"
                                    class="screen-per-page"
                                    name="max_terms"
                                    id="max_terms"
                                    maxlength="3"
                                    :disabled="isBuildingChart"
                                    v-model.number="maxTermsToDisplay">
                        </div>
                        <div 
                                v-if="selectedMetadatum && selectedMetadatum.id && currentTotalTerms >= 56"
                                class="box-header__item tablenav-pages">
                            <span class="displaying-num">{{ currentTotalTerms + ' ' + $i18n.get('terms') }}</span>
                            <span class="pagination-links">
                                <span
                                        @click="!isBuildingChart ? termsDisplayedPage = 1 : null"
                                        :class="{'tablenav-pages-navspan disabled' : termsDisplayedPage <= 1 || isBuildingChart}"
                                        class="first-page button"
                                        aria-hidden="true">
                                    «
                                </span>
                                <span
                                        @click="(termsDisplayedPage > 1 && !isBuildingChart) ? termsDisplayedPage-- : null"
                                        :class="{'tablenav-pages-navspan disabled' : termsDisplayedPage <= 1 || isBuildingChart}"
                                        class="prev-page button"
                                        aria-hidden="true">
                                    ‹
                                </span>
                                <span class="paging-input">
                                    <label
                                            for="current-page-selector"
                                            class="screen-reader-text">
                                        {{ $i18n.get('label_current_page') }}
                                    </label>
                                    <input
                                            class="current-page"
                                            id="current-page-selector"
                                            type="number"
                                            step="1"
                                            min="1"
                                            :disabled="isBuildingChart || maxTermsToDisplay >= currentTotalTerms"
                                            :max="Math.ceil(currentTotalTerms/maxTermsToDisplay)"
                                            name="paged"
                                            v-model.number="termsDisplayedPage"
                                            size="1"
                                            aria-describedby="table-paging">
                                    <span class="tablenav-paging-text"> {{ $i18n.get('info_of') }} <span class="total-pages">{{ Math.ceil(currentTotalTerms/maxTermsToDisplay) }}</span></span>
                                </span>
                                <span 
                                        @click="(!isBuildingChart && termsDisplayedPage < Math.ceil(currentTotalTerms/maxTermsToDisplay)) ? termsDisplayedPage++ : null"
                                        :class="{'tablenav-pages-navspan disabled' : isBuildingChart || termsDisplayedPage >= Math.ceil(currentTotalTerms/maxTermsToDisplay) }"
                                        aria-hidden="true"
                                        class="next-page button">
                                    ›
                                </span>
                                <span
                                        @click="!isBuildingChart ? termsDisplayedPage = Math.ceil(currentTotalTerms/maxTermsToDisplay) : null"
                                        :class="{'tablenav-pages-navspan disabled': isBuildingChart || termsDisplayedPage >= Math.ceil(currentTotalTerms/maxTermsToDisplay) }"
                                        class="last-page button"
                                        aria-hidden="true">
                                    »
                                </span>
                            </span>
                        </div>
                    </div>
                    <apexchart
                            v-if="!isFetchingData && !isBuildingChart && !isFetchingMetadatumTerms && selectedMetadatum && selectedMetadatum.id"
                            height="380px"
                            :series="chartSeries"
                            :options="chartOptions" />
                </div>
                <div 
                        v-if="!isFetchingData && !isFetchingMetadatumTerms && selectedMetadatum && selectedParentTerm"
                        class="column is-half">
                    <div class="box-header">
                        <div class="box-header__item">
                            <label 
                                    v-if="!isFetchingMetadatumChildTerms">
                                {{ $i18n.get('label_items_per_child_terms_of') }}&nbsp; <em>{{ selectedParentTerm }}</em>
                            </label>
                        </div>
                        <div 
                                v-if="currentTotalChildTerms >= 56"
                                class="box-header__item">
                            <label for="max_terms">{{ $i18n.get('label_terms_per_page') }}</label>
                            <input
                                    type="number"
                                    step="1"
                                    min="1"
                                    max="999"
                                    class="screen-per-page"
                                    name="max_terms"
                                    id="max_terms"
                                    maxlength="3"
                                    :disabled="isBuildingChildrenChart"
                                    v-model.number="maxChildTermsToDisplay">
                        </div>
                        <div 
                                v-if="currentTotalChildTerms >= 56"
                                class="box-header__item tablenav-pages">
                            <span class="displaying-num">{{ currentTotalChildTerms + ' ' + $i18n.get('terms') }}</span>
                            <span class="pagination-links">
                                <span
                                        @click="!isBuildingChildrenChart ? childTermsDisplayedPage = 1 : null"
                                        :class="{'tablenav-pages-navspan disabled' : childTermsDisplayedPage <= 1 || isBuildingChildrenChart}"
                                        class="first-page button"
                                        aria-hidden="true">
                                    «
                                </span>
                                <span
                                        @click="(childTermsDisplayedPage > 1 && !isBuildingChildrenChart) ? childTermsDisplayedPage-- : null"
                                        :class="{'tablenav-pages-navspan disabled' : childTermsDisplayedPage <= 1 || isBuildingChildrenChart}"
                                        class="prev-page button"
                                        aria-hidden="true">
                                    ‹
                                </span>
                                <span class="paging-input">
                                    <label
                                            for="current-page-selector"
                                            class="screen-reader-text">
                                        {{ $i18n.get('label_current_page') }}
                                    </label>
                                    <input
                                            class="current-page"
                                            id="current-page-selector"
                                            type="number"
                                            step="1"
                                            min="1"
                                            :disabled="isBuildingChildrenChart || maxChildTermsToDisplay >= currentTotalChildTerms"
                                            :max="Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay)"
                                            name="paged"
                                            v-model.number="childTermsDisplayedPage"
                                            size="1"
                                            aria-describedby="table-paging">
                                    <span class="tablenav-paging-text"> {{ $i18n.get('info_of') }} <span class="total-pages">{{ Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay) }}</span></span>
                                </span>
                                <span 
                                        @click="(!isBuildingChildrenChart && childTermsDisplayedPage < Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay)) ? childTermsDisplayedPage++ : null"
                                        :class="{'tablenav-pages-navspan disabled' : isBuildingChildrenChart || childTermsDisplayedPage >= Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay) }"
                                        aria-hidden="true"
                                        class="next-page button">
                                    ›
                                </span>
                                <span
                                        @click="!isBuildingChildrenChart ? childTermsDisplayedPage = Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay) : null"
                                        :class="{'tablenav-pages-navspan disabled': isBuildingChildrenChart || childTermsDisplayedPage >= Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay) }"
                                        class="last-page button"
                                        aria-hidden="true">
                                    »
                                </span>
                            </span>
                        </div>
                    </div>
                    <apexchart
                            v-if="!isBuildingChildrenChart && !isFetchingMetadatumChildTerms"
                            height="380px"
                            :series="childrenChartSeries"
                            :options="childrenChartOptions" />
                </div>
            </div>
        </div>
        <div 
                v-if="metadatumTermsLatestCachedOn"
                class="box-last-cached-on">
            <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(metadatumTermsLatestCachedOn).toLocaleString() }}</span>
            <button 
                    @click="loadMetadatumTerms(true)">
                <span class="screen-reader-text">
                    {{ $i18n.get('label_get_latest_report') }}
                </span>
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                </span>
            </button>
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { reportsChartMixin } from '../js/reports-mixin';

export default {
    mixins: [ reportsChartMixin ],
    props: {
        collectionId: ''
    },
    data() {
        return {
            isFetchingMetadatumTerms: false,
            selectedMetadatum: {},
            maxTermsToDisplay: 56,
            termsDisplayedPage: 1,
            selectedParentTerm: '',
            isFetchingMetadatumChildTerms: false,
            isBuildingChildrenChart: false,
            childrenChartSeries: [],
            childrenChartOptions: {},
            maxChildTermsToDisplay: 56,
            childTermsDisplayedPage: 1
        }
    },
    computed: {
        ...mapGetters('report', {
            metadataList: 'getMetadataList',
            taxonomyChildTerms: 'getTaxonomyChildTerms',
            stackedBarChartOptions: 'getStackedBarChartOptions',
            reportsLatestCachedOn: 'getReportsLatestCachedOn'
        }),
        metadataListArray() {
            return this.metadataList && Array.isArray(this.metadataList) ? this.metadataList : [];
        },
        metadatumTermsLatestCachedOn() {
            return this.reportsLatestCachedOn['taxonomy-terms-' + (this.collectionId ? this.collectionId : 'default') + '-' + this.selectedMetadatum.id];
        },
        currentTotalTerms() {
            return Array.isArray(this.chartData) ? this.chartData.length : 0 
        },
        currentTotalChildTerms() {
            return Array.isArray(this.taxonomyChildTerms) ? this.taxonomyChildTerms.length : 0 
        }
    },
    watch: {
        metadataListArray: {
            handler() {
                if (this.metadataListArray && this.metadataListArray.length)
                    this.selectedMetadatum = this.metadataListArray[0];
            },
            immediate: true
        },
        selectedMetadatum: {
            handler() {
                this.termsDisplayedPage = 1;
                if (this.selectedMetadatum && this.selectedMetadatum.id)
                    this.loadMetadatumTerms();
            },
            immediate: true
        },
        termsDisplayedPage() {
            this.buildMetadatumTermsChart();
        },
        maxTermsToDisplay() {
            this.termsDisplayedPage = 1;
            this.buildMetadatumTermsChart();
        },
        selectedParentTerm() {
            if (this.selectedParentTerm) {
                this.loadMetadatumChildTerms();
            }
        }
    },
    methods: {
        ...mapActions('report', [
            'fetchTaxonomyTerms'
        ]),
        ...mapActions('metadata', [
            'fetchMetadata'
        ]),
        buildMetadatumTermsChart() {
        
            this.isBuildingChart = true;
            
            // Building Taxonomy term usage chart
            let orderedTerms = JSON.parse(JSON.stringify(this.chartData)).sort((a, b) => b.total_items - a.total_items );
            orderedTerms = orderedTerms.slice((this.termsDisplayedPage - 1) * this.maxTermsToDisplay, ((this.termsDisplayedPage - 1) * this.maxTermsToDisplay) + this.maxTermsToDisplay);
            
            let termsValues = [];
            let termsLabels = [];

            orderedTerms.forEach(term => {
                termsValues.push(term.total_items);
                termsLabels.push(term.label);
            });
            
            this.chartSeries = [
                {
                    name: this.$i18n.get('label_terms_used'),
                    data: termsValues
                }
            ];
            this.chartOptions = {
                ...this.stackedBarChartOptions, 
                ...{
                    title: {},
                    xaxis: {
                        type: 'category',
                        tickPlacement: 'on',
                        categories: termsLabels,
                        labels: {
                            show: true,
                            trim: true,
                            hideOverlappingLabels: false
                        },
                        tooltip: { enabled: true }
                    },
                    chart: {
                        type: 'bar',
                        height: 350,
                        stacked: true,
                        toolbar: {
                            show: true
                        },
                        zoom: {
                            enabled: true,
                            autoScaleYaxis: true,
                        },
                        events: {
                            dataPointSelection: (event, chartContext, config) => {
                                if (config.dataPointIndex >=0 && orderedTerms[config.dataPointIndex] && orderedTerms[config.dataPointIndex].total_children) {
                                    this.selectedParentTerm = orderedTerms[config.dataPointIndex].value;
                                    console.log(orderedTerms[config.dataPointIndex])
                                }
                            }
                        },
                    },
                    yaxis: {
                        title: {
                            text: this.$i18n.get('label_number_of_items')
                        }
                    },
                    animations: {
                        enabled: orderedTerms.length <= 40
                    }
                }
            }

            setTimeout(() => this.isBuildingChart = false, 500);
        },
        buildMetadatumChildTermsChart() {
        
            this.isBuildingChildrenChart = true;
            
            // Building Taxonomy term usage chart
            let orderedTerms = JSON.parse(JSON.stringify(this.taxonomyChildTerms)).sort((a, b) => b.total_items - a.total_items );
            orderedTerms = orderedTerms.slice((this.termsDisplayedPage - 1) * this.maxTermsToDisplay, ((this.termsDisplayedPage - 1) * this.maxTermsToDisplay) + this.maxTermsToDisplay);
            
            let termsValues = [];
            let termsLabels = [];

            orderedTerms.forEach(term => {
                termsValues.push(term.total_items);
                termsLabels.push(term.label);
            });
            
            this.childrenChartSeries = [
                {
                    name: this.$i18n.get('label_terms_used'),
                    data: termsValues
                }
            ];
            this.childrenChartOptions = {
                ...this.stackedBarChartOptions, 
                ...{
                    title: {},
                    xaxis: {
                        type: 'category',
                        tickPlacement: 'on',
                        categories: termsLabels,
                        labels: {
                            show: true,
                            trim: true,
                            hideOverlappingLabels: false
                        },
                        tooltip: { enabled: true }
                    },
                    chart: {
                        type: 'bar',
                        height: 350,
                        stacked: true,
                        toolbar: {
                            show: true
                        },
                        zoom: {
                            enabled: true,
                            autoScaleYaxis: true,
                        },
                        events: {
                            dataPointSelection: (event, chartContext, config) => {
                                if (config.dataPointIndex >= 0 && orderedTerms[config.dataPointIndex] && orderedTerms[config.dataPointIndex].total_children) {
                                    this.selectedParentTerm = orderedTerms[config.dataPointIndex].value;
                                    console.log(orderedTerms[config.dataPointIndex])
                                }
                            }
                        },
                    },
                    yaxis: {
                        title: {
                            text: this.$i18n.get('label_number_of_items')
                        }
                    },
                    animations: {
                        enabled: orderedTerms.length <= 40
                    }
                }
            }

            setTimeout(() => this.isBuildingChildrenChart = false, 500);
            setTimeout(() => console.log(this.childrenChartSeries, this.isBuildingChildrenChart, this.isFetchingMetadatumChildTerms), 1000);
        },
        loadMetadatumTerms(force) {
            this.isFetchingMetadatumTerms = true;
            
            this.fetchTaxonomyTerms({ taxonomyId: this.selectedMetadatum.id, collectionId: this.collectionId, force: force })
                .then(() => {
                    this.buildMetadatumTermsChart();
                    this.selectedParentTerm = '';
                    this.isFetchingMetadatumTerms = false;
                })
                .catch(() => this.isFetchingMetadatumTerms = false);
        },
        loadMetadatumChildTerms(force) {
            this.isFetchingMetadatumChildTerms = true;
            
            this.fetchTaxonomyTerms({ taxonomyId: this.selectedMetadatum.id, collectionId: this.collectionId, parentTerm: this.selectedParentTerm, force: force })
                .then(() => {
                    this.buildMetadatumChildTermsChart();
                    this.isFetchingMetadatumChildTerms = false;
                })
                .catch(() => this.isFetchingMetadatumChildTerms = false);
        }
    }
}
</script>