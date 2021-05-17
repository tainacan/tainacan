<template>
    <div v-if="taxonomiesList != undefined">
        <div 
                v-if="taxonomiesListArray.length"
                :class="{ 'skeleton': isFetchingData || isBuildingChart || isFetchingTaxonomyTerms || !selectedTaxonomy || !selectedTaxonomy.id }"
                class="postbox">
            <div class="box-header">
                <div class="box-header__item">
                    <label 
                            v-if="!isFetchingData"
                            for="select_taxonomies">
                        {{ $i18n.get('label_items_per_term_from_taxonomy') }}&nbsp;
                    </label>
                    <select
                            v-if="!isFetchingData"
                            name="select_taxonomies"
                            id="select_taxonomies"
                            :placeholder="$i18n.get('label_select_a_taxonomy')"
                            v-model="selectedTaxonomy">
                        <option 
                                v-for="(taxonomy, index) of taxonomiesListArray"
                                :key="index"
                                :value="taxonomy">
                            {{ taxonomy.name + ' (' + taxonomy.total_terms + ' ' + ( taxonomy.total_terms == 1 ? $i18n.get('term') : $i18n.get('terms') ) + ')' }} 
                        </option>
                    </select>
                </div>
                <div 
                        v-if="selectedTaxonomy && selectedTaxonomy.id && currentTotalTerms >= 56"
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
                        v-if="selectedTaxonomy && selectedTaxonomy.id && currentTotalTerms >= 56"
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
                            <span class="tablenav-paging-text"> de <span class="total-pages">{{ Math.ceil(currentTotalTerms/maxTermsToDisplay) }}</span></span>
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
                    v-if="!isFetchingData && !isBuildingChart && !isFetchingTaxonomyTerms && selectedTaxonomy && selectedTaxonomy.id"
                    height="380px"
                    :series="chartSeries"
                    :options="chartOptions" />
        </div>
        <div 
                v-if="taxonomyTermsLatestCachedOn"
                class="box-last-cached-on">
            <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(taxonomyTermsLatestCachedOn).toLocaleString() }}</span>
            <button 
                    @click="loadTaxonomyTerms(true)">
                <span class="screen-reader-text">
                    {{ $i18n.get('label_get_latest_report') }}
                </span>
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                </span>
            </button>
        </div>
        <div 
                v-if="!isFetchingData && !isBuildingChart && (!taxonomiesListArray || !taxonomiesListArray.length)"
                style="min-height:380px"
                class="postbox">
            <div class="empty-postbox-placeholder">
                <p class="title is-4">
                    <span class="icon has-text-gray">
                        <i class="tainacan-icon tainacan-icon-taxonomies tainacan-icon-1-125em" />
                    </span>
                    &nbsp;{{ $i18n.get('taxonomies') }}
                </p>
                <br>
                <p class="subtitle is-6">{{ $i18n.get('info_no_taxonomy_created') }}</p>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { reportsChartMixin } from '../js/reports-mixin';

export default {
    mixins: [ reportsChartMixin ],
    data() {
        return {
            isFetchingTaxonomyTerms: false,
            selectedTaxonomy: {},
            maxTermsToDisplay: 56,
            termsDisplayedPage: 1
        }
    },
    computed: {
        ...mapGetters('report', {
            taxonomiesList: 'getTaxonomiesList',
            stackedBarChartOptions: 'getStackedBarChartOptions',
            reportsLatestCachedOn: 'getReportsLatestCachedOn'
        }),
        taxonomiesListArray() {
            return this.taxonomiesList && this.taxonomiesList != undefined ? Object.values(this.taxonomiesList) : [];
        },
        taxonomyTermsLatestCachedOn() {
            return this.reportsLatestCachedOn['taxonomy-terms-default-' + this.selectedTaxonomy.id];
        },
        currentTotalTerms() {
            return Array.isArray(this.chartData) ? this.chartData.length : 0 
        }
    },
    watch: {
        taxonomiesListArray: {
            handler() {
                if (this.taxonomiesListArray && this.taxonomiesListArray.length)
                    this.selectedTaxonomy = this.taxonomiesListArray[0];
            },
            immediate: true
        },
        selectedTaxonomy: {
            handler() {
                this.termsDisplayedPage = 1;
                if (this.selectedTaxonomy && this.selectedTaxonomy.id)
                    this.loadTaxonomyTerms();
            },
            immediate: true
        },
        termsDisplayedPage() {
            this.buildTaxonomyTermsChart();
        },
        maxTermsToDisplay() {
            this.termsDisplayedPage = 1;
            this.buildTaxonomyTermsChart();
        }
    },
    methods: {
        ...mapActions('report', [
            'fetchTaxonomyTerms'
        ]),
        buildTaxonomyTermsChart() {
        
            this.isBuildingChart = true;
            
            // Building Taxonomy term usage chart
            let orderedTerms = JSON.parse(JSON.stringify(this.chartData)).sort((a, b) => b.count - a.count);
            orderedTerms = orderedTerms.slice((this.termsDisplayedPage - 1) * this.maxTermsToDisplay, ((this.termsDisplayedPage - 1) * this.maxTermsToDisplay) + this.maxTermsToDisplay);
            
            let termsValues = [];
            let termsLabels = [];

            orderedTerms.forEach(term => {
                termsValues.push(term.count);
                termsLabels.push(term.name);
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
                    yaxis: {
                        title: {
                            text: this.$i18n.get('label_number_of_items')
                        }
                    },
                    animations: {
                        enabled: orderedTerms.length <= 40
                    },
                    colors: ['#01295c'],
                }
            }

            setTimeout(() => this.isBuildingChart = false, 500);
        },
        loadTaxonomyTerms(force) {
            this.isFetchingTaxonomyTerms = true;
            
            this.fetchTaxonomyTerms({ taxonomyId: this.selectedTaxonomy.id, force: force })
                .then(() => {
                    this.buildTaxonomyTermsChart();
                    this.isFetchingTaxonomyTerms = false;
                })
                .catch(() => this.isFetchingTaxonomyTerms = false);
        }
    }
}
</script>