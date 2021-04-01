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
                </div>
                <div
                        :class="{ 'is-one-third-tablet': isRepositoryLevel }"
                        class="column is-full is-half-tablet has-text-centered">
                    <number-block 
                            :class="{ 'skeleton': isFetchingSummary }"
                            class="postbox"
                            :summary="summary"
                            entity-type="items"/>
                </div>
                <div 
                        v-if="isRepositoryLevel"
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
                <collections-list-block
                        class="column is-full"
                        :chart-data="collectionsList"
                        :is-fetching-data="isFetchingCollectionsList"
                        v-if="isRepositoryLevel" />

                <metadata-types-block
                        class="column is-full"
                        :chart-data="metadata"
                        :is-fetching-data="isFetchingMetadata"
                        v-if="!isRepositoryLevel" />

                <terms-per-taxonomy-block
                        class="column is-full"
                        :chart-data="taxonomyList"
                        :is-fetching-data="isFetchingTaxonomiesList"
                        v-if="isRepositoryLevel"/>
            </div>
            <metadata-distribution-block
                    class="column is-full is-two-fifths-desktop"
                    :chart-data="metadata"
                    :is-fetching-data="isFetchingMetadata"
                    v-if="!isRepositoryLevel"/>

            <items-per-term-block
                        class="column is-full"
                        :chart-data="taxonomyTerms"
                        :is-fetching-data="isRepositoryLevel ? isFetchingTaxonomiesList : isFetchingMetadataList"
                        :is-repository-level="isRepositoryLevel"
                        v-if="isRepositoryLevel" />

            <activities-per-user-block
                    class="column is-full is-two-fifths-tablet"
                    :chart-data="activities"
                    :is-fetching-data="isFetchingActivities" />

            <activities-block
                    class="column is-full is-three-fifths-tablet"
                    :chart-data="activities"
                    :is-fetching-data="isFetchingActivities"
                    @time-range-update="loadActivities" />
            
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
            isFetchingTaxonomiesList: false
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
        }),
        isRepositoryLevel() {
            return !this.selectedCollection || this.selectedCollection == 'default';
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
        loadSummary() {
            this.isFetchingSummary = true;
            this.fetchSummary({ collectionId: this.selectedCollection })
                .then(() => this.isFetchingSummary = false)
                .catch(() => this.isFetchingSummary = false);
        },
        loadMetadata() {
            this.isFetchingMetadata = true;
            this.fetchMetadata({ collectionId: this.selectedCollection })
                .then(() => this.isFetchingMetadata = false)
                .catch(() => this.isFetchingMetadata = false);
        },
        loadCollectionsList() {
            this.isFetchingCollectionsList = true;
            this.fetchCollectionsList()
                .then(() => this.isFetchingCollectionsList = false)
                .catch(() => this.isFetchingCollectionsList = false);
        },
        loadTaxonomiesList() {
            this.isFetchingTaxonomiesList = true;
            this.fetchTaxonomiesList()
                .then(() => this.isFetchingTaxonomiesList = false)
                .catch(() => this.isFetchingTaxonomiesList = false);
        },
        loadMetadataList() {
            this.isFetchingMetadataList = true;
            this.fetchMetadataList({ collectionId: this.selectedCollection, onlyTaxonomies: true })
                .then(() => this.isFetchingMetadataList = false)
                .catch(() => this.isFetchingMetadataList = false);
        },
        loadActivities(startDate) {
            this.isFetchingActivities = true;
            this.fetchActivities({ collectionId: this.selectedCollection, startDate: startDate })
                .then(() => this.isFetchingActivities = false)
                .catch(() => this.isFetchingActivities = false);
        }
    }
}
</script>