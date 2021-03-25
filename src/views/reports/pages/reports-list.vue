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
                    <div class="postbox">
                        <div class="box-header">
                            <div class="box-header__item">
                                <label for="select_taxonomies">{{ $i18n.get('label_items_per_term_from_taxonomy') }}&nbsp;</label>
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
                                                :disabled="isBuildingTaxonomyTermsChart"
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
                <template v-if="selectedCollection && selectedCollection != 'default'">
                    <div class="column is-full">
                        <div 
                                v-if="!isFetchingMetadata && !isBuildingMetadataTypeChart"
                                class="postbox">
                            <label>{{ $i18n.get('metadata_types') }}&nbsp;</label>
                            <div class="graph-mode-switch">
                                <button 
                                        @click="metadataTypeChartMode = 'bar'"
                                        :class="{ 'current': metadataTypeChartMode == 'bar' }">
                                    <span class="screen-reader-text">
                                        {{ $i18n.get('label_bar_chart') }}
                                    </span>
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text tainacan-icon-rotate-270" />
                                    </span>
                                </button>
                                <button 
                                        @click="metadataTypeChartMode = 'circle'"
                                        :class="{ 'current': metadataTypeChartMode == 'circle' }">
                                    <span class="screen-reader-text">
                                        {{ $i18n.get('label_pie_chart') }}
                                    </span>
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-waiting tainacan-icon-rotate-270" />
                                    </span>
                                </button>
                            </div>
                            <apexchart
                                    height="380px"
                                    :series="metadataTypeChartSeries"
                                    :options="metadataTypeChartOptions" />
                        </div>
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
                        :style="{
                            maxHeight: ((170 + (metadata.totals.metadata.total * 36)) <= 690 ? (170 + (metadata.totals.metadata.total * 36)) : 690) + 'px'
                        }"
                        class="postbox metadata-distribution-box">
                    <apexchart
                            :height="100 + (metadata.totals.metadata.total * 36)"
                            :series="metadataDistributionChartSeries"
                            :options="metadataDistributionChartOptions" />
                </div>
                <div 
                        v-else
                        style="min-height=740px"
                        class="skeleton postbox metadata-distribution-box" />
            </div>
        </div>
        <div class="columns">
            <div 
                    v-if="metadataList != undefined && (selectedCollection && selectedCollection != 'default')"
                    class="column is-full">
                <div
                        style="margin-top: 0px"
                        class="postbox">
                    <label>{{ $i18n.get('label_amount_of_items_per_metadatum_value') }}&nbsp;</label>
                    <select 
                            v-if="!isFetchingMetadataList"
                            name="select_metadata"
                            id="select_metadata"
                            :placeholder="$i18n.get('label_select_a_metadatum')"
                            v-model="selectedMetadatum">
                        <option 
                                v-for="(metadatum, index) of metadataListArray"
                                :key="index"
                                :value="metadatum.id">
                            {{ metadatum.name }} 
                        </option>
                    </select>
                    <apexchart
                            v-if="!isFetchingMetadataList && selectedMetadatum"
                            height="380px"
                            :series="metadataListChartSeries"
                            :options="metadataListChartOptions" />
                    <div 
                        v-else
                        style="min-height=380px"
                        class="skeleton postbox" />
                </div>
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
            selectedTaxonomy: {},
            selectedMetadatum: '',
            isFetchingCollections: false,
            isFetchingSummary: false,
            isFetchingCollectionsList: false,
            isFetchingTaxonomiesList: false,
            isFetchingTaxonomyTerms: false,
            isFetchingMetadata: false,
            isBuildingMetadataTypeChart: false,
            isBuildingTaxonomyTermsChart: false,
            isFetchingMetadataList: false,
            isFetchingActivities: false,
            collectionsListChartSeries: [],
            collectionsListChartOptions: {},
            taxonomiesListChartSeries: [],
            taxonomiesListChartOptions: {},
            metadataListChartSeries: [],
            metadataListChartOptions: {},
            metadataTypeChartMode: 'bar',
            taxonomyTermsChartSeries: [],
            taxonomyTermsChartOptions: {},
            metadataTypeChartSeries: [],
            metadataTypeChartOptions: {},
            metadataDistributionChartSeries: [],
            metadataDistributionChartOptions: {},
            metadataDistributionChartHeight: 730,
            activitiesChartSeries: [],
            activitiesChartOptions: {},
            maxTermsToDisplay: 64,
            termsDisplayedPage: 1
        }
    },
    computed: {
        ...mapGetters('collection', {
            collections: 'getCollections',
        }),
        ...mapGetters('report', {
            summary: 'getSummary',
            metadata: 'getMetadata',
            metadataList: 'getMetadataList',
            taxonomyTerms: 'getTaxonomyTerms',
            taxonomiesList: 'getTaxonomiesList',
            collectionsList: 'getCollectionsList',
            activities: 'getActivities',
            stackedBarChartOptions: 'getStackedBarChartOptions',
            donutChartOptions: 'getDonutChartOptions',
            horizontalBarChartOptions: 'getHorizontalBarChartOptions',
            //heatMapChartOptions: 'getHeatMapChartOptions'
        }),
        taxonomiesListArray() {
            return this.taxonomiesList && this.taxonomiesList != undefined ? Object.values(this.taxonomiesList) : [];
        },
        metadataListArray() {
            return this.metadata && this.metadata != undefined && this.metadata.distribution ? Object.values(this.metadata.distribution) : [];
        }
    },
    watch: {
        '$route.query': {
            handler(to) {
                this.selectedCollection = to['collection'] ? to['collection'] : 'default';
                this.loadSummary();
                this.loadMetadata();
                this.loadActivities();

                if (!this.selectedCollection || this.selectedCollection == 'default') {
                    this.loadCollectionsList();
                    this.loadTaxonomiesList();
                }
                
            },
            immediate: true
        },
        selectedTaxonomy() {
            this.termsDisplayedPage = 1;
            if (this.selectedTaxonomy && this.selectedTaxonomy.id)
                this.loadTaxonomyTerms();
        },
        selectedMetadatum() {
            if (this.selectedMetadatum && this.selectedMetadatum != '')
                this.loadMetadataList();
        },
        metadataTypeChartMode() {
            this.buildMetadataTypeChart();
        },
        termsDisplayedPage() {
            this.buildTaxonomyTermsChart();
        },
        maxTermsToDisplay() {
            this.termsDisplayedPage = 1;
            this.buildTaxonomyTermsChart();
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
            'fetchMetadata',
            'fetchMetadataList',
            'fetchActivities'
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
        buildMetadataTypeChart() {

            this.isBuildingMetadataTypeChart = true;

            // Building Metadata Type Donut Chart
            let metadataTypeValues = [];
            let metadataTypeLabels = [];
            
            const orderedMetadataPerType = Object.values(this.metadata.totals.metadata_per_type).sort((a, b) => b.count - a.count);
            orderedMetadataPerType.forEach((metadataPerType) => {
                metadataTypeValues.push(metadataPerType.count ? metadataPerType.count : 0);
                metadataTypeLabels.push(metadataPerType.name ? metadataPerType.name : '');
            });
            
            this.metadataTypeChartSeries = this.metadataTypeChartMode == 'circle' ? metadataTypeValues : [{ data: metadataTypeValues }];

            if (this.metadataTypeChartMode == 'circle') {
                this.metadataTypeChartOptions = JSON.parse(JSON.stringify({
                    ...this.donutChartOptions,
                    ...{
                        title: {},
                        labels: metadataTypeLabels,
                    }
                }));
            } else {
                this.metadataTypeChartOptions = JSON.parse(JSON.stringify({
                    ...this.stackedBarChartOptions,
                    ...{
                        title: {},
                        xaxis: {
                            type: 'category',
                            tickPlacement: 'on',
                            categories: metadataTypeLabels,
                            labels: {
                                show: true,
                                trim: true,
                                hideOverlappingLabels: false
                            },
                            tooltip: true
                        }
                    }
                }));
            }

            setTimeout(() => { this.isBuildingMetadataTypeChart = false; }, 500);
            
        },
        loadMetadata() {
            this.isFetchingMetadata = true;
            this.fetchMetadata({ collectionId: this.selectedCollection })
                .then(() => {

                    if (this.metadata.totals)
                        this.buildMetadataTypeChart();

                    if (this.metadata.distribution) {

                        // Building Metadata Distribution Bar chart
                        const orderedMetadataDistributions = Object.values(this.metadata.distribution).sort((a, b) => b.fill_percentage - a.fill_percentage );
                        let metadataDistributionValues = [];
                        let metadataDistributionValuesInverted = [];
                        let metadataDistributionLabels = [];
                        const metadataCount = 100 + (this.metadata.totals.metadata.total * 36);

                        orderedMetadataDistributions.forEach(metadataDistribution => {
                            metadataDistributionValues.push(parseFloat(metadataDistribution.fill_percentage));
                            metadataDistributionValuesInverted.push(100.0000 - parseFloat(metadataDistribution.fill_percentage).toFixed(4));
                            metadataDistributionLabels.push(metadataDistribution.name);
                        })

                        // Sets first metadatum as the selected one 
                        if (orderedMetadataDistributions.length)
                            this.selectedMetadatum = orderedMetadataDistributions[0].id;

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
                    const orderedCollections = Object.values(this.collectionsList).sort((a, b) =>  b.items.total - a.items.total);
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
                                labels: {
                                    show: true,
                                    trim: true,
                                    hideOverlappingLabels: false
                                },
                                tooltip: true
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
                    const orderedTaxonomies = this.taxonomiesListArray.sort((a, b) => b.total_terms - a.total_terms);
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
                        this.selectedTaxonomy = orderedTaxonomies[0]; 

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
                                labels: {
                                    show: true,
                                    trim: true,
                                    hideOverlappingLabels: false
                                },
                                tooltip: true
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

            setTimeout(() => { this.isBuildingTaxonomyTermsChart = false; }, 500);
        },
        loadTaxonomyTerms() {
            this.isFetchingTaxonomyTerms = true;

            this.fetchTaxonomyTerms(this.selectedTaxonomy.id)
                .then(() => {
                    this.buildTaxonomyTermsChart();
                    this.isFetchingTaxonomyTerms = false;
                })
                .catch(() => this.isFetchingTaxonomyTerms = false);

        },
        loadMetadataList() {
            this.isFetchingMetadataList = true;
            this.fetchMetadataList({ collectionId: this.collectionId, metadatumId: this.selectedMetadatum })
                .then(() => {
                    
                    // Building Taxonomy term usage chart
                    const orderedMetadata = Object.values(this.metadataList).sort((a, b) => b.total_items - a.total_items);
                    let metadataItemValues = [];
                    let metadataItemLabels = [];

                    orderedMetadata.forEach(metadataItem => {
                        metadataItemValues.push(metadataItem.total_items);
                        metadataItemLabels.push(metadataItem.label);
                    }); 

                    this.metadataListChartSeries = [
                        {
                            name: this.$i18n.get('label_items_with_this_metadum_value'),
                            data: metadataItemValues
                        }
                    ];
                    
                    this.metadataListChartOptions = {
                        ...this.stackedBarChartOptions, 
                        ...{
                            title: {},
                            xaxis: {
                                type: 'category',
                                tickPlacement: 'on',
                                categories: metadataItemLabels,
                            },
                            yaxis: {
                                title: {
                                    text: this.$i18n.get('label_number_of_items')
                                }
                            }
                        }
                    }
                    
                    this.isFetchingMetadataList = false;
                })
                .catch(() => this.isFetchingMetadataList = false);
        },
        loadActivities() {
            this.isFetchingActivities = true;
            this.fetchActivities({ collectionId: this.selectedCollection })
                .then(() => {

                    const daysWithActivities = this.activities && this.activities.totals && this.activities.totals.last_year && this.activities.totals.last_year.general ? this.activities.totals.last_year.general : []; 
                    if (daysWithActivities && daysWithActivities.length) {
                        
                        let everyDayOfTheYear = Array.from(new Array(7),
                            (val,index) => {
                                return {
                                    name: (index + 1),
                                    data: new Array(53).fill({ x: '', y: 0 })
                                }
                            }
                        );
                        
                        const firstDayOfTheWeekWithActivity = parseInt(daysWithActivities[0].day_of_week);
                        let dayWithActivityIndex = 0;
                        let daysToSkip = 0;

                        // Loop for each column (number of the week in the year)
                        for (let column = 0; column < 53; column++) {

                            // Loop for each line (number of the day in the week)
                            for (let line = 0; line < 7; line++) {

                                // If there are no more days with activities, get outta here
                                if (dayWithActivityIndex < daysWithActivities.length - 1) {
                                        
                                    // We should only begin inserting days from firstDayOfTheWeekWithActivity
                                    if (column == 0 && line < firstDayOfTheWeekWithActivity - 1) {
                                        continue;

                                    // On the first day, we don't need to calculate distances, just set the value and save the date
                                    } else if (column == 0 && line == firstDayOfTheWeekWithActivity - 1) {
                                        everyDayOfTheYear[line].data[column] = {
                                            x: '',
                                            y: parseInt(daysWithActivities[dayWithActivityIndex].total)
                                        };

                                        const lastDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);
                                        dayWithActivityIndex++;

                                        const nextDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);

                                        daysToSkip = Math.floor( (nextDayWithActivity - lastDayWithActivity) / (1000 * 60 * 60 * 24) );
                                    } else {
                                        daysToSkip--;

                                        // If we don't have more days to skip, time to update values
                                        if ( daysToSkip <= 0) {
                                            everyDayOfTheYear[line].data[column] = {
                                                x: '',
                                                y: parseInt(daysWithActivities[dayWithActivityIndex].total)
                                            };

                                            const lastDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);
                                            dayWithActivityIndex++;

                                            const nextDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);

                                            daysToSkip = Math.floor( (nextDayWithActivity - lastDayWithActivity) / (1000 * 60 * 60 * 24) );
                                        }
                                    }
                                }
                            }
                        }
                        this.activitiesChartSeries = everyDayOfTheYear;
                        this.activitiesChartOptions = {
                             ...this.heatMapChartOptions,
                                title: {
                                    text: this.$i18n.get('label_activities_last_year')
                                },
                        };
                    } else {
                        this.activitiesChartSeries = [];
                        this.activitiesChartOptions = {
                             ...this.heatMapChartOptions,
                                title: {
                                    text: this.$i18n.get('label_activities_last_year')
                                },
                        }
                    }

                    this.isFetchingActivities = false;
                })
                .catch(() => this.isFetchingActivities = false);
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
    background-color: var(--tainacan-gray0, #f6f6f6);

    label {
        font-weight: bold;
        font-size: 0.875rem;
    }
    &.metadata-distribution-box {
        margin: 0px 0px 0.75rem 1.5rem !important;  
        overflow-y: auto;
    }

    .box-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        flex-wrap: wrap;

        .box-header__item {
            margin-bottom: 10px;
        }
    }
}
.graph-mode-switch {
    display: inline-block;
    button {
        border: none !important;
        background: none !important;
        box-shadow: none !important;
        padding: 0;
        cursor: pointer;

        &.current {
            color: var(--wp-admin-theme-color, #007cba);
        }
    }
}
</style>