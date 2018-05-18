<template>
    <div class="columns is-fullheight">
        <section class="column is-secondary-content">
            <tainacan-subheader 
                    :class="{ 'is-shrink': hasScrolled }"
                    :id="collectionId"/>
            <router-view
                    id="collecion-page-container"
                    ref="collection-page-container" 
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
            collectionId: Number,
            hasScrolled: false
        }
    },
    components: {
        TainacanSubheader
    },
    methods:{
        handleScroll() {
            this.hasScrolled = (this.$refs['collection-page-container'].$el.scrollTop > 5);
            console.log(this.$refs['collection-page-container'].$el.scrollTop);
        }
    },
    created(){
        this.collectionId = parseInt(this.$route.params.collectionId);
        this.$eventBusSearch.setCollectionId(this.collectionId);
    },
    mounted() {
        document.getElementById('collecion-page-container').addEventListener('scroll', this.handleScroll);
    }

}
</script>

<style scoped>

</style>


