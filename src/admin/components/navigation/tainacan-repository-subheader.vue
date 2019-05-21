<template>
    <div
            id="tainacan-repository-subheader" 
            class="level secondary-page"
            :class="{'is-menu-compressed': isMenuCompressed, 'is-repository-level' : isRepositoryLevel}">
        <h1 v-if="isRepositoryLevel">{{ repositoryName }}</h1>
        <h1 v-else>{{ $i18n.get('collection') + '' }} <span class="has-text-weight-bold">{{ collectionName }}</span></h1>

        <ul class="repository-subheader-icons">
            <li
                    v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 300,
                            },
                            content: $i18n.get('exporters'),
                            autoHide: false,
                            placement: 'bottom-start',
                            classes: ['header-tooltips']
                        }">
                <a
                        @click="openAvailableExportersModal"
                        class="button"
                        id="exporter-collection-button"
                        v-if="!isRepositoryLevel">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-export"/>
                    </span>
                </a>
            </li>
            <li     
                    v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 300,
                            },
                            content: $i18n.get('label_view_collection'),
                            autoHide: false,
                            placement: 'bottom-end',
                            classes: ['header-tooltips']
                        }">
                <a
                        :href="collectionURL"
                        target="_blank"
                        v-if="!isRepositoryLevel"
                        class="button"
                        id="view-collection-button">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-see"/>
                </span>
                    <!-- {{ $i18n.get('label_view_collection') }} -->
                </a>
            </li>
            <li     
                    v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 300,
                            },
                            content: $i18n.get('label_view_repository'),
                            autoHide: false,
                            placement: 'bottom-end',
                            classes: [ isRepositoryLevel ? 'repository-header-tooltips' : 'header-tooltips']
                        }">
                <a
                        :href="repositoryURL"
                        target="_blank"
                        v-if="isRepositoryLevel"
                        class="button"
                        id="view-repository-button">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-see"/>
                </span>
                    <!-- {{ $i18n.get('label_view_collection') }} -->
                </a>
            </li>
        </ul>


    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import AvailableExportersModal from '../other/available-exporters-modal.vue';

export default {
    name: 'TainacanRepositorySubheader',
    data() {
        return {
            repositoryName: tainacan_plugin.repository_name,
            repositoryURL: tainacan_plugin.theme_collection_list_url,
            collectionId: ''
        }
    },
    props: {
        isMenuCompressed: false,
        isRepositoryLevel: true
    },
    computed: {
        collectionName() {
            return this.getCollectionName();
        },
        collectionURL() {
            return this.getCollectionURL();
        }
    },
    watch: {
        '$route'  (to, from) {
            if (!this.isRepositoryLevel && from.path != undefined && to.path != from.path) {
                this.collectionId = this.$route.params.collectionId;
                this.fetchCollectionNameAndURL(this.collectionId);
            }
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollectionNameAndURL'
        ]),
        ...mapGetters('collection', [
            'getCollectionName',
            'getCollectionURL'
        ]),
        openAvailableExportersModal(){

            this.$modal.open({
                parent: this,
                component: AvailableExportersModal,
                hasModalCard: true,
                props: {
                    sourceCollection: this.collectionId,
                    hideWhenManualCollection: true
                }
            });
        }
    }
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";
    
    .header-tooltips .tooltip-inner {
        color: white;
        text-shadow: 1px 1px $turquoise4;
        background-color: $turquoise3;
        font-size: 0.75rem;
        font-weight: 400;
        padding: 10px 14px;
    }
    .header-tooltips .tooltip-arrow {
        border-color: $turquoise3;
    }

    .repository-header-tooltips .tooltip-inner {
        color: white;
        background-color: $blue3;
        font-size: 0.75rem;
        font-weight: 400;
        padding: 10px 14px;
    }
    .repository-header-tooltips .tooltip-arrow {
        border-color: $blue3;
    }

    // Tainacan Header
    #tainacan-repository-subheader {
        background-color: $turquoise5;
        height: 42px;
        max-height: 42px;
        width: 100%;
        // overflow-y: hidden;
        padding-top: 10px;
        padding-bottom: 10px;
        padding-right: 0;
        padding-left: calc((4.166666667% - 6.666666667px) + 160px);
        margin: 0px;
        display: flex;
        vertical-align: middle; 
        left: 0;
        right: 0;
        top: $header-height;
        position: absolute;
        z-index: 98;
        transition: padding-left 0.2s linear, background-color 0.2s linear;

        &.is-repository-level {
            background-color: $blue5;
            padding-right: $page-side-padding;

            .repository-subheader-icons { margin-right: -1rem !important; }
        }

        &.is-menu-compressed {     
            padding-left: calc((4.166666667% - 2.083333333px)  + 50px);
        }

        h1 {
            font-size: 1.125rem;
            color: white;
            line-height: 1.4rem;
            max-width: 100%;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;  
            transition: all 0.2s linear;
        }

        .repository-subheader-icons {
            display: flex;
            flex-wrap: nowrap;
            margin-right: calc(4.6666667% - 2.083333333px);

            #view-repository-button,
            #view-collection-button,
            #exporter-collection-button {
                border: none;
                border-radius: 0px !important;
                height: 42px !important;
                background-color: transparent;
                color: white;
                width: 48px;
            }
        }

        @media screen and (max-width: 769px) {
            top: 102px;
            padding-left: 4.166666667% !important;
        }
    }
</style>


