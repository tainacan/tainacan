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
                    :key="$route.query.authorid"
                    :collection-id="collectionId" 
                    class="page-container"
                    :class="{
                        'is-loading-collection-basics': isLoadingCollectionBasics
                    }" />
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


