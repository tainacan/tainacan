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
                    :class="{ 'is-three-fifths-desktop': selectedCollection && selectedCollection != 'default' }"
                    class="column is-full columns is-multiline">
                <div 
                        v-if="!selectedCollection || selectedCollection == 'default'"
                        class="column is-full is-one-third-tablet has-text-centered">
                    <number-block
                            :class="{ 'skeleton': isFetchingSummary }"
                            class="postbox"
                            :summary="summary"
                            entity-type="collections" />
                </div>
                <div
                        :class="{ 'is-one-third-tablet': !selectedCollection || selectedCollection == 'default' }"
                        class="column is-full is-half-tablet has-text-centered">
                    <number-block 
                            :class="{ 'skeleton': isFetchingSummary }"
                            class="postbox"
                            :summary="summary"
                            entity-type="items"/>
                </div>
                <div 
                        v-if="!selectedCollection || selectedCollection == 'default'"
                        class="column is-full is-one-third-tablet has-text-centered">
                <number-block
                            :class="{ 'skeleton': isFetchingSummary }"
                            class="postbox"
                            :summary="summary"
                            entity-type="taxonomies" />
                </div>
                <div 
                        v-else
                        class="column is-full is-half-tablet has-text-centered">
                <number-block
                            :class="{ 'skeleton': isFetchingMetadata }"
                            class="postbox"
                            :summary="metadata"
                            entity-type="metadata" />
                </div>
                <div 
                    v-if="!selectedCollection || selectedCollection == 'default'"
                        class="column is-full">
                    <apexchart
                            v-if="!isFetchingCollectionsList"
                            height="380px"
                            class="postbox"
                            :series="collectionsListChartSeries"
                            :options="collectionsListChartOptions" />
                    <div 
                            v-else
                            style="min-height=380px"
                            class="skeleton postbox" />
                </div>
                <div 
                    v-if="!selectedCollection || selectedCollection == 'default'"
                        class="column is-full">
                    <apexchart
                            v-if="!isFetchingTaxonomiesList"
                            height="380px"
                            class="postbox"
                            :series="taxonomiesListChartSeries"
                            :options="taxonomiesListChartOptions" />
                    <div 
                            v-else
                            style="min-height=380px"
                            class="skeleton postbox" />
                </div>
                <div 
                    v-if="taxonomiesList != undefined && (!selectedCollection || selectedCollection == 'default')"
                        class="column is-full">
                    <select 
                            v-if="!isFetchingTaxonomiesList"
                            name="select_taxonomies"
                            id="select_taxonomies"
                            :placeholder="$i18n.get('label_select_a_taxonomy')"
                            v-model="selectedTaxonomy">
                        <option 
                                v-for="(taxonomy, index) of taxonomiesListArray"
                                :key="index"
                                :value="taxonomy.id">
                            {{ taxonomy.name }}
                        </option>
                    </select>
                    <apexchart
                            v-if="!isFetchingTaxonomiesList && selectedTaxonomy"
                            height="380px"
                            class="postbox"
                            :series="taxonomyTermsChartSeries"
                            :options="taxonomyTermsChartOptions" />
                    <div 
                            v-else
                            style="min-height=380px"
                            class="skeleton postbox" />
                </div>
                <template v-if="selectedCollection && selectedCollection != 'default'">
                    <div class="column is-full">
                        <apexchart
                                v-if="!isFetchingMetadata"
                                height="380px"
                                class="postbox"
                                :series="metadataTypeChartSeries"
                                :options="metadataTypeChartOptions" />
                        <div 
                            v-else
                            style="min-height=380px"
                            class="skeleton postbox" />
                    </div>
                </template>
            </div>
            <div 
                    v-if="selectedCollection && selectedCollection != 'default'"
                    class="column is-full is-two-fifths-desktop">
                <div 
                        v-if="!isFetchingMetadata && metadata.totals && metadata.totals.metadata"
                        :style="{ overflowY: 'auto', maxHeight: ((140 + (metadata.totals.metadata.total * 36)) <= 660 ? (140 + (metadata.totals.metadata.total * 36)) : 660) + 'px' }"
                        class="postbox">
                    <apexchart
                            :height="100 + (metadata.totals.metadata.total * 36)"
                            :series="metadataDistributionChartSeries"
                            :options="metadataDistributionChartOptions" />
                </div>
                <div 
                        v-else
                        style="min-height=740px"
                        class="skeleton postbox" />
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
            selectedTaxonomy: '',
            isFetchingCollections: false,
            isFetchingSummary: false,
            isFetchingCollectionsList: false,
            isFetchingTaxonomiesList: false,
            isFetchingTaxonomyTerms: false,
            isFetchingMetadata: false,
            collectionsListChartSeries: [],
            collectionsListChartOptions: {},
            taxonomiesListChartSeries: [],
            taxonomiesListChartOptions: {},
            taxonomyTermsChartSeries: [],
            taxonomyTermsChartOptions: {},
            metadataTypeChartSeries: [],
            metadataTypeChartOptions: {},
            metadataDistributionChartSeries: [],
            metadataDistributionChartOptions: {},
            metadataDistributionChartHeight: 730
        }
    },
    computed: {
        ...mapGetters('collection', {
            collections: 'getCollections',
        }),
        ...mapGetters('report', {
            summary: 'getSummary',
            metadata: 'getMetadata',
            taxonomyTerms: 'getTaxonomyTerms',
            taxonomiesList: 'getTaxonomiesList',
            collectionsList: 'getCollectionsList',
            stackedBarChartOptions: 'getStackedBarChartOptions',
            donutChartOptions: 'getDonutChartOptions',
            horizontalBarChartOptions: 'getHorizontalBarChartOptions'
        }),
        taxonomiesListArray() {
            return this.taxonomiesList && this.taxonomiesList != undefined ? Object.values(this.taxonomiesList) : [];
        }
    },
    watch: {
        '$route.query': {
            handler(to) {
                this.selectedCollection = to['collection'] ? to['collection'] : 'default';
                this.loadSummary();

                if (this.selectedCollection && this.selectedCollection != 'default')
                    this.loadMetadata();
                else {
                    this.loadCollectionsList();
                    this.loadTaxonomiesList();
                }
                
            },
            immediate: true
        },
        selectedTaxonomy() {
            if (this.selectedTaxonomy && this.selectedTaxonomy != '')
                this.loadTaxonomyTerms();
        }
    },
    created() {
        // Obtains colleciton id from query, if passed
        this.selectedCollection = this.$route.query['collection'] ? this.$route.query['collection'] : 'default';
        
        // Loads collection for the select input
        this.loadCollections();
    },
    methods: {
        ...mapActions('collection', [
            'fetchAllCollectionNames'
        ]),
        ...mapActions('report', [
            'fetchSummary',
            'fetchCollectionsList',
            'fetchTaxonomiesList',
            'fetchTaxonomyTerms',
            'fetchMetadata'
        ]),
        loadCollections() {
            this.isFetchingCollections = true;
            this.fetchAllCollectionNames()
                .then(() => this.isFetchingCollections = false)
                .catch(() => this.isFetchingCollections = false);
        },
        loadSummary() {
            this.isFetchingSummary = true;
            this.fetchSummary({ collectionId: this.selectedCollection })
                .then(() => this.isFetchingSummary = false)
                .catch(() => this.isFetchingSummary = false);
        },
        loadMetadata() {
            this.isFetchingMetadata = true;
            this.fetchMetadata({ collectionId: this.selectedCollection })
                .then(() => {

                    if (this.metadata.totals) {
                        // Building Metadata Type Donut Chart
                        let metadataTypeValues = [];
                        let metadataTypeLabels = [];

                        for (const metadataType in this.metadata.totals.metadata_per_type) {
                            metadataTypeValues.push(this.metadata.totals.metadata_per_type[metadataType].count);
                            metadataTypeLabels.push(this.metadata.totals.metadata_per_type[metadataType].name);
                        }
                        
                        this.metadataTypeChartSeries = metadataTypeValues;
                        this.metadataTypeChartOptions = {
                            ...this.donutChartOptions,
                            ...{
                                title: {
                                    text: this.$i18n.get('metadata_types')
                                },
                                labels: metadataTypeLabels,
                            }
                        }
                    }

                    if (this.metadata.distribution) {
                        // Building Metadata Distribution Bar chart
                        const orderedMetadataDistributions = Object.values(this.metadata.distribution).sort((a, b) => b.fill_percentage - a.fill_percentage);
                        let metadataDistributionValues = [];
                        let metadataDistributionValuesInverted = [];
                        let metadataDistributionLabels = [];
                        const metadataCount = 100 + (this.metadata.totals.metadata.total * 36);

                        orderedMetadataDistributions.forEach(metadataDistribution => {
                            metadataDistributionValues.push(parseFloat(metadataDistribution.fill_percentage));
                            metadataDistributionValuesInverted.push(100.0000 - parseFloat(metadataDistribution.fill_percentage).toFixed(4));
                            metadataDistributionLabels.push(metadataDistribution.name);
                        })

                        this.metadataDistributionChartSeries = [
                            { 
                                name: this.$i18n.get('label_filled'),
                                data: metadataDistributionValues
                            },
                            { 
                                name: this.$i18n.get('label_not_filled'),
                                data: metadataDistributionValuesInverted
                            }
                        ];
                        this.metadataDistributionChartOptions = {
                            ...this.horizontalBarChartOptions,
                            ...{
                                chart: {
                                    type: 'bar',
                                    height: metadataCount,
                                    stacked: true,
                                    stackType: '100%',
                                    toolbar: {
                                        show: true
                                    },
                                    zoom: {
                                        type: 'y',
                                        enabled: true,
                                        autoScaleYaxis: true,
                                    }
                                },
                                title: {
                                    text: this.$i18n.get('label_metadata_fill_distribution')
                                },
                                labels: metadataDistributionLabels,
                                colors: ['#25a189', '#a23939']
                            }
                        }
                    }

                    this.isFetchingMetadata = false;
                })
                .catch(() => this.isFetchingMetadata = false);
        },
        loadCollectionsList() {
            this.isFetchingCollectionsList = true;
            this.fetchCollectionsList()
                .then(() => {

                    // Building Collections items chart
                    const orderedCollections = Object.values(this.collectionsList).sort((a, b) => a.items.total - b.items.total);
                    let privateItems = [];
                    let publicItems = [];
                    let trashItems = [];
                    let draftItems = [];
                    let collectionsLabels = [];

                    orderedCollections.forEach(collection => {
                        privateItems.push(collection.items.private);
                        publicItems.push(collection.items.publish);
                        draftItems.push(collection.items.draft);
                        trashItems.push(collection.items.trash);
                        collectionsLabels.push(collection.name);
                    });

                    this.collectionsListChartSeries = [
                        {
                            name: this.$i18n.get('status_publish'),
                            data: publicItems
                        },
                        {
                            name: this.$i18n.get('status_private'),
                            data: privateItems
                        },
                        {
                            name: this.$i18n.get('status_draft'),
                            data: draftItems
                        },
                        {
                            name: this.$i18n.get('status_trash'),
                            data: trashItems
                        }
                    ];
                    
                    this.collectionsListChartOptions = {
                        ...this.stackedBarChartOptions, 
                        ...{
                            title: {
                                text: this.$i18n.get('label_items_per_collection')
                            },
                            xaxis: {
                                type: 'category',
                                tickPlacement: 'on',
                                categories: collectionsLabels,
                            },
                            yaxis: {
                                title: {
                                    text: this.$i18n.get('items')
                                }
                            }
                        }
                    }
                    
                    this.isFetchingCollectionsList = false;
                })
                .catch(() => this.isFetchingCollectionsList = false);
        },
        loadTaxonomiesList() {
            this.isFetchingTaxonomiesList = true;
            this.fetchTaxonomiesList()
                .then(() => {

                    // Building Taxonomy term usage chart
                    const orderedTaxonomies = this.taxonomiesListArray.sort((a, b) => a.total_terms - b.total_terms);
                    let termsUsed = [];
                    let termsNotUsed = [];
                    let taxonomiesLabels = [];

                    orderedTaxonomies.forEach(taxonomy => {
                        termsUsed.push(taxonomy.total_terms_used);
                        termsNotUsed.push(taxonomy.total_terms_not_used);
                        taxonomiesLabels.push(taxonomy.name);
                    });

                    // Sets taxonomy terms now that we have the 
                    if (orderedTaxonomies.length)
                        this.selectedTaxonomy = orderedTaxonomies[orderedTaxonomies.length - 1].id; 

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
                        ...this.stackedBarChartOptions, 
                        ...{
                            title: {
                                text: this.$i18n.get('label_usage_of_terms_per_taxonomy')
                            },
                            xaxis: {
                                type: 'category',
                                tickPlacement: 'on',
                                categories: taxonomiesLabels,
                            },
                            yaxis: {
                                title: {
                                    text: this.$i18n.get('label_number_of_terms')
                                }
                            }
                        }
                    }
                    
                    this.isFetchingTaxonomiesList = false;
                })
                .catch(() => this.isFetchingTaxonomiesList = false);
        },
        loadTaxonomyTerms() {
            this.isFetchingTaxonomyTerms = true;

            this.fetchTaxonomyTerms(this.selectedTaxonomy)
                .then(() => {

                    // Building Taxonomy term usage chart
                    const orderedTerms = Object.values(this.taxonomyTerms).sort((a, b) => a.count - b.count);
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
                            title: {
                                text: this.$i18n.get('label_items_per_term')
                            },
                            xaxis: {
                                type: 'category',
                                tickPlacement: 'on',
                                categories: termsLabels,
                            },
                            yaxis: {
                                title: {
                                    text: this.$i18n.get('label_number_of_items')
                                }
                            }
                        }
                    }
                    
                    this.isFetchingTaxonomyTerms = false;
                })
                .catch(() => this.isFetchingTaxonomyTerms = false);

        }
    }
}
</script>

<style lang="scss" scoped> 
.postbox {
    padding: 1.125rem 1.25rem;
    margin-bottom: 0;
    height: 100%;
    min-height: 380px;
}
</style>