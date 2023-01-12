<template>
    <div 
            v-if="(isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_metadata')) || !isRepositoryLevel"
            class="column available-metadata-types-area" >

        <b-loading :active.sync="isLoadingMetadataTypes"/>

        <div class="field">
            <h3 class="label">{{ $i18n.get('label_available_metadata_types') }}</h3>
            <draggable 
                    v-model="availableMetadatumList"
                    :sort="false" 
                    :group="{ name:'metadata', pull: 'clone', put: false, revertClone: true }"
                    drag-class="sortable-drag">
                <div 
                        :id="metadatum.component"
                        @click.prevent.once="addMetadatumViaButton(metadatum)"
                        class="available-metadatum-item"
                        :class="{ 'hightlighted-metadatum' : hightlightedMetadatum == metadatum.name, 'inherited-metadatum': metadatum.inherited || isRepositoryLevel }"
                        v-for="(metadatum, index) in availableMetadatumList"
                        :key="index">
                    <span
                            v-tooltip="{
                                content: $i18n.get('instruction_click_or_drag_metadatum_create'),
                                autoHide: true,
                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                placement: 'auto-start'
                            }"   
                            class="icon grip-icon">
                        <!-- <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-drag"/> -->
                        <svg 
                                xmlns="http://www.w3.org/2000/svg" 
                                height="24px"
                                viewBox="0 0 24 24"
                                width="24px"
                                fill="currentColor">
                            <path
                                    d="M0 0h24v24H0V0z"
                                    fill="transparent"/>
                            <path d="M11 18c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm-2-8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6 4c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                    </span>
                    <span class="metadatum-name">
                        {{ metadatum.name }}
                        <span 
                                v-tooltip="{
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '', 'metadata-type-preview-tooltip'],
                                    content: getPreviewTemplateContent(metadatum),
                                    html: true,
                                    delay: {
                                        shown: 0,
                                        hide: 100,
                                    },
                                    placement: 'top',
                                }"
                                class="icon preview-help-icon has-text-secondary">
                            <i class="tainacan-icon tainacan-icon-help"/>
                        </span>
                    </span>
                    <span 
                            class="loading-spinner" 
                            v-if="hightlightedMetadatum == metadatum.name"/>
                </div>
            </draggable>

            <draggable
                    v-if="!isRepositoryLevel" 
                    v-model="availableMetadataSectionsList"
                    :sort="false" 
                    :group="{ name:'metadata-sections', pull: 'clone', put: false, revertClone: true }"
                    drag-class="sortable-drag">
                <div 
                        :id="metadataSection.id"
                        @click.prevent="addMetadataSectionViaButton()"
                        class="available-metadata-section-item"
                        v-for="(metadataSection, index) in availableMetadataSectionsList"
                        :key="index">
                    <span
                            v-tooltip="{
                                content: $i18n.get('instruction_click_or_drag_metadatum_create'),
                                autoHide: true,
                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                placement: 'auto-start'
                            }"   
                            class="icon grip-icon">
                        <!-- <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-drag"/> -->
                        <svg 
                                xmlns="http://www.w3.org/2000/svg" 
                                height="24px"
                                viewBox="0 0 24 24"
                                width="24px"
                                fill="currentColor">
                            <path
                                    d="M0 0h24v24H0V0z"
                                    fill="transparent"/>
                            <path d="M11 18c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm-2-8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6 4c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                    </span>
                    <span class="metadatum-name">
                        {{ metadataSection.label }}
                        <span 
                                v-tooltip="{
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '', 'metadata-type-preview-tooltip'],
                                    content: $i18n.get('info_create_section'),
                                    html: true,
                                    delay: {
                                        shown: 0,
                                        hide: 100,
                                    },
                                    placement: 'top',
                                }"
                                class="icon preview-help-icon has-text-secondary">
                            <i class="tainacan-icon tainacan-icon-help"/>
                        </span>
                    </span>
                </div>
            </draggable>
        </div>
    </div> 
</template>

<script>
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'MetadataTypesList',
    props: {
        isRepositoryLevel: Boolean,
        hightlightedMetadatum: String
    },
    data() {
        return {
            isLoadingMetadataTypes: true,
            availableMetadataSectionsList: [{ 
                label: this.$i18n.get('label_add_new_section'),
                name: this.$i18n.get('label_new_metadata_section'),
                id: 'metadataSectionCreator'
            }],
        }
    },
    computed: {
        availableMetadatumList: {
            get() {
                return this.getMetadatumTypes();
            },
            set(value) {
                return this.updateMetadatumTypes(value);
            }
        },
    },
    mounted() {

        this.isLoadingMetadataTypes = true;

        this.fetchMetadatumTypes()
            .then(() => {
                this.$emit('onFinishedLoadingMetadataTypes');
                this.isLoadingMetadataTypes = false;
            })
            .catch(() => {
                this.isLoadingMetadataTypes = false;
            });
    },
    methods: {
        ...mapActions('metadata', [
            'fetchMetadatumTypes'
        ]),
        ...mapGetters('metadata',[
            'getMetadatumTypes'
        ]),
        addMetadatumViaButton(metadatumType) {
            this.$eventBusMetadataList.onAddMetadatumViaButton(metadatumType);
        },
        addMetadataSectionViaButton() {
            this.$eventBusMetadataList.onAddMetadataSectionViaButton();
        },
        getPreviewTemplateContent(metadatum) {
            return `<div class="metadata-type-preview tainacan-form">
                        <span class="metadata-type-label">` + this.$i18n.get('label_metadatum_type_preview') + `</span>
                        <div class="field">
                            <span class="collapse-handle">
                                <span class="icon">
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"></i>
                                </span> 
                                <label class="label has-tooltip">`
                                    + metadatum.name +
                                `</label>
                            </span>
                            <div>` + metadatum.preview_template + `</div>
                        </div>
                    </div>`;
        },
    }
}
</script>

