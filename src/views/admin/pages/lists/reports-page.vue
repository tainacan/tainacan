<template>
    <div 
            class="page-container tainacan-reports-page"
            :class="{
                'tainacan-repository-level-colors': isRepositoryLevel
            }">
        <tainacan-title />
        
        <tainacan-reports-subheader />

        <div 
                class="records-cards-container"
                :class="{ 'records-cards-container--collection': !isRepositoryLevel }">

            <number-block
                    v-if="isRepositoryLevel"
                    :class="{ 'skeleton': isFetchingSummary }"
                    :summary="summary"
                    entity-type="collections">
                <div 
                        v-if="summaryLatestCachedOn"
                        class="report-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(summaryLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadSummary(true)">
                        <span class="sr-only">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </number-block>

            <number-block 
                    :class="{ 'skeleton': isFetchingSummary }"
                    :summary="summary"
                    entity-type="items"
                    :is-repository-level="isRepositoryLevel">
                <div 
                        v-if="summaryLatestCachedOn"
                        class="report-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(summaryLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadSummary(true)">
                        <span class="sr-only">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </number-block>

            <number-block
                    v-if="isRepositoryLevel"
                    :class="{ 'skeleton': isFetchingSummary }"
                    :summary="summary"
                    entity-type="taxonomies"
                    :is-repository-level="isRepositoryLevel">
                <div 
                        v-if="summaryLatestCachedOn"
                        class="report-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(summaryLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadSummary(true)">
                        <span class="sr-only">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </number-block>
              
            <number-block
                    v-else
                    :class="{ 'skeleton': isFetchingMetadata }"
                    :summary="metadata"
                    entity-type="metadata">
                <div 
                        v-if="summaryLatestCachedOn"
                        class="report-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(summaryLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadSummary(true)">
                        <span class="sr-only">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </number-block>

            <collections-list-block
                    v-if="isRepositoryLevel"
                    :chart-data="collectionsList"
                    :is-fetching-data="isFetchingCollectionsList">
                <div 
                        v-if="collectionsLatestCachedOn"
                        class="report-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(collectionsLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadCollectionsList(true)">
                        <span class="sr-only">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </collections-list-block>

            <metadata-distribution-block
                    v-if="!isRepositoryLevel"
                    :chart-data="metadata"
                    :is-fetching-data="isFetchingMetadata">
                <div 
                        v-if="metadataLatestCachedOn"
                        class="report-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(metadataLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadMetadata(true)">
                        <span class="sr-only">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </metadata-distribution-block>

            <metadata-types-block
                    v-if="!isRepositoryLevel"
                    :chart-data="metadata"
                    :is-fetching-data="isFetchingMetadata">
                <div 
                        v-if="metadataLatestCachedOn"
                        class="report-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(metadataLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadMetadata(true)">
                        <span class="sr-only">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </metadata-types-block>

            <terms-per-taxonomy-block
                    v-if="isRepositoryLevel"
                    :chart-data="taxonomyList"
                    :is-fetching-data="isFetchingTaxonomiesList">
                <div 
                        v-if="taxonomiesLatestCachedOn"
                        class="report-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(taxonomiesLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadTaxonomiesList(true)">
                        <span class="sr-only">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </terms-per-taxonomy-block>
            
            <items-per-term-block
                    v-if="isRepositoryLevel"
                    :chart-data="taxonomyTerms"
                    :is-fetching-data="isFetchingTaxonomiesList" />
            <items-per-term-collection-block
                    v-else
                    :chart-data="taxonomyTerms"
                    :is-fetching-data="isFetchingMetadataList"
                    :collection-id="selectedCollection" />

            <activities-per-user-block
                    :chart-data="activities"
                    :is-fetching-data="isFetchingActivities">
                <div 
                        v-if="activitiesLatestCachedOn"
                        class="report-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(activitiesLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadActivities(null, true)">
                        <span class="sr-only">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </activities-per-user-block>

            <activities-block
                    :chart-data="activities"
                    :is-fetching-data="isFetchingActivities"
                    @time-range-update="loadActivities">
                <div 
                        v-if="activitiesLatestCachedOn"
                        class="report-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(activitiesLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadActivities(null, true)">
                        <span class="sr-only">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </activities-block>
            
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

