<template>
    <div>     
        <b-field 
                grouped 
                group-multiline>
            <button 
                    v-if="isSelectingCollections && collections.length > 0 && collections[0].current_user_can_edit" 
                    class="button field is-danger" 
                    @click="deleteSelectedCollections()">
                <span>{{ $i18n.get('instruction_delete_selected_collections') }} </span>
                <b-icon icon="delete"/>
            </button>
        </b-field>
        <div class="field select-all">
            <b-checkbox
                    :value="allCollectionsOnPageSelected"
                    @input="selectAllCollectionsOnPage($event)"
                    size="is-small">Selecionar todas as coleções desta página.</b-checkbox>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <!-- Checking list -->
                        <th class="checkbox-cell">
                            <!-- nothing to show on header -->
                        </th>
                        <!-- Thumbnail -->
                        <th class="table-thumbnail-column">
                            <div class="th-wrap">{{ $i18n.get('label_thumbnail') }}</div>
                        </th>
                        <!-- Name -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_name') }}</div>
                        </th>
                        <!-- Description -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_description') }}</div>
                        </th>
                        <!-- Creation -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_creation') }}</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr     
                            :class="{ 'selected-row': selectedCollections[index] }"
                            :key="index"
                            v-for="(collection, index) of collections">
                        <!-- Checking list -->
                        <td 
                                :class="{ 'is-selecting': isSelectingCollections }"
                                class="checkbox-cell">
                            <b-checkbox v-model="selectedCollections[index]"/> 
                        </td>
                        <!-- Thumbnail -->
                        <td 
                                class="column-default-width"
                                @click="goToCollectionPage(collection.id)"
                                :label="$i18n.get('label_thumbnail')" 
                                :aria-label="$i18n.get('label_thumbnail')">
                            <span>
                                <img 
                                        class="table-thumb" 
                                        :src="collection.thumbnail">
                            </span>
                        </td>
                        <!-- Name -->
                        <td 
                                class="column-default-width"
                                @click="goToCollectionPage(collection.id)"
                                :label="$i18n.get('label_name')" 
                                :aria-label="$i18n.get('label_name') + ': ' + collection.name">
                            <p>{{ collection.name }}</p>
                        </td>
                        <!-- Description -->
                        <td
                                class="column-default-width" 
                                @click="goToCollectionPage(collection.id)"
                                :label="$i18n.get('label_description')" 
                                :aria-label="$i18n.get('label_description') + ': ' + collection.description">
                            <p>{{ collection.description }}</p>
                        </td>
                        <!-- Creation -->
                        <td
                                @click="goToCollectionPage(collection.id)"
                                class="table-creation column-default-width" 
                                :label="$i18n.get('label_creation')" 
                                :aria-label="$i18n.get('label_creation') + ': ' + collection.creation">
                            <p v-html="collection.creation" />
                        </td>
                        <!-- Actions -->
                        <td 
                                @click="goToCollectionPage(collection.id)"
                                class="actions-cell column-default-width" 
                                :label="$i18n.get('label_actions')">
                            <a 
                                    id="button-edit" 
                                    :aria-label="$i18n.getFrom('collections','edit_item')" 
                                    @click.prevent.stop="goToCollectionEditPage(collection.id)"><b-icon 
                                    type="is-secondary" 
                                    icon="pencil"/></a>
                            <a 
                                    id="button-delete" 
                                    :aria-label="$i18n.get('label_button_delete')" 
                                    @click.prevent.stop="deleteOneCollection(collection.id)"><b-icon 
                                    type="is-secondary" 
                                    icon="delete"/></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty state image -->
        <div v-if="!totalCollections || totalCollections <= 0">
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <b-icon
                                icon="inbox"
                                size="is-large"/>
                    </p>
                    <p>{{ $i18n.get('info_no_collection_created') }}</p>
                    <router-link
                            id="button-create-collection"
                            tag="button"
                            class="button is-primary"
                            :to="{ path: $routerHelper.getNewCollectionPath() }">
                        {{ $i18n.getFrom('collections', 'new_item') }}
                    </router-link>
                </div>
            </section>
        </div>

    </div>
</template>

<script>
import { mapActions } from 'vuex'

