<template>
    <div 
            v-if="totalCategories > 0 && !isLoading"
            class="table-container">

        <div class="selection-control">
            <div class="field select-all is-pulled-left">
                <span>
                    <b-checkbox 
                            @click.native="selectAllCategoriesOnPage()" 
                            :value="allCategoriesOnPageSelected">{{ $i18n.get('label_select_all_categories_page') }}</b-checkbox>
                </span>
            </div>
            <div class="field is-pulled-right">
                <b-dropdown
                        position="is-bottom-left"
                        v-if="categories[0].current_user_can_edit"
                        :disabled="!isSelectingCategories"
                        id="bulk-actions-dropdown">
                    <button
                            class="button is-white"
                            slot="trigger">
                        <span>{{ $i18n.get('label_bulk_actions') }}</span>
                        <b-icon icon="menu-down"/>
                    </button> 

                    <b-dropdown-item
                            id="item-delete-selected-items"
                            @click="deleteSelectedCategories()">
                        {{ $i18n.get('label_delete_selected_categories') }}
                    </b-dropdown-item>
                    <b-dropdown-item disabled>{{ $i18n.get('label_edit_selected_categories') + ' (Not ready)' }}
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
                        <!-- Name -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_name') }}</div>
                        </th>
                        <!-- Description -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_description') }}</div>
                        </th>
                        <!-- Actions -->
                        <th class="actions-header">
                            &nbsp;
                            <!-- nothing to show on header for actions cell-->
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr     
                            :class="{ 'selected-row': selectedCategories[index] }"
                            :key="index"
                            v-for="(category, index) of categories">
                        <!-- Checking list -->
                        <td 
                                :class="{ 'is-selecting': isSelectingCategories }"
                                class="checkbox-cell">
                            <b-checkbox 
                                    size="is-small"
                                    v-model="selectedCategories[index]"/> 
                        </td>
                        <!-- Name -->
                        <td 
                                class="column-default-width column-main-content"
                                @click="goToCategoryEditPage(category.id)"
                                :label="$i18n.get('label_name')" 
                                :aria-label="$i18n.get('label_name') + ': ' + category.name">
                            <p>{{ category.name }}</p>
                        </td>
                        <!-- Description -->
                        <td
                                class="column-large-width" 
                                @click="goToCategoryEditPage(category.id)"
                                :label="$i18n.get('label_description')" 
                                :aria-label="$i18n.get('label_description') + ': ' + category.description">
                            <p>{{ category.description }}</p>
                        </td>
                        <!-- Actions -->
                        <td 
                                @click="goToCategoryEditPage(category.id)"
                                class="actions-cell column-default-width" 
                                :label="$i18n.get('label_actions')">
                            <div class="actions-container">
                                <a 
                                        id="button-edit" 
                                        :aria-label="$i18n.getFrom('categories','edit_item')" 
                                        @click="goToCategoryEditPage(category.id)">
                                    <b-icon 
                                            type="is-secondary" 
                                            icon="pencil"/>
                                </a>
                                <a 
                                        id="button-delete" 
                                        :aria-label="$i18n.get('label_button_delete')" 
                                        @click.prevent.stop="deleteOneCategory(category.id)">
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
        name: 'CategoriesList',
        data() {
            return {
                selectedCategories: [],
                allCategoriesOnPageSelected: false,
                isSelectingCategories: false
            }
        },
        props: {
            isLoading: false,
            totalCategories: 0,
            page: 1,
            categoriesPerPage: 12,
            categories: Array
        },
        watch: {
            categories() {
                this.selectedCategories = [];
                for (let i = 0; i < this.categories.length; i++)
                    this.selectedCategories.push(false);    
            },
            selectedCategories() {
                let allSelected = true;
                let isSelecting = false;
                for (let i = 0; i < this.selectedCategories.length; i++) {
                    if (this.selectedCategories[i] == false) {
                        allSelected = false;
                    } else {
                        isSelecting = true;
                    }
                }
                this.allCategoriesOnPageSelected = allSelected;
                this.isSelectingCategories = isSelecting;
            }
        },
        methods: {
            ...mapActions('category', [
                'deleteCategory'
            ]),
            selectAllCategoriesOnPage() {
                for (let i = 0; i < this.selectedCategories.length; i++) 
                    this.selectedCategories.splice(i, 1, !this.allCategoriesOnPageSelected);
            },
            deleteOneCategory(categoryId) {
                this.$dialog.confirm({
                    message: this.$i18n.get('info_warning_category_delete'),
                    onConfirm: () => {
                        this.deleteCategory(categoryId)
                            .then(() => {
                                // this.$toast.open({
                                //     duration: 3000,
                                //     message: this.$i18n.get('info_category_deleted'),
                                //     position: 'is-bottom',
                                //     type: 'is-secondary',
                                //     queue: true
                                // });
                                for (let i = 0; i < this.selectedCategories.length; i++) {
                                    if (this.selectedCategories[i].id === this.categoryId)
                                        this.selectedCategories.splice(i, 1);
                                }
                            })
                            .catch(() => {
                                // this.$toast.open({
                                //     duration: 3000,
                                //     message: this.$i18n.get('info_error_deleting_category'),
                                //     position: 'is-bottom',
                                //     type: 'is-danger',
                                //     queue: true
                                // });
                            });
                    }
                });
            },
            deleteSelectedCategories() {
                this.$dialog.confirm({
                    message: this.$i18n.get('info_warning_selected_categories_delete'),
                    onConfirm: () => {

                        for (let i = 0; i < this.categories.length;  i++) {
                            if (this.selectedCategories[i]) {
                                this.deleteCategory(this.categories[i].id)
                                    .then(() => {
                                        // this.loadCategories();
                                        // this.$toast.open({
                                        //     duration: 3000,
                                        //     message: this.$i18n.get('info_category_deleted'),
                                        //     position: 'is-bottom',
                                        //     type: 'is-secondary',
                                        //     queue: false
                                        // })
                                    }).catch(() => {
                                    // this.$toast.open({
                                    //     duration: 3000,
                                    //     message: this.$i18n.get('info_error_deleting_category'),
                                    //     position: 'is-bottom',
                                    //     type: 'is-danger',
                                    //     queue: false
                                    // });
                                });
                            }
                        }
                        this.allCategoriesOnPageSelected = false;
                    }
                });
            },
            goToCategoryPage(categoryId) {
                this.$router.push(this.$routerHelper.getCategoryPath(categoryId));
            },
            goToCategoryEditPage(categoryId) {
                this.$router.push(this.$routerHelper.getCategoryEditPath(categoryId));
            }
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
            }
            label span.control-label {
                display: none;
            }
            &.is-selecting {
                position: sticky !important;
                position: -webkit-sticky !important;
                &::before { visibility: visible !important; }
            }
        }
        // Only to be used in case we can implement Column resizing
        // th:not(:last-child) {
        //     border-right: 1px solid $tainacan-input-background !important;
        // }

        .thumbnail-cell {
            width: 60px;
            text-align: center;
        }
  
        .column-small-width {
            min-width: 80px;
            max-width: 80px;
            p {
                color: $gray-light;
                font-size: 11px;
                line-height: 1.5;
            }
        }
        .column-default-width {
            min-width: 80px;
            max-width: 160px;
            p {
                color: $gray-light;
                font-size: 11px;
                line-height: 1.5;
            }
        }
        .column-medium-width {
            min-width: 120px;
            max-width: 200px;
            p {
                color: $gray-light;
                font-size: 11px;
                line-height: 1.5;
            }
        }
        .column-large-width {
            min-width: 120px;
            max-width: 240px;
            p {
                color: $gray-light;
                font-size: 11px;
                line-height: 1.5;
            }
        }
        .column-main-content {
            min-width: 120px !important;
            max-width: 240px !important;
            p { 
                font-size: 14px !important;
                color: $tainacan-input-color !important;
                margin: 0px !important; 
            }
        }
        .column-needed-width {
            max-width: unset !important;
        }
        .column-align-right {
            text-align: right !important;
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
                        position: sticky !important;
                        position: -webkit-sticky !important;
                        
                        &::before { visibility: visible; }
                        
                        .checkbox {  
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


