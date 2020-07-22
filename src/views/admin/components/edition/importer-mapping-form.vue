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

        <b-loading 
                :active.sync="isLoading" 
                :can-cancel="false"/>

        <form 
                class="tainacan-form" 
                label-width="120px"
                v-if="importer != undefined && importer != null">
            <p>{{ $i18n.get('info_metadata_mapping_helper') }}</p>
            <br>


            <b-loading 
                    :is-full-page="false"
                    :active.sync="isLoadingSourceInfo" 
                    :can-cancel="false"/>
            
            <!-- Metadata Mapping -->
            <div 
                    v-if="importerSourceInfo != undefined && 
                            importerSourceInfo != null &&
                            !isLoading">
                <div 
                        class="mapping-header"
                        v-if="importerSourceInfo.source_metadata.length > 0 || (importerSourceInfo.source_special_fields && importerSourceInfo.source_special_fields.length > 0)">
                    <p>{{ $i18n.get('label_from_source_collection') }}</p>
                    <hr>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-pointer" />
                    </span>
                    <hr>
                    <p>{{ $i18n.get('label_to_target_collection') }}</p>
                </div>
                <div
                        class="source-metadatum"
                        v-for="(sourceMetadatum, index) of importerSourceInfo.source_metadata"
                        :key="index">
                    <template v-if="typeof sourceMetadatum == 'string'">
                        <p>{{ sourceMetadatum }}</p>
                        <b-select
                                v-if="collectionMetadata != undefined &&
                                    collectionMetadata.length > 0 &&
                                    !isFetchingCollectionMetadata"
                                :value="checkCurrentSelectedCollectionMetadatum(sourceMetadatum)"
                                @input="onSelectCollectionMetadata($event, sourceMetadatum)"
                                :placeholder="$i18n.get('label_select_metadatum')">
                            <option :value="null">
                                {{ $i18n.get('label_select_metadatum') }}
                            </option>
                            <option
                                    v-if="collection && collection.current_user_can_edit_metadata"
                                    :value="'create_metadata' + index">
                                {{ $i18n.get('label_create_metadatum') }}
                            </option>
                            <option
                                    v-for="(collectionMetadatum, metadatumIndex) of collectionMetadata"
                                    :key="metadatumIndex"
                                    :value="collectionMetadatum.id"
                                    v-if="!checkIfMetadatumIsChild(collectionMetadatum)"
                                    :disabled="checkIfMetadatumIsAvailable(collectionMetadatum.id)">
                                <span class="metadatum-name">
                                    {{ collectionMetadatum.name }}
                                </span>
                                <span class="label-details">  
                                    ({{ collectionMetadatum.metadata_type_object.name }}) <em>{{ (collectionMetadatum.collection_id != collectionId) ? $i18n.get('label_inherited') : '' }}</em>
                                </span>
                            </option>
                        </b-select>
                    </template>
                    <template v-else-if="typeof sourceMetadatum == 'object' && Object.entries(sourceMetadatum)[0]">
                        <p>{{ Object.entries(sourceMetadatum)[0][0] }}</p>
                        <b-select
                                v-if="collectionMetadata != undefined &&
                                    collectionMetadata.length > 0 &&
                                    !isFetchingCollectionMetadata"
                                :value="checkCurrentSelectedCollectionMetadatum(Object.entries(sourceMetadatum)[0][0], true)"
                                @input="onSelectCollectionMetadata($event, Object.entries(sourceMetadatum)[0][0], true, Object.entries(sourceMetadatum)[0][1])"
                                :placeholder="$i18n.get('label_select_metadatum')">
                            <option :value="null">
                                {{ $i18n.get('label_select_metadatum') }}
                            </option>
                            <option
                                    v-if="collection && collection.current_user_can_edit_metadata"
                                    :value="'create_metadata' + index">
                                {{ $i18n.get('label_create_metadatum') }}
                            </option>
                            <option
                                    v-for="(collectionMetadatum, metadatumIndex) of collectionMetadata"
                                    :key="metadatumIndex"
                                    :value="collectionMetadatum.id"
                                    v-if="!checkIfMetadatumIsChild(collectionMetadatum)"
                                    :disabled="!checkIfMetadatumIsCompound(collectionMetadatum) || checkIfMetadatumIsAvailable(collectionMetadatum.id)">
                                <span class="metadatum-name">
                                    {{ collectionMetadatum.name }}
                                </span>
                                <span class="label-details">  
                                    ({{ collectionMetadatum.metadata_type_object.name }}) <em>{{ (collectionMetadatum.collection_id != collectionId) ? $i18n.get('label_inherited') : '' }}</em>
                                </span>
                            </option>
                        </b-select>
                        <div 
                                :class="{ 'disabled-child-source-metadatum': [undefined, null, false, 'create_metadata' + index].includes(checkCurrentSelectedCollectionMetadatum(Object.entries(sourceMetadatum)[0][0], true)) }"
                                class="child-source-metadatum">
                            <div
                                    class="source-metadatum"
                                    v-for="(childSourceMetadatum, childIndex) of Object.entries(sourceMetadatum)[0][1]"
                                    :key="childIndex">
                                <p>{{ childSourceMetadatum }}</p>
                                <b-select
                                        v-if="collectionMetadata != undefined &&
                                            collectionMetadata.length > 0 &&
                                            !isFetchingCollectionMetadata"
                                        :disabled="[undefined, null, false, 'create_metadata' + index].includes(checkCurrentSelectedCollectionMetadatum(Object.entries(sourceMetadatum)[0][0], true))"
                                        :value="checkCurrentSelectedCollectionChildMetadatum(childSourceMetadatum, checkCurrentSelectedCollectionMetadatum(Object.entries(sourceMetadatum)[0][0], true))"
                                        @input="onSelectCollectionChildMetadata($event, childSourceMetadatum, checkCurrentSelectedCollectionMetadatum(Object.entries(sourceMetadatum)[0][0], true), Object.entries(sourceMetadatum)[0][0])"
                                        :placeholder="$i18n.get('label_select_metadatum')">
                                    <option :value="null">
                                        {{ $i18n.get('label_select_metadatum') }}
                                    </option>
                                    <option
                                            v-for="(collectionMetadatum, metadatumIndex) of getChildOfSelectedCompoundMetadata(sourceMetadatum)"
                                            :key="metadatumIndex"
                                            :value="collectionMetadatum.id"
                                            :disabled="checkIfChildMetadatumIsAvailable(collectionMetadatum.id, checkCurrentSelectedCollectionMetadatum(Object.entries(sourceMetadatum)[0][0], true), Object.entries(sourceMetadatum)[0][0])">
                                        <span class="metadatum-name">
                                            {{ collectionMetadatum.name }}
                                        </span>
                                        <span class="label-details">  
                                            ({{ collectionMetadatum.metadata_type_object.name }}) <em>{{ (collectionMetadatum.collection_id != collectionId) ? $i18n.get('label_inherited') : '' }}</em>
                                        </span>
                                    </option>
                                </b-select>
                            </div>
                        </div>
                    </template>
                    <p v-if="collectionMetadata == undefined || collectionMetadata.length <= 0">{{ $i18n.get('info_select_collection_to_list_metadata') }}</p>
                </div>
                <div
                        v-if="importerSourceInfo.source_special_fields && importerSourceInfo.source_special_fields.length > 0"
                        class="source-metadatum"
                        :key="specialFieldIndex"
                        v-for="(specialField, specialFieldIndex) of importerSourceInfo.source_special_fields">
                    <p style="font-style: italic">{{ specialField }}</p>
                    <p>{{ $i18n.get('info_special_fields_mapped_default') }}</p>
                </div>
                <p v-if="importerSourceInfo.source_metadata.length <= 0">{{ $i18n.get('info_no_metadata_source_file') }}<br></p>
                <p v-if="(!importerSourceInfo.source_special_fields || importerSourceInfo.source_special_fields.length <= 0)">{{ $i18n.get('info_no_special_fields_available') }}<br></p>
                <b-modal 
                        @close="onMetadatumEditionCanceled()"
                        :active.sync="isNewMetadatumModalActive"
                        trap-focus
                        aria-modal
                        aria-role="dialog">
                    <div 
                            autofocus="true"
                            tabindex="-1"
                            role="dialog"
                            aria-modal>
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
                    </div>
                </b-modal>
                <a
                        v-if="collectionId != null && collectionId != undefined && importerSourceInfo.source_metadata.length > 0 && collection && collection.current_user_can_edit_metadata"
                        style="font-size: 0.875em;"
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

            <!-- Form submit -------------------------------- --> 
            <div class="field is-grouped form-submit">
                <div class="control">
                    <button
                            id="button-cancel-importer-mapping"
                            class="button is-outlined"
                            type="button"
                            @click="cancelBack">{{ $i18n.get('cancel') }}</button>
                </div>
                <p class="help is-danger">{{ formErrorMessage }}</p>
                <div class="control">
                    <button
                            :disabled="sessionId == undefined || importer == undefined"
                            id="button-submit-importer-mapping"
                            @click.prevent="onRunImporter"
                            :class="{'is-loading': isLoadingRun }"
                            class="button is-success">{{ $i18n.get('run') }}</button>
                </div>
            </div>
        </form>

        <!-- Prompt to show title -->
        <b-modal 
                v-if="importerSourceInfo"
                :active.sync="showTitlePromptModal"
                :can-cancel="false"
                :width="820"
                scroll="keep"
                trap-focus                
                autofocus
                role="dialog"
                tabindex="-1"
                aria-modal>
            <form class="tainacan-modal-content tainacan-form">
                <div class="tainacan-modal-title">
                    <h2>{{ $i18n.get('instruction_select_title_mapping') }}</h2>
                    <hr>
                </div>
                <div class="columns">
                    <div class="column">
                        <p style="margin: 12px 0px 24px 0px">{{ $i18n.get('info_title_mapping') }}</p>
                        <b-field>
                            <b-select
                                    expanded
                                    v-model="selectedTitle"
                                    :placeholder="$i18n.get('label_select_metadatum')">
                                <option
                                        v-for="(sourceMetadatum, index) of importerSourceInfo.source_metadata"
                                        :key="index"
                                        :value="index">
                                    <span class="metadatum-name">
                                        {{ sourceMetadatum }}
                                    </span>
                                </option>
                            </b-select>
                        </b-field>
                    </div>
                    <div 
                            style="text-align: right"
                            class="column">
                        <div 
                                v-for="item in 4"
                                :key="item"
                                class="item-demo">
                            <p>{{ selectedTitle == '' || selectedTitle == undefined ? $i18n.get('label_title') : importerSourceInfo.source_metadata[selectedTitle] }}</p>
                            <div />
                        </div>
                    </div>   
                </div>
                <div 
                        style="margin-top: -24px"
                        class="field is-grouped form-submit">
                    <div class="control">
                        <button 
                                class="button is-outlined" 
                                type="button"
                                @click="selectedTitle = ''; showTitlePromptModal = false;">
                            {{ $i18n.get('cancel') }}
                        </button>
                    </div>
                    <div 
                            style="margin-left: auto"
                            class="control">
                        <button 
                                class="button is-secondary" 
                                type="button"
                                @click="selectedTitle = ''; showTitlePromptModal = false; onRunImporter(true)">
                            {{ $i18n.get('skip') }}
                        </button>
                    </div>
                    <div class="control">
                        <button 
                                type="submit"
                                class="button is-success"
                                @click="onConfirmTitleSelection"
                                :disabled="selectedTitle === '' || selectedTitle == undefined">
                            {{ $i18n.get('apply') }}
                        </button>
                    </div>
                </div>
            </form>
        </b-modal>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import MetadatumEditionForm from './../edition/metadatum-edition-form.vue';

