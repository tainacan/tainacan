<template>
    <div class="columns is-fullheight">
        <section class="column is-secondary-content">
            <tainacan-subheader 
                    :class="{ 'is-shrink': headerShouldShrink }"
                    :id="collectionId"/>
            <router-view
                    id="collection-page-container"
                    :collection-id="collectionId" 
                    class="page-container page-container-small"/>
        </section>
    </div>
</template>

<script>
import TainacanSubheader from '../../components/navigation/tainacan-subheader.vue';

export default {
    name: 'CollectionPage',
    data(){
        return {
            collectionId: Number
        }
    },
    computed: {
        headerShouldShrink() {
            return this.$shouldShrink;
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


