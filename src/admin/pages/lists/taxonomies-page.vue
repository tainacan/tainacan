<template>
    <div>
        <div class="repository-level-page page-container">
            <tainacan-title 
                    :bread-crumb-items="[{ path: '', label: this.$i18n.get('taxonomies') }]"/>
            <div 
                    class="sub-header" 
                    v-if="$userCaps.hasCapability('edit_tainacan-taxonomies')">

                <!-- New Taxonomy Button ----  -->
                <div class="header-item">
                    <router-link
                            id="button-create-taxonomy" 
                            tag="button" 
                            class="button is-secondary"
                            :to="{ path: $routerHelper.getNewTaxonomyPath() }">
                        {{ $i18n.getFrom('taxonomies', 'new_item') }}
                    </router-link>
                </div>

                <!-- Sorting options ----  -->
                <b-field class="header-item">
                    <label class="label">{{ $i18n.get('label_sort') }}</label>
                    <b-dropdown
                            :mobile-modal="true"
                            :disabled="taxonomies.length <= 0 || isLoading"
                            @input="onChangeOrder(order == 'asc' ? 'desc' : 'asc')"
                            aria-role="list">
                        <button
                                :aria-label="$i18n.get('label_sorting_direction')"
                                class="button is-white"
                                slot="trigger">
                            <span class="icon is-small gray-icon">
                                <i 
                                        :class="order == 'desc' ? 'tainacan-icon-sortdescending' : 'tainacan-icon-sortascending'"
                                        class="tainacan-icon tainacan-icon-18px"/>
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown" />
                            </span>
                        </button>
                        <b-dropdown-item
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': order == 'desc' }"
                                :value="'desc'"
                                aria-role="listitem"
                                style="padding-bottom: 0.45rem">
                            <span class="icon is-small gray-icon">
                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortdescending"/>
                            </span>
                            {{ $i18n.get('label_descending') }}
                        </b-dropdown-item>
                        <b-dropdown-item
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': order == 'asc' }"
                                :value="'asc'"
                                aria-role="listitem"
                                style="padding-bottom: 0.45rem">
                            <span class="icon is-small gray-icon">
                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortascending"/>
                            </span>
                            {{ $i18n.get('label_ascending') }}
                        </b-dropdown-item>
                    </b-dropdown>
                    <span
                            class="label"
                            style="padding-left: 0.65rem;">
                        {{ $i18n.get('info_by_inner') }}
                    </span>
                    <b-select
                            class="sorting-select"
                            :disabled="taxonomies.length <= 0"
                            @input="onChangeOrderBy($event)"
                            :value="orderBy"
                            :label="$i18n.get('label_sorting')">
                        <option
                                v-for="(option, index) in sortingOptions"
                                :value="option.value"
                                :key="index">
                            {{ option.label }}
                        </option>
                    </b-select>
                </b-field>
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
                                :class="{ 'is-active': status == undefined || status == ''}"
                                v-tooltip="{
                                    content: $i18n.get('info_taxonomies_tab_all'),
                                    autoHide: true,
                                    placement: 'auto',
                                }">
                            <a :style="{ fontWeight: 'bold', color: '#454647 !important', lineHeight: '1.5rem' }">
                                {{ `${$i18n.get('label_all_taxonomies')}` }}
                                <span class="has-text-gray">&nbsp;{{ repositoryTotalTaxonomies ? `(${Number(repositoryTotalTaxonomies.private) + Number(repositoryTotalTaxonomies.publish)})` : '' }}</span>
                            </a>
                        </li>
                        <li 
                                v-for="(statusOption, index) of $statusHelper.getStatuses()"
                                :key="index"
                                @click="onChangeTab(statusOption.slug)"
                                :class="{ 'is-active': status == statusOption.slug}"
                                :style="{ marginRight: statusOption.slug == 'private' ? 'auto' : '', marginLeft: statusOption.slug == 'draft' ? 'auto' : '' }"
                                v-tooltip="{
                                    content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('taxonomies')]),
                                    autoHide: true,
                                    placement: 'auto',
                                }">
                            <a>
                                <span 
                                        v-if="$statusHelper.hasIcon(statusOption.slug)"
                                        class="icon has-text-gray">
                                    <i 
                                            class="tainacan-icon tainacan-icon-18px"
                                            :class="$statusHelper.getIcon(statusOption.slug)"
                                            />
                                </span>
                                {{ statusOption.name }}
                                <span class="has-text-gray">&nbsp;{{ repositoryTotalTaxonomies ? `(${repositoryTotalTaxonomies[statusOption.slug]})` : '' }}</span>
                            </a>
                        </li>
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
                                <span class="icon is-medium">
                                    <i class="tainacan-icon tainacan-icon-36px tainacan-icon-terms"/>
                                </span>
                                <p v-if="status == undefined || status == ''">{{ $i18n.get('info_no_taxonomy_created') }}</p>
                                <p
                                        v-for="(statusOption, index) of $statusHelper.getStatuses()"
                                        :key="index"
                                        v-if="status == statusOption.slug">
                                    {{ $i18n.get('info_no_taxonomies_' + statusOption.slug) }}
                                </p>
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
                                    :per-page="taxonomiesPerPage"
                                    :aria-next-label="$i18n.get('label_next_page')"
                                    :aria-previous-label="$i18n.get('label_previous_page')"
                                    :aria-page-label="$i18n.get('label_page')"
                                    :aria-current-label="$i18n.get('label_current_page')"/>
                       
                        </div>
                    </div>
                </div>
            </div>    
        </div>
    </div>
