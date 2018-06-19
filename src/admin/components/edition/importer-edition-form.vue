<template>
    <div 
            class="primary-page page-container">
        <tainacan-title />
        <form 
                class="tainacan-form" 
                label-width="120px">

            <b-field
                    :addons="false">
                <label class="label">{{ $i18n.get('label_source_file') }}</label>
                <help-button 
                        :title="$i18n.get('label_source_file')" 
                        :message="$i18n.get('info_source_file_upload')"/>
                <br>
                <b-upload 
                        v-model="importerFile"
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
            </b-field>

            <!-- Target collection selection -------------------------------- --> 
            <b-field
                    v-if="form != undefined"
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
                            class="button is-success">{{ $i18n.get('save') }}</button>
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
                
            })
            .catch(error => this.$console.error(error));
        },
        cancelBack(){
            this.$router.go(-1);
        },
        runImporter() {
            this.runImporter({ sessionId: this.sessionId })
            .then(backgroundProcess => {    
                this.$console.log(backgroundProcess);
            })
            .catch((errors) => {
                this.$console.log(errors);
            });
        }
    },
    created(){

        this.importerType = this.$route.params.importerSlug;

        if (this.$route.params.sessionId == undefined || this.$route.params.sessionId == null) {
            this.createImporter();
        } else {

            this.isLoading = true;

            this.sessionId = this.$route.params.sessionId

            this.fetchImporter(this.sessionId).then(res => {
                this.sessionId = res.id;
                this.importer = JSON.parse(JSON.stringify(res));

                this.form = this.importer.options;
                this.isLoading = false;
            });
        } 

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


