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
                            :to="{ path: $routerHelper.getNewCollectionPath(), params: { fromImporter: importerType }}">
                        <b-icon
                                icon="plus-circle"
                                size="is-small"
                                type="is-secondary"/>
                            {{ $i18n.get('new_blank_collection') }}</router-link>
                </div>
            </b-field>

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
                    <div
                            class="source-metadatum"
                            v-for="(source_metadatum, index) of importerSourceInfo.source_metadata"
                            :key="index"><p>{{ source_metadatum }}</p>
                    
                        <b-select
                                v-if="collectionMetadata != undefined &&
                                      collectionMetadata.length > 0 &&
                                      !isFetchingCollectionMetadata"
                                @input="onSelectCollectionMetadata($event, source_metadatum)"
                                :placeholder="$i18n.get('label_select_metadatum')">
                            <option
                                    v-for="(metadatum, index) of collectionMetadata"
                                    :key="index"
                                    :value="metadatum.id"
                                    :disabled="checkIfMetadatumIsAvailable(metadatum.id)">{{ metadatum.name }}
                            </option>
                        </b-select>
                        <p v-if="collectionMetadata == undefined || collectionMetadata.length <= 0">{{ $i18n.get('info_select_collection_to_list_metadata') }}</p>
                    </div>
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
import { mapActions } from 'vuex';

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
            collectionId: undefined
        }
    },
    methods: {
        ...mapActions('importer', [
            'fetchImporterTypes',
            'fetchImporter',
            'sendImporter',
            'updateImporter',
            'updateImporterFile',
            'fetchImporterSourceInfo',
            'updateImporterCollection',
            'runImporter'
        ]),
        ...mapActions('collection', [
            'fetchCollectionsForParent'
        ]),
        ...mapActions('metadata', [
            'fetchMetadata'
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
        onRunImporter() {
            if (this.importer.manual_collection) {
                this.updateImporterCollection({ sessionId: this.sessionId, collection: this.mappedCollection })
                .then(updatedImporter => {    
                    this.importer = updatedImporter;

                    this.runImporter(this.sessionId)
                    .then(backgroundProcess => {    
                        this.$console.log(backgroundProcess);
                    })
                    .catch((errors) => {
                        this.$console.log(errors);
                    });
                })
                .catch((errors) => {
                    this.$console.log(errors);
                });
            } else {
                this.runImporter(this.sessionId)
                .then(backgroundProcess => {    
                    this.$console.log(backgroundProcess);
                })
                .catch((errors) => {
                    this.$console.log(errors);
                });
            }
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
                this.collectionMetadata = metadata;
                this.isFetchingCollectionMetadata = false;
            })
            .catch((error) => {
                this.$console.error(error);
                this.isFetchingCollectionMetadata = false;
            }); 
            
        },
        onSelectCollectionMetadata(selectedMetadatum, sourceMetadatum) {
            this.isFetchingCollectionMetadata = true;
            this.mappedCollection['mapping'][selectedMetadatum] = sourceMetadatum;
            this.isFetchingCollectionMetadata = false;
        }
    },
    created(){
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

</style>


