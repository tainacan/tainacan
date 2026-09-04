<template>
    <div 
            v-if="metadataList != undefined && metadataListArray.length"
            :class="{ 'skeleton': !hasChartSeries && (isFetchingData || isFetchingMetadatumTerms || !selectedMetadatum || !selectedMetadatum.id) }"
            class="report-card is-full">
        <div 
                :style="!isChildColumnCollapsed ? 'margin-inline-start: 0px;' : ''"
                :class="!isChildColumnCollapsed ? 'columns is-6' : ''">
            <div 
                    :class="!isChildColumnCollapsed ? 'column is-half is-full-tablet' : ''"
                    style="position: relative;">
                <div class="report-card-header">
                    <div 
                            v-if="selectedParentTerm.length <= 1"
                            class="report-card-header__item">
                        <label 
                                v-if="!isFetchingData"
                                for="select_metadata_for_terms">
                            {{ $i18n.get('label_items_per_term_from_taxonomy_metadatum') }}&nbsp;
                        </label>
                        <span class="select">
                            <select
                                    v-if="!isFetchingData"
                                    id="select_metadata_for_terms"
                                    v-model="selectedMetadatum"
                                    name="select_metadata_for_terms"
                                    :placeholder="$i18n.get('label_select_a_taxonomy_metadatum')">
                                <option 
                                        v-for="(metadatum, index) of metadataListArray"
                                        :key="index"
                                        :value="metadatum">
                                    {{ metadatum.name }} 
                                </option>
                            </select>
                        </span>
                        <div class="graph-mode-switch">
                            <button 
                                    :class="{ 'current': itemsPerTermChartMode == 'bar' }"
                                    @click="itemsPerTermChartMode = 'bar'">
                                <span class="sr-only">
                                    {{ $i18n.get('label_bar_chart') }}
                                </span>
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text tainacan-icon-rotate-270" />
                                </span>
                            </button>
                            <button 
                                    :class="{ 'current': itemsPerTermChartMode == 'treemap' }"
                                    @click="itemsPerTermChartMode = 'treemap'">
                                <span class="sr-only">
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
                            class="report-card-header__item"
                            style="display: flex; align-items: baseline;">
                        
                        <b-button
                                outlined
                                @click="backToParentTerm">
                            {{ $i18n.get('label_parent_term') }}
                        </b-button>&nbsp;
                        <span 
                                v-if="!isFetchingMetadatumChildTerms">
                            &nbsp;{{ $i18n.get('label_items_per_child_terms_of') }}&nbsp; <strong>{{ selectedParentTerm[selectedParentTerm.length - 2].label }}</strong>
                        </span>
                    </div>
                    <div 
                            v-if="selectedMetadatum && selectedMetadatum.id && currentTotalTerms >= 56"
                            class="report-card-header__item">
                        <label for="max_terms">{{ $i18n.get('label_terms_per_page') }}</label>
                        <input
                                id="max_terms"
                                v-model.number="maxTermsToDisplay"
                                type="number"
                                step="1"
                                min="1"
                                max="999"
                                class="screen-per-page"
                                name="max_terms"
                                maxlength="3"
                                :disabled="isFetchingMetadatumTerms">
                    </div>
                    <div 
                            v-if="selectedMetadatum && selectedMetadatum.id && currentTotalTerms >= 56"
                            class="report-card-header__item tablenav-pages">
                        <span class="displaying-num">{{ currentTotalTerms + ' ' + $i18n.get('terms') }}</span>
                        <span class="pagination-links">
                            <span
                                    :class="{'tablenav-pages-navspan disabled' : termsDisplayedPage <= 1 || isFetchingMetadatumTerms}"
                                    class="first-page button"
                                    aria-hidden="true"
                                    @click="!isFetchingMetadatumTerms ? termsDisplayedPage = 1 : null">
                                «
                            </span>
                            <span
                                    :class="{'tablenav-pages-navspan disabled' : termsDisplayedPage <= 1 || isFetchingMetadatumTerms}"
                                    class="prev-page button"
                                    aria-hidden="true"
                                    @click="(termsDisplayedPage > 1 && !isFetchingMetadatumTerms) ? termsDisplayedPage-- : null">
                                ‹
                            </span>
                            <span class="paging-input">
                                <label
                                        for="current-page-selector"
                                        class="sr-only">
                                    {{ $i18n.get('label_current_page') }}
                                </label>
                                <input
                                        id="current-page-selector"
                                        v-model.number="termsDisplayedPage"
                                        class="current-page"
                                        type="number"
                                        step="1"
                                        min="1"
                                        :disabled="isFetchingMetadatumTerms || maxTermsToDisplay >= currentTotalTerms"
                                        :max="Math.ceil(currentTotalTerms/maxTermsToDisplay)"
                                        name="paged"
                                        size="1"
                                        aria-describedby="table-paging">
                                <span class="tablenav-paging-text"> {{ $i18n.get('info_of') }} <span class="total-pages">{{ Math.ceil(currentTotalTerms/maxTermsToDisplay) }}</span></span>
                            </span>
                            <span 
                                    :class="{'tablenav-pages-navspan disabled' : isFetchingMetadatumTerms || termsDisplayedPage >= Math.ceil(currentTotalTerms/maxTermsToDisplay) }"
                                    aria-hidden="true"
                                    class="icon next-page button is-outlined"
                                    @click="(!isFetchingMetadatumTerms && termsDisplayedPage < Math.ceil(currentTotalTerms/maxTermsToDisplay)) ? termsDisplayedPage++ : null">
                                <i class="tainacan-icon tainacan-icon-previous tainacan-icon-is-rtl-mirrored tainacan-icon-1-25em" />
                            </span>
                            <span
                                    :class="{'tablenav-pages-navspan disabled': isFetchingMetadatumTerms || termsDisplayedPage >= Math.ceil(currentTotalTerms/maxTermsToDisplay) }"
                                    class="icon last-page button is-outlined"
                                    aria-hidden="true"
                                    @click="!isFetchingMetadatumTerms ? termsDisplayedPage = Math.ceil(currentTotalTerms/maxTermsToDisplay) : null">
                                <i class="tainacan-icon tainacan-icon-next tainacan-icon-1-25em" />
                            </span>
                        </span>
                    </div>
                </div>
                <div
                        class="report-chart"
                        :class="{ 'is-reloading-cache': isReloadingCache }"
                        :aria-busy="isReloadingCache ? 'true' : 'false'">
                    <apexchart
                            v-if="hasChartSeries && selectedMetadatum && selectedMetadatum.id"
                            ref="parentTermsChart"
                            :key="'parent-' + itemsPerTermChartMode"
                            :type="itemsPerTermChartMode"
                            height="380px"
                            :series="chartSeries"
                            :options="chartOptions"
                            @data-point-selection="handleDataPointClick" />
                </div>
                <button 
                        v-if="!isFetchingData && selectedMetadatum"
                        type="button"
                        class="button is-outlined hide-column-button"
                        @click="isChildColumnCollapsed = !isChildColumnCollapsed">
                    <span class="icon">
                        <i 
                                :class="isChildColumnCollapsed ? 'tainacan-icon-arrowleft' : 'tainacan-icon-arrowright'"
                                class="tainacan-icon tainacan-icon-1-25em tainacan-icon-is-rtl-mirrored" />
                    </span>
                </button>
            </div>
            <div 
                    v-if="!isChildColumnCollapsed && !isFetchingData && selectedMetadatum"
                    class="child-term-column column is-half is-full-tablet"
                    style="position: relative;">
                <div v-if="selectedParentTerm[selectedParentTerm.length - 1]">
                    <div class="report-card-header">
                        <div class="report-card-header__item">
                            <span 
                                    v-if="!isFetchingMetadatumChildTerms">
                                {{ $i18n.get('label_items_per_child_terms_of') }}&nbsp; <strong>{{ selectedParentTerm[selectedParentTerm.length - 1].label }}</strong>
                            </span>
                        </div>
                        <div 
                                v-if="currentTotalChildTerms >= 56"
                                class="report-card-header__item">
                            <label for="max_terms">{{ $i18n.get('label_terms_per_page') }}</label>
                            <input
                                    id="max_terms"
                                    v-model.number="maxChildTermsToDisplay"
                                    type="number"
                                    step="1"
                                    min="1"
                                    max="999"
                                    class="screen-per-page"
                                    name="max_terms"
                                    maxlength="3"
                                    :disabled="isFetchingMetadatumChildTerms">
                        </div>
                        <div 
                                v-if="currentTotalChildTerms >= 56"
                                class="report-card-header__item tablenav-pages">
                            <span class="displaying-num">{{ currentTotalChildTerms + ' ' + $i18n.get('terms') }}</span>
                            <span class="pagination-links">
                                <span
                                        :class="{'tablenav-pages-navspan disabled' : childTermsDisplayedPage <= 1 || isFetchingMetadatumChildTerms}"
                                        class="first-page button"
                                        aria-hidden="true"
                                        @click="!isFetchingMetadatumChildTerms ? childTermsDisplayedPage = 1 : null">
                                    «
                                </span>
                                <span
                                        :class="{'tablenav-pages-navspan disabled' : childTermsDisplayedPage <= 1 || isFetchingMetadatumChildTerms}"
                                        class="prev-page button"
                                        aria-hidden="true"
                                        @click="(childTermsDisplayedPage > 1 && !isFetchingMetadatumChildTerms) ? childTermsDisplayedPage-- : null">
                                    ‹
                                </span>
                                <span class="paging-input">
                                    <label
                                            for="current-page-selector"
                                            class="sr-only">
                                        {{ $i18n.get('label_current_page') }}
                                    </label>
                                    <input
                                            id="current-page-selector"
                                            v-model.number="childTermsDisplayedPage"
                                            class="current-page"
                                            type="number"
                                            step="1"
                                            min="1"
                                            :disabled="isFetchingMetadatumChildTerms || maxChildTermsToDisplay >= currentTotalChildTerms"
                                            :max="Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay)"
                                            name="paged"
                                            size="1"
                                            aria-describedby="table-paging">
                                    <span class="tablenav-paging-text"> {{ $i18n.get('info_of') }} <span class="total-pages">{{ Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay) }}</span></span>
                                </span>
                                <span 
                                        :class="{'tablenav-pages-navspan disabled' : isFetchingMetadatumChildTerms || childTermsDisplayedPage >= Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay) }"
                                        aria-hidden="true"
                                        class="next-page button"
                                        @click="(!isFetchingMetadatumChildTerms && childTermsDisplayedPage < Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay)) ? childTermsDisplayedPage++ : null">
                                    ›
                                </span>
                                <span
                                        :class="{'tablenav-pages-navspan disabled': isFetchingMetadatumChildTerms || childTermsDisplayedPage >= Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay) }"
                                        class="last-page button"
                                        aria-hidden="true"
                                        @click="!isFetchingMetadatumChildTerms ? childTermsDisplayedPage = Math.ceil(currentTotalChildTerms/maxChildTermsToDisplay) : null">
                                    »
                                </span>
                            </span>
                        </div>
                    </div>
                    <div
                            class="report-chart"
                            :class="{ 'is-reloading-cache': isReloadingChildCache }"
                            :aria-busy="isReloadingChildCache ? 'true' : 'false'">
                        <apexchart
                                v-if="hasChildrenChartSeries"
                                ref="childTermsChart"
                                :key="'child-' + itemsPerTermChartMode"
                                :type="itemsPerTermChartMode"
                                height="380px"
                                :series="childrenChartSeries"
                                :options="childrenChartOptions"
                                @data-point-selection="handleDataPointClickChildren" />
                    </div>
                </div>
                <div 
                        v-else
                        class="empty-report-card-placeholder">
                    <p class="title is-4">
                        <span class="icon has-text-dark">
                            <i class="tainacan-icon tainacan-icon-taxonomies tainacan-icon-1em" />
                        </span>
                        &nbsp;{{ $i18n.get('label_children_terms') }}
                    </p>
                    <br>
                    <p class="subtitle is-6">
                        {{ $i18n.get('info_child_terms_chart') }}
                    </p>
                </div>
            </div>
        </div>
        <div 
                v-if="metadatumTermsLatestCachedOn"
                style="left: calc(1px + 0.75rem); right: auto;"
                class="report-last-cached-on"
                :class="{ 'is-reloading': isReloadingCache }">
            <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(metadatumTermsLatestCachedOn).toLocaleString() }}</span>
            <button 
                    type="button"
                    :disabled="isReloadingCache"
                    @click="loadMetadatumTerms(true)">
                <span class="sr-only">
                    {{ $i18n.get('label_get_latest_report') }}
                </span>
                <span class="icon">
                    <i 
                            class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270"
                            :class="{ 'tainacan-icon-spin': isReloadingCache }" />
                </span>
            </button>
        </div>
        <div 
                v-if="!isChildColumnCollapsed && !isFetchingData && !isFetchingMetadatumTerms && selectedMetadatum && metadatumChildTermsLatestCachedOn"
                class="report-last-cached-on"
                :class="{ 'is-reloading': isReloadingChildCache }">
            <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(metadatumChildTermsLatestCachedOn).toLocaleString() }}</span>
            <button 
                    type="button"
                    :disabled="isReloadingChildCache"
                    @click="loadMetadatumChildTerms(true)">
                <span class="sr-only">
                    {{ $i18n.get('label_get_latest_report') }}
                </span>
                <span class="icon">
                    <i 
                            class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270"
                            :class="{ 'tainacan-icon-spin': isReloadingChildCache }" />
                </span>
            </button>
        </div>
    </div>
    
    <div 
            v-if="metadataList != undefined && !isFetchingData && (!metadataListArray || !metadataListArray.length)"
            style="min-height:380px"
            class="report-card is-full">
        <div class="empty-report-card-placeholder">
            <p class="title is-4">
                <span class="icon has-text-dark">
                    <i class="tainacan-icon tainacan-icon-metadata tainacan-icon-1em" />
                </span>
                &nbsp;{{ $i18n.get('label_items_per_term_from_taxonomy_metadatum') }}
            </p>
            <br>
            <p class="subtitle is-6">
                {{ $i18n.get('info_no_taxonomy_metadata_created') }}
            </p>
        </div>
    </div>
