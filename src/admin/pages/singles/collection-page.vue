<template>
    <div class="columns is-fullheight">
        <section class="column is-secondary-content">
            <tainacan-subheader :id="collectionId"/>
            <router-view 
                    :collection-id="collectionId" 
                    class="page-container"/>
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
    components: {
        TainacanSubheader
    },
    created(){
        this.collectionId = parseInt(this.$route.params.collectionId);
    }

}
</script>

<style scoped>

</style>


