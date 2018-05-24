<template>
    <div class="columns is-fullheight">
        <section class="column is-secondary-content">
            <tainacan-subheader 
                    :class="{ 'is-shrink': shouldShrinkHeader }"
                    :id="collectionId"/>
            <router-view
                    @onShrinkHeader="shouldShrinkHeader = $event"
                    id="collection-page-container"
                    :collection-id="collectionId" 
                    class="page-container page-container-small"
                    :class="{'page-container-shrinked': shouldShrinkHeader }"/>
        </section>
    </div>
</template>

<script>
import TainacanSubheader from '../../components/navigation/tainacan-subheader.vue';

export default {
    name: 'CollectionPage',
    data(){
        return {
            collectionId: Number,
            shouldShrinkHeader: false
        }
    },
    components: {
        TainacanSubheader
    },
    created(){
        this.collectionId = parseInt(this.$route.params.collectionId);
        this.$eventBusSearch.setCollectionId(this.collectionId);
    },
    mounted() {
    }

}
</script>

<style scoped>

</style>