import TainacanReportsSubheader from '../../components/reports/tainacan-reports-subheader.vue';
import NumberBlock from '../../components/reports/number-block.vue';
import ItemsPerTermBlock from '../../components/reports/items-per-term-block.vue';
import ItemsPerTermCollectionBlock from '../../components/reports/items-per-term-collection-block.vue';
import TermsPerTaxonomyBlock from '../../components/reports/terms-per-taxonomy-block.vue';
import MetadataTypesBlock from '../../components/reports/metadata-types-block.vue';
import MetadataDistributionBlock from '../../components/reports/metadata-distribution-block.vue';
import CollectionsListBlock from '../../components/reports/collections-list-block.vue';
import ActivitiesBlock from '../../components/reports/activities-block.vue';
import ActivitiesPerUserBlock from '../../components/reports/activities-per-user-block.vue';

export default { 
    name: "ReportsPage",
    components: {
        TainacanReportsSubheader,
        NumberBlock,
        ItemsPerTermBlock,
        ItemsPerTermCollectionBlock,
        TermsPerTaxonomyBlock,
        MetadataTypesBlock,
        MetadataDistributionBlock,
        CollectionsListBlock,
        ActivitiesBlock,
        ActivitiesPerUserBlock
    },
    data() {
        return {
            selectedCollection: 'default',
            isFetchingCollections: false,
            isFetchingSummary: false,
            isFetchingCollectionsList: false,
            isFetchingMetadata: false,
            isFetchingActivities: false,
            isFetchingTaxonomiesList: false,
            activitiesStartDate: ''
        }
    },
    computed: {
        ...mapGetters('collection', {
            collections: 'getCollections',
        }),
        ...mapGetters('report', {
            summary: 'getSummary',
            metadata: 'getMetadata',
            collectionsList: 'getCollectionsList',
            metadataList: 'getMetadataList',
            taxonomyTerms: 'getTaxonomyTerms',
            activities: 'getActivities',
            taxonomyList: 'getTaxonomiesList',
            reportsLatestCachedOn: 'getReportsLatestCachedOn',
            startDate: 'getStartDate'
        }),
        summaryLatestCachedOn() {
            return this.reportsLatestCachedOn['summary-' + (this.selectedCollection ? this.selectedCollection : 'default')];
        },
        metadataLatestCachedOn() {
            return this.reportsLatestCachedOn['metadata-' + (this.selectedCollection ? this.selectedCollection : 'default')];
        },
        collectionsLatestCachedOn() {
            return this.reportsLatestCachedOn['collections'];
        },
        taxonomiesLatestCachedOn() {
            return this.reportsLatestCachedOn['taxonomies'];
        },
        activitiesLatestCachedOn() {
            return this.reportsLatestCachedOn['activities-' + (this.selectedCollection ? this.selectedCollection : 'default') + (this.activitiesStartDate ? '-' + this.activitiesStartDate : '')];
        }
    },
    created() {
        this.selectedCollection = this.$route.params.collectionId ? this.$route.params.collectionId : 'default';
        this.isRepositoryLevel = (this.$route.params.collectionId === undefined || this.$route.params.collectionId === 'default');
        
        this.loadSummary();
        this.loadMetadata();
        this.loadActivities();

        if (this.isRepositoryLevel) {
            this.loadCollectionsList();
            this.loadTaxonomiesList();
        } else {
            this.loadMetadataList();
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchAllCollectionNames'
        ]),
        ...mapActions('report', [
            'fetchSummary',
            'fetchCollectionsList',
            'fetchMetadata',
            'fetchMetadataList',
            'fetchTaxonomiesList',
            'fetchActivities'
        ]),
        loadCollections() {
            this.isFetchingCollections = true;
            this.fetchAllCollectionNames()
                .then(() => this.isFetchingCollections = false)
                .catch(() => this.isFetchingCollections = false);
        },
        loadSummary(force) {
            this.isFetchingSummary = true;
            this.fetchSummary({ collectionId: this.selectedCollection, force: force })
                .then(() => this.isFetchingSummary = false)
                .catch(() => this.isFetchingSummary = false);
        },
        loadMetadata(force) {
            this.isFetchingMetadata = true;
            this.fetchMetadata({ collectionId: this.selectedCollection, force: force })
                .then(() => this.isFetchingMetadata = false)
                .catch(() => this.isFetchingMetadata = false);
        },
        loadCollectionsList(force) {
            this.isFetchingCollectionsList = true;
            this.fetchCollectionsList(force)
                .then(() => this.isFetchingCollectionsList = false)
                .catch(() => this.isFetchingCollectionsList = false);
        },
        loadTaxonomiesList(force) {
            this.isFetchingTaxonomiesList = true;
            this.fetchTaxonomiesList(force)
                .then(() => this.isFetchingTaxonomiesList = false)
                .catch(() => this.isFetchingTaxonomiesList = false);
        },
        loadMetadataList() {
            this.isFetchingMetadataList = true;
            this.fetchMetadataList({ collectionId: this.selectedCollection, onlyTaxonomies: true })
                .then(() => this.isFetchingMetadataList = false)
                .catch(() => this.isFetchingMetadataList = false);
        },
        loadActivities(startDate, force) {
            if (startDate == null)
                startDate = this.startDate;

            this.isFetchingActivities = true;
            this.fetchActivities({ collectionId: this.selectedCollection, startDate: startDate, force: force })
                .then(() => this.isFetchingActivities = false)
                .catch(() => this.isFetchingActivities = false);
            this.activitiesStartDate = startDate;
        }
    }
}
</script>

