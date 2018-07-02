<template>
    <div 
            class="primary-page page-container">
        <tainacan-title />
        <form 
                class="tainacan-form" 
                label-width="120px"
                v-if="importer != undefined && importer != null">


            <!-- Target collection selection -------------------------------- --> 
            <b-field
                    v-if="importer.manual_collection"
                    :addons="false" 
                    :label="$i18n.get('label_target_collection')">
                <help-button 
                        :title="$i18n.get('label_target_collection')" 
                        :message="$i18n.get('info_target_collection_helper')"/>
                <br>
                <div class="is-inline">
                    <b-select
                            id="tainacan-select-target-collection"
                            :value="collectionId"
                            @input="onSelectCollection($event)"
                            :loading="isFetchingCollections"
                            :placeholder="$i18n.get('instruction_select_a_target_collection')">
                        <option
                                v-for="collection of collections"
                                :key="collection.id"
                                :value="collection.id">{{ collection.name }}
                        </option>
                    </b-select>
                    <router-link
                            tag="a" 
                            class="is-inline add-link"     
                            :to="{ path: $routerHelper.getNewCollectionPath(), query: { fromImporter: true }}">
                        <b-icon
                                icon="plus-circle"
                                size="is-small"
                                type="is-secondary"/>
                            {{ $i18n.get('new_blank_collection') }}</router-link>
                </div>
            </b-field>

            <!-- Importer custom options -->
            <form 
                    id="importerOptionsForm"
                    v-if="importer.options_form != undefined && importer.options_form != null && importer.options_form != ''">
                <div v-html="importer.options_form"/>
            </form>

            <!-- File Source input -->
            <b-field
                    v-if="importer.accepts.file"
                    :addons="false">
                <label class="label">{{ $i18n.get('label_source_file') }}</label>
                <help-button 
                        :title="$i18n.get('label_source_file')" 
                        :message="$i18n.get('info_source_file_upload')"/>
                <br>
                <b-upload
                        v-if="importer.tmp_file == undefined" 
                        :value="importerFile"
                        @input="onUploadFile($event)"
                        drag-drop>
                    <section class="drop-inner">
                        <div class="content has-text-centered">
                            <p>
                                <b-icon
                                        icon="upload"
                                        size="is-large"/>
                            </p>
                            <p>{{ $i18n.get('instruction_drop_file_or_click_to_upload') }}</p>
                        </div>
                    </section>
                </b-upload>
                <div v-if="importer.tmp_file != undefined">{{ importer.tmp_file }}</div>
            </b-field>

            <!-- URL source input -------------------------------- --> 
            <b-field 
                    v-if="importer.accepts.url"
                    :addons="false"
                    :label="$i18n.get('label_url_source_link')">
                <help-button 
                        :title="$i18n.get('label_url_source_link')" 
                        :message="$i18n.get('info_url_source_link_helper')"/>
                <b-input
                        id="tainacan-url-link-source"
                        :value="url"
                        @input="onInputURL($event)"/>  
            </b-field>
                    
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
                            <b-select
                                    :value="selectedMetadatumType"
                                    @input="onSelectMetadatumType($event)"
                                    :placeholder="$i18n.get('label_select_metadatum_type')">
                                <option
                                        v-for="(metadatumType, index) of metadatumTypes"
                                        :key="index"
                                        :value="metadatumType">
                                    {{ metadatumType.name }}
                                </option>
                            </b-select>
                        </div>
                        <div 
                                v-if="isEditingMetadatum"
                                class="tainacan-modal-content">
                            <div class="tainacan-modal-title">
                                <h2>{{ $i18n.get('instruction_configure_new_metadatum') }}</h2>
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
                            class="is-inline is-pulled-right add-link"
                            @click="createNewMetadatum()">
                        <b-icon
                                icon="plus-circle"
                                size="is-small"
                                type="is-secondary"/>
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
                <div 
                        v-if="!hasRunImporter"
                        class="control">
                    <button
                            :disabled="sessionId == undefined || importer == undefined"
                            id="button-submit-collection-creation"
                            @click.prevent="onRunImporter"
                            class="button is-success">{{ $i18n.get('run') }}</button>
                </div>
                <div 
                        v-if="hasRunImporter"
                        class="control">
                    <button
                            :disabled="sessionId == undefined || importer == undefined"
                            id="button-submit-collection-creation"
                            @click.prevent="onCheckBackgroundProcessStatus"
                            class="button is-success">Check Status</button>
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
            isFetchingCollections: false,
            form: {
                
            },
            mappedCollection: {
                'id': Number,
                'mapping': {},
                'total_items': Number
            },
            importerTypes: [],
            importerType: '',
            importerFile: {},
            importerSourceInfo: null,
            collections: [],
            collectionMetadata: [],
            collectionId: undefined,
            url: '',
            isNewMetadatumModalActive: false,
            isLoadingMetadatumTypes: false,
            selectedMetadatumType: undefined,
            isEditingMetadatum: false,
            metadatum: {},
            editedMetadatum: {},
            hasRunImporter: false,
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
            'fetchImporterTypes',
            'fetchImporter',
            'sendImporter',
            'updateImporter',
            'updateImporterFile',
            'updateImporterURL',
            'updateImporterOptions',
            'fetchImporterSourceInfo',
            'updateImporterCollection',
            'runImporter'
        ]),
        ...mapActions('collection', [
            'fetchCollectionsForParent'
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
        createImporter() {
            // Puts loading on Draft Importer creation
            this.isLoading = true;

            // Creates draft Importer
            this.sendImporter(this.importerType)
            .then(res => {

                this.sessionId = res.id;
                this.importer = JSON.parse(JSON.stringify(res));

                this.form = this.importer.options;
                this.isLoading = false;

                if (this.importer.manual_collection)
                    this.loadCollections();
                
            })
            .catch(error => this.$console.error(error));
        },
        cancelBack(){
            this.$router.go(-1);
        },
        onUploadFile(file) {
            this.updateImporterFile({ sessionId: this.sessionId, file: file[0] })
            .then(updatedImporter => {    
                this.importer = updatedImporter;
                this.importerFile = this.importer.tmp_file;

                this.fetchImporterSourceInfo(this.sessionId)
                .then(importerSourceInfo => {    
                    this.importerSourceInfo = importerSourceInfo;
                    this.mappedCollection['total_items'] = this.importerSourceInfo.source_total_items;
                })
                .catch((errors) => {
                    this.$console.log(errors);
                });
            })
            .catch((errors) => {
                this.$console.log(errors);
            });
        },
        checkIfMetadatumIsAvailable(metadatumId) {
            return this.mappedCollection['mapping'][metadatumId] != undefined;
        },
        checkCurrentSelectedCollectionMetadatum(sourceMetadatum) {
            for (let key in this.mappedCollection['mapping']) {
                if(this.mappedCollection['mapping'][key] == sourceMetadatum)
                    return key;
            }
            return undefined;
        },
        onInputURL(event) {
            this.url = event;

            this.updateImporterURL({ sessionId: this.sessionId, url: this.url })
                .then(updatedImporter => {    
                    this.importer = updatedImporter;
                })
                .catch((errors) => {
                    this.$console.log(errors);
                });
        },
        onRunImporter() {
            if (this.importer.manual_collection) {
                this.updateImporterCollection({ sessionId: this.sessionId, collection: this.mappedCollection })
                .then(updatedImporter => {    
                    this.importer = updatedImporter;

                    if (this.importer.options_form != undefined && this.importer.options != null && this.importer.options_form != '') {
                        
                        let formElement = document.getElementById('importerOptionsForm');
                        let formData = new FormData(formElement);
                        let formObj = {};

                        for (let [key, value] of formData.entries())
                            formObj[key] = value;

                        this.updateImporterOptions({ sessionId: this.sessionId, options: formObj })
                        .then(updatedImporter => {    
                            this.importer = updatedImporter;
                            this.finishRunImporter();
                         })
                        .catch((errors) => {
                            this.$console.log(errors);
                        });
                    
                    } else
                        this.finishRunImporter();
                })
                .catch((errors) => {
                    this.$console.log(errors);
                });
            } else {
                if (this.importer.options_form != undefined && this.importer.options != null && this.importer.options_form != '') {
                        
                    let formElement = document.getElementById('importerOptionsForm');
                    let formData = new FormData(formElement);
                    let formObj = {};

                    for (let [key, value] of formData.entries())
                        formObj[key] = value;

                    this.updateImporterOptions({ sessionId: this.sessionId, optionsForm: formObj })
                    .then(updatedImporter => {    
                        this.importer = updatedImporter;
                        this.finishRunImporter();
                        })
                    .catch((errors) => {
                        this.$console.log(errors);
                    });
                
                } else
                    this.finishRunImporter();
            }
        },
        finishRunImporter() {
            this.runImporter(this.sessionId)
                    .then(backgroundProcess => {
                        this.hasRunImporter = true;    
                        this.backgroundProcess = backgroundProcess;
                    })
                    .catch((errors) => {
                        this.$console.log(errors);
                    });
        },
        onCheckBackgroundProcessStatus() {
            this.fetchProcess(this.backgroundProcess.bg_process_id)
            .then((backgroundProcess) => {
                this.$console.log(JSON.stringify(backgroundProcess));
            })
            .catch((error) => {
                this.$console.error(error);
            }); 
        },
        loadCollections() {
            // Generates options for target collection
            this.isFetchingCollections = true;
            this.fetchCollectionsForParent()
            .then((collections) => {
                this.collections = collections;
                this.isFetchingCollections = false;
            })
            .catch((error) => {
                this.$console.error(error);
                this.isFetchingCollections = false;
            }); 
        },
        onSelectCollection(collectionId) {
            this.collectionId = collectionId;
            this.mappedCollection['id'] = collectionId;

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
        onSelectCollectionMetadata(selectedMetadatum, sourceMetadatum) {

            if (selectedMetadatum)
                this.mappedCollection['mapping'][selectedMetadatum] = sourceMetadatum;
            else {
                let removedKey = '';
                for (let key in this.mappedCollection['mapping']) {
                    if(this.mappedCollection['mapping'][key] == sourceMetadatum)
                        removedKey = key;
                }

                if (removedKey != '')
                    delete this.mappedCollection['mapping'][removedKey];
            }
            
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
        this.importerType = this.$route.params.importerSlug;
        this.createImporter();    
    }

}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";


    .field {
        position: relative;
    }

    .section-label {
        font-size: 16px !important;
        font-weight: 500 !important;
        color: $tertiary !important;
        line-height: 1.2em;
    }

    .source-metadatum {
        padding: 2px 0;
        border-bottom: 1px solid $tainacan-input-background;
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
        color: $gray-light;
        margin: 12px 0 6px 0;
    }

    .modal .animation-content {
        width: 100%;
        z-index: 99999;

        #metadatumEditForm {
            background-color: white;
        }
    }


</style>