export default {
    name: 'ImporterEditionForm',
    components: {
        MetadatumEditionForm
    },
    data(){
        return {
            importerId: Number,
            importer: null,
            isLoading: false,
            isLoadingSourceInfo: false,
            isLoadingRun: false,
            mappedCollection: {
                'id': Number,
                'mapping': {},
                'total_items': Number
            },
            importerType: '',
            importerName: '',
            collection: undefined,
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
            backgroundProcess: undefined,
            metadataSearchCancel: undefined,
            showTitlePromptModal: false,
            selectedTitle: undefined,
            formErrorMessage: ''
        }
    },
    computed: {
        metadatumTypes() {
            return this.getMetadatumTypes();
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

        this.fetchCollectionBasics({ collectionId: this.collectionId, isContextEdit: true })
            .then((collection) => {
                this.collection = collection;
            });
    },
    beforeDestroy() {
        // Cancels previous Request
        if (this.metadataSearchCancel != undefined)
            this.metadataSearchCancel.cancel('Metadata search Canceled.');
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
        ...mapActions('collection', [
            'fetchCollectionBasics'
        ]),
        loadImporter() {
            
            // Puts loading on Draft Importer creation
            this.isLoading = true;
            this.formErrorMessage = '';

            // Creates draft Importer
            this.fetchImporter(this.sessionId)
                .then(res => {

                    this.sessionId = res.id;
                    this.importer = JSON.parse(JSON.stringify(res));

                    this.isLoading = false;
                    this.isLoadingSourceInfo = true;
                    this.formErrorMessage = '';

                    this.fetchImporterSourceInfo(this.sessionId)
                        .then(importerSourceInfo => {    
                            this.importerSourceInfo = importerSourceInfo;
                            this.mappedCollection['total_items'] = this.importerSourceInfo.source_total_items;
                            
                            this.isLoadingSourceInfo = false;
                            this.loadMetadata();
                        })
                        .catch((errors) => {
                            this.isLoadingSourceInfo = false;
                            this.formErrorMessage = errors.error_message;
                            this.$console.log(errors);
                        });
                    
                })
                .catch(error => {
                    this.$console.error(error);
                    this.formErrorMessage = error.error_message;
                });
        },
        loadMetadata() {
            // Generates options for metadata listing
            this.isFetchingCollectionMetadata = true;

            // Cancels previous Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');

            this.fetchMetadata({
                collectionId: this.collectionId, 
                isRepositoryLevel: false, 
                isContextEdit: false,
                parent: 'any' 
            }).then((resp) => {
                
                this.formErrorMessage = '';

                resp.request
                    .then((metadata) => {
                        this.collectionMetadata = JSON.parse(JSON.stringify(metadata));
                        
                        this.isFetchingCollectionMetadata = false;

                        this.fetchMappingImporter({ collection: this.collectionId, sessionId: this.sessionId })
                            .then(res => {
                                if (res)
                                    this.mappedCollection['mapping'] = res;
                            });
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.formErrorMessage = error.error_message;
                        this.isFetchingCollectionMetadata = false;
                    });
                    
                    // Search Request Token for cancelling
                    this.metadataSearchCancel = resp.source;
                })
                .catch(() => this.isFetchingCollectionMetadata = false);   
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

                if (source_metadata && source_metadata.indexOf(val) >= 0)
                    return true;
            }

            return false;
        },
        checkIfChildMetadatumIsAvailable(metadatumId, parentId, parentSource) {
            if (this.mappedCollection['mapping'][parentId] && 
                this.mappedCollection['mapping'][parentId][parentSource] &&
                this.mappedCollection['mapping'][parentId][parentSource][metadatumId] &&
                this.importerSourceInfo != undefined &&
                this.importerSourceInfo != null)
                    return true;
            else
                return false;
        },
        checkIfMetadatumIsCompound(metadatum) {
            return metadatum.metadata_type_object && metadatum.metadata_type_object.component && metadatum.metadata_type_object.component == 'tainacan-compound';
        },
        checkCurrentSelectedCollectionMetadatum(sourceMetadatum, isCompound) {
            for (let key in this.mappedCollection['mapping']) {
                if (this.mappedCollection['mapping'][key] == sourceMetadatum)
                    return key;
                if (isCompound && Object.keys(this.mappedCollection['mapping'][key]) && Object.keys(this.mappedCollection['mapping'][key])[0] && Object.keys(this.mappedCollection['mapping'][key])[0] == sourceMetadatum)
                    return key;
            }
            return undefined;
        },
        checkCurrentSelectedCollectionChildMetadatum(sourceMetadatum, parent) {
            
            if (this.mappedCollection['mapping'][parent] && Object.values(this.mappedCollection['mapping'][parent]) && Object.values(this.mappedCollection['mapping'][parent])[0]) {
                let parentMappings = Object.values(this.mappedCollection['mapping'][parent])[0]
                for (let key in parentMappings) {
                    if (parentMappings[key] == sourceMetadatum)
                        return key;
                }
                return undefined;
            }
            return undefined;
        },
        checkIfMetadatumIsChild(metadatum) {
            return metadatum.parent && metadatum.parent > 0;
        },
        getChildOfSelectedCompoundMetadata(sourceMetadatum) {
            return this.collectionMetadata.filter((metadatum) => metadatum.parent == this.checkCurrentSelectedCollectionMetadatum(Object.entries(sourceMetadatum)[0][0], true));
        },
        onRunImporter(skipTitleCheck) {

            if (skipTitleCheck !== true) {
                let coreTitleIndex = this.collectionMetadata.findIndex((metadatum) => metadatum.metadata_type == 'Tainacan\\Metadata_Types\\Core_Title');
                if (coreTitleIndex >= 0 &&
                    this.mappedCollection &&
                    this.mappedCollection.mapping &&
                    !this.mappedCollection.mapping[this.collectionMetadata[coreTitleIndex].id]
                ) {
                    this.showTitlePromptModal = true;
                    return;     
                }
            }
        
            this.isLoadingRun = true;
            this.formErrorMessage = '';

            this.updateImporterCollection({ sessionId: this.sessionId, collection: this.mappedCollection })
                .then(updatedImporter => {    
                    this.importer = updatedImporter;
                    this.finishRunImporter();
                })
                .catch((errors) => {
                    this.isLoadingRun = false;
                    this.$console.log(errors);

                    this.formErrorMessage = errors.error_message;
                });

        },
        finishRunImporter() {
            this.formErrorMessage = '';

            this.runImporter(this.sessionId)
                .then(backgroundProcess => {
                    this.backgroundProcess = backgroundProcess;
                    this.isLoadingRun = false;
                    this.$router.push(this.$routerHelper.getProcessesPage());
                })
                .catch((errors) => {
                    this.isLoadingRun = false;
                    this.$console.log(errors);

                    this.formErrorMessage = errors.error_message;
                });
        },
        onSelectCollectionChildMetadata(selectedMetadatum, sourceMetadatum, parentId, parentSource) {

            this.formErrorMessage = '';
            
            if (this.mappedCollection['mapping'][parentId] && this.mappedCollection['mapping'][parentId] && this.mappedCollection['mapping'][parentId][parentSource]) {
                let parentMappings = Array.isArray(this.mappedCollection['mapping'][parentId][parentSource]) ? {} : this.mappedCollection['mapping'][parentId][parentSource];

                let removedKey = '';
                for (let key in parentMappings) {
                    if (parentMappings[key] == sourceMetadatum)
                        removedKey = key;
                }
                if (removedKey != '')
                    delete parentMappings[removedKey];
                
                if (selectedMetadatum)
                    parentMappings[selectedMetadatum] = sourceMetadatum;

                this.mappedCollection['mapping'][parentId][parentSource] = parentMappings;
                    // Necessary for causing reactivity to re-check if metadata remains available
                this.collectionMetadata.push("");
                this.collectionMetadata.pop();
            }
        },
        onSelectCollectionMetadata(selectedMetadatum, sourceMetadatum, isCompound, childSourceMetadata) {

            this.formErrorMessage = '';

            let removedKey = '';
            for (let key in this.mappedCollection['mapping']) {
                if (this.mappedCollection['mapping'][key] == sourceMetadatum)
                    removedKey = key;
                if (isCompound && Object.keys(this.mappedCollection['mapping'][key]) && Object.keys(this.mappedCollection['mapping'][key])[0] && Object.keys(this.mappedCollection['mapping'][key])[0] == sourceMetadatum)
                    removedKey = key;
            }

            if (removedKey != '')
                delete this.mappedCollection['mapping'][removedKey];

            let mappingValue = '';
            if (isCompound) {
                mappingValue = {} 
                mappingValue[sourceMetadatum] = childSourceMetadata;
                
            } else {
                mappingValue = sourceMetadatum;
            }
            this.mappedCollection['mapping'][selectedMetadatum] = mappingValue;

            // Necessary for causing reactivity to re-check if metadata remains available
            this.collectionMetadata.push("");
            this.collectionMetadata.pop();
        },
        onSelectMetadatumType(newMetadatum) {
            
            this.formErrorMessage = '';

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
            
             // Updates options for metadata listing
            this.isFetchingCollectionMetadata = true;

            // Cancels previous Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');

             this.fetchMetadata({
                collectionId: this.collectionId, 
                isRepositoryLevel: false, 
                isContextEdit: false,
                parent: 'any' 
            }).then((resp) => {
                resp.request
                    .then((metadata) => {
                        this.collectionMetadata = JSON.parse(JSON.stringify(metadata));
                        this.isFetchingCollectionMetadata = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingCollectionMetadata = false;
                    }); 
                    // Search Request Token for cancelling
                    this.metadataSearchCancel = resp.source;
                })
                .catch(() => this.isFetchingCollectionMetadata = false);   
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
        },
        onConfirmTitleSelection(event) {
            event.preventDefault();
            let coreTitleIndex = this.collectionMetadata.findIndex((metadatum) => metadatum.metadata_type == 'Tainacan\\Metadata_Types\\Core_Title');
            if (coreTitleIndex >= 0)
                this.onSelectCollectionMetadata(this.collectionMetadata[coreTitleIndex].id, this.importerSourceInfo.source_metadata[this.selectedTitle])
            
            this.showTitlePromptModal = false;
            this.onRunImporter();
        }
    }
}
</script>

