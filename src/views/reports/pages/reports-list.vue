<template>
    <div>
        <h1 class="wp-heading-inline">{{ $route.meta.title }}</h1>
        <select 
                name="select_collections"
                id="select_collections"
                @input="(inputEvent) => $router.push({ query: { collection: inputEvent.target.value } })"
                :value="selectedCollection">
            <option value="default">
                {{ $i18n.get('repository') }}
            </option>
            <option 
                    v-for="(collection, index) of collections"
                    :key="index"
                    :value="collection.id">
                {{ collection.name }}
            </option>
        </select>
        <div class="columns is-multiline">
            <div 
                    v-if="!selectedCollection || selectedCollection == 'default'"
                    class="column is-full is-one-third-tablet has-text-centered">
                <number-block
                        :class="{ 'skeleton': isFetchingSummary }"
                        class="postbox"
                        :source-collection="selectedCollection"
                        :summary="summary"
                        entity-type="collections"/>
            </div>
            <div
                    :class="{ 'is-one-third-tablet': !selectedCollection || selectedCollection == 'default' }"
                    class="column is-full has-text-centered">
                <number-block 
                        :class="{ 'skeleton': isFetchingSummary }"
                        class="postbox"
                        :source-collection="selectedCollection"
                        :summary="summary"
                        entity-type="items"/>
            </div>
            <div 
                    v-if="!selectedCollection || selectedCollection == 'default'"
                    class="column is-full is-one-third-tablet has-text-centered">
               <number-block
                        :class="{ 'skeleton': isFetchingSummary }"
                        class="postbox"
                        :source-collection="selectedCollection"
                        :summary="summary"
                        entity-type="taxonomies"/>
            </div>
            <div class="column is-full">
                <chart-block
                        :class="{ 'skeleton': isFetchingTaxonomiesList}"
                        class="postbox"
                        :chart-series="taxonomiesListChartSeries"
                        :chart-options="taxonomiesListChartOptions" />
            </div>
            <div class="column is-half is-one-quarter-widescreen">
                <chart-block
                        class="postbox"
                        :chart-series="reports[0].chartSeries"
                        :chart-options="reports[0].chartOptions" />
            </div>
            <div class="column is-half is-one-quarter-widescreen">
                <chart-block
                        class="postbox"
                        :chart-series="reports[1].chartSeries"
                        :chart-options="reports[1].chartOptions" />
            </div>
            <div class="column is-full is-half-widescreen">
                <chart-block
                        class="postbox"
                        :chart-series="reports[2].chartSeries"
                        :chart-options="reports[2].chartOptions" />
            </div>
            <div class="column is-full is-half-desktop">
                <chart-block
                        class="postbox"
                        :chart-series="reports[4].chartSeries"
                        :chart-options="reports[4].chartOptions" />
            </div>
            <div class="column is-full is-half-desktop">
                <chart-block
                        class="postbox"
                        :chart-series="reports[5].chartSeries"
                        :chart-options="reports[5].chartOptions" />
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default { 
    name: "ReportsList",
    data() {
        return {
            selectedCollection: 'default',
            isFetchingCollections: false,
            isFetchingSummary: false,
            isFetchingTaxonomiesList: false,
            taxonomiesListChartSeries: [],
            taxonomiesListChartOptions: {}
        }
    },
    computed: {
        ...mapGetters('collection', {
            collections: 'getCollections',
        }),
        ...mapGetters('report', {
            reports: 'getReports',
            summary: 'getSummary',
            taxonomiesList: 'getTaxonomiesList'
        })
    },
    watch: {
        '$route.query': {
            handler(to) {
                this.selectedCollection = to['collection'] ? to['collection'] : 'default';
                this.loadSummary();
            },
            immediate: true
        }
    },
    created() {
        // Obtains colleciton id from query, if passed
        this.selectedCollection = this.$route.query['collection'] ? this.$route.query['collection'] : 'default';
        
        // Loads collection for the select input
        this.isFetchingCollections = true;
        this.fetchAllCollectionNames()
            .then(() => {
                this.loadSummary();
                this.isFetchingCollections = false;
            })
            .catch(() => this.isFetchingCollections = false);
        
       this.loadTaxonomiesList();
    },
    methods: {
        ...mapActions('collection', [
            'fetchAllCollectionNames'
        ]),
        ...mapActions('report', [
            'fetchSummary',
            'fetchTaxonomiesList'
        ]),
        ...mapGetters('report', [
            'getTaxonomiesList' 
        ]),
        loadSummary() {
            this.isFetchingSummary = true;
            this.fetchSummary({ collectionId: this.selectedCollection })
                .then(() => this.isFetchingSummary = false)
                .catch(() => this.isFetchingSummary = false);
        },
        loadTaxonomiesList() {
            this.isFetchingTaxonomiesList = true;
            this.fetchTaxonomiesList()
                .then(() => {
                    let termsUsed = [];
                    let termsNotUsed = [];
                    let taxonomiesLabels = [];
                    for (const taxonomy in this.taxonomiesList) {
                        termsUsed.push(this.taxonomiesList[taxonomy].total_terms_used);
                        termsNotUsed.push(this.taxonomiesList[taxonomy].total_terms_not_used);
                        taxonomiesLabels.push(this.taxonomiesList[taxonomy].name);
                    }
                    
                    this.taxonomiesListChartSeries = [
                        {
                            name: this.$i18n.get('label_terms_used'),
                            data: termsUsed
                        },
                        {
                            name: this.$i18n.get('label_terms_not_used'),
                            data: termsNotUsed
                        }
                    ];
                    
                    this.taxonomiesListChartOptions = {
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
                            }
                        },
                        title: {
                            text: this.$i18n.get('label_usage_of_terms_per_taxonomy')
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                legend: {
                                    position: 'bottom',
                                    offsetX: -10,
                                    offsetY: 0
                                }
                            }
                        }],
                        plotOptions: {
                            bar: {
                                borderRadius: 0,
                                horizontal: false,
                            },
                        },
                        xaxis: {
                            type: 'category',
                            tickPlacement: 'on',
                            categories: taxonomiesLabels,
                        },
                         yaxis: {
                            title: {
                                text: this.$i18n.get('label_number of terms')
                            }
                        },
                        legend: {
                            position: 'right',
                            offsetY: 40
                        },
                        fill: {
                            opacity: 1
                        }
                    }

                    this.isFetchingTaxonomiesList = false;
                })
                .catch(() => this.isFetchingTaxonomiesList = false);
        }
    }
}
</script>

<style lang="scss" scoped> 
.postbox {
    padding: 1.125rem 1.25rem;
    margin-bottom: 0;
    height: 100%;
}
</style>