</template>

<script>
import { mapActions, mapMutations, mapGetters } from 'vuex';
import { reportsChartMixin } from '../../js/mixins';

export default {
    mixins: [ reportsChartMixin ],
    props: {
        collectionId: ''
    },
    data() {
        return {
            isFetchingMetadatumTerms: false,
            isReloadingCache: false,
            selectedMetadatum: {},
            maxTermsToDisplay: 56,
            termsDisplayedPage: 1,
            selectedParentTerm: [],
            isFetchingMetadatumChildTerms: false,
            isReloadingChildCache: false,
            childrenChartSeries: [],
            childrenChartOptions: {},
            maxChildTermsToDisplay: 56,
            childTermsDisplayedPage: 1,
            isChildColumnCollapsed: false,
            itemsPerTermChartMode: 'bar',
            orderedTerms: [],
            orderedChildTerms: []
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
        },
        hasChildrenChartSeries() {
            return Array.isArray(this.childrenChartSeries) && this.childrenChartSeries.length > 0;
        }
    },
    watch: {
        metadataListArray: {
            handler() {
                if (this.metadataListArray && this.metadataListArray.length)
                    this.selectedMetadatum = this.metadataListArray[0];
            },
            immediate: true,
            deep: true
        },
        selectedMetadatum: {
            handler() {
                this.termsDisplayedPage = 1;
                if (this.selectedMetadatum && this.selectedMetadatum.id) {
                    this.selectedParentTerm = [];
                    this.loadMetadatumTerms();
                }
            },
            immediate: true,
            deep: true
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
        selectedParentTerm: {
            handler() {
                if (this.selectedParentTerm[this.selectedParentTerm.length - 1] && this.selectedParentTerm[this.selectedParentTerm.length - 1].id) {
                    this.loadMetadatumChildTerms();
                } else {
                    this.childrenChartSeries = [];
                    this.childrenChartOptions = {};
                    this.orderedChildTerms = [];
                }
            },
            deep: true
        },
        itemsPerTermChartMode() {
            this.termsDisplayedPage = 1;
            this.childTermsDisplayedPage = 1;
            this.buildMetadatumTermsChart();
            if (this.selectedParentTerm.length && this.selectedParentTerm[this.selectedParentTerm.length - 1])
                this.buildMetadatumChildTermsChart();
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
        handleDataPointClick(event, chartContext, config) {
            if ( this.itemsPerTermChartMode == 'treemap' ) {
                if (config.dataPointIndex >= 0 && this.orderedTerms[config.dataPointIndex]) {
                    const existingParentTermIndex = this.selectedParentTerm.findIndex((term) => term.id == this.orderedTerms[config.dataPointIndex].value);
                    if (existingParentTermIndex < 0) {
                        this.selectedParentTerm.push({
                            id: this.orderedTerms[config.dataPointIndex].value,
                            label: this.orderedTerms[config.dataPointIndex].label
                        })
                    }
                }
            } else {
                if (config.dataPointIndex >= 0 && this.orderedTerms[config.dataPointIndex]) {
                    const existingParentTermIndex = this.selectedParentTerm.findIndex((term) => term.id == this.orderedTerms[config.dataPointIndex].value);
                    if (existingParentTermIndex < 0) {
                        // Removes siblings from the hierarchy, if existing
                        if (this.selectedParentTerm.length && (this.selectedParentTerm[this.selectedParentTerm.length - 1].id != this.orderedTerms[config.dataPointIndex].parent) )
                            this.selectedParentTerm.pop();

                        this.selectedParentTerm.push({
                            id: this.orderedTerms[config.dataPointIndex].value,
                            label: this.orderedTerms[config.dataPointIndex].label
                        });
                    }
                }
            }
        },
        handleDataPointClickChildren(event, chartContext, config) {
            if ( this.itemsPerTermChartMode == 'treemap' ) {
                if (config.dataPointIndex >= 0 && this.orderedChildTerms[config.dataPointIndex]) {
                    const existingParentTermIndex = this.selectedParentTerm.findIndex((term) => term.id == this.orderedChildTerms[config.dataPointIndex].value);
                    if (existingParentTermIndex < 0) {

                        // Removes siblings from the hierarchy, if existing
                        if (this.selectedParentTerm.length && (this.selectedParentTerm[this.selectedParentTerm.length - 1].id != this.orderedChildTerms[config.dataPointIndex].parent) )
                            this.selectedParentTerm.pop();

                        const previousMetadatumChildTermsLatestCachedOn = this.metadatumChildTermsLatestCachedOn ? this.metadatumChildTermsLatestCachedOn.replace('-is-child-chart', '') : '';
                        this.selectedParentTerm.push({
                            id: this.orderedChildTerms[config.dataPointIndex].value,
                            label: this.orderedChildTerms[config.dataPointIndex].label
                        });
                        
                        this.setTaxonomyTerms(this.taxonomyChildTerms);
                        this.setReportLatestCachedOn({
                            report: 'taxonomy-terms-' + (this.collectionId ? this.collectionId : 'default') + '-' + this.selectedMetadatum.id + (this.selectedParentTerm.length > 2 && this.selectedParentTerm[this.selectedParentTerm.length - 2] && this.selectedParentTerm[this.selectedParentTerm.length - 2].id ? '-' + this.selectedParentTerm[this.selectedParentTerm.length - 1].id : ''),
                            reportLatestCachedOn: previousMetadatumChildTermsLatestCachedOn
                        });
                        this.buildMetadatumTermsChart();
                    }
                }
            } else {
                if (config.dataPointIndex >= 0 && this.orderedChildTerms[config.dataPointIndex]) {
                    const previousMetadatumChildTermsLatestCachedOn = this.metadatumChildTermsLatestCachedOn ? this.metadatumChildTermsLatestCachedOn.replace('-is-child-chart', '') : '';
                    this.selectedParentTerm.push({
                        id: this.orderedChildTerms[config.dataPointIndex].value,
                        label: this.orderedChildTerms[config.dataPointIndex].label
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
        getItemsPerTermTooltipHtml(terms, dataPointIndex) {
            const term = terms[dataPointIndex];
            if (!term)
                return '';

            return `<div class="tainacan-custom-tooltip">
                    <div class="tainacan-custom-tooltip__header">` + term.label + `</div>
                    <div class="tainacan-custom-tooltip__body">
                        <span>` + this.$i18n.get('label_items_per_term') + `: <strong>` + term.total_items + `</strong></span>
                        `+ (term.total_children 
                            ? (`<span>` + this.$i18n.getWithVariables(term.total_children > 1 ? 'instruction_click_to_see_%s_child_terms' : 'instruction_click_to_see_%s_child_term', [ term.total_children ]) + `</span>`) 
                            : ``
                        ) +
                    `</div></div>`;
        },
        buildItemsPerTermChartOptions({ stacked, getTerms }) {
            const tooltipCustom = ({ dataPointIndex }) => this.getItemsPerTermTooltipHtml(getTerms(), dataPointIndex);

            if (this.itemsPerTermChartMode == 'treemap') {
                return {
                    ...this.treeMapChartOptions, 
                    ...{
                        title: {},
                        chart: {
                            type: 'treemap',
                            height: 350,
                            toolbar: {
                                show: true,
                                export: {
                                    scale: 3
                                }
                            },
                            zoom: {
                                enabled: false
                            }
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
                            custom: tooltipCustom
                        },
                        noData: {
                            text: '0 ' + this.$i18n.get('label_items_with_this_metadatum_value')
                        }
                    }
                };
            }

            return {
                ...this.stackedBarChartOptions, 
                ...{
                    title: {},
                    xaxis: {
                        type: 'category',
                        tickPlacement: 'on',
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
                        stacked: stacked,
                        toolbar: {
                            show: true,
                            export: {
                                scale: 3
                            }
                        },
                        zoom: {
                            enabled: true,
                            autoScaleYaxis: true,
                        }
                    },
                    tooltip: {
                        custom: tooltipCustom
                    },
                    yaxis: {
                        title: {
                            text: this.$i18n.get('label_number_of_items')
                        }
                    },
                    animations: {
                        enabled: getTerms().length <= 40
                    },
                    noData: {
                        text: '0 ' + this.$i18n.get('label_items_with_this_metadatum_value')
                    }
                }
            };
        },
        buildMetadatumTermsChart() {
            if (!Array.isArray(this.taxonomyTerms))
                return;

            let preOrderedTerms = JSON.parse(JSON.stringify(this.taxonomyTerms)).sort((a, b) => b.total_items - a.total_items );
            this.orderedTerms = preOrderedTerms.slice((this.termsDisplayedPage - 1) * this.maxTermsToDisplay, ((this.termsDisplayedPage - 1) * this.maxTermsToDisplay) + this.maxTermsToDisplay);

            this.chartSeries = [
                {
                    name: this.$i18n.get('label_items_per_term'),
                    data: this.orderedTerms.map((aTerm) => ({ 
                        x: aTerm.label,
                        y: aTerm.total_items
                    }))
                }
            ];

            if (!this.chartOptions.chart || this.chartOptions.chart.type !== this.itemsPerTermChartMode) {
                this.chartOptions = this.buildItemsPerTermChartOptions({
                    stacked: false,
                    getTerms: () => this.orderedTerms
                });
            } else {
                this.syncBarChartCategories('parentTermsChart', this.orderedTerms);
            }
        },
        buildMetadatumChildTermsChart() {
            if (!Array.isArray(this.taxonomyChildTerms))
                return;

            let preOrderedTerms = JSON.parse(JSON.stringify(this.taxonomyChildTerms)).sort((a, b) => b.total_items - a.total_items );
            this.orderedChildTerms = preOrderedTerms.slice((this.childTermsDisplayedPage - 1) * this.maxChildTermsToDisplay, ((this.childTermsDisplayedPage - 1) * this.maxChildTermsToDisplay) + this.maxChildTermsToDisplay);

            this.childrenChartSeries = [
                {
                    name: this.$i18n.get('label_items_per_term'),
                    data: this.orderedChildTerms.map((aTerm) => ({ 
                        x: aTerm.label,
                        y: aTerm.total_items
                    }))
                }
            ];

            if (!this.childrenChartOptions.chart || this.childrenChartOptions.chart.type !== this.itemsPerTermChartMode) {
                this.childrenChartOptions = this.buildItemsPerTermChartOptions({
                    stacked: true,
                    getTerms: () => this.orderedChildTerms
                });
            } else {
                this.syncBarChartCategories('childTermsChart', this.orderedChildTerms);
            }
        },
        syncBarChartCategories(refName, terms) {
            if (this.itemsPerTermChartMode !== 'bar')
                return;

            this.$nextTick(() => {
                const chartComponent = this.$refs[refName];
                if (chartComponent && chartComponent.updateOptions) {
                    chartComponent.updateOptions({
                        xaxis: { categories: terms.map(term => term.label) }
                    }, false, true);
                }
            });
        },
        loadMetadatumTerms(force) {
            this.isFetchingMetadatumTerms = true;
            this.isReloadingCache = !!force;
            
            this.fetchTaxonomyTerms({
                    taxonomyId: this.selectedMetadatum.id,
                    collectionId: this.collectionId,
                    parentTerm: this.selectedParentTerm.length > 1 ? this.selectedParentTerm[this.selectedParentTerm.length - 2].id : null,
                    force: force
                })
                .then(() => {
                    this.buildMetadatumTermsChart();
                    this.isFetchingMetadatumTerms = false;
                    this.isReloadingCache = false;
                })
                .catch(() => {
                    this.isFetchingMetadatumTerms = false;
                    this.isReloadingCache = false;
                });
        },
        loadMetadatumChildTerms(force) {
            this.isFetchingMetadatumChildTerms = true;
            this.isReloadingChildCache = !!force;
            
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
                    this.isReloadingChildCache = false;
                })
                .catch(() => {
                    this.isFetchingMetadatumChildTerms = false;
                    this.isReloadingChildCache = false;
                });
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
    border-inline-start: 1px dashed var(--tainacan-gray3, #a5a5a5);

    &>* {
        margin-inline-start: 1.25rem;
    }
}
.hide-column-button {
    position: absolute;
    inset-inline-end: 0;
    top: calc(50% - 1rem);
    margin: 0;
    margin-inline-end: -0.875rem;
    padding: 0px;
    border: 1px solid;
    background-color: var(--tainacan-gray1) !important;
    z-index: 9;
}
</style>
