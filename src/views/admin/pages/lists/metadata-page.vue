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
                <b-tab-item :label="isRepositoryLevel ? repositoryTabLabel : collectionTabLabel">
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
                    <metadata-mapping-list
                            v-if="activeTab == 1"
                            :is-repository-level="isRepositoryLevel"/>
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
        },
        repositoryTabLabel() {
            let label = this.$i18n.get('metadata');
            const metadata = this.getMetadata();

            if (Array.isArray(metadata) && metadata.length)
                label += ' (' + metadata.length + ')';
                
            return label;
        },
        collectionTabLabel() {
            let label = this.$i18n.get('label_metadata_and_sections');
            const metadataSections  = this.getMetadataSections();

            if (Array.isArray(metadataSections) && metadataSections.length) {
                const totalMetadata = metadataSections.reduce((total, aMetadataSection) => (aMetadataSection.metadata_object_list ? (total + parseInt(aMetadataSection.metadata_object_list.length)) : 0), 0);
                label = this.$i18n.getWithVariables('label_metadata_%s_and_sections_%s', [totalMetadata, metadataSections.length]);
            }
            
            return label;
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
            'getMetadatumTypes',
            'getMetadata',
            'getMetadataSections'
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
            padding-right: 0;
        }

        .column {
            overflow-x: hidden;
            overflow-y: auto;
            padding: 0.75em 0;

            section.field.is-grouped-centered.section {
                position: absolute;
                width: 63%;
                background: var(--tainacan-background-color);
                padding-top: 5em;
            }

            &:not(.available-metadata-types-area){
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
            margin-left: -0.5em;
            margin-right: auto;
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
            transition: min-height 0.2s ease;

            &:empty {
                min-height: 220px;
            }

            @media screen and (max-width: 769px) {
                min-height: 45px;
                margin: 0; 
                padding-right: 0em;
            }
            &.metadata-area-receive {
                border: 1px dashed var(--tainacan-gray4);
            }

            .collapse {
                display: initial;
            }

            &.active-metadata-sections-area {
                font-size: 0.875em;
                margin-left: 1.5em;
                padding-right: 1em;
                min-height: 330px;

                .active-metadata-area {
                    margin-left: 0.5rem;
                }
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
                    white-space: nowrap;
                    font-weight: bold;
                    margin-left: 0.4em;
                    margin-right: 0.4em;
                    color: var(--tainacan-gray4);

                    &.is-danger {
                        color: var(--tainacan-danger) !important;
                    }
                    
                }
                &.active-metadata-sections-item {
                    padding-left: 0;
                    margin-top: 1rem;
                    border-bottom: 1px solid var(--tainacan-gray3);

                    .metadatum-name {
                        h3 {
                            color: var(--tainacan-gray4) !important;
                            font-size: 1.25em;
                            line-height: normal;
                            font-weight: 600;
                        }
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

                .sorting-buttons {
                    display: flex;
                    flex-direction: column;
                    position: absolute;
                    overflow: hidden;
                    border-top-right-radius: 0;
                    border-bottom-right-radius: 0;
                    border-top-left-radius: 3px;
                    border-bottom-left-radius: 3px;
                    font-size: 0.875em;
                    left: 0em; 
                    top: 0px;
                    opacity: 0;
                    visibility: hidden;
                    transition: opacity 0.2s ease, left 0.2s ease;

                    button {
                        border: none;
                        background: var(--tainacan-turquoise1);
                        &:hover {
                            color: var(--tainacan-secondary);
                        }
                    }
                }
                &:not(.not-sortable-item):hover {
                    .sorting-buttons {
                        opacity: 1.0;
                        visibility: visible;
                        left: -2em
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

                    .metadatum-name {
                        opacity: 0.7;
                    }
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
            .active-metadata-sections-item:hover:not(.not-sortable-item) {
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
                padding: 0.25em 0.9em;
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
            &.active-metadata-sections-item:hover:not(.not-sortable-item) {
                background-color: var(--tainacan-blue1);
                border-color: var(--tainacan-blue1);
                
                .grip-icon { 
                    color: var(--tainacan-blue5) !important; 
                }
            }
            .sorting-buttons button {
                background: var(--tainacan-blue1);
                &:hover {
                    color: var(--tainacan-blue5);
                }
            }
        }
    }

    .repository-level-page {
        .tainacan-form.sub-header {
            padding-left: 2.75em !important;
        }
        .active-metadata-sections-area,
        .active-metadata-area {
            margin-left: 0px !important;
        }
    }
</style>
