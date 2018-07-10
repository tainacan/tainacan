<template>
    <div class="columns is-fullheight">
        <section class="column is-secondary-content">
            <tainacan-subheader 
                    :class="{ 'is-shrink': shouldShrinkHeader }"
                    :id="collectionId"/>
            <router-view
                    @onShrinkHeader="onUpdateShrinkHeader($event)"
                    id="export-page-container"
                    :collection-id="collectionId" 
                    class="page-container page-container-small"
                    :class="{'page-container-shrinked': shouldShrinkHeader }"/>
        </section>
    </div>
</template>

<script>
import TainacanSubheader from '../../components/navigation/tainacan-subheader.vue';

export default {
    name: 'ExportPage',
    data(){
        return {
            collectionId: Number,
            itemId: Number,
            selectedList: [],
            shouldShrinkHeader: false
        }
    },
    components: {
        TainacanSubheader
    },
    created(){
        this.collectionId = parseInt(this.$route.params.collectionId);
        this.itemId = parseInt(this.$route.params.itemId);
        this.selectedList = [],
        this.$eventBusSearch.setCollectionId(this.collectionId);
    },
    methods: {
        onUpdateShrinkHeader(event) {
            if (this.shouldShrinkHeader != event)
                this.shouldShrinkHeader = event;
        }
    }

}
</script>

<style scoped>

</style>


