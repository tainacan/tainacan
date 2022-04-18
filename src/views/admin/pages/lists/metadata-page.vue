<template>
    <div
            :class="{ 'repository-level-page page-container': isRepositoryLevel }"
            style="padding-bottom: 0;">
        <tainacan-title 
                :bread-crumb-items="[{ path: '', label: $i18n.get('metadata') }]"/>
        
        <template v-if="isRepositoryLevel">
            <p>{{ $i18n.get('info_repository_metadata_inheritance') }}</p>
            <br>
        </template>
        
        <div class="metadata-list-page">
            <b-tabs 
                    v-if="(isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_metadata') || (!isRepositoryLevel && collection && collection.current_user_can_edit_metadata))"
                    v-model="activeTab">    
                <b-tab-item :label="$i18n.get('label_metadata_and_sections')">
                    <div class="columns">

                        <!-- Active Metadata and Sections Area -->
                        <repository-metadata-list
                                v-if="isRepositoryLevel"
                                :metadata-type-filter-options="metadataTypeFilterOptions"
                                @onUpdatehightlightedMetadatum="(newValue) => hightlightedMetadatum = newValue" />
                        <collection-metadata-list
                                v-else
                                :metadata-type-filter-options="metadataTypeFilterOptions"
                                @onUpdatehightlightedMetadatum="(newValue) => hightlightedMetadatum = newValue" />

                        <!-- Available Metadata Area -->
                        <metadata-types-list 
                                :hightlighted-metadatum="hightlightedMetadatum"
                                :is-repository-level="isRepositoryLevel"
                                @onFinishedLoadingMetadataTypes="createMetadataTypeFilterOptions"/>
                    </div>
                </b-tab-item>

                <!-- Mapping ------------------- -->
                <b-tab-item :label="$i18n.get('mapping')">
                    <metadata-mapping-list :is-repository-level="isRepositoryLevel"/>
                </b-tab-item>
            </b-tabs>
            
            <section 
                    v-else
                    class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-30px tainacan-icon-metadata"/>
                        </span>
                    </p>
                    <p>{{ $i18n.get('info_can_not_edit_metadata') }}</p>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import RepositoryMetadataList from '../../components/lists/repository-metadata-list.vue';
import CollectionMetadataList from '../../components/lists/collection-metadata-list.vue';
import MetadataMappingList from '../../components/lists/metadata-mapping-list.vue';
import MetadataTypesList from '../../components/lists/metadata-types-list.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'MetadataPage',
    components: {
        RepositoryMetadataList,
        CollectionMetadataList,
        MetadataTypesList,
        MetadataMappingList,
    },
    data() {
        return {
            isRepositoryLevel: false,
            activeTab: 0,
            hightlightedMetadatum: '',
            metadataTypeFilterOptions: []
        }
    },
    computed: {
        collection() {
            return this.getCollection();
        }
    },
    created() {
        this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
    },
    mounted() {
        if (!this.isRepositoryLevel)
            this.$root.$emit('onCollectionBreadCrumbUpdate', [{ path: '', label: this.$i18n.get('metadata') }]);
    },
    methods: {
        ...mapGetters('collection',[
            'getCollection'
        ]),
        ...mapGetters('metadata',[
            'getMetadatumTypes'
        ]),
        createMetadataTypeFilterOptions() {
            this.metadataTypeFilterOptions = JSON.parse(JSON.stringify(this.getMetadatumTypes()))
                .map((metadatumType) => {
                    return {
                        enabled: false,
                        name: metadatumType.name,
                        type: metadatumType.className
                    }
                });
        }
    }
}
</script>

