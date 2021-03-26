<template>
     <div
            v-if="taxonomiesList != undefined"
            class="column is-full">
        <div class="postbox">
            <div class="box-header">
                <div class="box-header__item">
                    <label 
                            v-if="!isFetchingTaxonomiesList"
                            for="select_taxonomies">
                        {{ $i18n.get('label_items_per_term_from_taxonomy') }}&nbsp;
                    </label>
                    <select
                            v-if="!isFetchingTaxonomiesList"
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
                        v-if="selectedTaxonomy && selectedTaxonomy.id"
                        class="box-header__item">
                    <label for="max_terms">Termos por página:</label>
                    <input
                            type="number"
                            step="1"
                            min="1"
                            max="999"
                            class="screen-per-page"
                            name="max_terms"
                            id="max_terms"
                            maxlength="3"
                            :disabled="isBuildingTaxonomyTermsChart"
                            v-model.number="maxTermsToDisplay">
                </div>
                <div 
                        v-if="selectedTaxonomy && selectedTaxonomy.id"
                        class="box-header__item tablenav-pages">
                    <span class="displaying-num">{{ selectedTaxonomy.total_terms + ' ' + $i18n.get('terms') }}</span>
                    <span class="pagination-links">
                        <span
                                @click="!isBuildingTaxonomyTermsChart ? termsDisplayedPage = 1 : null"
                                :class="{'tablenav-pages-navspan disabled' : termsDisplayedPage <= 1 || isBuildingTaxonomyTermsChart}"
                                class="first-page button"
                                aria-hidden="true">
                            «
                        </span>
                        <span
                                @click="(termsDisplayedPage > 1 && !isBuildingTaxonomyTermsChart) ? termsDisplayedPage-- : null"
                                :class="{'tablenav-pages-navspan disabled' : termsDisplayedPage <= 1 || isBuildingTaxonomyTermsChart}"
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
                                    :disabled="isBuildingTaxonomyTermsChart || maxTermsToDisplay >= selectedTaxonomy.total_terms"
                                    :max="Math.ceil(selectedTaxonomy.total_terms/maxTermsToDisplay)"
                                    name="paged"
                                    v-model.number="termsDisplayedPage"
                                    size="1"
                                    aria-describedby="table-paging">
                            <span class="tablenav-paging-text"> de <span class="total-pages">{{ Math.ceil(selectedTaxonomy.total_terms/maxTermsToDisplay) }}</span></span>
                        </span>
                        <span 
                                @click="(!isBuildingTaxonomyTermsChart && termsDisplayedPage < Math.ceil(selectedTaxonomy.total_terms/maxTermsToDisplay)) ? termsDisplayedPage++ : null"
                                :class="{'tablenav-pages-navspan disabled' : isBuildingTaxonomyTermsChart || termsDisplayedPage >= Math.ceil(selectedTaxonomy.total_terms/maxTermsToDisplay) }"
                                aria-hidden="true"
                                class="next-page button">
                            ›
                        </span>
                        <span
                                @click="!isBuildingTaxonomyTermsChart ? termsDisplayedPage = Math.ceil(selectedTaxonomy.total_terms/maxTermsToDisplay) : null"
                                :class="{'tablenav-pages-navspan disabled': isBuildingTaxonomyTermsChart || termsDisplayedPage >= Math.ceil(selectedTaxonomy.total_terms/maxTermsToDisplay) }"
                                class="last-page button"
                                aria-hidden="true">
                            »
                        </span>
                    </span>
                </div>
            </div>
            <apexchart
                    v-if="!isFetchingTaxonomiesList && !isFetchingTaxonomyTerms && selectedTaxonomy && selectedTaxonomy.id"
                    height="380px"
                    :series="taxonomyTermsChartSeries"
                    :options="taxonomyTermsChartOptions" />
            <div 
                v-else
                style="min-height=380px"
                class="skeleton postbox" />
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    data() {
        return {
            isFetchingTaxonomyTerms: false,
            isBuildingTaxonomyTermsChart: false,
            selectedTaxonomy: {},
            maxTermsToDisplay: 64,
            termsDisplayedPage: 1,
            taxonomyTermsChartSeries: [],
            taxonomyTermsChartOptions: {},
        }
    },
    computed: {
        ...mapGetters('report', {
            taxonomyTerms: 'getTaxonomyTerms',
            taxonomiesList: 'getTaxonomiesList',
            stackedBarChartOptions: 'getStackedBarChartOptions',
        }),
        isFetchingTaxonomiesList() {
            return !(this.taxonomiesListArray && this.taxonomiesListArray.length);
        },
        taxonomiesListArray() {
            return this.taxonomiesList && this.taxonomiesList != undefined ? Object.values(this.taxonomiesList) : [];
        },
    },
    watch: {
        taxonomiesListArray: {
            handler() {
                if (this.taxonomiesListArray && this.taxonomiesListArray.length)
                    this.selectedTaxonomy = this.taxonomiesListArray[0];
            },
            immediate: true
        },
        selectedTaxonomy() {
            this.termsDisplayedPage = 1;
            if (this.selectedTaxonomy && this.selectedTaxonomy.id)
                this.loadTaxonomyTerms();
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
        
            this.isBuildingTaxonomyTermsChart = true;

            // Building Taxonomy term usage chart
            let orderedTerms = Object.values(this.taxonomyTerms).sort((a, b) => b.count - a.count);
            orderedTerms = orderedTerms.slice((this.termsDisplayedPage - 1) * this.maxTermsToDisplay, ((this.termsDisplayedPage - 1) * this.maxTermsToDisplay) + this.maxTermsToDisplay);
            
            let termsValues = [];
            let termsLabels = [];

            orderedTerms.forEach(term => {
                termsValues.push(term.count);
                termsLabels.push(term.name);
            });
            
            this.taxonomyTermsChartSeries = [
                {
                    name: this.$i18n.get('label_terms_used'),
                    data: termsValues
                }
            ];
            this.taxonomyTermsChartOptions = {
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
                        tooltip: true
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

            setTimeout(() => { 
                this.isBuildingTaxonomyTermsChart = false;
            }, 500);
        },
        loadTaxonomyTerms() {
            this.isFetchingTaxonomyTerms = true;

            this.fetchTaxonomyTerms(this.selectedTaxonomy.id)
                .then(() => {
                    this.buildTaxonomyTermsChart();
                    this.isFetchingTaxonomyTerms = false;
                })
                .catch(() => this.isFetchingTaxonomyTerms = false);

        }
    }
}
</script>