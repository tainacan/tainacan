<template>
    <div>
        <div class="repository-level-page page-container">
            <tainacan-title />
            <div 
                    class="sub-header" 
                    v-if="$userCaps.hasCapability('edit_tainacan-taxonomies')">
                <div class="header-item">
                    <router-link
                            id="button-create-taxonomy" 
                            tag="button" 
                            class="button is-secondary"
                            :to="{ path: $routerHelper.getNewTaxonomyPath() }">
                        {{ $i18n.getFrom('taxonomies', 'new_item') }}
                    </router-link>
                </div>
            </div>

            <div class="above-subheader">
                <b-loading
                        :is-full-page="true" 
                        :active.sync="isLoading" 
                        :can-cancel="false"/>
                <div class="tabs">
                    <ul>
                        <li 
                                @click="onChangeTab('')"
                                :class="{ 'is-active': status == undefined || status == ''}"><a>{{ $i18n.get('label_all_items') }}</a></li>
                        <li 
                                @click="onChangeTab('draft')"
                                :class="{ 'is-active': status == 'draft'}"><a>{{ $i18n.get('label_draft_items') }}</a></li>
                        <li 
                                @click="onChangeTab('trash')"
                                :class="{ 'is-active': status == 'trash'}"><a>{{ $i18n.get('label_trash_items') }}</a></li>
                    </ul>
                </div>
                <div>
                    <taxonomies-list
                            :is-loading="isLoading"
                            :total="total"
                            :is-on-trash="status == 'trash'"
                            :page="page"
                            :taxonomies-per-page="taxonomiesPerPage"
                            :taxonomies="taxonomies"/>
                    
                    <!-- Empty state image -->
                    <div v-if="taxonomies.length <= 0 && !isLoading">
                        <section class="section">
                            <div class="content has-text-grey has-text-centered">
                                <p>
                                    <taxonomies-icon class="taxonomies-term-icon"/>
                                </p>
                                <p v-if="status == undefined || status == ''">{{ $i18n.get('info_no_taxonomy_created') }}</p>
                                <p v-if="status == 'draft'">{{ $i18n.get('info_no_taxonomy_draft') }}</p>
                                <p v-if="status == 'trash'">{{ $i18n.get('info_no_taxonomy_trash') }}</p>
                                <router-link
                                        v-if="status == undefined || status == ''"
                                        id="button-create-taxonomy"
                                        tag="button"
                                        class="button is-secondary"
                                        :to="{ path: $routerHelper.getNewTaxonomyPath() }">
                                    {{ $i18n.getFrom('taxonomies', 'new_item') }}
                                </router-link>
                            </div>
                        </section>
                    </div>
                    <!-- Footer -->
                    <div 
                            class="pagination-area" 
                            v-if="taxonomies.length > 0">
                        <div class="shown-items">
                            {{
                                $i18n.get('info_showing_taxonomies') +
                                (taxonomiesPerPage * (page - 1) + 1) +
                                $i18n.get('info_to') +
                                getLastTaxonomyNumber() +
                                $i18n.get('info_of') + total + '.'
                            }}
                        </div>
                        <div class="items-per-page">
                            <b-field 
                                    horizontal 
                                    :label="$i18n.get('label_taxonomies_per_page')">
                                <b-select
                                        :value="taxonomiesPerPage"
                                        @input="onChangePerPage"
                                        :disabled="taxonomies.length <= 0">
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
                                    :total="total"
                                    :current.sync="page"
                                    order="is-centered"
                                    size="is-small"
                                    :per-page="taxonomiesPerPage"/>
                        </div>
                    </div>
                </div>
            </div>    
        </div>
    </div>
</template>

<script>
    import TaxonomiesList from "../../components/lists/taxonomies-list.vue";
    import TaxonomiesIcon from '../../components/other/taxonomies-icon.vue';
    import { mapActions, mapGetters } from 'vuex';
    //import moment from 'moment'

    export default {
        name: 'Page',
        data(){
            return {
                isLoading: false,
                total: 0,
                page: 1,
                taxonomiesPerPage: 12,
                status: ''
            }
        },
        components: {
            TaxonomiesList,
            TaxonomiesIcon
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetch',
            ]),
            ...mapGetters('taxonomy', [
                'get'
            ]),
            onChangeTab(status) {
                this.status = status;
                this.load();
            },
            onChangePerPage(value) {
                if (value != this.taxonomiesPerPage) { 
                    this.$userPrefs.set('taxonomies_per_page', value)
                        .then((newValue) => {
                            this.taxonomiesPerPage = newValue;
                        })
                        .catch(() => {
                            this.$console.log("Error settings user prefs for taxonomies per page")
                        });

                }
                this.taxonomiesPerPage = value;
                this.load();
            },
            onPageChange(page) {
                this.page = page;
                this.load();
            },
            load() {
                this.isLoading = true;

                this.fetch({ 'page': this.page, 'taxonomiesPerPage': this.taxonomiesPerPage, 'status': this.status })
                    .then((res) => {
                        this.isLoading = false;
                        this.total = res.total;
                    })
                    .catch(() => {
                        this.isLoading = false;
                    });
            },
            getLastTaxonomyNumber() {
                let last = (Number(this.taxonomiesPerPage * (this.page - 1)) + Number(this.taxonomiesPerPage));
                return last > this.total ? this.total : last;
            }
        },
        computed: {
            taxonomies(){
                return this.get();
            }
        },
        created() {
            this.taxonomiesPerPage = this.$userPrefs.get('taxonomies_per_page');
        },
        mounted(){
            if (this.taxonomiesPerPage != this.$userPrefs.get('taxonomies_per_page'))
                this.taxonomiesPerPage = this.$userPrefs.get('taxonomies_per_page');

            if (!this.taxonomiesPerPage) {
                this.taxonomiesPerPage = 12;
                this.$userPrefs.set('taxonomies_per_page', 12);
            }
            
            this.load();
        }
    }
</script>

<style lang="scss" scoped>
    .taxonomies-icon {
        height: 24px;
        width: 24px;
    }
    @import '../../scss/_variables.scss';

    .sub-header {
        max-height: $subheader-height;
        height: $header-height;
        margin-left: -$page-side-padding;
        margin-right: -$page-side-padding;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        border-bottom: 1px solid #ddd;

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
    .tabs {
        padding-top: 20px;
        margin-bottom: 20px;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
    }
    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        height: auto;
    }
</style>