<style lang="scss">

    .metadata-list-page {
        padding-bottom: 0;

        .tainacan-page-title {
            margin-bottom: 14px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;

            h1, h2 {
                font-size: 1.25em;
                font-weight: 500;
                color: var(--tainacan-heading-color);
                display: inline-block;
                width: 80%;
                flex-shrink: 1;
                flex-grow: 1;
            }
            a.back-link {
                font-weight: 500;
                float: right;
                margin-top: 5px;
            }
            hr {
                margin: 3px 0px 4px 0px; 
                height: 1px;
                background-color: var(--tainacan-secondary);
                width: 100%;
            }
        }
                  
        .b-tabs .tab-content {
            overflow: visible;
            min-height: 300px;
            padding-bottom: 0;
            padding-left: 0;
        }

        .column {
            overflow-x: hidden;
            overflow-y: auto;
            padding: 0.75em 0;

            &>section.field {
                position: absolute;
            }

            &:not(.available-metadata-area){
                margin-right: var(--tainacan-one-column);
                flex-grow: 2;

                @media screen and (max-width: 769px) {
                    margin-right: 0;
                }
            }
            h3 {
                font-weight: 500;
            }
        }

        .page-title {
            border-bottom: 1px solid var(--tainacan-secondary);
            margin: 1em 0em 2.0em 0em;
            h2 {
                color: var(--tainacan-blue5);
                font-weight: 500;
            }
        }

        .sub-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5em 1em 0.5em 2em;

            .header-item {
                margin-left: 0.75rem;
                margin-bottom: 0px;
            }

            h3 {
                margin-right: auto;
            }

            .dropdown-menu {
                display: block;

                div.dropdown-content {
                    padding: 0;

                    .metadata-options-container {
                        max-height: 288px;
                        overflow: auto;
                        font-size: 1.125em;
                    }
                    .dropdown-item {
                        padding: 0.25em 1.0em 0.25em 0.75em;
                    }
                    .dropdown-item span{
                        vertical-align: middle;
                    }
                }
            }
        }

        .collapse-all {
            display: inline-flex;
            align-items: center;
            margin-left: 1.5em;
        }
        .collapse-all__text {
            font-size: 0.75em !important;
        }
        .loading-spinner {
            animation: spinAround 500ms infinite linear;
            border: 2px solid var(--tainacan-gray2);
            border-radius: 290486px;
            border-right-color: transparent;
            border-top-color: transparent;
            content: "";
            display: inline-block;
            height: 1em; 
            width: 1em;
        }

        .active-metadata-area,
        .active-metadata-sections-area {
            min-height: 3em;

            @media screen and (max-width: 769px) {
                min-height: 45px;
                margin: 0; 
                padding-right: 0em;
            }
            @media screen and (max-width: 1216px) {
                padding-right: 1em;
            }

            &.metadata-area-receive {
                border: 1px dashed var(--tainacan-gray4);
            }

            .collapse {
                display: initial;
            }

            &.active-metadata-sections-area {
                font-size: 0.875em;
                margin-left: -0.8em;
                padding-right: 1em;
                min-height: 330px;
            }

            .active-metadatum-item,
            .active-metadata-sections-item {
                background-color: var(--tainacan-white);
                padding: 0.7em 0.9em;
                margin: 0px 4px;
                min-height: 2.8571em;
                display: block; 
                position: relative;
                cursor: grab;
                opacity: 1 !important;
                        
                &>.field, form {
                    background-color: var(--tainacan-white) !important;
                }
                
                .handle {
                    padding-right: 6em;
                    white-space: nowrap;
                    display: flex;
                }
                .grip-icon { 
                    color: var(--tainacan-gray3); 
                    position: relative;
                }
                .metadatum-name {
                    white-space: normal;
                    font-weight: bold;
                    margin-left: 0.4em;
                    margin-right: 0.4em;

                    &.is-danger {
                        color: var(--tainacan-danger) !important;
                    }
                }
                &.active-metadata-sections-item {
                    border-bottom: 1px solid var(--tainacan-gray3);

                    .metadatum-name {
                        font-size: 1.25em;
                        color: var(--tainacan-secondary);
                    }
                }
                .label-details {
                    font-weight: normal;
                    color: var(--tainacan-gray3);
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    overflow: hidden;
                }
                .not-saved {
                    font-style: italic;
                    font-weight: bold;
                    color: var(--tainacan-danger);
                    margin-left: 0.5em;
                }
                .controls { 
                    font-size: 0.875em;
                    position: absolute;
                    right: 10px;
                    top: 10px;
                    .switch {
                        position: relative;
                        bottom: 1px;
                    }
                    .icon {
                        bottom: 1px;   
                        position: relative;
                        i, i:before { font-size: 1.25em; }
                    }
                }

                &.is-compact-item .metadatum-name {
                    text-overflow: ellipsis;
                    overflow-x: hidden;
                    white-space: nowrap;
                }
        
                &.not-sortable-item,
                &.not-sortable-item:hover {
                    cursor: default;
                    background-color: var(--tainacan-white) !important;
                } 
                &.not-focusable-item,
                &.not-focusable-item:hover {
                    cursor: default;
                
                    .metadatum-name {
                        color: var(--tainacan-secondary);
                    }
                }
                &.disabled-metadatum:not(.not-sortable-item),
                &.disabled-metadatum:not(.not-sortable-item):hover {
                    color: var(--tainacan-gray3);
                    .label-details, .not-saved {
                        color: var(--tainacan-gray3) !important;
                    }
                }
            }
            .active-metadatum-item:not(:hover) .icon-level-identifier .tainacan-icon::before,
            .active-filter-item:hover.not-sortable-item .icon-level-identifier .tainacan-icon::before {
                color: var(--tainacan-gray3) !important;
            }
            .active-metadatum-item:hover:not(.not-sortable-item),
            .active-metadata-section-item:hover:not(.not-sortable-item) {
                background-color: var(--tainacan-turquoise1);
                border-color: var(--tainacan-turquoise1);

                .label-details, .not-saved {
                    color: var(--tainacan-gray4) !important;
                }
                .grip-icon { 
                    color: var(--tainacan-secondary) !important; 
                }
            }
            .sortable-ghost {
                border: 1px dashed var(--tainacan-gray2);
                background: var(--tainacan-white);
                display: block;
                padding: 0.7em 0.9em;
                margin: 4px;
                height: 2.8571em;
                position: relative;

                .grip-icon { 
                    color: var(--tainacan-white); 
                }
            }
        }

        
        .inherited-metadatum {
            .switch.is-small input[type="checkbox"]:checked + .check {
                border: 1.5px solid var(--tainacan-blue5);
                &::before { background-color: var(--tainacan-blue5); }
            }

            &.active-metadatum-item:hover:not(.not-sortable-item),
            &.active-metadata-section-item:hover:not(.not-sortable-item) {
                background-color: var(--tainacan-blue1);
                border-color: var(--tainacan-blue1);
                
                .grip-icon { 
                    color: var(--tainacan-blue5) !important; 
                }
            }
        }
    }
</style>
