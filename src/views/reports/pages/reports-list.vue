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
            <div class="column is-full">
                <chart-block
                        class="postbox"
                        :chart-series="reports[3].chartSeries"
                        :chart-options="reports[3].chartOptions" />
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
            isFetchingSummary: false
        }
    },
    computed: {
        ...mapGetters('collection', {
            collections: 'getCollections',
        }),
        ...mapGetters('report', {
            reports: 'getReports',
            summary: 'getSummary' 
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
    },
    methods: {
        ...mapActions('collection', [
            'fetchAllCollectionNames'
        ]),
        ...mapActions('report', [
            'fetchSummary',
        ]),
        loadSummary() {
            this.isFetchingSummary = true;
            this.fetchSummary({ collectionId: this.selectedCollection })
                .then(() => this.isFetchingSummary = false)
                .catch(() => this.isFetchingSummary = false);
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