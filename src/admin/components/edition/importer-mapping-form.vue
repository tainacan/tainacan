<template>
    <div 
            class="repository-level-page page-container">
         <div class="tainacan-page-title">
            <h1>{{ $i18n.get('label_metadata_mapping') }} </h1>
            <a 
                    @click="$router.go(-1)"
                    class="back-link has-text-secondary">
                {{ $i18n.get('back') }}
            </a>
            <hr>
            <nav class="breadcrumbs">
                <router-link 
                        tag="a" 
                        :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('repository') }}</router-link> > 
                <router-link 
                        tag="a" 
                        :to="$routerHelper.getAvailableImportersPath()">{{ $i18n.get('importers') }}</router-link> > 
                <router-link 
                        tag="a" 
                        :to="$routerHelper.getImporterPath(importerType, sessionId)">{{ importerType != undefined ? (importerName != undefined ? importerName :importerType) : $i18n.get('title_importer_page') }}</router-link> >
                <router-link 
                        tag="a" 
                        :to="$routerHelper.getImporterMappingPath(importerType, sessionId, collectionId)">{{ $i18n.get('label_metadata_mapping') }}</router-link> 
            </nav>

        </div>
        <form 
                class="tainacan-form" 
                label-width="120px"
                v-if="importer != undefined && importer != null">

            <!-- Metadata Mapping -->
            <b-field
                    v-if="importer.manual_mapping"
                    :addons="false" 
                    :label="$i18n.get('label_metadata_mapping')">
                <help-button 
                        :title="$i18n.get('label_metadata_mapping')" 
                        :message="$i18n.get('info_metadata_mapping_helper')"/>
   
                <div 
                        v-if="importerSourceInfo != undefined && 
                            importerSourceInfo != null">
                    <p class="mapping-header-label is-inline">{{ $i18n.get('label_from_source_collection') }}</p>
                    <p class="mapping-header-label is-pulled-right">{{ $i18n.get('label_to_target_collection') }}</p>
                    <div
                            class="source-metadatum"
                            v-for="(source_metadatum, index) of importerSourceInfo.source_metadata"
                            :key="index"><p>{{ source_metadatum }}</p>
                    
                        <b-select
                                v-if="collectionMetadata != undefined &&
                                      collectionMetadata.length > 0 &&
                                      !isFetchingCollectionMetadata"
                                :value="checkCurrentSelectedCollectionMetadatum(source_metadatum)"
                                @input="onSelectCollectionMetadata($event, source_metadatum)"
                                :placeholder="$i18n.get('label_select_metadatum')">
                            <option :value="undefined">
                                {{ $i18n.get('label_select_metadatum') }}
                            </option>
                            <option :value="'create_metadata' + index">
                                {{ $i18n.get('label_create_metadatum') }}
                            </option>
                            <option
                                    v-for="(metadatum, index) of collectionMetadata"
                                    :key="index"
                                    :value="metadatum.id"
                                    :disabled="checkIfMetadatumIsAvailable(metadatum.id)">
                                <span class="metadatum-name">
                                    {{ metadatum.name }}
                                </span>
                                <span class="label-details">  
                                    ({{ $i18n.get(metadatum.metadata_type_object.component) }}) <em>{{ (metadatum.collection_id != collectionId) ? $i18n.get('label_inherited') : '' }}</em>
                                </span>
                            </option>
                        </b-select>
                        <p v-if="collectionMetadata == undefined || collectionMetadata.length <= 0">{{ $i18n.get('info_select_collection_to_list_metadata') }}</p>
                    </div>
                    <b-modal 
                            @close="onMetadatumEditionCanceled()"
                            :active.sync="isNewMetadatumModalActive">
                         <b-loading 
                                :is-full-page="isFullPage" 
                                :active.sync="isLoadingMetadatumTypes"/>
                        <div 
                                v-if="selectedMetadatumType == undefined && !isEditingMetadatum"
                                class="tainacan-modal-content">
                            <div class="tainacan-modal-title">
                                <h2>{{ $i18n.get('instruction_select_metadatum_type') }}</h2>
                                <hr>
                            </div>
                            <section class="tainacan-form">
                                <div class="metadata-types-container">
                                    <div
                                            class="metadata-type"
                                            v-for="(metadatumType, index) of metadatumTypes"
                                            :key="index"
                                            @click="onSelectMetadatumType(metadatumType)">
                                        <h4>{{ metadatumType.name }}</h4>           
                                    </div>
                                </div>
                                <div class="field is-grouped form-submit">
                                    <div class="control">
                                        <button
                                                id="button-cancel-importer-edition"
                                                class="button is-outlined"
                                                type="button"
                                                @click="onMetadatumEditionCanceled(); isNewMetadatumModalActive = false">
                                            {{ $i18n.get('cancel') }}</button>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div 
                                v-if="isEditingMetadatum"
                                class="tainacan-modal-content">
                            <div class="tainacan-modal-title">
                                <h2>{{ $i18n.get('instruction_configure_new_metadatum') }}</h2>
                                <a 
                                        class="back-link" 
                                        @click="isEditingMetadatum = false">
                                    {{ $i18n.get('back') }}
                                </a>
                                <hr>
                            </div>
                            <metadatum-edition-form
                                    :collection-id="collectionId"
                                    :is-repository-level="false"
                                    @onEditionFinished="onMetadatumEditionFinished()"
                                    @onEditionCanceled="onMetadatumEditionCanceled()"
                                    :index="0"
                                    :original-metadatum="metadatum"
                                    :edited-metadatum="editedMetadatum"
                                    :is-on-modal="true"/>
                        </div>
                    </b-modal>
                    <a
                            v-if="collectionId != null && collectionId != undefined"
                            class="is-inline is-pulled-right add-link has-text-secondary"
                            @click="createNewMetadatum()">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-add"/>
                        </span>
                        {{ $i18n.get('label_add_more_metadata') }}</a>
                </div>
                <div 
                        v-if="importerSourceInfo == undefined || 
                            importerSourceInfo == null">
                    <p>{{ $i18n.get('info_upload_a_source_to_see_metadata') }}</p>
                </div>
            
            </b-field>

            <!-- Form submit -------------------------------- --> 
            <div class="field is-grouped form-submit">
                <div class="control">
                    <button
                            id="button-cancel-collection-creation"
                            class="button is-outlined"
                            type="button"
                            @click="cancelBack">{{ $i18n.get('cancel') }}</button>
                </div>
                <div class="control">
                    <button
                            :disabled="sessionId == undefined || importer == undefined"
                            id="button-submit-collection-creation"
                            @click.prevent="onRunImporter"
                            :class="{'is-loading': isLoadingRun }"
                            class="button is-success">{{ $i18n.get('run') }}</button>
                </div>
            </div>
        </form>

        <b-loading 
                :active.sync="isLoading" 
                :can-cancel="false"/>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import MetadatumEditionForm from './../edition/metadatum-edition-form.vue';

