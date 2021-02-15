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
            <div class="column is-full is-one-third-tablet has-text-centered">
                <number-block
                        class="postbox"
                        :source-collection="selectedCollection"
                        entity-type="collections"/>
            </div>
            <div class="column is-full is-one-third-tablet has-text-centered">
                <number-block 
                        class="postbox"
                        :source-collection="selectedCollection"
                        entity-type="items"/>
            </div>
            <div class="column is-full is-one-third-tablet has-text-centered">
               <number-block
                        class="postbox"
                        :source-collection="selectedCollection"
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
            isLoadingCollections: false,
        }
    },
    computed: {
        ...mapGetters('collection', {
            collections: 'getCollections',
        }),
        ...mapGetters('report', {
            reports: 'getReports',
        }),
    },
    watch: {
        '$route.query' (to) {
            this.selectedCollection = to['collection'] ? to['collection'] : 'default';
        }
    },
    created() {
        // Obtains colleciton id from query, if passed
        this.selectedCollection = this.$route.query['collection'] ? this.$route.query['collection'] : 'default';
        
        // Loads collection for the select input
        this.isLoadingCollections = true;
        this.fetchAllCollectionNames()
            .then(() => this.isLoadingCollections = false)
            .catch(() => this.isLoadingCollections = false);
    },
    methods: {
        ...mapActions('collection', [
            'fetchAllCollectionNames'
        ])
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