<style lang="scss">
    .available-metadata-types-area {
        padding: 10px 0px 10px 10px !important;
        margin: 0;
        max-width: 380px;
        min-width: 20.8333333%;
        max-height: calc(100vh - 7.75em);
        top: -28px;
        position: sticky;
        font-size: 0.875em;

        @media screen and (max-width: 769px) {
            max-width: 100%;
            padding: 10px;
            h3 {
                margin: 1em 0em 1em 0em !important;
            }
            .available-metadatum-item::before,
            .available-metadatum-item::after,
            .available-metadata-section-item::before,
            .available-metadata-section-item::after {
                display: none !important;
            }
        }

        h3 {
            margin: 0.875em 0em 1em 0em;
        }

        .available-metadatum-item,
        .available-metadata-section-item {
            padding: 0.6em;
            margin: 4px 4px 4px 1.2em;
            background-color: var(--tainacan-white);
            cursor: pointer;
            left: 0;
            height: 2.8571em;
            position: relative;
            border: 1px solid var(--tainacan-gray2);
            border-radius: 1px;
            transition: left 0.2s ease;
            
            .grip-icon { 
                color: var(--tainacan-gray3);
                position: relative;
            }
            .icon {
                position: relative;
                bottom: 1px;
            }
            .preview-help-icon {
                position: absolute;
                top: 6px;
            }
            .metadatum-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
                font-weight: bold;
                margin-left: 0.4em;
                display: inline-block;
                max-width: 180px;
                width: 60%;
            }
            &::after,
            &::before {
                content: '';
                display: block;
                position: absolute;
                right: 100%;
                width: 0;
                height: 0;
                border-style: solid;
            }
            &::after {
                top: -1px;
                border-color: transparent white transparent transparent;
                border-right-width: 16px;
                border-top-width: 1.4286em;
                border-bottom-width: 1.4286em;
                left: -19px;
            }
            &::before {
                top: -1px;
                border-color: transparent var(--tainacan-gray2) transparent transparent;
                border-right-width: 16px;
                border-top-width: 1.4286em;
                border-bottom-width: 1.4286em;
                left: -20px;
            }
        }

        .available-metadata-section-item {
            margin-top: 2em;
            color: var(--tainacan-secondary);
            border-color: var(--tainacan-secondary);
            &::before {
                border-color: transparent var(--tainacan-secondary) transparent transparent;
            }
        }

        .sortable-drag {
            opacity: 1 !important;
        }

        .sortable-chosen {
            .metadata-type-preview {
                display: none;
            }
        }

        @keyframes hightlighten {
            0%   {
                color: #222;             
                background-color: var(--tainacan-white);
                border-color: var(--tainacan-white);
            }
            25%  {
                color: var(--tainacan-white);            
                background-color: var(--tainacan-secondary); 
                border-color: var(--tainacan-secondary);
            }
            75%  {
                color: var(--tainacan-white);            
                background-color: var(--tainacan-secondary); 
                border-color: var(--tainacan-secondary);
            }
            100% {
                color: #222;             
                background-color: var(--tainacan-white);
                border-color: var(--tainacan-white);
            }
        }
        @keyframes hightlighten-icon {
            0%   { color: var(--tainacan-gray3); }
            25%  { color: var(--tainacan-white); }
            75%  { color: var(--tainacan-white); }
            100% { color: var(--tainacan-gray3); }
        }
        @keyframes hightlighten-arrow {
            0%   {
                border-color: transparent white transparent transparent;
                border-color: transparent white transparent transparent; 
            }
            25%  {
                border-color: transparent var(--tainacan-secondary) transparent transparent;
                border-color: transparent var(--tainacan-secondary) transparent transparent; 
            }
            75%  {
                border-color: transparent var(--tainacan-secondary) transparent transparent;
                border-color: transparent var(--tainacan-secondary)transparent transparent; 
            }
            100% {
                border-color: transparent white transparent transparent;
                border-color: transparent white transparent transparent;  
            }
        }
        .hightlighted-metadatum {
            background-color: var(--tainacan-white);
            position: relative;
            left: 0px;
            animation-name: hightlighten;
            animation-duration: 1.0s;
            animation-iteration-count: 2;
            
            .grip-icon{
                animation-name: hightlighten-icon;
                animation-duration: 1.0s;
                animation-iteration-count: 2; 
            }

            &::before,
            &::after {
                animation-name: hightlighten-arrow;
                animation-duration: 1.0s;
                animation-iteration-count: 2;
            }
        }
        .available-metadatum-item:hover,
        .available-metadata-section-item:hover {
            background-color: var(--tainacan-turquoise1);
            border-color: var(--tainacan-turquoise2);
            position: relative;
            left: -4px;

            &:after {
                border-color: transparent var(--tainacan-turquoise1) transparent transparent;
            }
            &:before {
                border-color: transparent var(--tainacan-turquoise2) transparent transparent;
            }
            .grip-icon { 
                color: var(--tainacan-secondary);
            }
        }
    }
    .inherited-metadatum {
        &.available-metadatum-item:hover,
        &.available-metadata-section-item:hover {
            background-color: var(--tainacan-blue1) !important;
            border-color: var(--tainacan-blue2) !important;

            .grip-icon { 
                color: var(--tainacan-blue5) !important; 
            }
            &:after {
                border-color: transparent var(--tainacan-blue1) transparent transparent !important;
            }
            &:before {
                border-color: transparent var(--tainacan-blue2) transparent transparent !important;
            }
        }
    }
</style>