<style lang="scss" scoped>

    .tainacan-page-title {
        margin-bottom: 40px;

        h1, h2 {
            font-size: 1.25em;
            font-weight: 500;
            color: var(--tainacan-blue5);
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
            background-color: var(--tainacan-secondary);
        }
        .breadcrumbs {
            font-size: 0.75em;
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

    .tainacan-form {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 247px;
    }

    .form-submit {
        margin-top: 24px;
    }

    .section-label {
        font-size: 1em !important;
        font-weight: 500 !important;
        color: var(--tainacan-blue5) !important;
        line-height: 1.2em;
    }

    .source-metadatum {
        padding: 2px 0 2px 8px;
        min-height: 35px;
        border-bottom: 1px solid var(--tainacan-gray2);
        width: 100%;
        margin-bottom: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        
        &>p {
            font-weight: normal;
            transition: font-weight 0.1s ease;
        }

        &:hover {
            &>p {
                font-weight: bold;
            }
        }
    }

    .child-source-metadatum {
        flex-basis: 100%;
        border-left: 1px solid var(--tainacan-gray2);
        padding-left: 1em;
        opacity: 1;
        transition: border-left 0.2s ease, opacity 0.2s ease;

        .source-metadatum {
            border-bottom: none;
            margin-bottom: 0;
            margin-top: 2px;
            padding-top: 8px;
            padding-bottom: 0px;
            border-top: 1px solid var(--tainacan-gray2);
        }

        &.disabled-child-source-metadatum {
            border-left: 1px solid var(--tainacan-gray1);
            opacity: 0.70;
        }
    }

    .is-inline .control{
        display: inline;
    }
    .drop-inner{
        padding: 1em 3em;
    }

    .mapping-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--tainacan-info-color);
        font-size: 0.875em;
        font-weight: bold;
        margin: 18px 0 6px 0;

        p {
            white-space: nowrap;
        }
        hr {
            width: 100%;
            margin-left: 12px;
            margin-right: 12px;
            height: 1px;
        }

        @media screen and (max-width: 768px) {
            p {
                white-space: normal;
            }
            hr {
                display: none;
            }
        }
    }

    .modal .animation-content {
        width: 100%;
        z-index: 99999;

        #metadatumEditForm {
            background-color: var(--tainacan-background-color);
        }
    }

    .metadata-types-container {

        .metadata-type {
            border-bottom: 1px solid var(--tainacan-gray2);
            padding: 15px calc(2 * var(--tainacan-one-column));
            cursor: pointer;
        
            &:first-child {
                margin-top: 15px;
            }
            &:last-child {
                border-bottom: none;
            }
            &:hover {
                background-color: var(--tainacan-gray1);
            }
        }
    }

    .item-demo {
        display: inline-block;
        margin: 8px 12px;

        p {
            max-width: 74px;
            font-size: 0.875em;
            color: var(--tainacan-gray5);
            margin: 4px 8px;
            text-align: left;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }
        div {
            height: 90px;
            width: 90px;
            background-color: var(--tainacan-gray2);
            border-radius: 2px;
        }
    }
</style>


