<template>
    <div>
        <div class="tainacan-reports-header">
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
            <a 
                    v-if="!isRepositoryLevel && collectionEditionPage"
                    :href="collectionEditionPage"
                    class="page-title-action">
                {{ $i18n.get('label_manage_collection') }}
            </a>
        </div>
        <tainacan-reports-subheader />
        <div class="columns is-multiline">
            <div 
                    :class="{ 'is-three-fifths-desktop': !isRepositoryLevel }"
                    style="margin-bottom: 0px;"
                    class="column is-full columns is-multiline">
                <div 
                        v-if="isRepositoryLevel"
                        class="column is-full is-one-third-tablet has-text-centered">
                    <number-block
                            :class="{ 'skeleton': isFetchingSummary }"
                            class="postbox"
                            :summary="summary"
                            entity-type="collections" />
                    <div 
                            v-if="summaryLatestCachedOn"
                            class="box-last-cached-on">
                        <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(summaryLatestCachedOn).toLocaleString() }}</span>
                        <button 
                                @click="loadSummary(true)">
                            <span class="screen-reader-text">
                                {{ $i18n.get('label_get_latest_report') }}
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                            </span>
                        </button>
                    </div>
                </div>
                <div
                        :class="{ 'is-one-third-tablet': isRepositoryLevel }"
                        class="column is-full is-half-tablet has-text-centered">
                    <number-block 
                            :class="{ 'skeleton': isFetchingSummary }"
                            class="postbox"
                            :summary="summary"
                            entity-type="items"
                            :is-repository-level="isRepositoryLevel"/>
                    <div 
                            v-if="summaryLatestCachedOn"
                            class="box-last-cached-on">
                        <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(summaryLatestCachedOn).toLocaleString() }}</span>
                        <button 
                                @click="loadSummary(true)">
                            <span class="screen-reader-text">
                                {{ $i18n.get('label_get_latest_report') }}
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                            </span>
                        </button>
                    </div>
                </div>
                <div 
                        v-if="isRepositoryLevel"
                        class="column is-full is-one-third-tablet has-text-centered">
                    <number-block
                            :class="{ 'skeleton': isFetchingSummary }"
                            class="postbox"
                            :summary="summary"
                            entity-type="taxonomies"
                            :is-repository-level="isRepositoryLevel" />
                    <div 
                            v-if="summaryLatestCachedOn"
                            class="box-last-cached-on">
                        <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(summaryLatestCachedOn).toLocaleString() }}</span>
                        <button 
                                @click="loadSummary(true)">
                            <span class="screen-reader-text">
                                {{ $i18n.get('label_get_latest_report') }}
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                            </span>
                        </button>
                    </div>
                </div>
                <div 
                        v-else
                        class="column is-full is-half-tablet has-text-centered">
                    <number-block
                            :class="{ 'skeleton': isFetchingMetadata }"
                            class="postbox"
                            :summary="metadata"
                            entity-type="metadata" />
                    <div 
                            v-if="summaryLatestCachedOn"
                            class="box-last-cached-on">
                        <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(summaryLatestCachedOn).toLocaleString() }}</span>
                        <button 
                                @click="loadSummary(true)">
                            <span class="screen-reader-text">
                                {{ $i18n.get('label_get_latest_report') }}
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                            </span>
                        </button>
                    </div>
                </div>
                <collections-list-block
                        class="column is-full"
                        :chart-data="collectionsList"
                        :is-fetching-data="isFetchingCollectionsList"
                        v-if="isRepositoryLevel">
                    <div 
                            v-if="collectionsLatestCachedOn"
                            class="box-last-cached-on">
                        <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(collectionsLatestCachedOn).toLocaleString() }}</span>
                        <button 
                                @click="loadCollectionsList(true)">
                            <span class="screen-reader-text">
                                {{ $i18n.get('label_get_latest_report') }}
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                            </span>
                        </button>
                    </div>
                </collections-list-block>

                <metadata-types-block
                        class="column is-full"
                        :chart-data="metadata"
                        :is-fetching-data="isFetchingMetadata"
                        v-if="!isRepositoryLevel">
                    <div 
                            v-if="metadataLatestCachedOn"
                            class="box-last-cached-on">
                        <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(metadataLatestCachedOn).toLocaleString() }}</span>
                        <button 
                                @click="loadMetadata(true)">
                            <span class="screen-reader-text">
                                {{ $i18n.get('label_get_latest_report') }}
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                            </span>
                        </button>
                    </div>
                </metadata-types-block>

                <terms-per-taxonomy-block
                        class="column is-full"
                        :chart-data="taxonomyList"
                        :is-fetching-data="isFetchingTaxonomiesList"
                        v-if="isRepositoryLevel">
                    <div 
                            v-if="taxonomiesLatestCachedOn"
                            class="box-last-cached-on">
                        <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(taxonomiesLatestCachedOn).toLocaleString() }}</span>
                        <button 
                                @click="loadTaxonomiesList(true)">
                            <span class="screen-reader-text">
                                {{ $i18n.get('label_get_latest_report') }}
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                            </span>
                        </button>
                    </div>
                </terms-per-taxonomy-block>
            </div>
            <metadata-distribution-block
                    class="column is-full is-two-fifths-desktop"
                    :chart-data="metadata"
                    :is-fetching-data="isFetchingMetadata"
                    v-if="!isRepositoryLevel">
                <div 
                        v-if="metadataLatestCachedOn"
                        class="box-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(metadataLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadMetadata(true)">
                        <span class="screen-reader-text">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </metadata-distribution-block>

            <items-per-term-block
                        v-if="isRepositoryLevel"
                        class="column is-full"
                        :chart-data="taxonomyTerms"
                        :is-fetching-data="isFetchingTaxonomiesList" />
            <items-per-term-collection-block
                        v-else
                        class="column is-full"
                        :chart-data="taxonomyTerms"
                        :is-fetching-data="isFetchingMetadataList"
                        :collection-id="selectedCollection" />

            <activities-per-user-block
                    class="column is-full is-two-fifths-tablet"
                    :chart-data="activities"
                    :is-fetching-data="isFetchingActivities">
                <div 
                        v-if="activitiesLatestCachedOn"
                        class="box-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(activitiesLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadActivities(null, true)">
                        <span class="screen-reader-text">
                            {{ $i18n.get('label_get_latest_report') }}
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-rotate-270" />
                        </span>
                    </button>
                </div>
            </activities-per-user-block>

            <activities-block
                    class="column is-full is-three-fifths-tablet"
                    :chart-data="activities"
                    :is-fetching-data="isFetchingActivities"
                    @time-range-update="loadActivities">
                <div 
                        v-if="activitiesLatestCachedOn"
                        class="box-last-cached-on">
                    <span>{{ $i18n.get('label_report_generated_on') + ': ' + new Date(activitiesLatestCachedOn).toLocaleString() }}</span>
                    <button 
                            @click="loadActivities(null, true)">
                        <span class="screen-reader-text">
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

export default { 
    name: "ReportsList",
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
        collectionEditionPage() {
            return (this.selectedCollection && this.selectedCollection != 'default') ? (tainacan_plugin.admin_url + 'admin.php?page=tainacan_admin#/collections/' + this.selectedCollection) : '';
        },
        isRepositoryLevel() {
            return !this.selectedCollection || this.selectedCollection == 'default';
        },
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
    watch: {
        '$route.query': {
            handler(to) {
                this.selectedCollection = to['collection'] ? to['collection'] : 'default';
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
            immediate: true
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