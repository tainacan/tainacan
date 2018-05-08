<template>
    <div class="columns is-fullheight">
        <section class="column is-secondary-content">
            <tainacan-subheader
                    :current-user-can-edit="currentUserCanEdit" 
                    :id="collectionId"/>
            <router-view 
                    :collection-id="collectionId" 
                    class="page-container page-container-small"/>
        </section>
    </div>
</template>

<script>
import TainacanSubheader from '../../components/navigation/tainacan-subheader.vue';
import {mapActions} from 'vuex';

export default {
    name: 'CollectionPage',
    data(){
        return {
            collectionId: Number,
            currentUserCanEdit: false
        }
    },
    components: {
        TainacanSubheader
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollection'
        ]),
    },
    created(){
        this.collectionId = parseInt(this.$route.params.collectionId);
        this.$eventBusSearch.setCollectionId(this.collectionId);

        this.fetchCollection(this.collectionId).then((collection) => this.currentUserCanEdit = collection.current_user_can_edit);
    }

}
</script>

<style scoped>

</style>


