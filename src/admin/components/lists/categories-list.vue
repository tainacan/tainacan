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
                            <p
                                    v-tooltip="{
                                        content: category.name,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }">
                                {{ category.name }}</p>
                        </td>
                        <!-- Description -->
                        <td
                                class="column-large-width" 
                                @click="goToCategoryEditPage(category.id)"
                                :label="$i18n.get('label_description')" 
                                :aria-label="$i18n.get('label_description') + ': ' + category.description">
                            <p
                                    v-tooltip="{
                                        content: category.description,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }">
                                {{ category.description }}</p>
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

</style>


