<template>
     <div v-if="metadataList != undefined">
        <div 
                v-if="metadataListArray.length"
                :class="{ 'skeleton': isFetchingData || isBuildingChart || isFetchingMetadatumTerms || !selectedMetadatum || !selectedMetadatum.id }"
                class="postbox">
            <div 
                    :style="!isChildColumnCollapsed ? 'margin-left: 0px;' : ''"
                    :class="!isChildColumnCollapsed ? 'columns is-6' : ''">
                <div :class="!isChildColumnCollapsed ? 'column is-half is-full-tablet' : ''">
                    <div class="box-header">
                        <div 
                                v-if="selectedParentTerm.length <= 1"
                                class="box-header__item">
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
                            <div class="graph-mode-switch">
                                <button 
                                        @click="itemsPerTermChartMode = 'bar'"
                                        :class="{ 'current': itemsPerTermChartMode == 'bar' }">
                                    <span class="screen-reader-text">
                                        {{ $i18n.get('label_bar_chart') }}
                                    </span>
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text tainacan-icon-rotate-270" />
                                    </span>
                                </button>
                                <button 
                                        @click="itemsPerTermChartMode = 'treemap'"
                                        :class="{ 'current': itemsPerTermChartMode == 'treemap' }">
                                    <span class="screen-reader-text">
                                        {{ $i18n.get('label_tree_map') }}
                                    </span>
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-viewmasonry tainacan-icon-rotate-270" />
                                    </span>
                                </button>
                            </div>
                        </div>
                        <div
                                v-else
                                class="box-header__item"
                                style="display: flex; align-items: baseline;">
                            
                            <button
                                    class="button button-secondary"
                                    @click="backToParentTerm">
                                {{ $i18n.get('label_parent_term') }}
                            </button>&nbsp;
                            <span 
                                    v-if="!isFetchingMetadatumChildTerms">
                                &nbsp;{{ $i18n.get('label_items_per_child_terms_of') }}&nbsp; <strong>{{ selectedParentTerm[selectedParentTerm.length - 2].label }}</strong>
                            </span>
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
                    <button 
                            v-if=" !isFetchingData && !isFetchingMetadatumTerms && selectedMetadatum"
                            @click="isChildColumnCollapsed = !isChildColumnCollapsed"
                            class="button-secondary hide-column-button">
                        <span class="icon">
                            <i 
                                    :class="isChildColumnCollapsed ? 'tainacan-icon-arrowleft' : 'tainacan-icon-arrowright'"
                                    class="tainacan-icon tainacan-icon-1-25em" />
                        </span>
                    </button>
                </div>
                <div 
                        v-if="!isChildColumnCollapsed && !isFetchingData && !isFetchingMetadatumTerms && selectedMetadatum"
                        class="child-term-column column is-half is-full-tablet">
                    <div v-if="selectedParentTerm[selectedParentTerm.length - 1]">
                        <div class="box-header">
                            <div class="box-header__item">
                                <span 
                                        v-if="!isFetchingMetadatumChildTerms">
                                    {{ $i18n.get('label_items_per_child_terms_of') }}&nbsp; <strong>{{ selectedParentTerm[selectedParentTerm.length - 1].label }}</strong>
                                </span>
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
                    <div 
                            v-else
                            class="empty-postbox-placeholder">
                        <p class="title is-4">
                            <span class="icon has-text-gray">
                                <i class="tainacan-icon tainacan-icon-taxonomies tainacan-icon-1-125em" />
                            </span>
                            &nbsp;{{ $i18n.get('label_children_terms') }}
                        </p>
                        <br>
                        <p class="subtitle is-6">{{ $i18n.get('info_child_terms_chart') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div 
                v-if="metadatumTermsLatestCachedOn"
                style="left: calc(1px + 0.75rem); right: auto;"
                class="box-last-cached-on">
            <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(metadatumTermsLatestCachedOn).toLocaleString() }}</span>
            <button @click="loadMetadatumTerms(true)">
                <span class="screen-reader-text">
                    {{ $i18n.get('label_get_latest_report') }}
                </span>
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                </span>
            </button>
        </div>
        <div 
                v-if="!isChildColumnCollapsed && !isFetchingData && !isFetchingMetadatumTerms && selectedMetadatum && metadatumChildTermsLatestCachedOn"
                class="box-last-cached-on">
            <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(metadatumChildTermsLatestCachedOn).toLocaleString() }}</span>
            <button 
                    @click="loadMetadatumChildTerms(true)">
                <span class="screen-reader-text">
                    {{ $i18n.get('label_get_latest_report') }}
                </span>
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                </span>
            </button>
        </div>
        <div 
                v-if="!isFetchingData && !isBuildingChart && (!metadataListArray || !metadataListArray.length)"
                style="min-height:380px"
                class="postbox">
            <div class="empty-postbox-placeholder">
                <p class="title is-4">
                    <span class="icon has-text-gray">
                        <i class="tainacan-icon tainacan-icon-metadata tainacan-icon-1-125em" />
                    </span>
                    &nbsp;{{ $i18n.get('label_items_per_term_from_taxonomy_metadatum') }}
                </p>
                <br>
                <p class="subtitle is-6">{{ $i18n.get('info_no_taxonomy_metadata_created') }}</p>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions, mapMutations, mapGetters } from 'vuex';
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
            selectedParentTerm: [],
            isFetchingMetadatumChildTerms: false,
            isBuildingChildrenChart: false,
            childrenChartSeries: [],
            childrenChartOptions: {},
            maxChildTermsToDisplay: 56,
            childTermsDisplayedPage: 1,
            isChildColumnCollapsed: false,
            itemsPerTermChartMode: 'bar'
        }
    },
    computed: {
        ...mapGetters('report', {
            metadataList: 'getMetadataList',
            taxonomyTerms: 'getTaxonomyTerms',
            taxonomyChildTerms: 'getTaxonomyChildTerms',
            stackedBarChartOptions: 'getStackedBarChartOptions',
            treeMapChartOptions: 'getTreeMapChartOptions',
            reportsLatestCachedOn: 'getReportsLatestCachedOn'
        }),
        metadataListArray() {
            return this.metadataList && Array.isArray(this.metadataList) ? this.metadataList : [];
        },
        metadatumTermsLatestCachedOn() {
            return this.reportsLatestCachedOn['taxonomy-terms-' + (this.collectionId ? this.collectionId : 'default') + '-' + this.selectedMetadatum.id + (this.selectedParentTerm.length > 2 && this.selectedParentTerm[this.selectedParentTerm.length - 2] && this.selectedParentTerm[this.selectedParentTerm.length - 2].id ? '-' + this.selectedParentTerm[this.selectedParentTerm.length - 1].id : '')];
        },
        metadatumChildTermsLatestCachedOn() {
            return this.reportsLatestCachedOn['taxonomy-terms-' + (this.collectionId ? this.collectionId : 'default') + '-' + this.selectedMetadatum.id + (this.selectedParentTerm[this.selectedParentTerm.length - 1] && this.selectedParentTerm[this.selectedParentTerm.length - 1].id ? '-' + this.selectedParentTerm[this.selectedParentTerm.length - 1].id : '') + '-is-child-chart'];
        },
        currentTotalTerms() {
            return Array.isArray(this.taxonomyTerms) ? this.taxonomyTerms.length : 0 
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
                if (this.selectedMetadatum && this.selectedMetadatum.id) {
                    this.selectedParentTerm = [];
                    this.loadMetadatumTerms();
                }
            },
            immediate: true
        },
        termsDisplayedPage() {
            this.buildMetadatumTermsChart();
        },
        childTermsDisplayedPage() {
            this.buildMetadatumChildTermsChart();
        },
        maxTermsToDisplay() {
            this.termsDisplayedPage = 1;
            this.buildMetadatumTermsChart();
        },
        maxChildTermsToDisplay() {
            this.childTermsDisplayedPage = 1;
            this.buildMetadatumChildTermsChart();
        },
        selectedParentTerm() {
            if (this.selectedParentTerm[this.selectedParentTerm.length - 1] && this.selectedParentTerm[this.selectedParentTerm.length - 1].id) {
                this.loadMetadatumChildTerms();
            }
        },
        itemsPerTermChartMode() {
            this.termsDisplayedPage = 1;
            this.loadMetadatumTerms();
        },
    },
    methods: {
        ...mapActions('report', [
            'fetchTaxonomyTerms'
        ]),
        ...mapMutations('report', [
            'setTaxonomyTerms',
            'setReportLatestCachedOn'
        ]),
        ...mapActions('metadata', [
            'fetchMetadata'
        ]),
        buildMetadatumTermsChart() {
            this.isBuildingChart = true;
            
            // Building Taxonomy term usage chart
            let orderedTerms = JSON.parse(JSON.stringify(this.taxonomyTerms)).sort((a, b) => b.total_items - a.total_items );
            orderedTerms = orderedTerms.slice((this.termsDisplayedPage - 1) * this.maxTermsToDisplay, ((this.termsDisplayedPage - 1) * this.maxTermsToDisplay) + this.maxTermsToDisplay);
            
            if (this.itemsPerTermChartMode == 'treemap') {
                this.chartSeries = [
                    {
                        name: this.$i18n.get('label_items_per_term'),
                        data: orderedTerms.map((aTerm) => { return { 
                            x: aTerm.label,
                            y: aTerm.total_items
                        } })
                    }
                ];
                this.chartOptions = {
                    ...this.treeMapChartOptions, 
                    ...{
                        title: {},
                        chart: {
                            type: 'treemap',
                            height: 350,
                            toolbar: {
                                show: true
                            },
                            zoom: {
                                enabled: false
                            },
                            events: {
                                dataPointSelection: (event, chartContext, config) => {
                                    if (config.dataPointIndex >= 0 && orderedTerms[config.dataPointIndex]) {
                                        const existingParentTermIndex = this.selectedParentTerm.findIndex((term) => term.id == orderedTerms[config.dataPointIndex].value);
                                        if (existingParentTermIndex < 0) {
                                            this.selectedParentTerm.push({
                                                id: orderedTerms[config.dataPointIndex].value,
                                                label: orderedTerms[config.dataPointIndex].label
                                            })
                                        }
                                    }
                                }
                            },
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '16px',
                            },
                            formatter: function(text, op) {
                                return [text, op.value]
                            },
                            offsetY: -4
                        },
                        tooltip: {
                            custom: ({ dataPointIndex }) => {
                                return `<div class="tainacan-custom-tooltip">
                                        <div class="tainacan-custom-tooltip__header">` + orderedTerms[dataPointIndex].label + `</div>
                                        <div class="tainacan-custom-tooltip__body">
                                            <span>` + this.$i18n.get('label_items_per_term') + `: <strong>` + orderedTerms[dataPointIndex].total_items + `</strong></span>
                                            `+ (orderedTerms[dataPointIndex].total_children 
                                                ? (`<span>` + this.$i18n.getWithVariables(orderedTerms[dataPointIndex].total_children > 1 ? 'instruction_click_to_see_%s_child_terms' : 'instruction_click_to_see_%s_child_term', [ orderedTerms[dataPointIndex].total_children ]) + `</span>`) 
                                                : ``
                                            ) +
                                        `</div></div>`;
                            }
                        },
                        noData: {
                            text: '0 ' + this.$i18n.get('label_items_with_this_metadatum_value')
                        }
                    }
                }
            } else {

                let termsValues = [];
                let termsLabels = [];

                orderedTerms.forEach(term => {
                    termsValues.push(term.total_items);
                    termsLabels.push(term.label);
                });
                
                this.chartSeries = [
                    {
                        name: this.$i18n.get('label_items_per_term'),
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
                            stacked: false,
                            toolbar: {
                                show: true
                            },
                            zoom: {
                                enabled: true,
                                autoScaleYaxis: true,
                            },
                            events: {
                                dataPointSelection: (event, chartContext, config) => {
                                    if (config.dataPointIndex >= 0 && orderedTerms[config.dataPointIndex]) {
                                        const existingParentTermIndex = this.selectedParentTerm.findIndex((term) => term.id == orderedTerms[config.dataPointIndex].value);
                                        if (existingParentTermIndex < 0) {
                                            // Removes siblings from the hierarchy, if existing
                                            if (this.selectedParentTerm.length && (this.selectedParentTerm[this.selectedParentTerm.length - 1].id != orderedTerms[config.dataPointIndex].parent) )
                                                this.selectedParentTerm.pop();

                                            this.selectedParentTerm.push({
                                                id: orderedTerms[config.dataPointIndex].value,
                                                label: orderedTerms[config.dataPointIndex].label
                                            });
                                        }
                                    }
                                }
                            },
                        },
                        tooltip: {
                            custom: ({ dataPointIndex }) => {
                                return `<div class="tainacan-custom-tooltip">
                                        <div class="tainacan-custom-tooltip__header">` + orderedTerms[dataPointIndex].label + `</div>
                                        <div class="tainacan-custom-tooltip__body">
                                            <span>` + this.$i18n.get('label_items_per_term') + `: <strong>` + orderedTerms[dataPointIndex].total_items + `</strong></span>
                                            `+ (orderedTerms[dataPointIndex].total_children 
                                                ? (`<span>` + this.$i18n.getWithVariables(orderedTerms[dataPointIndex].total_children > 1 ? 'instruction_click_to_see_%s_child_terms' : 'instruction_click_to_see_%s_child_term', [ orderedTerms[dataPointIndex].total_children ]) + `</span>`) 
                                                : ``
                                            ) +
                                        `</div></div>`;
                            }
                        },
                        yaxis: {
                            title: {
                                text: this.$i18n.get('label_number_of_items')
                            }
                        },
                        animations: {
                            enabled: orderedTerms.length <= 40
                        },
                        noData: {
                            text: '0 ' + this.$i18n.get('label_items_with_this_metadatum_value')
                        }
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
            
            if (this.itemsPerTermChartMode == 'treemap') {
                this.childrenChartSeries = [
                    {
                        name: this.$i18n.get('label_items_per_term'),
                        data: orderedTerms.map((aTerm) => { return { 
                            x: aTerm.label,
                            y: aTerm.total_items
                        } })
                    }
                ];
                this.childrenChartOptions = {
                    ...this.treeMapChartOptions, 
                    ...{
                        title: {},
                        chart: {
                            type: 'treemap',
                            height: 350,
                            toolbar: {
                                show: true
                            },
                            zoom: {
                                enabled: false
                            },
                            events: {
                                dataPointSelection: (event, chartContext, config) => {
                                    if (config.dataPointIndex >= 0 && orderedTerms[config.dataPointIndex]) {
                                        const existingParentTermIndex = this.selectedParentTerm.findIndex((term) => term.id == orderedTerms[config.dataPointIndex].value);
                                        if (existingParentTermIndex < 0) {

                                            // Removes siblings from the hierarchy, if existing
                                            if (this.selectedParentTerm.length && (this.selectedParentTerm[this.selectedParentTerm.length - 1].id != orderedTerms[config.dataPointIndex].parent) )
                                                this.selectedParentTerm.pop();

                                            const previousMetadatumChildTermsLatestCachedOn = this.metadatumChildTermsLatestCachedOn ? this.metadatumChildTermsLatestCachedOn.replace('-is-child-chart', '') : '';
                                            this.selectedParentTerm.push({
                                                id: orderedTerms[config.dataPointIndex].value,
                                                label: orderedTerms[config.dataPointIndex].label
                                            });
                                            
                                            this.setTaxonomyTerms(this.taxonomyChildTerms);
                                            this.setReportLatestCachedOn({
                                                report: 'taxonomy-terms-' + (this.collectionId ? this.collectionId : 'default') + '-' + this.selectedMetadatum.id + (this.selectedParentTerm.length > 2 && this.selectedParentTerm[this.selectedParentTerm.length - 2] && this.selectedParentTerm[this.selectedParentTerm.length - 2].id ? '-' + this.selectedParentTerm[this.selectedParentTerm.length - 1].id : ''),
                                                reportLatestCachedOn: previousMetadatumChildTermsLatestCachedOn
                                            });
                                            this.buildMetadatumTermsChart();
                                        }
                                    }
                                }
                            },
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '16px',
                            },
                            formatter: function(text, op) {
                                return [text, op.value]
                            },
                            offsetY: -4
                        },
                        tooltip: {
                             custom: ({ dataPointIndex }) => {
                                return `<div class="tainacan-custom-tooltip">
                                        <div class="tainacan-custom-tooltip__header">` + orderedTerms[dataPointIndex].label + `</div>
                                        <div class="tainacan-custom-tooltip__body">
                                            <span>` + this.$i18n.get('label_items_per_term') + `: <strong>` + orderedTerms[dataPointIndex].total_items + `</strong></span>
                                            `+ (orderedTerms[dataPointIndex].total_children 
                                                ? (`<span>` + this.$i18n.getWithVariables(orderedTerms[dataPointIndex].total_children > 1 ? 'instruction_click_to_see_%s_child_terms' : 'instruction_click_to_see_%s_child_term', [ orderedTerms[dataPointIndex].total_children ]) + `</span>`) 
                                                : ``
                                            ) +
                                        `</div></div>`;
                            }
                        },
                        noData: {
                            text: '0 ' + this.$i18n.get('label_items_with_this_metadatum_value')
                        }
                    }
                }
            } else {

                let termsValues = [];
                let termsLabels = [];

                orderedTerms.forEach(term => {
                    termsValues.push(term.total_items);
                    termsLabels.push(term.label);
                });
                
                this.childrenChartSeries = [
                    {
                        name: this.$i18n.get('label_items_per_term'),
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
                                    if (config.dataPointIndex >= 0 && orderedTerms[config.dataPointIndex]) {
                                        const previousMetadatumChildTermsLatestCachedOn = this.metadatumChildTermsLatestCachedOn ? this.metadatumChildTermsLatestCachedOn.replace('-is-child-chart', '') : '';
                                        this.selectedParentTerm.push({
                                            id: orderedTerms[config.dataPointIndex].value,
                                            label: orderedTerms[config.dataPointIndex].label
                                        });
                                        
                                        this.setTaxonomyTerms(this.taxonomyChildTerms);
                                        this.setReportLatestCachedOn({
                                            report: 'taxonomy-terms-' + (this.collectionId ? this.collectionId : 'default') + '-' + this.selectedMetadatum.id + (this.selectedParentTerm.length > 2 && this.selectedParentTerm[this.selectedParentTerm.length - 2] && this.selectedParentTerm[this.selectedParentTerm.length - 2].id ? '-' + this.selectedParentTerm[this.selectedParentTerm.length - 1].id : ''),
                                            reportLatestCachedOn: previousMetadatumChildTermsLatestCachedOn
                                        });
                                        this.buildMetadatumTermsChart();
                                    }
                                }
                            },
                        },
                        yaxis: {
                            title: {
                                text: this.$i18n.get('label_number_of_items')
                            }
                        },
                        tooltip: {
                            custom: ({ dataPointIndex }) => {
                                return `<div class="tainacan-custom-tooltip">
                                        <div class="tainacan-custom-tooltip__header">` + orderedTerms[dataPointIndex].label + `</div>
                                        <div class="tainacan-custom-tooltip__body">
                                            <span>` + this.$i18n.get('label_items_per_term') + `: <strong>` + orderedTerms[dataPointIndex].total_items + `</strong></span>
                                            `+ (orderedTerms[dataPointIndex].total_children 
                                                ? (`<span>` + this.$i18n.getWithVariables(orderedTerms[dataPointIndex].total_children > 1 ? 'instruction_click_to_see_%s_child_terms' : 'instruction_click_to_see_%s_child_term', [ orderedTerms[dataPointIndex].total_children ]) + `</span>`) 
                                                : ``
                                            ) +
                                        `</div></div>`;
                            }
                        },
                        animations: {
                            enabled: orderedTerms.length <= 40
                        },
                        noData: {
                            text: this.$i18n.get('label_items_with_this_metadatum_value')
                        }
                    }
                }
            }

            setTimeout(() => this.isBuildingChildrenChart = false, 500);
        },
        loadMetadatumTerms(force) {
            this.isFetchingMetadatumTerms = true;
            
            this.fetchTaxonomyTerms({
                    taxonomyId: this.selectedMetadatum.id,
                    collectionId: this.collectionId,
                    parentTerm: this.selectedParentTerm.length > 1 ? this.selectedParentTerm[this.selectedParentTerm.length - 2].id : null,
                    force: force
                })
                .then(() => {
                    this.buildMetadatumTermsChart();
                    this.isFetchingMetadatumTerms = false;
                })
                .catch(() => this.isFetchingMetadatumTerms = false);
        },
        loadMetadatumChildTerms(force) {
            this.isFetchingMetadatumChildTerms = true;
            
            this.fetchTaxonomyTerms({
                    taxonomyId: this.selectedMetadatum.id,
                    collectionId: this.collectionId,
                    parentTerm: this.selectedParentTerm[this.selectedParentTerm.length - 1].id,
                    isChildChart: true,
                    force: force
                })
                .then(() => {
                    this.buildMetadatumChildTermsChart();
                    this.isFetchingMetadatumChildTerms = false;
                })
                .catch(() => this.isFetchingMetadatumChildTerms = false);
        },
        backToParentTerm() {
            this.selectedParentTerm.pop();
            this.loadMetadatumTerms();
        }
    }
}
</script>

<style lang="scss" scoped>
.child-term-column {
    border-left: 1px dashed var(--tainacan-block-gray3, #cbcbcb);

    &>* {
        margin-left: 1.25rem;
    }
}
.hide-column-button {
    position: absolute;
    right: 0;
    top: calc(50% - 1rem);
    margin: 0;
    margin-right: -0.875rem;
    padding: 0px;
    border: 1px solid;
    background-color: white;
    z-index: 9;
}
</style>
