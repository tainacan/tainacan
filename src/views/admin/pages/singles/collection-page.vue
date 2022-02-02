<template>
    <div 
            class="columns is-fullheight"
            :class="{ 'tainacan-admin-collection-mobile-app-mode': $adminOptions.mobileAppMode }">
        <section 
                class="column is-secondary-content"
                :style="$adminOptions.hideRepositorySubheader ? 'margin-top: 0; height: 100%;' : ''">
            <tainacan-collection-subheader v-if="!$adminOptions.hideCollectionSubheader" />
            <router-view
                    id="collection-page-container"
                    :collection-id="collectionId" 
                    class="page-container"
                    :class="{ 'page-container-small': !$adminOptions.hideRepositorySubheader }"/>
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
    data() {
        return {
            collectionId: Number
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
    created() {
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