export default {
    name: 'CollectionsList',
    data(){
        return {
            selectedCollections: [],
            allCollectionsOnPageSelected: false,
            isSelectingCollections: false
        }
    },
    props: {
        isLoading: false,
        totalCollections: 0,
        page: 1,
        collectionsPerPage: 12,
        collections: Array
    },
    watch: {
        collections() {
            for (let i = 0; i < this.collections.length; i++)
                this.selectedCollections.push(false);    
        },
        selectedCollections() {
            let allSelected = true;
            let isSelecting = false;
            for (let i = 0; i < this.selectedCollections.length; i++) {
                if (this.selectedCollections[i] == false) {
                    allSelected = false;
                } else {
                    isSelecting = true;
                }
            }
            this.allCollectionsOnPageSelected = allSelected;
            this.isSelectingCollections = isSelecting;
        }
    },
    methods: {
        ...mapActions('collection', [
            'deleteCollection'
        ]),
        selectAllCollectionsOnPage(event) {
            for (let i = 0; i < this.selectedCollections.length; i++) 
                this.selectedCollections.splice(i, 1, event);
        },
        deleteOneCollection(collectionId) {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_collection_delete'),
                onConfirm: () => {
                    // this.deleteCollection(collectionId).then(() => {
                    //     this.$toast.open({
                    //         duration: 3000,
                    //         message: this.$i18n.get('info_collection_deleted'),
                    //         position: 'is-bottom',
                    //         type: 'is-secondary',
                    //         queue: true
                    //     });
                    //     for (let i = 0; i < this.selectedCollections.length; i++) {
                    //         if (this.selectedCollections[i].id == this.collectionId)
                    //             this.selectedCollections.splice(i, 1);
                    //     }
                    // }).catch(() =>
                    //     this.$toast.open({
                    //         duration: 3000,
                    //         message: this.$i18n.get('info_error_deleting_collection'),
                    //         position: 'is-bottom',
                    //         type: 'is-danger',
                    //         queue: true
                    //     })
                    // );
                }
            });
        },
        deleteSelectedCollections() {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_selected_collections_delete'),
                onConfirm: () => {

                    for (let i = 0; i < this.collections.length; i++) {
                        if (this.selectedCollections[i])
                        this.deleteCollection(this.collections[i].id)
                        // .then(() => {
                        //     this.loadCollections();
                        //     this.$toast.open({
                        //         duration: 3000,
                        //         message: this.$i18n.get('info_collection_deleted'),
                        //         position: 'is-bottom',
                        //         type: 'is-secondary',
                        //         queue: false
                        //     })                            
                        // }).catch(() => { 
                        //     this.$toast.open({
                        //         duration: 3000,
                        //         message: this.$i18n.get('info_error_deleting_collection'),
                        //         position: 'is-bottom',
                        //         type: 'is-danger',
                        //         queue: false
                        //     });
                        // });
                    }
                    this.allCollectionsOnPageSelected = false;
                }
            });
        },
        goToCollectionPage(collectionId) {
            this.$router.push(this.$routerHelper.getCollectionPath(collectionId));
        },
        goToCollectionEditPage(collectionId) {
            this.$router.push(this.$routerHelper.getCollectionEditPath(collectionId));
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .select-all {
        color: $gray-light;
        font-size: 14px;
        &:hover {
            color: $gray-light;
        }
    }

    .table {
        width: 100%;
        position: relative;

        .table-thumbnail-column {
            width: 58px;
        }
        .checkbox-cell {
            width: 44px;
            .checkbox { 
                visibility: hidden;
                .control-label {
                    padding-left: 0;
                }  
            }
            &.is-selecting .checkbox {
                visibility: visible; 
            }
        }
        tbody {
            tr {
                background-color: transparent;
                &.selected-row { background-color: $primary-lighter;}
                td {
                    height: 56px;
                    max-height: 56px;
                    padding: 10px;
                    vertical-align: middle;
                    line-height: 12px;
                    p { font-size: 14px; }
                }
                td.column-default-width{
                    max-width: 250px;
                    p {
                        text-overflow: ellipsis;
                        overflow-x: hidden;
                        white-space: nowrap;
                    }
                }
                img.table-thumb {
                    max-height: 38px !important;
                    border-radius: 3px;
                }

                td.table-creation p {
                    color: $gray-light;
                    font-size: 11px;
                    line-height: 1.5;
                }

                td.actions-cell {
                    padding: 10px;
                    visibility: hidden;
                    position: absolute;
                    right: 0px;
                    display: none;
                    background-color: $tainacan-input-background;

                    a .icon {
                        margin: 8px;
                    }
                }

                &:hover {
                    background-color: $tainacan-input-background;
                    cursor: pointer;

                    .checkbox-cell .checkbox {
                        visibility: visible; 
                    }
                    .actions-cell {
                        visibility: visible;
                        display: block;
                        box-shadow: -2px 0px 5px -4px #555;
                    }
                }
            }
        }

        .clickable-row{ cursor: pointer !important; }

    }
    
</style>