</template>

<script>
    import TaxonomiesList from "../../components/lists/taxonomies-list.vue";
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
                status: '',
                order: 'asc',
                ordeBy: 'date',
                sortingOptions: [
                    { label: this.$i18n.get('label_title'), value: 'title' },
                    { label: this.$i18n.get('label_creation_date'), value: 'date' },
                ]
            }
        },
        components: {
            TaxonomiesList
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetch',
            ]),
            ...mapGetters('taxonomy', [
                'get',
                'getRepositoryTotalTaxonomies'
            ]),
            onChangeTab(status) {
                this.status = status;
                this.load();
            },
            onChangeOrder(newOrder) {
                if (newOrder != this.order) { 
                    this.$userPrefs.set('taxonomies_order', newOrder)
                        .then((newOrder) => {
                            this.order = newOrder;
                        })
                        .catch(() => {
                            this.$console.log("Error settings user prefs for taxonomies order")
                        });

                }
                this.order = newOrder;
                this.load();
            },
            onChangeOrderBy(newOrderBy) {
                if (newOrderBy != this.orderBy) { 
                    this.$userPrefs.set('taxonomies_order_by', newOrderBy)
                        .then((newOrderBy) => {
                            this.orderBy = newOrderBy;
                        })
                        .catch(() => {
                            this.$console.log("Error settings user prefs for taxonomies orderby")
                        });
                }
                this.orderBy = newOrderBy;
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

                this.fetch({ 
                    page: this.page, 
                    taxonomiesPerPage: this.taxonomiesPerPage, 
                    status: this.status, 
                    order: this.order,
                    orderby: this.orderBy
                })
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
            },
            repositoryTotalTaxonomies(){
                return this.getRepositoryTotalTaxonomies();
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

            if (this.order != this.$userPrefs.get('taxonomies_order'))
                this.order = this.$userPrefs.get('taxonomies_order');
            if (!this.order) {
                this.order = 'asc';
                this.$userPrefs.set('taxonomies_order', 'asc');
            }


            if (this.orderBy != this.$userPrefs.get('taxonomies_order_by'))
                this.orderBy = this.$userPrefs.get('taxonomies_order_by');
            if (!this.orderBy) {
                this.orderBy = 'title';
                this.$userPrefs.set('taxonomies_order_by', 'title');
            }
            
            this.load();
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../scss/_variables.scss';

    .sub-header {
        min-height: $subheader-height;
        height: $header-height;
        padding-left: 0;
        padding-right: 0;
        border-bottom: 1px solid #ddd;
        display: inline-flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;

        .header-item {

            &:not(:last-child) {
                padding-right: 0.5em;
            }

            .label {
                font-size: 0.875rem;
                font-weight: normal;
                margin-top: 3px;
                margin-bottom: 2px;
                cursor: default;
            }

            .button {
                display: flex;
                align-items: center;
            }
            
            .field {
                align-items: center;
            }

            .gray-icon, .gray-icon .icon {
                color: $gray4 !important;
                padding-right: 10px;
            }
            .gray-icon .icon i::before, 
            .gray-icon i::before {
                font-size: 1.3125rem !important;
                max-width: 26px;
            }
        }

        @media screen and (max-width: 769px) {
            height: 60px;
            margin-top: -0.5em;
            padding-top: 0.9em;

            .header-item:not(:last-child) {
                padding-right: 0.2em;
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