export default {
    name: 'ImporterEditionForm',
    data(){
        return {
            importerId: Number,
            importer: null,
            isLoading: false,
            isLoadingRun: false,
            mappedCollection: {
                'id': Number,
                'mapping': {},
                'total_items': Number
            },
            importerType: '',
            importerName: '',
            importerSourceInfo: null,
            collections: [],
            collectionMetadata: [],
            collectionId: undefined,
            isNewMetadatumModalActive: false,
            isLoadingMetadatumTypes: false,
            selectedMetadatumType: undefined,
            isEditingMetadatum: false,
            metadatum: {},
            editedMetadatum: {},
            backgroundProcess: undefined
        }
    },
    components: {
        MetadatumEditionForm
    },
    computed: {
        metadatumTypes() {
            return this.getMetadatumTypes();
        }
    },
    methods: {
        ...mapActions('importer', [
            'fetchAvailableImporters',
            'fetchImporter',
            'sendImporter',
            'updateImporter',
            'updateImporterFile',
            'updateImporterURL',
            'updateImporterOptions',
            'fetchImporterSourceInfo',
            'updateImporterCollection',
            'runImporter',
            'fetchMappingImporter'
        ]),
        ...mapActions('bgprocess', [
            'fetchProcess'
        ]),
        ...mapActions('metadata', [
            'fetchMetadata',
            'fetchMetadatumTypes',
            'sendMetadatum'
        ]),
        ...mapGetters('metadata', [
            'getMetadatumTypes'
        ]),
        ...mapGetters('bgprocess', [
            'getProcess'
        ]),
        loadImporter() {
            
            // Puts loading on Draft Importer creation
            this.isLoading = true;

            // Creates draft Importer
            this.fetchImporter(this.sessionId)
                .then(res => {

                    this.sessionId = res.id;
                    this.importer = JSON.parse(JSON.stringify(res));

                    this.isLoading = false;

                    this.fetchImporterSourceInfo(this.sessionId)
                        .then(importerSourceInfo => {    
                            this.importerSourceInfo = importerSourceInfo;
                            this.mappedCollection['total_items'] = this.importerSourceInfo.source_total_items;
                        })
                        .catch((errors) => {
                            this.$console.log(errors);
                        });
                    
                })
                .catch(error => this.$console.error(error));
        },
        loadMetadata() {
            // Generates options for metadata listing
            this.isFetchingCollectionMetadata = true;
            this.fetchMetadata({collectionId: this.collectionId, isRepositoryLevel: false, isContextEdit: false })
            .then((metadata) => {
                this.collectionMetadata = JSON.parse(JSON.stringify(metadata));
                this.isFetchingCollectionMetadata = false;

                this.fetchMappingImporter({ collection: this.collectionId, sessionId: this.sessionId })
                    .then(res => {
                        if( res ) {
                            this.mappedCollection['mapping'] = res;
                        }
                    })
            })
            .catch((error) => {
                this.$console.error(error);
                this.isFetchingCollectionMetadata = false;
            }); 
        },
        cancelBack(){
            this.$router.go(-2);
        },
        checkIfMetadatumIsAvailable(metadatumId) {
            if ( this.mappedCollection['mapping'][metadatumId] != undefined &&
                this.importerSourceInfo != undefined &&
                this.importerSourceInfo != null){
                let val = this.mappedCollection['mapping'][metadatumId];
                const { source_metadata } = this.importerSourceInfo;

                if(source_metadata && source_metadata.indexOf(val) >= 0) {
                    return true;
                }
            }

            return false;
        },
        checkCurrentSelectedCollectionMetadatum(sourceMetadatum) {
            for (let key in this.mappedCollection['mapping']) {
                if(this.mappedCollection['mapping'][key] == sourceMetadatum)
                    return key;
            }
            return undefined;
        },
        onRunImporter() {
            this.isLoadingRun = true;
            this.updateImporterCollection({ sessionId: this.sessionId, collection: this.mappedCollection })
                .then(updatedImporter => {    
                    this.importer = updatedImporter;
                    this.finishRunImporter();
                })
                .catch((errors) => {
                    this.isLoadingRun = false;
                    this.$console.log(errors);
                });

        },
        finishRunImporter() {
            this.runImporter(this.sessionId)
                .then(backgroundProcess => {
                    this.backgroundProcess = backgroundProcess;
                    this.isLoadingRun = false;
                    this.$router.push(this.$routerHelper.getProcessesPage());
                })
                .catch((errors) => {
                    this.isLoadingRun = false;
                    this.$console.log(errors);
                });
        },
        onSelectCollectionMetadata(selectedMetadatum, sourceMetadatum) {
     
            let removedKey = '';
            for (let key in this.mappedCollection['mapping']) {
                if(this.mappedCollection['mapping'][key] == sourceMetadatum)
                    removedKey = key;
            }

            if (removedKey != '')
                delete this.mappedCollection['mapping'][removedKey];
                
            this.mappedCollection['mapping'][selectedMetadatum] = sourceMetadatum;

            // Necessary for causing reactivity to re-check if metadata remains available
            this.collectionMetadata.push("");
            this.collectionMetadata.pop();
        },
        onSelectMetadatumType(newMetadatum) {
            this.sendMetadatum({
                collectionId: this.collectionId, 
                name: newMetadatum.name, metadatumType: 
                newMetadatum.className, 
                status: 'auto-draft', 
                isRepositoryLevel: false, 
                newIndex: 0
            })
            .then((metadatum) => {
                this.metadatum = metadatum;
                this.editedMetadatum = JSON.parse(JSON.stringify(metadatum));
                this.editedMetadatum.saved = false;
                this.editedMetadatum.status = 'publish';
                this.isEditingMetadatum = true;
            })
            .catch((error) => {
                this.$console.error(error);
            });
        },
        createNewMetadatum() {
            this.fetchMetadatumTypes()
                .then(() => {
                    this.isLoadingMetadatumTypes = false;
                    this.isNewMetadatumModalActive = true;
                })
                .catch(() => {
                    this.isLoadingMetadatumTypes = false;
                });
        },
        onMetadatumEditionFinished() {
            // Reset variables for metadatum creation
            delete this.metadatum;
            delete this.editedMetadatum;
            this.isEditingMetadatum = false;
            this.isNewMetadatumModalActive = false;
            this.selectedMetadatumType = undefined;
            
             // Generates options for metadata listing
            this.isFetchingCollectionMetadata = true;
            this.fetchMetadata({collectionId: this.collectionId, isRepositoryLevel: false, isContextEdit: false })
            .then((metadata) => {
                this.collectionMetadata = JSON.parse(JSON.stringify(metadata));
                this.isFetchingCollectionMetadata = false;
            })
            .catch((error) => {
                this.$console.error(error);
                this.isFetchingCollectionMetadata = false;
            }); 
        },
        onMetadatumEditionCanceled() {
            // Reset variables for metadatum creation
            if (this.metadatum)
                delete this.metadatum;
            if (this.editedMetadatum)
                delete this.editedMetadatum;
            this.isEditingMetadatum = false;
            this.isNewMetadatumModalActive = false;
            this.selectedMetadatumType = undefined;
        }
    },
    created() {
        this.importerType = this.$route.params.importerType;
        this.sessionId = this.$route.params.sessionId;
        this.collectionId = this.$route.params.collectionId;
        this.mappedCollection['id'] = this.collectionId;

        // Set importer's name
        this.fetchAvailableImporters().then((importerTypes) => {
           if (importerTypes[this.importerType]) 
            this.importerName = importerTypes[this.importerType].name;
        });

        this.loadImporter();    
        this.loadMetadata();
    }

}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .tainacan-page-title {
        margin-bottom: 40px;

        h1, h2 {
            font-size: 20px;
            font-weight: 500;
            color: $blue5;
            display: inline-block;
        }
        a.back-link{
            font-weight: 500;
            float: right;
            margin-top: 5px;
        }
        hr{
            margin: 3px 0px 4px 0px; 
            height: 1px;
            background-color: $secondary;
        }
        .breadcrumbs {
            font-size: 12px;
        }
        .level-left {
            .level-item {
                display: inline-block;
                margin-left: 268px;
            }  
        }
        @media screen and (max-width: 769px) {
            .level-left {
                margin-left: 0px !important;
                .level-item {
                    margin-left: 30px;
                }
            }
            .level-right {
                display: none;
            }

            top: 206px;
            margin-bottom: 0px !important;
        }
    }

    .field {
        position: relative;
    }

    .form-submit {
        margin-top: 24px;
    }

    .section-label {
        font-size: 16px !important;
        font-weight: 500 !important;
        color: $blue5 !important;
        line-height: 1.2em;
    }

    .source-metadatum {
        padding: 2px 0;
        border-bottom: 1px solid $gray2;
        width: 100%;
        margin-bottom: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .is-inline .control{
        display: inline;
    }
    .drop-inner{
        padding: 1rem 3rem;
    }

    .mapping-header-label {
        color: $gray4;
        margin: 12px 0 6px 0;
    }

    .modal .animation-content {
        width: 100%;
        z-index: 99999;

        #metadatumEditForm {
            background-color: white;
        }
    }

    .metadata-types-container {

        .metadata-type {
            border-bottom: 1px solid $gray2;
            padding: 15px 8.3333333%;
            cursor: pointer;
        
            &:first-child {
                margin-top: 15px;
            }
            &:last-child {
                border-bottom: none;
            }
            &:hover {
                background-color: $gray1;
            }
        }
    }



</style>


