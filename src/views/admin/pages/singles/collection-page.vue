<template>
    <div 
            class="columns is-fullheight"
            :class="{ 'tainacan-admin-collection-mobile-mode': isMobileMode }">
        <section class="column is-secondary-content">
            <tainacan-collection-subheader v-if="!isIframeMode && !isMobileMode" />

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
    components: {
        TainacanCollectionSubheader
    },
    data(){
        return {
            collectionId: Number
        }
    },
    computed: {
        isIframeMode() {
            return this.$route && this.$route.query && this.$route.query.iframemode;
        },
        isMobileMode() {
            return this.$route && this.$route.query && this.$route.query.mobilemode;
        }
    },
    watch: {
        '$route' (to, from) {
            if (!this.isRepositoryLevel && from.path != undefined && to.path != from.path && this.collectionId != this.$route.params.collectionId) {
                this.collectionId = this.$route.params.collectionId;
                this.fetchCollectionBasics({ collectionId: this.collectionId, isContextEdit: true })
                    .catch((error) => this.$console.error(error));
            }
        }
    },
    created(){
        this.collectionId = this.$route.params.collectionId;
        
        this.$eventBusSearch.setCollectionId(this.collectionId);

        // Loads to store basic collection info such as name, url, current_user_can_edit... etc.
        this.fetchCollectionBasics({ collectionId: this.collectionId, isContextEdit: true })
            .catch((error) => this.$console.error(error));
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollectionBasics'
        ])
    }
}
</script>


