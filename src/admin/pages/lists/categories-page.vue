<template>
    <div>
        <div class="primary-page page-container">
            <tainacan-title />
            <div 
                    class="sub-header" 
                    v-if="totalCategories > 0">
                <div class="header-item">
                    <router-link
                            id="button-create-category" 
                            tag="button" 
                            class="button is-secondary"
                            :to="{ path: $routerHelper.getNewCategoryPath() }">
                        {{ $i18n.getFrom('taxonomies', 'new_item') }}
                    </router-link>
                </div>
            </div>

            <div class="above-subheader">

                <categories-list
                        :is-loading="isLoading"
                        :total-categories="totalCategories"
                        :page="page"
                        :categories-per-page="categoriesPerPage"
                        :categories="categories"/>

                <!-- Footer -->
                <div 
                        class="pagination-area" 
                        v-if="totalCategories > 0">
                    <div class="shown-items">
                        {{
                            $i18n.get('info_showing_categories') +
                            (categoriesPerPage * (page - 1) + 1) +
                            $i18n.get('info_to') +
                            getLastCategoryNumber() +
                            $i18n.get('info_of') + totalCategories + '.'
                        }}
                    </div>
                    <div class="items-per-page">
                        <b-field 
                                horizontal 
                                :label="$i18n.get('label_categories_per_page')">
                            <b-select
                                    :value="categoriesPerPage"
                                    @input="onChangeCategoriesPerPage"
                                    :disabled="categories.length <= 0">
                                <option value="12">12</option>
                                <option value="24">24</option>
                                <option value="48">48</option>
                                <option value="96">96</option>
                            </b-select>
                        </b-field>
                    </div>
                    <div class="pagination">
                        <b-pagination
                                @change="onPageChange"
                                :total="totalCategories"
                                :current.sync="page"
                                order="is-centered"
                                size="is-small"
                                :per-page="categoriesPerPage"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import CategoriesList from "../../components/lists/categories-list.vue";
    import { mapActions, mapGetters } from 'vuex';
    //import moment from 'moment'

    export default {
        name: 'CategoriesPage',
        data(){
            return {
                isLoading: false,
                totalCategories: 0,
                page: 1,
                categoriesPerPage: 12
            }
        },
        components: {
            CategoriesList
        },
        methods: {
            ...mapActions('category', [
                'fetchCategories',
            ]),
            ...mapGetters('category', [
                'getCategories'
            ]),
            onChangeCategoriesPerPage(value) {
                let prevValue = this.categoriesPerPage;
                this.categoriesPerPage = value;
                this.$userPrefs.set('categories_per_page', value,  prevValue);
                this.loadCategories();
            },
            onPageChange(page) {
                this.page = page;
                this.loadCategories();
            },
            loadCategories() {
                this.isLoading = true;

                this.fetchCategories({ 'page': this.page, 'categoriesPerPage': this.categoriesPerPage })
                    .then((res) => {
                        this.isLoading = false;
                        this.totalCategories = res.total;
                    })
                    .catch(() => {
                        this.isLoading = false;
                    });
            },
            getLastCategoryNumber() {
                let last = (Number(this.categoriesPerPage * (this.page - 1)) + Number(this.categoriesPerPage));
                return last > this.totalCategories ? this.totalCategories : last;
            }
        },
        computed: {
            categories(){
                return this.getCategories();
                // for (let category of categories)
                //     category['creation'] = this.$i18n.get('info_created_by') +
                //         category['author_name'] + '<br>' + this.$i18n.get('info_date') +
                //         moment(category['creation_date'], 'YYYY-MM-DD').format('DD/MM/YYYY');
            }
        },
        created() {
            this.$userPrefs.get('categories_per_page')
                .then((value) => {
                    this.categoriesPerPage = value;
                })
                .catch(() => {
                    this.$userPrefs.set('categories_per_page', 12, null);
                });
        },
        mounted(){
            this.loadCategories();
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../scss/_variables.scss';

    .sub-header {
        max-height: $subheader-height;
        height: $subheader-height;
        margin-left: -$page-side-padding;
        margin-right: -$page-side-padding;
        padding-top: $page-small-top-padding;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        border-bottom: 0.5px solid #ddd;

        .header-item {
            display: inline-block;
            padding-right: 8em;
        }

        @media screen and (max-width: 769px) {
            height: 60px;
            margin-top: -0.5em;
            padding-top: 0.9em;

            .header-item {
                padding-right: 0.5em;
            }
        }
    }

    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        min-height: 100%;
        height: auto;
    }
</style>


