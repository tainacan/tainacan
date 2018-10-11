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
                    lista
                </div>
                <div class="column">

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
                    <tainacan-form-item
                            v-if="!isLoadingMetadata"
                            v-for="(metadatum, index) of metadatumList"
                            :key="index"
                            :metadatum="metadatum"
                            :is-collapsed="metadatumCollapses[index]"
                            @changeCollapse="onChangeCollapse($event, index)"/>
                    <b-loading 
                            :is-full-page="false"
                            :active.sync="isLoadingMetadata"
                            :can-cancel="false"/>
                </div>
            </div>
            <div class="field is-grouped form-submit">
                <div class="control">
                    <button 
                            type="button"
                            class="button is-outlined" 
                            @click.prevent="$router.go(-1)" 
                            slot="trigger">{{ $i18n.get('cancel') }}</button>
                </div>
                <div class="control">
                    <button 
                            class="button is-success" 
                            type="submit">{{ $i18n.get('save') }}</button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'ItemMetadataBulkEditionForm',
    data(){
        return {
            isLoadingItems: false,
            isLoadingMetadata: false,
            collectionName: '',
            items: '',
            collapseAll: false,
            metadatumCollapses: [],
        }
    },
    computed: {
        metadatumList() {
            return JSON.parse(JSON.stringify(this.getMetadata()));
        },
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
            'fetchItemIdInSequence',
            'fetchGroup',
            'createEditGroup'
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
        onChangeCollapse(event, index) {
            this.metadatumCollapses.splice(index, 1, event);
        },
    },
    created() {
        // Obtains collection ID
        this.collectionId = this.$route.params.collectionId;

        // Obtains collection name
        this.fetchCollectionName(this.collectionId).then((collectionName) => {
            this.collectionName = collectionName;
        });

        this.isLoadingMetadata = true;
        // Get Collection Metadata list
        this.fetchMetadata({ collectionId: this.collectionId, isRepositoryLevel: false })
            .then(() => {
                this.isLoadingMetadata = false;
                for (let metadatum of this.metadatumList) {
                    this.metadatumCollapses.push(metadatum.metadatum.required == 'yes');
                }
            })
            .catch((error) => {
                this.$console.error(error);
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
        .source-file-upload {
            width: 100%;
            display: grid;
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
    }

</style>
