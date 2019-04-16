<template>
    <div 
            class="repository-level-page page-container">
        <tainacan-title 
                :bread-crumb-items="[
                    { path: $routerHelper.getAvailableImportersPath(), label: $i18n.get('importers') },
                    { path: '', label: importerType != undefined ? (importerName != undefined ? importerName :importerType) : $i18n.get('title_importer_page') }
                ]"/>
        <form   
                @click="formErrorMessage = ''"
                class="tainacan-form" 
                label-width="120px"
                v-if="importer != undefined && importer != null">
            <div class="columns is-gapless">
                <div 
                        v-if="importer.options_form != undefined && importer.options_form != null && importer.options_form != ''"
                        class="column">
                    <!-- Importer custom options -->
                    <form id="importerOptionsForm">
                        <div v-html="importer.options_form"/>
                    </form>
                </div>
                <div 
                        v-if="importer.manual_collection || importer.accepts.file || importer.accepts.url"
                        class="column">
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
                                    expanded
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
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-add"/>
                                </span>
                                {{ $i18n.get('new_blank_collection') }}
                            </router-link>
                        </div>
                    </b-field>
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
                                v-if="importer.tmp_file == undefined && (importerFile == undefined || importerFile == null || importerFile == '')" 
                                v-model="importerFile"
                                drag-drop
                                class="source-file-upload">
                            <section class="drop-inner">
                                <div class="content has-text-centered">
                                    <p>
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-upload"/>
                                        </span>
                                    </p>
                                    <p>{{ $i18n.get('instruction_drop_file_or_click_to_upload') }}</p>
                                </div>
                            </section>
                        </b-upload>
                        <div 
                                class="control selected-source-file"
                                v-if="importerFile != undefined">
                            <span>{{ (importerFile.length != undefined && importerFile.length > 0 ) ? importerFile[0].name : importerFile.name }}</span>
                            <a 
                                    target="_blank"
                                    @click.prevent="importerFile = undefined">
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('remove_value'),
                                            autoHide: true,
                                            placement: 'bottom',
                                            classes: ['tooltip', 'repository-tooltip'],
                                        }"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-close"/>
                                </span>
                            </a>
                        </div>
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
                                v-model="url"/>  
                    </b-field>
                            
                    
                </div>
            </div>
            <!-- Form submit -------------------------------- --> 
            <div class="columns is-gapless field is-grouped form-submit">
                <div class="control">
                    <button
                            id="button-cancel-collection-creation"
                            class="button is-outlined"
                            type="button"
                            @click="cancelBack">{{ $i18n.get('cancel') }}</button>
                </div>
                <span class="help is-danger">{{ formErrorMessage }}</span>
                <div 
                        v-if="!importer.manual_mapping"
                        class="control">
                    <button
                            :disabled="
                                    (formErrorMessage != undefined && formErrorMessage != '') ||
                                    sessionId == undefined || 
                                    importer == undefined || 
                                    (importer.manual_collection && collectionId == undefined) ||
                                    (importer.accepts.file && !importer.accepts.url && !importerFile) || 
                                    (!importer.accepts.file && importer.accepts.url && !url) ||
                                    (importer.accepts.file && importer.accepts.url && !importerFile && !url)"
                            id="button-submit-collection-creation"
                            @click.prevent="onFinishImporter()"
                            :class="{'is-loading': isLoadingRun }"
                            class="button is-success">{{ $i18n.get('run') }}</button>
                </div>
                <div 
                        v-if="importer.manual_mapping"
                        class="control">
                    <button
                            :disabled="
                                    (formErrorMessage != undefined && formErrorMessage != '') ||
                                    sessionId == undefined || 
                                    importer == undefined || 
                                    (importer.manual_collection && collectionId == undefined) ||
                                    (importer.accepts.file && !importer.accepts.url && !importerFile) || 
                                    (!importer.accepts.file && importer.accepts.url && !url) ||
                                    (importer.accepts.file && importer.accepts.url && !importerFile && !url)"
                            id="button-submit-collection-creation"
                            @click.prevent="onFinishImporter()"
                            :class="{'is-loading': isLoadingUpload }"
                            class="button is-success">{{ $i18n.get('next') }}</button>
                </div>
            </div>
        </form>
        <b-loading 
                :active.sync="isLoading" 
                :can-cancel="false"/>
    </div>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'ImporterEditionForm',
    data(){
        return {
            importerId: Number,
            importer: null,
            isLoading: false,
            isLoadingRun: false,
            isLoadingUpload: false,
            isFetchingCollections: false,
            form: {},
            formErrorMessage: '',
            mappedCollection: {
                'id': Number,
                'mapping': {},
                'total_items': Number
            },
            importerTypes: [],
            importerType: '',
            importerName: '',
            importerFile: null,
            importerSourceInfo: null,
            collections: [],
            collectionId: undefined,
            url: '',
            backgroundProcess: undefined
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
            'runImporter'
        ]),
        ...mapActions('collection', [
            'fetchCollectionsForParent'
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
        loadImporter() {
            // Puts loading on Draft Importer creation
            this.isLoading = true;

            // Creates draft Importer
            this.fetchImporter(this.sessionId)
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
        onUploadFile() {
            return new Promise((resolve, reject) => {
               this.updateImporterFile({ sessionId: this.sessionId, file: (this.importerFile.length != undefined && this.importerFile.length > 0) ? this.importerFile[0] : this.importerFile})
                .then(updatedImporter => {    
                    this.importer = updatedImporter;
                    resolve();
                })
                .catch((errors) => {
                    this.formErrorMessage = errors.error_message;
                    this.$console.error(errors);
                    reject(errors);
                });
            });
        },
        onInputURL() {
            return new Promise((resolve, reject) => {
                this.updateImporterURL({ sessionId: this.sessionId, url: this.url })
                    .then(updatedImporter => {    
                        this.importer = updatedImporter;
                        resolve();
                    })
                    .catch((errors) => {
                        this.formErrorMessage = errors.error_message;
                        this.$console.error(errors);
                        reject(errors);
                    });
            });
        },
        onUpdateOptions() {
            return new Promise((resolve, reject) => {

                if (this.importer.options_form != undefined && this.importer.options != null && this.importer.options_form != '') {
                    let formElement = document.getElementById('importerOptionsForm');
                    let formData = new FormData(formElement);
                    let formObj = {};
                    
                    for (let [key, value] of formData.entries())
                        formObj[key] = value;

                    this.updateImporterOptions({ sessionId: this.sessionId, options: formObj })
                        .then(updatedImporter => {    
                            this.importer = updatedImporter;
                            resolve();
                        })
                        .catch((errors) => {
                            this.formErrorMessage = errors.error_message;
                            this.$console.log(errors);
                            reject(errors);
                        });

                } else {
                    resolve();
                }
            });
        },
        uploadSource() {
            this.isLoadingUpload = true;
            return new Promise((resolve, reject) => {
                if (this.importer.accepts.file && !this.importer.accepts.url) {
                    this.onUploadFile()
                        .then(() => { this.isLoadingUpload = false; resolve(); })
                        .catch((errors) => { this.isLoadingUpload = false; this.$console.error(errors) }); 
                } else if (!this.importer.accepts.file && this.importer.accepts.url) {
                    this.onInputURL()
                        .then(() => { this.isLoadingUpload = false; resolve() })
                        .catch((errors) => { this.isLoadingUpload = false; this.$console.log(errors); }); 
                } else if (this.importer.accepts.file && this.importer.accepts.url) {
                    if (this.importerFile) {
                        this.onUploadFile()
                            .then(() => { this.isLoadingUpload = false; resolve(); })
                            .catch((errors) => { this.isLoadingUpload = false; this.$console.log(errors) });
                    } else if (this.url) {
                        this.onInputURL()
                            .then(() => { this.isLoadingUpload = false; resolve() })
                            .catch((errors) => { this.isLoadingUpload = false; this.$console.log(errors); }); 
                    } else {
                        this.isLoadingUpload = false;
                        reject('No source file given');
                    }
                } else {
                    this.isLoadingUpload = false;
                    resolve();
                }
            });
        },
        onFinishImporter() {
            this.isLoadingRun = true;
            this.onUpdateOptions().then(() => {
                this.uploadSource()
                    .then(() => { 
                        if (this.importer.manual_mapping) {   
                            this.goToMappingPage();
                            this.isLoadingRun = false;
                        } else {
                            this.onRunImporter();
                        }
                    }).catch((errors) => {
                        this.isLoadingRun = false;
                        this.$console.log(errors);
                    });   
            })
            .catch((errors) => {
                this.isLoadingRun = false;
                this.$console.log(errors);
            });
          
        },
        onRunImporter() {
            this.runImporter(this.sessionId)
                .then(backgroundProcess => {  
                    this.backgroundProcess = backgroundProcess;
                    this.isLoadingRun = false;
                    this.$router.push(this.$routerHelper.getProcessesPage(backgroundProcess.bg_process_id));
                })
                .catch((errors) => {
                    this.isLoadingRun = false;
                    this.$console.log(errors);
                });
        },
        goToMappingPage() {
            this.$router.push(this.$routerHelper.getImporterMappingPath(this.importerType, this.sessionId, this.collectionId));
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
        }
    },
    created() {
        this.importerType = this.$route.params.importerSlug;
        this.collectionId = this.$route.query.targetCollection;
        this.sessionId = this.$route.params.sessionId;

        if (this.collectionId != undefined) {
            this.onSelectCollection(this.collectionId);
        }

        // Set importer's name
        this.fetchAvailableImporters().then((importerTypes) => {
           if (importerTypes[this.importerType]) 
            this.importerName = importerTypes[this.importerType].name;
        });

        if (this.sessionId != undefined)
            this.loadImporter();
        else
            this.createImporter();    
    }

}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .columns.is-gapless {
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;

        .column:not(:first-child) {
            margin-left: $page-side-padding;
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

    .source-file-upload {
        width: 100%;
        display: grid;
    }

    .selected-source-file {
        border: 1px solid $gray2;
        padding: 2px 10px;
        font-size: .75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }


</style>


