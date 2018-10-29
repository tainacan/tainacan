<template>
    <div>
        <!-- <b-loading
                :is-full-page="false"
                :active.sync="isLoadingItems"
                :can-cancel="false"/> -->
        <div class="tainacan-page-title">
            <h1>{{ $i18n.get('title_create_item_collection') + ' ' }}<span style="font-weight: 600;">{{ collectionName }}</span></h1>
            <a 
                    @click="$router.go(-1)"
                    class="back-link has-text-secondary">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </div>
        <form
                class="tainacan-form" 
                label-width="120px">
                
            <div class="columns">
                <div class="column document-list">
                    <transition-group name="item-appear">
                        <div 
                                class="document-item"
                                v-for="(item, index) of uploadedItems"
                                :key="index">
                            <img 
                                    v-if="item.document!= undefined && item.document != '' && item.document_type != 'empty'"
                                    class="document-thumb"
                                    :alt="item.title"
                                    :src="item.thumbnail.tainacan_small ? item.thumbnail.tainacan_small : item.thumbnail.thumb" > 
                            <span 
                                class="document-name"
                                v-html="item.title" />                            
                            <span 
                                    v-if="item.errorMessage != undefined" 
                                    class="help is-danger">
                                {{ item.errorMessage }}
                            </span>                                 
                        </div>
                    </transition-group>
                </div>
                <div class="column">
                     
                    <!-- Visibility (status public or private) -------------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_visibility') }}</label>
                        <span class="required-metadatum-asterisk">*</span>
                        <help-button
                                :title="$i18n.get('label_visibility')"
                                :message="$i18n.get('info_visibility_helper')"/>
                    </div>
                    <div class="section-status">
                        <div class="field has-addons">
                            <b-radio
                                    v-model="visibility"
                                    value="publish"
                                    native-value="publish">
                                <span class="icon">
                                    <i class="mdi mdi-earth"/>
                                </span> {{ $i18n.get('publish_visibility') }}
                            </b-radio>
                            <b-radio
                                    v-model="visibility"
                                    value="private"
                                    native-value="private">
                                <span class="icon">
                                    <i class="mdi mdi-lock"/>
                                </span>  {{ $i18n.get('private_visibility') }}
                            </b-radio>
                        </div>
                    </div>

                    <!-- Metadata from Collection-------------------------------- -->
                    <span class="section-label">
                        <label>{{ $i18n.get('metadata') }}</label>
                    </span>
                    <br>
                    <a
                            class="collapse-all"
                            @click="toggleCollapseAll()">
                        {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                        <b-icon :icon=" collapseAll ? 'menu-down' : 'menu-right'" />
                    </a>

                    <template 
                                v-for="(metadatum, index) of metadata">
                        <b-field
                                :key="index"
                                :addons="false"
                                :message="getErrorMessage(metadatum)"
                                :type="metadatumTypeMessage">
                            <span   
                                    class="collapse-handle"
                                    @click="changeCollapse(metadatumTypeMessage != 'is-danger' ? !metadatumCollapses[index] : true, index)">
                                <b-icon 
                                        type="is-secondary"
                                        :icon="metadatumCollapses[index] || metadatumTypeMessage == 'is-danger' ? 'menu-down' : 'menu-right'" />
                                <label class="label">{{ metadatum.name }}</label>
                                <span
                                        v-if="metadatum.required == 'yes'"
                                        class="required-metadatum-asterisk"
                                        :class="metadatumTypeMessage">*</span>
                                <span class="metadata-type">({{ $i18n.get(metadatum.metadata_type_object.component) }})</span>
                                <help-button 
                                        :title="metadatum.name"
                                        :message="metadatum.description"/>
                            </span>
                            <transition name="filter-item">
                                <div   
                                        v-show="metadatumCollapses[index] || metadatumTypeMessage == 'is-danger'"
                                        v-if="isTextInputComponent( metadatum.metadata_type_object.component )">

                                        <component
                                                :forced-component-type="metadatum.metadata_type_object.component.includes('taxonomy') ? 'tainacan-taxonomy-tag-input' : ''"
                                                :allow-new="false"
                                                :allow-select-to-create="metadatum.metadata_type_options.allow_new_terms === 'yes'"
                                                :maxtags="1"
                                                :id="metadatum.metadata_type_object.component +
                                                '-' + metadatum.slug"
                                                :is="metadatum.metadata_type_object.component"
                                                :metadatum="{ metadatum: metadatum }"
                                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"
                                                @input="bulkEdit($event, metadatum)" />
                                                <!-- :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
                                                :disabled="bulkEditionProcedures[criterion].isDone || bulkEditionProcedures[criterion].isExecuting" -->
                                </div>
                            </transition>
                        </b-field>
                    </template>

                    <b-loading 
                            :is-full-page="false"
                            :active.sync="isLoadingMetadata"
                            :can-cancel="false"/>
                </div>
            </div>
            <footer class="footer">
                <!-- Last Updated Info --> 
                <div class="update-info-section"> 
                    <p v-if="!isExecutingBulkEdit">
                        {{ ($i18n.get('info_updated_at') + ' ' + 'TO BE IMPLEMENTED lastUpdated') }}
                        <span class="help is-danger">{{ formErrorMessage }}</span>
                    </p>     
                    <p 
                            class="update-warning"
                            v-if="isExecutingBulkEdit">
                        <b-icon icon="autorenew" />{{ $i18n.get('info_updating_metadata_values') }}
                        <span class="help is-danger">{{ formErrorMessage }}</span>
                    </p> 
                </div>  
                <div 
                        class="form-submission-footer"
                        v-if="status == 'trash'">
                    <button 
                            @click="onDeletePermanently()"
                            type="button"
                            class="button is-outlined">{{ $i18n.get('label_delete_permanently') }}</button>
                    <button 
                            @click="onSubmit('draft')"
                            type="button"
                            class="button is-secondary">{{ $i18n.get('label_save_as_draft') }}</button>
                    <button 
                            @click="onSubmit(visibility)"
                            type="button"
                            class="button is-success">{{ $i18n.get('label_publish') }}</button>
                </div>
                <div 
                        class="form-submission-footer"
                        v-if="status == 'auto-draft' || status == 'draft' || status == undefined">
                    <button 
                            v-if="status == 'draft'"
                            @click="onSubmit('trash')"
                            type="button"
                            class="button is-outlined">{{ $i18n.get('label_send_to_trash') }}</button>
                    <button 
                            v-if="status == 'auto-draft'"
                            @click="onDiscard()"
                            type="button"
                            class="button is-outlined">{{ $i18n.get('label_discard') }}</button>
                    <button 
                            @click="onSubmit('draft')"
                            type="button"
                            class="button is-secondary">{{ status == 'draft' ? $i18n.get('label_update') : $i18n.get('label_save_as_draft') }}</button>
                    <button 
                            @click="onSubmit(visibility)"
                            type="button"
                            class="button is-success">{{ $i18n.get('label_publish') }}</button>
                </div>
                <div 
                        class="form-submission-footer"
                        v-if="status == 'publish' || status == 'private'">
                    <button 
                            @click="onSubmit('trash')"
                            type="button"
                            class="button is-outlined">{{ $i18n.get('label_send_to_trash') }}</button>
                    <button 
                            @click="onSubmit('draft')"
                            type="button"
                            class="button is-secondary">{{ $i18n.get('label_return_to_draft') }}</button>
                    <button 
                            :disabled="formErrorMessage != undefined && formErrorMessage != ''"
                            @click="onSubmit(visibility)"
                            type="button"
                            class="button is-success">{{ $i18n.get('label_update') }}</button>
                </div>
            </footer>
        </form>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'ItemMetadataBulkEditionForm',
    data(){
        return {
            collectionId: '',
            isLoadingItems: false,
            isLoadingMetadata: false,
            isExecutingBulkEdit: false,
            collectionName: '',
            items: '',
            visibility: 'publish',
            collapseAll: true,
            metadatumCollapses: [],
            metadatumTypeMessage:'',
            status: 'draft',
            groupID: null
        }
    },
    computed: {
        metadata() {
            return this.getMetadata();
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollectionName'
        ]),
        ...mapActions('metadata', [
            'fetchMetadata',
        ]),
        ...mapGetters('metadata', [
            'getMetadata',
        ]),
        ...mapActions('bulkedition', [
            'setValueInBulk',
            'addValueInBulk',
            'replaceValueInBulk',
            'redefineValueInBulk',
            'setStatusInBulk',
            'removeValueInBulk',
            'deleteItemsInBulk',
            'trashItemsInBulk'
        ]),
        ...mapGetters('bulkedition', [
            'getItemIdInSequence',
            'getGroup'
        ]),
        toggleCollapseAll() {
            this.collapseAll = !this.collapseAll;

            for (let i = 0; i < this.metadatumCollapses.length; i++)
                this.metadatumCollapses[i] = this.collapseAll;
        },
        changeCollapse(event, index) {
            this.metadatumCollapses.splice(index, 1, event);
        },
        getErrorMessage(metadatum) {
                
            let msg = '';
            // let errors = eventBus.getErrors(metadatum.id);

            // if ( errors) {
            //     this.metadatumTypeMessage = 'is-danger';
            //     for (let error of errors) { 
            //         for (let index of Object.keys(error)) {
            //             // this.$console.log(index);
            //             msg += error[index] + '\n';
            //         }
            //     }
            // } else {
            //     this.metadatumTypeMessage = '';
            // }

            return msg;
        },
        isTextInputComponent( component ){
            let array = ['tainacan-relationship','tainacan-taxonomy'];
            return !( array.indexOf( component ) >= 0 );
        },
        bulkEdit: _.debounce(function(newValue, metadatum) {
            this.isExecutingBulkEdit = true;
            this.setValueInBulk({
                collectionID: this.collectionId,
                groupID: this.groupID,
                bodyParams: {
                    metadatum_id: metadatum.id,
                    value: newValue,
                }
            }).then(() => {
                this.isExecutingBulkEdit = false;
                // this.finalizeProcedure(criterion);
            }).catch(() => this.isExecutingBulkEdit = false);
        }, 1000),
        onSubmit(status) {
            this.isExecutingBulkEdit = true;
            console.log(status)
            if (this.status != status && status != 'trash') {

                this.setStatusInBulk({
                        groupID: this.groupID,
                        collectionID: this.collectionId,
                        bodyParams: { value: status }
                    }).then(() => {
                        this.status = status;
                        this.isExecutingBulkEdit = false;

                        if (this.status != 'draft' && this.status != 'auto-draft')
                            this.$router.push(this.$routerHelper.getCollectionItemsPath(this.collectionId));
                    }).catch(() => this.isExecutingBulkEdit = false);

            } else if (this.status != status && status == 'trash') {
                
                this.trashItemsInBulk({
                        groupID: this.groupID,
                        collectionID: this.collectionId
                    }).then(() => {
                        this.status = status;
                        this.isExecutingBulkEdit = false;
                        this.$router.push(this.$routerHelper.getCollectionItemsPath(this.collectionId));
                    }).catch(() => this.isExecutingBulkEdit = false);
            
            } else {   

                // Triggers updating animation.
                this.isExecutingBulkEdit = true;
                this.isExecutingBulkEdit = false;
                if (this.status != 'draft' && this.status != 'auto-draft')
                    this.$router.push(this.$routerHelper.getCollectionItemsPath(this.collectionId));
            
            }
        },
        onDeletePermanently() {
            this.isExecutingBulkEdit = true;
            this.deleteItemsInBulk({
                    collectionID: this.collectionId,
                    groupID: this.groupID
                }).then(() => {
                    this.isExecutingBulkEdit = false;
                    this.$router.push(this.$routerHelper.getCollectionItemsPath(this.collectionId));
                }).catch(() => this.isExecutingBulkEdit = false);
        },
        onDiscard() {
            this.$router.push(this.$routerHelper.getCollectionItemsPath(this.collectionId));
        }
    },
    created() {
        // Obtains collection ID
        this.collectionId = this.$route.params.collectionId;
        this.groupID = this.$route.params.groupId;

        // Obtains collection name
        this.fetchCollectionName(this.collectionId).then((collectionName) => {
            this.collectionName = collectionName;
        });

        this.isLoadingMetadata = true;

        this.fetchMetadata({
            collectionId: this.collectionId,
            isRepositoryLevel: false,
            isContextEdit: true,
            includeDisabled: false,
        }).then(() => {
            this.isLoadingMetadata = false;
            for (let i = 0; i < this.metadata.length; i++) {
                this.metadatumCollapses.push(false);
                this.metadatumCollapses[i] = true;
            }
            this.visibility = 'publish';
        });
       
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";


    .page-container {

        &>.tainacan-form {
            padding: 0 $page-side-padding; 
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

        .document-list {
            display: inline-block;
            width: 100%;

            .document-item {
                display: flex;
                flex-wrap: nowrap;
                width: 100%;
                justify-content: space-between;
                align-items: center;
                margin: 0.75rem;
                cursor: default;

                .document-thumb {
                    max-height: 42px;
                    max-width: 42px;
                    margin-right: 0.75rem;
                }

                .document-actions {
                    margin-left: auto;
                    

                    .loading-icon .control.is-loading::after {
                        position: relative !important;
                        right: 0;
                        top: 0;
                    }
                }

                .help.is-danger {
                    margin-left: auto;
                }
            }

            .sequence-progress {
                height: 5px;
                background: $turquoise5;
                width: 0%;
                transition: width 0.2s;
            }
            .sequence-progress-background {
                height: 5px;
                background: $gray3;
                width: 100%;
                top: -5px;
                z-index: -1;
                position: relative;
            }        
        }

        .column {

            .section-label {
                position: relative;
                label {
                    font-size: 16px !important;
                    font-weight: 500 !important;
                    color: $gray5 !important;
                    line-height: 1.2em;
                }
            }

            .collapse-all {
                font-size: 12px;
                .icon { 
                    vertical-align: bottom; 
                }
            }

            .multiple-inputs {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .field {
                border-bottom: 1px solid $gray2;
                padding: 10px 0px 10px 60px;

                .label {
                    font-size: 0.875rem;
                    font-weight: 500;
                    margin-left: 15px;
                    margin-bottom: 0.5em;
                }
                .metadata-type {
                    font-size: 0.8125rem;
                    font-weight: 400;
                    color: $gray3;
                    top: -0.2em;
                    position: relative;
                }
                .help-wrapper {
                    top: -0.2em;
                }
                .collapse-handle {
                    cursor: pointer;
                    position: relative;
                    margin-left: -42px;
                }
            }
        }

        .footer {
            padding: 18px $page-side-padding;
            position: absolute;
            bottom: 0;
            z-index: 999999;
            background-color: $gray1;
            width: 100%;
            height: 65px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            left: 0;

            .form-submission-footer {    
                .button {
                    margin-left: 16px;
                    margin-right: 6px;
                }
                .is-outlined {
                    border: none;
                }
            }
            

            @keyframes blink {
                from { color: $blue5; }
                to { color: $gray4; }
            }

            .update-warning {
                color: $blue5;
                animation-name: blink;
                animation-duration: 0.5s;
                animation-delay: 0.5s;
                align-items: center;
                display: flex;
            }

            .update-info-section {
                color: $gray4;
                margin-right: auto;
            }

            .help {
                display: inline-block;
                font-size: 1.0em;
                margin-top: 0;
                margin-left: 24px;
            }
        }

    }

</style>