<style lang="scss">

    // TAINACAN Variables
    @import "../../scss/_animations.scss";

    .tainacan-reports-page {

        a:hover {
            cursor: pointer;
        }

        .records-cards-container {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: var(--tainacan-report-card-gap, 0.75rem);

            & > .is-full {
                grid-column: span 3;
            } 
            & > .is-medium {
                grid-column: span 2;
            } 

            @supports (contain: inline-size) {
                container-type: inline-size;
                container-name: recordscardscontainer; 
            }

            @container recordscardscontainer (max-width: 768px) {
                & > * {
                    grid-column: span 3 !important; 
                }
            }
            @supports not (contain: inline-size) {
                @media (max-width: 1024px) {
                    & > * {
                        grid-column: span 3 !important;
                    }
                }
            }
        }

        .report-card {
            position: relative;
            background-color: var(--tainacan-report-card-background, var(--tainacan-gray0));
            border-radius: var(--tainacan-report-card-border-radius, 8px);
            box-shadow: var(--tainacan-report-card-box-shadow, none);
            border: var(--tainacan-report-card-border, 1px solid var(--tainacan-gray2));
            margin-bottom: 2.5rem;
            padding: 1.125rem clamp(0.75rem, 1vw, 1.125rem);
            margin-bottom: 0;
            height: 100%;
            min-height: 380px;

            label {
                font-weight: bold;
                font-size: 0.875rem;
            }

            .report-card-header {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                flex-wrap: wrap;

                .report-card-header__item {
                    margin-bottom: 10px;
                    line-height: 2rem;
                }
            }
        }
        .report-last-cached-on {
            font-size: 0.75em;
            line-height: 2em;
            position: absolute;
            bottom: 0;
            right: 0;
            display: inline-block;
            padding: 2px 10px;
            background-color: var(--tainacan-gray2, #dbdbdb);
            color: var(--tainacan-gray4, #505253);
            border-top-left-radius: 4px;
            border-bottom-right-radius: var(--tainacan-report-card-border-radius, 8px);
            opacity: 0.0;
            transition: opacity 0.3s ease;

            button {
                border: none;
                background: none;
                cursor: pointer;

                &:hover {
                    color: var(--wp-admin-theme-color, #007cba);
                }
            }
        }
        .report-card:hover>.report-last-cached-on,
        .report-card:hover>.report-last-cached-on+.report-last-cached-on,
        .report-last-cached-on:hover {
            opacity: 1.0;
        }


        .empty-report-card-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            min-height: 380px;
            flex-direction: column;
            opacity: 0.75;

            p {
                color: var(--tainacan-gray4, #505253);
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

        .tainacan-custom-tooltip {
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-direction: column;

            .tainacan-custom-tooltip__header {
                background-color: var(--tainacan-gray1, #f2f2f2);
                display: flex;
                justify-content: flex-start;
                align-items: center;
                width: 100%;
                padding: 6px 10px 4px 10px;
            }
            
            .tainacan-custom-tooltip__header+.tainacan-custom-tooltip__body {
                padding: 4px 10px 6px 10px;
            }
            .tainacan-custom-tooltip__body {
                width: 100%;
                padding: 6px 10px;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                flex-direction: column;

                p {
                    margin-bottom: 4px;
                    font-size: 0.85rem;
                }
            }
        }

        .apexcharts-legend-series {
            display: flex;
            align-items: center;
        }

        #current-page-selector {
            min-width: 48px;
        }
    }
</style>