<template>
    <div class="table-container">
        <b-field 
                grouped 
                group-multiline>
            <button
                    v-if="selectedCategories.length > 0"
                    class="button field is-danger"
                    @click="deleteSelectedCategories()">
                <span>{{ $i18n.get('instruction_delete_selected_categories') }} </span>
                <b-icon icon="delete"/>
            </button>
        </b-field>

        <b-table
                v-if="totalCategories > 0"
                ref="categoryTable"
                :data="categories"
                :checked-rows.sync="selectedCategories"
                checkable
                :loading="isLoading"
                hoverable
                striped
                selectable
                backend-sorting>

            <template slot-scope="props">
                <b-table-column
                        tabindex="0"
                        :label="$i18n.get('label_name')"
                        :aria-label="$i18n.get('label_name')"
                        field="props.row.name">
                    <router-link
                            class="clickable-row"
                            tag="span"
                            :to="{path: $routerHelper.getCategoryEditPath(props.row.id)}">
                        {{ props.row.name }}
                    </router-link>
                </b-table-column>

                <b-table-column
                        tabindex="0"
                        :aria-label="$i18n.get('label_description')"
                        :label="$i18n.get('label_description')"
                        property="description"
                        show-overflow-tooltip
                        field="props.row.description">
                    <router-link 
                            class="clickable-row" 
                            tag="span" 
                            :to="{path: $routerHelper.getCategoryEditPath(props.row.id)}">
                        {{ props.row.description }}
                    </router-link>
                </b-table-column>

                <b-table-column
                        tabindex="0"
                        :label="$i18n.get('label_actions')"
                        width="78"
                        :aria-label="$i18n.get('label_actions')">
                    <!-- <a id="button-view" :aria-label="$i18n.get('label_button_view')" @click.prevent.stop="goToCollectionPage(props.row.id)"><b-icon icon="eye"></a> -->
                    <a
                            id="button-edit"
                            :aria-label="$i18n.getFrom('categories','edit_item')"
                            @click.prevent.stop="goToCategoryEditPage(props.row.id)">
                        <b-icon 
                                type="is-gray" 
                                icon="pencil" />
                    </a>
                    <a
                            id="button-delete"
                            :aria-label="$i18n.get('label_button_delete')"
                            @click.prevent.stop="deleteOneCategory(props.row.id)">
                        <b-icon 
                                type="is-gray" 
                                icon="delete" />
                    </a>
                </b-table-column>
            </template>

        </b-table>

        <div v-if="(!totalCategories || totalCategories <= 0) && !isLoading">
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <b-icon
                                icon="inbox"
                                size="is-large"/>
                    </p>
                    <p>{{ $i18n.get('info_no_category_created') }}</p>
                    <router-link 
                            tag="button" 
                            class="button is-secondary"
                            :to="{ path: $routerHelper.getNewCategoryPath() }">
                        {{ $i18n.getFrom('taxonomies', 'new_item') }}
                    </router-link>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'

    export default {
        name: 'CategoriesList',
        props: {
            isLoading: false,
            totalCategories: 0,
            page: 1,
            categoriesPerPage: 12,
            categories: Array
        },
        data() {
            return {
                selectedCategories: []
            }
        },
        methods: {
            ...mapActions('category', [
                'deleteCategory'
            ]),
            deleteOneCategory(categoryId) {
                this.$dialog.confirm({
                    message: this.$i18n.get('info_warning_category_delete'),
                    onConfirm: () => {
                        this.deleteCategory(categoryId)
                            .then(() => {
                                this.$toast.open({
                                    duration: 3000,
                                    message: this.$i18n.get('info_category_deleted'),
                                    position: 'is-bottom',
                                    type: 'is-secondary',
                                    queue: true
                                });
                                for (let i = 0; i < this.selectedCategories.length; i++) {
                                    if (this.selectedCategories[i].id === this.categoryId)
                                        this.selectedCategories.splice(i, 1);
                                }
                            })
                            .catch(() => {
                                this.$toast.open({
                                    duration: 3000,
                                    message: this.$i18n.get('info_error_deleting_category'),
                                    position: 'is-bottom',
                                    type: 'is-danger',
                                    queue: true
                                });
                            });
                    }
                });
            },
            deleteSelectedCategories() {
                this.$dialog.confirm({
                    message: this.$i18n.get('info_selected_categories_delete'),
                    onConfirm: () => {

                        for (let category of this.selectedCategories) {
                            this.deleteCategory(category.id)
                                .then(() => {
                                    this.loadCategories();
                                    this.$toast.open({
                                        duration: 3000,
                                        message: this.$i18n.get('info_category_deleted'),
                                        position: 'is-bottom',
                                        type: 'is-secondary',
                                        queue: false
                                    })
                                }).catch(() => {
                                this.$toast.open({
                                    duration: 3000,
                                    message: this.$i18n.get('info_error_deleting_category'),
                                    position: 'is-bottom',
                                    type: 'is-danger',
                                    queue: false
                                });
                            });
                        }
                        this.selectedCategories = [];
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

    .table-thumb {
        max-height: 38px !important;
        vertical-align: middle !important;
    }

    .row-creation span {
        color: $gray-light;
        font-size: 0.75em;
        line-height: 1.5
    }

    .clickable-row{ cursor: pointer !important; }

</style>


