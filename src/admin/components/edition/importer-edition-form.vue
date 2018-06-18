<template>
    <div 
            class="page-container">
        <tainacan-title />
        <form 
                class="tainacan-form" 
                label-width="120px">

            <div class="columns">

                <div class="column">
                    <!-- Status -------------------------------- --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_registered_importer_types')">
                        <help-button 
                                :title="$i18n.get('label_registered_importer_types')" 
                                :message="$i18n.get('info_registered_importer_types_helper')"/>
                        <b-select
                                id="tainacan-select-registered-importer-types"
                                v-model="importerType"
                                :placeholder="$i18n.get('instruction_select_an_importer_type')">
                            <option
                                    v-for="anImporterType in importerTypes"
                                    :key="anImporterType.slug"
                                    :value="anImporterType.slug">{{ statusOption.label }}
                            </option>
                        </b-select>
                    </b-field>

                    <!-- Target collection selection -------------------------------- --> 
                    <b-field
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
                collectionId
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
        onSubmit() {
            this.isLoading = true;

            let data = { 
                collection_id: this.collectionId
            };
            this.updateImporter({ sessionId: this.sessionId, options: data })
            .then(updatedImporter => {    
                
                this.importer = updatedImporter;
                this.form = this.updateImporter.options;

                this.$router.push(this.$routerHelper.getCollectionPath(this.collectionId));
            })
            .catch((errors) => {
                this.$console.log(errors);
                this.isLoading = false;
            });
        },
        createImporter() {
            // Puts loading on Draft Importer creation
            this.isLoading = true;

            // Creates draft Importer
            let data = { collectionId: '' };
            this.sendImporter(data).then(res => {

                this.sessionId = res.id;
                this.form = res.options;
                this.isLoading = false;
                
            })
            .catch(error => this.$console.error(error));
        },
        cancelBack(){
            this.$router.push(this.$routerHelper.getCollectionsPath());
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

        if (this.$route.fullPath.split("/").pop() == "new") {
            this.createImporter();
        } else {

            this.isLoading = true;

            // Obtains current Session ID from URL
            this.pathArray = this.$route.fullPath.split("/").reverse(); 
            this.sessionId = this.pathArray[1];

            this.fetchImporter(this.sessionId).then(res => {
                this.importer = res;
                this.form = res.options;
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

    },
    mounted() {

        if (this.$route.fullPath.split("/").pop() != "new") {
            document.getElementById('collection-page-container').addEventListener('scroll', ($event) => {
                this.$emit('onShrinkHeader', ($event.target.scrollTop > 53)); 
            });
        }
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

</style>


