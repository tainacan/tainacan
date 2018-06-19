<template>
    <div 
            class="primary-page page-container">
        <tainacan-title />
        <form 
                class="tainacan-form" 
                label-width="120px"
                v-if="importer != undefined && importer != null">

            <b-field
                    v-if="importer.accepts.file"
                    :addons="false">
                <label class="label">{{ $i18n.get('label_source_file') }}</label>
                <help-button 
                        :title="$i18n.get('label_source_file')" 
                        :message="$i18n.get('info_source_file_upload')"/>
                <br>
                <b-upload 
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

            <!-- Target collection selection -------------------------------- --> 
            <b-field
                    v-if="importer.manual_collection"
                    :addons="false" 
                    :label="$i18n.get('label_target_collection')">
                <help-button 
                        :title="$i18n.get('label_target_collection')" 
                        :message="$i18n.get('info_target_collection_helper')"/>
                <b-select
                        id="tainacan-select-target-collection"
                        v-model="form.collectionId"
                        :loading="isFetchingCollections"
                        :placeholder="$i18n.get('instruction_select_a_target_collection')">
                    <option
                            v-for="collection of collections"
                            :key="collection.id"
                            :value="collection.id">{{ collection.name }}
                    </option>
                </b-select>
            </b-field>

            <!-- Metadata Mapping -->
            <b-field
                    v-if="importer.manual_mapping"
                    :addons="false" 
                    :label="$i18n.get('label_metadata_mapping')">
                <help-button 
                        :title="$i18n.get('label_metadata_mapping')" 
                        :message="$i18n.get('info_metadata_mapping_helper')"/>
                <div class="columns">
                    <div class="column">
                        <ol v-if="importerSourceInfo != undefined">
                            <li
                                    v-for="(source_metadatum, index) of importerSourceInfo.source_metadata"
                                    :key="index">{{ source_metadatum }}</li>
                        </ol>
                        <div v-else>
                            Upload a source to load metadata.
                        </div>
                    </div>
                    <div class="column">
                        <div>Select collection to list metadada. (no implemented)</div>
                    </div>
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
                            id="button-submit-collection-creation"
                            @click.prevent="runImporter"
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
                collectionId: Number
            },
            importerTypes: [],
            importerType: '',
            importerFile: {},
            importerSourceInfo: {},
            collections: [],
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
            'runImporter'
        ]),
        ...mapActions('collection', [
            'fetchCollectionsForParent',
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
                    console.log(this.importerSourceInfo);
                })
                .catch((errors) => {
                    this.$console.log(errors);
                });
            })
            .catch((errors) => {
                this.$console.log(errors);
            });
        },
        runImporter() {
            this.runImporter({ sessionId: this.sessionId })
            .then(backgroundProcess => {    
                this.$console.log(backgroundProcess);
            })
            .catch((errors) => {
                this.$console.log(errors);
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

    .drop-inner{
        padding: 1rem 3rem;
    }

</style>


