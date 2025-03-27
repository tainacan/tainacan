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
            <router-view
                    id="collection-page-container"
                    :collection-id="collectionId" 
                    class="page-container"
                    :class="{
                        'is-loading-collection-basics': isLoadingCollectionBasics
                    }"
                    :key="$route.query.authorid" />
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'CollectionPage',
    data() {
        return {
            collectionId: [String, Number],
            isLoadingCollectionBasics: Boolean
        }
    },
    computed: {
        ...mapGetters('collection', {
            'collection': 'getCollection'
        }),
        ...mapGetters('item', {
            'item': 'getItem'
        })
    },
    watch: {
        '$route': {
            handler(to, from) {
                if (
                    !this.isRepositoryLevel &&
                    (from != undefined) &&
                    (from.path != undefined) &&
                    (to.path != from.path)
                ) {
                    if ( this.collectionId != this.$route.params.collectionId) {
                        this.isLoadingCollectionBasics = true;
                        this.collectionId = Number(this.$route.params.collectionId);
                        this.fetchCollectionBasics({ collectionId: this.collectionId, isContextEdit: true })
                            .then(() => {
                                wp.hooks.doAction('tainacan_navigation_path_updated', { currentRoute: to, adminOptions: this.$adminOptions, collection: this.collection, item: this.item });
                                this.isLoadingCollectionBasics = false;
                            })
                            .catch((error) => {
                                this.$console.error(error);
                                this.isLoadingCollectionBasics = false;
                            });
                    } else {
                        wp.hooks.doAction('tainacan_navigation_path_updated', { currentRoute: to, adminOptions: this.$adminOptions, collection: this.collection, item: this.item });
                    }
                } 
            },
            deep: true
        }
    },
    created() {
        this.collectionId = Number(this.$route.params.collectionId);
        this.$eventBusSearch.setCollectionId(this.collectionId);

        // Loads to store basic collection info such as name, url, current_user_can_edit... etc.
        this.fetchCollectionBasics({ collectionId: this.collectionId, isContextEdit: true })
            .then(() => {
                wp.hooks.doAction('tainacan_navigation_path_updated', { currentRoute: this.$route, adminOptions: this.$adminOptions, collection: this.collection, item: this.item });
                
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


