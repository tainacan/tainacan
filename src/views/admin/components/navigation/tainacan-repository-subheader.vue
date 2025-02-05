<template>
    <div
            id="tainacan-repository-subheader" 
            class="level secondary-page"
            :class="{ 'is-repository-level': isRepositoryLevel }">
        <div
                v-if="$adminOptions.hideCollectionSubheader"
                class="back-button is-hidden-mobile">
            <router-link
                    v-if="activeRoute == 'ItemPage' || activeRoute == 'ItemEditionForm' || activeRoute == 'ItemCreatePage'"
                    v-slot="{ navigate }" 
                    :to="{ path: collection && collection.id ? $routerHelper.getCollectionItemsPath(collection.id, '') : '', query: activeRoute == 'CollectionItemsPage' ? $route.query : '' }"
                    custom>
                <button
                        role="link"
                        class="button is-turquoise4"
                        type="button"
                        :aria-label="$i18n.get('back')"
                        @click="navigate()">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-previous" />
                    </span>
                </button>
            </router-link>
        </div>

        <h1 
                v-if="isRepositoryLevel"
                style="margin-right: auto;">
            {{ repositoryName }}
        </h1>
        <h1 
                v-else
                style="margin-right: auto;">
            {{ $i18n.get('collection') + '' }} 
            <router-link 
                    v-if="collection && collection.id"
                    :to="$routerHelper.getCollectionPath(collection.id)"
                    class="has-text-weight-bold has-text-white">
                {{ collection.name ? collection.name : '' }}
                <span 
                        v-if="collection.status && $statusHelper.hasIcon(collection.status)"
                        v-tooltip="{
                            content: $i18n.get('status_' + collection.status),
                            autoHide: true,
                            popperClass: ['tainacan-tooltip', 'tooltip'],
                            placement: 'auto-start'
                        }"
                        class="icon has-text-white">
                    <i 
                            class="tainacan-icon tainacan-icon-1em"
                            :class="$statusHelper.getIcon(collection.status)"
                        />
                </span>
            </router-link>
        </h1>

        <ul class="repository-subheader-icons">
            <li v-if="!isRepositoryLevel && !$adminOptions.hideRepositorySubheaderExportButton && $userCaps.hasCapability('manage_tainacan')">
                <a
                        id="exporter-collection-button"
                        class="button"
                        :aria-label="$i18n.get('exporters')"
                        @click="openAvailableExportersModal">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-export" />
                    </span>
                    <span class="is-hidden-mobile">{{ $i18n.get('exporters') }}</span>
                </a>
            </li>
            <li>
                <a
                        v-if="!isRepositoryLevel && collection && collection.url && !$adminOptions.hideRepositorySubheaderViewCollectionButton"
                        id="view-collection-button"
                        :href="collection && collection.url ? collection.url : ''"
                        target="_blank"
                        class="button">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                    </span>
                    <span class="is-hidden-mobile">{{ $i18n.get('label_view_collection_on_website') }}</span>
                </a>
            </li>
            <li>
                <a
                        v-if="isRepositoryLevel && !$adminOptions.hideRepositorySubheaderViewTaxonomiesButton"
                        id="view-repository-button--taxonomies"
                        :href="repositoryTaxonomiesURL"
                        target="_blank"
                        class="button">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                    </span>
                    <span class="is-hidden-mobile">{{ $i18n.get('label_view_taxonomies_on_website') }}</span>
                </a>
            </li>
            <li>
                <a
                        v-if="isRepositoryLevel && !$adminOptions.hideRepositorySubheaderViewCollectionsButton"
                        id="view-repository-button"
                        :href="repositoryURL"
                        target="_blank"
                        class="button">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                    </span>
                    <span class="is-hidden-mobile">{{ $i18n.get('label_view_collections_on_website') }}</span>
                </a>
            </li>
        </ul>

    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import AvailableExportersModal from '../modals/available-exporters-modal.vue';

export default {
    name: 'TainacanRepositorySubheader',
    props: {
        isMenuCompressed: false,
        isRepositoryLevel: true,
        activeRoute: ''
    },
    data() {
        return {
            repositoryName: tainacan_plugin.repository_name,
            repositoryURL: tainacan_plugin.theme_collection_list_url,
            repositoryTaxonomiesURL: tainacan_plugin.theme_taxonomy_list_url,
            collectionId: ''
        }
    },
    computed: {
        ...mapGetters('collection', {
            'collection': 'getCollection'
        })
    },
    methods: {
        openAvailableExportersModal(){

            this.$buefy.modal.open({
                parent: this,
                component: AvailableExportersModal,
                hasModalCard: true,
                props: {
                    sourceCollection: this.collection.id,
                    hideWhenManualCollection: true
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        }
    }
}
</script>

<style lang="scss">

    // Tainacan Header
    #tainacan-repository-subheader {
        background-color: var(--tainacan-turquoise6);
        height: var(--tainacan-admin-header-height, 3.0em);
        max-height: var(--tainacan-admin-header-height, 3.0em);
        width: 100%;
        padding-top: 8px;
        padding-bottom: 8px;
        padding-right: var(--tainacan-one-column);
        padding-left: var(--tainacan-one-column);
        margin: 0px;
        display: flex;
        vertical-align: middle; 
        z-index: 8;
        transition: padding-left 0.2s linear, background-color 0.2s linear;

        &.is-repository-level {
            background-color: var(--tainacan-blue5);
        }

        h1 {
            font-size: 1.25em;
            color: var(--tainacan-white);
            max-width: 100%;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;  
            transition: all 0.2s linear;
        }

        .back-button {
            padding: 0;
            margin: 0;
            width: var(--tainacan-one-column);
            min-width: var(--tainacan-one-column);
            color: var(--tainacan-white);
            display: flex;
            margin-left: calc(-1 * var(--tainacan-one-column));

            button, 
            button:hover, 
            button:focus, 
            button:active {
                width: 100%;
                color: var(--tainacan-white);
                background-color: transparent !important;
                border: none;
                height: var(--tainacan-admin-header-height, 3.0em) !important;

                .icon {
                    margin-top: -2px;
                    font-size: 1.5em;
                }
            }
        }

        .repository-subheader-icons {
            display: flex;
            flex-wrap: nowrap;

            #view-collection-button,
            #exporter-collection-button {
                font-size: 0.9375em !important;
                border: none;
                border-radius: 4px !important;
                height: 2.25em !important;
                background-color: transparent;
                color: var(--tainacan-white);

                &:hover {
                    background-color: var(--tainacan-turquoise5) !important;
                }
            }
            #view-repository-button,
            #view-repository-button--taxonomies {
                font-size: 0.9375em !important;
                border: none;
                border-radius: 4px !important;
                height: 2.25em !important;
                background-color: transparent;
                color: var(--tainacan-white);

                &:hover {
                    background-color: var(--tainacan-blue4) !important;
                }
            }

            @media screen and  (max-width: 768px) {
                .icon {
                    margin-right: 0;
                }
            }
        }
    }
</style>


