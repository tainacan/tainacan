<template>
    <div class="columns is-fullheight">
        <section class="column is-secondary-content">
            <tainacan-collection-subheader :id="collectionId"/>
          
            <router-view
                    id="collection-page-container"
                    :collection-id="collectionId" 
                    class="page-container page-container-small"/>
            
        </section>
    </div>
</template>

<script>
import TainacanCollectionSubheader from '../../components/navigation/tainacan-collection-subheader.vue';

export default {
    name: 'CollectionPage',
    data(){
        return {
            collectionId: Number
        }
    },
    components: {
        TainacanCollectionSubheader
    },
    created(){
        this.collectionId = parseInt(this.$route.params.collectionId);
        this.$eventBusSearch.setCollectionId(this.collectionId);
    }
}
</script>

<style scoped>

</style>


