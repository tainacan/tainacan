<template>
    <div 
            class="columns is-fullheight"
            :class="{
                'tainacan-admin-collection-item-edition-mode': $adminOptions.itemEditionMode,
                'tainacan-admin-collection-mobile-app-mode': $adminOptions.mobileAppMode
            }">
        <section 
                class="column is-secondary-content"
                :style="$adminOptions.hideRepositorySubheader ? 'margin-top: 0; height: 100%;' : ''">
            <tainacan-collection-subheader v-if="!$adminOptions.hideCollectionSubheader" />
            <router-view
                    id="collection-page-container"
                    :collection-id="collectionId" 
                    class="page-container"
                    :class="{
                        'page-container-small': !$adminOptions.hideRepositorySubheader && !$adminOptions.hideCollectionSubheader,
                        'is-loading-collection-basics': isLoadingCollectionBasics
                    }"/>
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
            collectionId: Number,
            isLoadingCollectionBasics: Boolean
        }
    },
    watch: {
        '$route' (to, from) {
            if (!this.isRepositoryLevel &&
                (from != undefined) &&
                (from.path != undefined) &&
                (to.path != from.path) &&
                (this.collectionId != this.$route.params.collectionId)
            ) {
                this.isLoadingCollectionBasics = true;
                this.collectionId = this.$route.params.collectionId;
                this.fetchCollectionBasics({ collectionId: this.collectionId, isContextEdit: true })
                    .then(() => {
                        this.isLoadingCollectionBasics = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isLoadingCollectionBasics = false;
                    });
            }
        }
    },
    created() {
        this.collectionId = this.$route.params.collectionId;
        
        this.$eventBusSearch.setCollectionId(this.collectionId);

        // Loads to store basic collection info such as name, url, current_user_can_edit... etc.
        this.fetchCollectionBasics({ collectionId: this.collectionId, isContextEdit: true })
            .then(() => {
                this.isLoadingCollectionBasics = false;
            })
            .catch((error) => {
                this.$console.error(error);
                this.isLoadingCollectionBasics = false;
            });
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollectionBasics'
        ])
    }
}
</script>

<style lang="scss">
    #collection-page-container.is-loading-collection-basics {
        .section {
            display: none; // Prevents info as "No permissions to see this" to appear before we finished loading.
        }
    }
</style>


