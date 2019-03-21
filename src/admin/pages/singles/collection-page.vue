<template>
    <div class="columns is-fullheight">
        <section class="column is-secondary-content">
            <tainacan-collection-subheader 
                    :current-user-can-edit="currentUserCanEdit"
                    :id="collectionId"/>
          
            <router-view
                    id="collection-page-container"
                    :collection-id="collectionId" 
                    class="page-container page-container-small"/>
        </section>
    </div>
</template>

<script>
import TainacanCollectionSubheader from '../../components/navigation/tainacan-collection-subheader.vue';
import { mapActions } from 'vuex';

export default {
    name: 'CollectionPage',
    data(){
        return {
            collectionId: Number,
            currentUserCanEdit: Boolean
        }
    },
    components: {
        TainacanCollectionSubheader
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollectionUserCanEdit'
        ])
    },
    created(){
        this.collectionId = parseInt(this.$route.params.collectionId);
        this.$eventBusSearch.setCollectionId(this.collectionId);
    },
    mounted() {
        this.fetchCollectionUserCanEdit(this.collectionId).then((caps) => {
            this.currentUserCanEdit = caps;
        }).catch((error) => this.$console.error(error));
    }
}
</script>

<style scoped>

</style>


