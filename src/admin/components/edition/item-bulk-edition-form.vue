<template>
    <div>
        <b-loading
                :is-full-page="false"
                :active.sync="isLoading"
                :can-cancel="false"/>
        <div class="tainacan-page-title">
            <h1 v-if="isCreatingBulkAdd">{{ $i18n.get('title_create_item_collection') + ' ' }}<span style="font-weight: 600;">{{ collectionName }}</span></h1>
            <h1 v-else>{{ $i18n.get('title_edit_item') + ' ' }}<span style="font-weight: 600;">{{ (item != null && item != undefined) ? item.title : '' }}</span></h1>
            <a 
                    @click="$router.go(-1)"
                    class="back-link has-text-secondary">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </div>
        <form
                v-if="!isLoading"
                class="tainacan-form" 
                label-width="120px">
            <div class="columns">
                <div class="column">
                    
                    <!-- File Source input -->
                    <b-field :addons="false">
                        <label class="label">{{ $i18n.get('label_source_file') }}</label>
                        <br>
                        <b-upload
                                v-model="submitedFileList"
                                drag-drop
                                multiple
                                @input="uploadFiles()"
                                class="source-file-upload">
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
                    <div class="document-list">
                        <transition name="page-left">
                            <div 
                                    class="document-item"
                                    v-for="(submitedFile, index) of submitedFileList"
                                    :key="index"
                                    v-if="submitedFileList.length > 0">
                                <img 
                                    class="document-thumb"
                                    :alt="$i18n.get('label_placeholder')"
                                    :src="thumbPlaceholderPath" > 
                                <span class="document-name">{{ submitedFile.name }}</span>                       
                            </div>
                        </transition>
                    </div>
                </div>
                <div class="column document-list">
                    <transition-group name="page-left">
                        <div 
                                class="document-item"
                                v-for="(uploadedFile, index) of uploadedFileList"
                                :key="index"
                                v-if="uploadedFileList.length > 0">
                            <img 
                                    class="document-thumb"
                                    :alt="uploadedFile.title.rendered"
                                    :src="uploadedFile.media_details.sizes.thumbnail.source_url" > 
                            <span 
                                class="document-name"
                                v-html="uploadedFile.title.rendered" />
                                                
                        </div>
                    </transition-group>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'ItemBulkEditionForm',
    data(){
        return {
            isLoading: false,
            isCreatingBulkAdd: true,
            collectionName: '',
            submitedFileList: [],
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png'
        }
    },
    computed: {
        uploadedFileList() {
            return this.getFiles();
        }
    },
    methods: {
        ...mapActions('collection', [
            'sendFile',
            'cleanFiles',
            'fetchCollectionName'
        ]),
         ...mapGetters('collection', [
            'getFiles'
        ]),
        ...mapActions('bulkedition', [
            'fetchItemIdInSequence',
            'fetchGroup'
        ]),
        ...mapGetters('bulkedition', [
            'getItemIdInSequence',
            'getGroup'
        ]),
        uploadFiles() {

            for (let file of this.submitedFileList) {

                this.sendFile(file)
                    .then((uploadedFile) => {
                        let indexToRemove = this.submitedFileList.findIndex(aFile => aFile.name == uploadedFile.source_url.split('/').pop())
                        if (indexToRemove >= 0) {
                            this.submitedFileList.splice(indexToRemove, 1);
                        }
                    })
                    .catch((error) => {
                        this.$console.error(error);
                    });
                
            }
        } 
    },
    created() {
        // Obtains collection ID
        this.collectionId = this.$route.params.collectionId;

        // Obtains collection name
        this.fetchCollectionName(this.collectionId).then((collectionName) => {
            this.collectionName = collectionName;
        });

        this.cleanFiles();
        // ITEM BULK ADDITION
        if (this.$route.fullPath.split("/").pop() == "bulk-add") {
            this.isCreatingBulkAdd = true;

        // EDITING BULK ADDITION
        } else  {
            this.isLoading = true;
        }

    }
   
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";


    .page-container {

        &>.tainacan-form {
            margin-bottom: 110px;
        }

        .tainacan-page-title {
            margin-bottom: 40px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;

            h1, h2 {
                font-size: 20px;
                font-weight: 500;
                color: $gray5;
                display: inline-block;
                width: 80%;
                flex-shrink: 1;
                flex-grow: 1;
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
                width: 100%;
            }
        }
        .source-file-upload {
            width: 100%;
            display: grid;
        }
        .document-list {
            display: inline-block;

            .document-item {
                display: flex;
                flex-wrap: nowrap;
                width: 100%;
                justify-content: flex-start;
                align-items: center;
                margin: 0.75rem;

                .document-thumb {
                    max-height: 42px;
                    max-width: 42px;
                    margin-right: 0.75rem;
                }
            }
        
        }
    }

</style>
