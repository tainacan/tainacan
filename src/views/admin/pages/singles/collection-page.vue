<template>
    <router-view
            id="collection-page-container"
            :key="$route.query.authorid"
            :collection-id="collectionId" 
            class="page-container is-secondary-content"
            :class="{
                'is-loading-collection-basics': isLoadingCollectionBasics,
                'tainacan-admin-collection-item-edition-mode': $adminOptions.itemEditionMode,
                'tainacan-admin-collection-mobile-app-mode': $adminOptions.mobileAppMode
            }" />
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
                                wp.hooks.doAction(
                                    'tainacan_navigation_path_updated', 
                                    { 
                                        currentRoute: to,
                                        adminOptions: this.$adminOptions,
                                        collection: this.collection,
                                        parentEntity: {
                                            rootLink: 'collections',
                                            name: this.collection.name,
                                            defaultLink: `collections/${this.collectionId}/items`,
                                            label: this.$i18n.get('collections')
                                        }
                                    }
                                );
                                this.isLoadingCollectionBasics = false;     
                                this.$routerHelper.appendToPageTitle(this.collection.name);           
                            })
                            .catch((error) => {
                                this.$console.error(error);
                                this.isLoadingCollectionBasics = false;
                            });
                    } else {
                        this.$routerHelper.appendToPageTitle(this.collection.name);
                        wp.hooks.doAction(
                            'tainacan_navigation_path_updated', 
                            { 
                                currentRoute: to,
                                adminOptions: this.$adminOptions,
                                collection: this.collection,
                                parentEntity: {
                                    rootLink: 'collections',
                                    name: this.collection.name,
                                    defaultLink: `collections/${this.collectionId}/items`,
                                    label: this.$i18n.get('collections')
                                }
                            }
                        );
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
                wp.hooks.doAction(
                    'tainacan_navigation_path_updated', 
                    { 
                        currentRoute: this.$route,
                        adminOptions: this.$adminOptions,
                        collection: this.collection,
                        parentEntity: {
                            rootLink: 'collections',
                            name: this.collection ? this.collection.name : '',
                            defaultLink: `collections/${this.collectionId}/items`,
                            label: this.$i18n.get('collections')
                        }
                    }
                );
                this.isLoadingCollectionBasics = false;
                this.$routerHelper.appendToPageTitle(this.collection.name);
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


