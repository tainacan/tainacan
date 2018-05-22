<template>
    <div 
            v-if="totalCollections > 0 && !isLoading"
            class="table-container">
        <div class="selection-control">
            <div class="field select-all is-pulled-left">
                <span>
                    <b-checkbox 
                            @click.native="selectAllCollectionsOnPage()" 
                            :value="allCollectionsOnPageSelected">{{ $i18n.get('label_select_all_collections_page') }}</b-checkbox>
                </span>
            </div>
            <div class="field is-pulled-right">
                <b-dropdown
                        position="is-bottom-left"
                        v-if="collections[0].current_user_can_edit"
                        :disabled="!isSelectingCollections"
                        id="bulk-actions-dropdown">
                    <button
                            class="button is-white"
                            slot="trigger">
                        <span>{{ $i18n.get('label_bulk_actions') }}</span>
                        <b-icon icon="menu-down"/>
                    </button> 

                    <b-dropdown-item
                            id="item-delete-selected-items"
                            @click="deleteSelectedCollections()">
                        {{ $i18n.get('label_delete_selected_collections') }}
                    </b-dropdown-item>
                    <b-dropdown-item disabled>{{ $i18n.get('label_edit_selected_collections') + ' (Not ready)' }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <!-- Checking list -->
                        <th>
                            &nbsp;
                            <!-- nothing to show on header -->
                        </th>
                        <!-- Thumbnail -->
                        <th class="thumbnail-cell">
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
                        <th class="actions-header">
                            &nbsp;
                            <!-- nothing to show on header for actions cell-->
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
                            <b-checkbox 
                                    size="is-small"
                                    v-model="selectedCollections[index]"/> 
                        </td>
                        <!-- Thumbnail -->
                        <td 
                                class="thumbnail-cell column-default-width"
                                @click="goToCollectionPage(collection.id)"
                                :label="$i18n.get('label_thumbnail')" 
                                :aria-label="$i18n.get('label_thumbnail')">
                            <span>
                                <img 
                                        class="table-thumb" 
                                        :src="collection.thumbnail.thumb">
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
                            <div class="actions-container">
                                <a 
                                        id="button-edit" 
                                        :aria-label="$i18n.getFrom('collections','edit_item')" 
                                        @click.prevent.stop="goToCollectionEditPage(collection.id)">
                                    <b-icon 
                                            type="is-secondary" 
                                            icon="settings"/>
                                </a>
                                <a 
                                        id="button-delete" 
                                        :aria-label="$i18n.get('label_button_delete')" 
                                        @click.prevent.stop="deleteOneCollection(collection.id)">
                                    <b-icon 
                                            type="is-secondary" 
                                            icon="delete"/>
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
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
            this.selectedCollections = [];
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
        selectAllCollectionsOnPage() {
            for (let i = 0; i < this.selectedCollections.length; i++) 
                this.selectedCollections.splice(i, 1, !this.allCollectionsOnPageSelected);
        },
        deleteOneCollection(collectionId) {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_collection_delete'),
                onConfirm: () => {
                    this.deleteCollection(collectionId)
                    .then(() => {
                    //     this.$toast.open({
                    //         duration: 3000,
                    //         message: this.$i18n.get('info_collection_deleted'),
                    //         position: 'is-bottom',
                    //         type: 'is-secondary',
                    //         queue: true
                    //     });
                        for (let i = 0; i < this.selectedCollections.length; i++) {
                            if (this.selectedCollections[i].id == this.collectionId)
                                this.selectedCollections.splice(i, 1);
                        }
                    }).catch(() => {
                    //     this.$toast.open({
                    //         duration: 3000,
                    //         message: this.$i18n.get('info_error_deleting_collection'),
                    //         position: 'is-bottom',
                    //         type: 'is-danger',
                    //         queue: true
                    //     })
                    });
                }
            });
        },
        deleteSelectedCollections() {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_selected_collections_delete'),
                onConfirm: () => {

                    for (let i = 0; i < this.collections.length; i++) {
                        if (this.selectedCollections[i]) {
                            this.deleteCollection(this.collections[i].id)
                            .then(() => {
                            //     this.loadCollections();
                            //     this.$toast.open({
                            //         duration: 3000,
                            //         message: this.$i18n.get('info_collection_deleted'),
                            //         position: 'is-bottom',
                            //         type: 'is-secondary',
                            //         queue: false
                            //     })                            
                            }).catch(() => { 
                            //     this.$toast.open({
                            //         duration: 3000,
                            //         message: this.$i18n.get('info_error_deleting_collection'),
                            //         position: 'is-bottom',
                            //         type: 'is-danger',
                            //         queue: false
                            //     });
                            });
                        }
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
    },
    mounted() {
        // COLUMN RESIZE
        // This feature is not implemented as it would require whitespace 
        // on table cells to be 'wrap' instead of 'no-wrap'. Once the table
        // needs a scroll, the minimum size for the columns would be reached
        // text would start to ellipsis, but the column is not resizible anymore.
        // (function () {
        //     var thElm;
        //     var startOffset;

        //     Array.prototype.forEach.call(
        //     document.querySelectorAll("table th"),
        //     function (th) {
        //         th.style.position = 'relative';

        //         var grip = document.createElement('div');
        //         grip.innerHTML = "&nbsp;";
        //         grip.style.top = 0;
        //         grip.style.right = 0;
        //         grip.style.bottom = 0;
        //         grip.style.width = '5px';
        //         grip.style.position = 'absolute';
        //         grip.style.cursor = 'col-resize';
        //         grip.addEventListener('mousedown', function (e) {
        //             thElm = th;
        //             startOffset = th.offsetWidth - e.pageX;
        //         });

        //         th.appendChild(grip);
        //     });

        //     document.addEventListener('mousemove', function (e) {
        //     if (thElm) {
        //         thElm.style.width = startOffset + e.pageX + 'px';
        //     }
        //     });

        //     document.addEventListener('mouseup', function () {
        //         thElm = undefined;
        //     });
        // })();  
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .selection-control {
        
        padding: 6px 0px 0px 13px;
        background: white;
        height: 40px;

        .select-all {
            color: $gray-light;
            font-size: 14px;
            &:hover {
                color: $gray-light;
            }
        }
    }

    .table {
        width: 100%;
        border-collapse: separate;
        
        th {
            position: sticky;
            position: -webkit-sticky;
            background-color: white;
            border-bottom: 1px solid $tainacan-input-background;
            top: 0px;
            z-index: 9;
            padding: 10px;
            vertical-align: bottom;

            &.actions-header {
                min-width: 8.333333333%;
            }
        }

        .checkbox-cell {
            min-width: 40px;
            width: 40px;
            padding: 0;
            position: sticky !important;
            position: -webkit-sticky !important;
            left: 0;
            top: auto;
            display: table-cell;
            
            &::before {
                box-shadow: inset 50px 0 10px -12px #222;
                content: " ";
                width: 50px;
                height: 100%;
                position: absolute;
                left: 0;
                top: 0;
                visibility: hidden;
            }

            label.checkbox {  
                border-radius: 0px;
                background-color: white;
                padding: 0;
                width: 100%;
                height: 100%; 
                display: flex;
                justify-content: center;
                visibility: hidden;
            }
            label span.control-label {
                display: none;
            }
            &.is-selecting {
                .checkbox { visibility: visible; }
                &::before { visibility: visible !important; }
            }
        }
        // Only to be used in case we can implement Column resizing
        // th:not(:last-child) {
        //     border-right: 1px solid $tainacan-input-background !important;
        // }

        .thumbnail-cell {
            width: 60px;
        }
  
        tbody {
            tr {
                cursor: pointer;
                background-color: transparent;

                &.selected-row { 
                    background-color: $primary-lighter; 
                    .checkbox-cell .checkbox, .actions-cell .actions-container {
                        background-color: $primary-lighter;
                    }
                }
                td {
                    height: 60px;
                    max-height: 60px;
                    padding: 10px;
                    vertical-align: middle;
                    line-height: 12px;
                    border: none;
                    p { 
                        font-size: 14px; 
                        margin: 0px;
                    }
                    
                }
                td.column-default-width{
                    max-width: 300px;
                    p {
                        text-overflow: ellipsis;
                        overflow-x: hidden;
                        white-space: nowrap;
                    }
                }
                img.table-thumb {
                    max-height: 37px !important;
                    border-radius: 3px;
                }

                td.table-creation p {
                    color: $gray-light;
                    font-size: 11px;
                    line-height: 1.5;
                }

                td.actions-cell {
                    padding: 0px;
                    position: sticky !important;
                    position: -webkit-sticky !important;
                    right: 0px;
                    top: auto;
                    width: 80px;

                    .actions-container {
                        visibility: hidden;
                        display: flex;
                        position: relative;
                        padding: 0;
                        height: 100%;
                        width: 80px;
                        z-index: 9;
                        background-color: transparent; 
                        float: right;
                    }

                    a {
                        margin: auto;
                        font-size: 18px !important;
                    }

                }

                &:hover {
                    background-color: $tainacan-input-background !important;
                    cursor: pointer;

                    .checkbox-cell {
                        &::before { visibility: visible; }
                        .checkbox { 
                            visibility: visible; 
                            background-color: $tainacan-input-background !important; 
                        }
                    }
                    .actions-cell {
                        .actions-container {
                            visibility: visible;
                            background: $tainacan-input-background !important;
                        }

                        &::after {
                            box-shadow: inset -97px 0 17px -21px #222;
                            content: " ";
                            width: 100px;
                            height: 100%;
                            position: absolute;
                            right: 0px;
                            top: 0;
                        }
                    }

                }
            }
        }
    }
    
</style>


