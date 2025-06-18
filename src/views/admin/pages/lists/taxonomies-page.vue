<template>
    <div class="tainacan-repository-level-colors page-container">

        <tainacan-external-link 
                :link-label="$i18n.get('label_view_taxonomies_on_website')"
                :link-url="repositoryTaxonomiesURL" />

        <tainacan-title :is-sticky="true">
            <h1>
                {{ $route.meta.title }}
            </h1>

            <!-- New Taxonomy Button ----  -->
            <div 
                    v-if="$userCaps.hasCapability('tnc_rep_edit_taxonomies')"
                    class="header-item">
                <router-link
                        v-slot="{ navigate }"
                        :to="{ path: $routerHelper.getNewTaxonomyPath() }"
                        custom>
                    <button 
                            id="button-create-taxonomy"
                            type="button"
                            role="link" 
                            class="button is-secondary"
                            @click="navigate()">
                        {{ $i18n.getFrom('taxonomies', 'new_item') }}
                    </button>
                </router-link>
            </div>

        </tainacan-title>
        
        <div class="sub-header tainacan-sub-header--sticky">

            <!-- Textual Search -------------->
            <b-field class="header-item">
                <b-input 
                        v-model="searchQuery"
                        :placeholder="$i18n.get('instruction_search')"
                        type="search"
                        size="is-small"
                        :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('taxonomies')"
                        autocomplete="on"
                        icon-right="magnify"
                        icon-right-clickable
                        @keyup.enter="searchTaxonomies()"
                        @icon-right-click="searchTaxonomies()" />
            </b-field>

            <!-- Sorting options ----  -->
            <b-field 
                    id="taxonomies-page-sorting-options"
                    class="header-item">
                <b-dropdown
                        ref="sortingDropdown" 
                        :mobile-modal="true"
                        :multiple="false"
                        class="show sorting-options-dropdown"
                        aria-role="list"
                        trap-focus
                        position="is-bottom-left"
                        :close-on-click="false"
                        :disabled="taxonomies.length <= 0 || isLoading"
                        @active-change="() => { newOrder = order; newOrderBy = orderBy; }">
                    <template #trigger>
                        <button
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: $i18n.getWithVariables('info_sorting_%s_by_%s', [order == 'asc' ? $i18n.get('label_ascending') : $i18n.get('label_descending'), orderByName]),
                                    autoHide: false,
                                    html: true,
                                    placement: 'auto-start',
                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']
                                }"
                                :aria-label="$i18n.get('label_sorting')"
                                class="button is-white">
                            <span class="is-small gray-icon">
                                <i 
                                        :class="order == 'desc' ? 'tainacan-icon-sortdescending' : 'tainacan-icon-sortascending'"
                                        class="tainacan-icon" />
                            </span>
                            &nbsp;
                            <span class="is-hidden-touch is-hidden-desktop-only">{{ $i18n.get('label_sorting') }}</span>
                            <span class="is-hidden-widescreen">{{ $i18n.get('label_sort') }}</span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                    </template>
                    <div class="sorting-options-container">
                        <div class="sorting-options-container-direction">
                            <b-dropdown-item
                                    aria-controls="taxonomies-list-results"
                                    role="button"
                                    :class="{ 'is-active': newOrder == 'desc' }"
                                    :value="'desc'"
                                    aria-role="listitem"
                                    @click="newOrder = 'desc'">
                                <span class="icon gray-icon">
                                    <i class="tainacan-icon tainacan-icon-sortdescending" />
                                </span>
                                <span>{{ $i18n.get('label_descending') }}</span>
                            </b-dropdown-item>
                            <b-dropdown-item
                                    aria-controls="taxonomies-list-results"
                                    role="button"
                                    :class="{ 'is-active': newOrder == 'asc' }"
                                    :value="'asc'"
                                    aria-role="listitem"
                                    @click="newOrder = 'asc'">
                                <span class="icon gray-icon">
                                    <i class="tainacan-icon tainacan-icon-sortascending" />
                                </span>
                                <span>{{ $i18n.get('label_ascending') }}</span>
                            </b-dropdown-item>
                        </div>
                        <div class="sorting-options-container-orderby">
                            <template 
                                    v-for="option in sortingOptions"
                                    :key="option.value">
                                <b-dropdown-item
                                        aria-controls="taxonomies-list-results"
                                        role="button"
                                        :class="{ 'is-active': newOrderBy == option.value }"
                                        :value="option.value"
                                        aria-role="listitem"
                                        @click="newOrderBy = option.value">
                                    {{ option.label }}
                                </b-dropdown-item>
                            </template>
                        </div>
                     </div>
                    <div class="dropdown-item-apply">
                        <button 
                                aria-controls="items-list-results"
                                class="button is-success"
                                @click="onChangeOrderAndOrderBy(newOrder, newOrderBy)">
                            {{ $i18n.get('label_apply_changes') }}
                        </button>
                    </div>  
                </b-dropdown>
            </b-field>

        </div>

        <div class="above-subheader">
            <b-loading
                    v-model="isLoading" 
                    :is-full-page="true" 
                    :can-cancel="false" />
            <div class="tabs">
                <ul>
                    <li 
                            v-tooltip="{
                                content: $i18n.get('info_taxonomies_tab_all'),
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']
                            }"
                            :class="{ 'is-active': status == undefined || status == ''|| status == 'publish,private,draft'}"
                            @click="onChangeTab('')">
                        <a style="font-weight: bold;">
                            {{ `${$i18n.get('label_all_taxonomies')}` }}
                            <span class="has-text-gray">&nbsp;{{ repositoryTotalTaxonomies ? `(${Number(repositoryTotalTaxonomies.private) + Number(repositoryTotalTaxonomies.publish) + Number(repositoryTotalTaxonomies.draft)})` : '' }}</span>
                        </a>
                    </li>
                    <li 
                            v-for="(statusOption, index) of statusOptionsForTaxonomies"
                            :key="index"
                            v-tooltip="{
                                content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('taxonomies')]),
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']
                            }"
                            :class="{ 'is-active': status == statusOption.slug}"
                            :style="{ marginRight: statusOption.slug == 'draft' ? 'auto' : '', marginLeft: statusOption.slug == 'trash' ? 'auto' : '' }"
                            @click="onChangeTab(statusOption.slug)">
                        <a>
                            <span 
                                    v-if="$statusHelper.hasIcon(statusOption.slug)"
                                    class="icon has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-1-125em"
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
                        :status="status"
                        :page="page"
                        :taxonomies-per-page="taxonomiesPerPage"
                        :taxonomies="taxonomies" />
                
                <!-- Empty state image -->
                <div v-if="taxonomies.length <= 0 && !isLoading">
                    <section class="section">
                        <div class="content has-text-grey has-text-centered">
                            <p>
                                <span class="icon is-medium">
                                    <i class="tainacan-icon tainacan-icon-30px tainacan-icon-terms" />
                                </span>
                            </p>
                            <p v-if="status == undefined || status == ''">
                                {{ $i18n.get('info_no_taxonomy_created') }}
                            </p>
                            <p v-else>
                                {{ $i18n.get('info_no_taxonomies_' + status) }}
                            </p>
                            <router-link
                                    v-if="status == undefined || status == ''"
                                    v-slot="{ navigate }"
                                    :to="{ path: $routerHelper.getNewTaxonomyPath() }"
                                    custom>
                                <button
                                        id="button-create-taxonomy"
                                        role="link"
                                        type="button"
                                        class="button is-secondary"
                                        @click="navigate()">
                                    {{ $i18n.getFrom('taxonomies', 'new_item') }}
                                </button>
                            </router-link>
                        </div>
                    </section>
                </div>
                <!-- Footer -->
                <div 
                        v-if="taxonomies.length > 0" 
                        class="pagination-area">
                    <div class="shown-items">
                        {{
                            $i18n.get('info_showing_taxonomies') + ' ' +
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
                                    :aria-label="$i18n.get('label_taxonomies_per_page')"
                                    :model-value="taxonomiesPerPage"
                                    :disabled="taxonomies.length <= 0"
                                    @update:model-value="onChangePerPage">
                                <option value="12">
                                    12
                                </option>
                                <option value="24">
                                    24
                                </option>
                                <option value="48">
                                    48
                                </option>
                                <option :value="maxTaxonomiesPerPage">
                                    {{ maxTaxonomiesPerPage }}
                                </option>
                            </b-select>
                        </b-field>
                    </div>
                    <div class="pagination">
                        <b-pagination
                                v-model="page"
                                :total="total"
                                order="is-centered"
                                size="is-small"
                                :per-page="taxonomiesPerPage"
                                :aria-next-label="$i18n.get('label_next_page')"
                                :aria-previous-label="$i18n.get('label_previous_page')"
                                :aria-page-label="$i18n.get('label_page')"
                                :aria-current-label="$i18n.get('label_current_page')"
                                @change="onPageChange" />
                    
                    </div>
                </div>
            </div>
        </div>    
    </div>
</template>

<script>
    import TaxonomiesList from "../../components/lists/taxonomies-list.vue";
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'TaxonomyPage',
        components: {
            TaxonomiesList
        },
        data(){
            return {
                isLoading: false,
                total: 0,
                page: 1,
                taxonomiesPerPage: 12,
                status: '',
                order: 'asc',
                orderBy: 'date',
                newOrder: 'asc',
                newOrderBy: 'date',
                searchQuery: '',
                sortingOptions: [
                    { label: this.$i18n.get('label_title'), value: 'title' },
                    { label: this.$i18n.get('label_creation_date'), value: 'date' },
                ],
                maxTaxonomiesPerPage: tainacan_plugin.api_max_items_per_page ? Number(tainacan_plugin.api_max_items_per_page) : 96,
                repositoryTaxonomiesURL: tainacan_plugin.theme_taxonomy_list_url
            }
        },
        computed: {
            ...mapGetters('taxonomy', {
                'taxonomies': 'get',
                'repositoryTotalTaxonomies': 'getRepositoryTotalTaxonomies'
            }),
            statusOptionsForTaxonomies() {
                return this.$statusHelper.getStatuses().filter((status) => status.slug != 'draft' && (status.slug != 'private' || (status.slug == 'private' && this.$userCaps.hasCapability('tnc_rep_read_private_taxonomies'))));
            },
            orderByName() {
                const selectedOrderOption = this.sortingOptions.find( aOption => aOption.value == this.orderBy);
                if ( selectedOrderOption )
                    return selectedOrderOption.label;

                return this.orderBy;
            },
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
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetch',
            ]),
            onChangeTab(status) {
                if ( status != this.status ) {
                    this.page = 1;
                    this.status = status;
                    this.load();
                }
            },
            onChangeOrderAndOrderBy(newOrder, newOrderBy) {
                this.$userPrefs.set('taxonomies_order', newOrder)
                    .then((newOrder) => {
                        this.order = newOrder;
                    })
                    .catch(() => {
                        this.$console.log("Error settings user prefs for taxonomies order")
                    });
                this.$userPrefs.set('taxonomies_order_by', newOrderBy)
                    .then((newOrderBy) => {
                        this.orderBy = newOrderBy;
                    })
                    .catch(() => {
                        this.$console.log("Error settings user prefs for taxonomies orderby")
                    });
                this.page = 1;
                this.order = newOrder;
                this.orderBy = newOrderBy;
                this.$refs['sortingDropdown'].toggle();
                this.load();
            },
            onChangePerPage(value) {
                if ( value != this.taxonomiesPerPage ) { 
                    this.$userPrefs.set('taxonomies_per_page', value)
                        .then((newValue) => {
                            this.taxonomiesPerPage = newValue;
                        })
                        .catch(() => {
                            this.$console.log("Error settings user prefs for taxonomies per page")
                        });
                    this.page = 1;
                    this.taxonomiesPerPage = value;
                    this.load();
                }
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
                    orderby: this.orderBy,
                    search: this.searchQuery
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
            },
            searchTaxonomies() {
                this.page = 1;
                this.load();
            }
        }
    }
</script>

<style lang="scss" scoped>

    .sub-header {
        gap: 8px;

        .header-item {
            margin-bottom: 0 !important;
            min-height: 1.875em;

            &:first-child {
                margin-right: auto;
            }
            &:not(:last-child) {
                padding-right: 0.875em;
            }

            .label {
                font-size: 0.875em;
                font-weight: normal;
                margin-top: 5px;
                margin-bottom: 2px;
                cursor: default;
                display: flex;
                align-items: center;
            }
            
            .field {
                align-items: center;
            }

            .gray-icon .icon i::before, 
            .gray-icon i::before {
                font-size: 1.3125em !important;
                color: var(--tainacan-info-color) !important;
                max-width: 1.25em;   
            }

            .icon {
                pointer-events: all;
                cursor: pointer;
                color: var(--tainacan-blue5);
                height: 27px;
                font-size: 1.125em !important;
                height: 1.75em;
            }

            .dropdown-menu {
                display: block;

                div.dropdown-content {
                    padding: 0;

                    .sorting-options-container,
                    .metadata-options-container {
                        max-height: 288px;
                        overflow: auto;
                    }
                    .sorting-options-container-direction {
                        position: sticky;
                        top: 0;
                        display: flex;
                        background-color: var(--tainacan-background-color);
                        border-bottom: 1px solid var(--tainacan-input-border-color);
                        padding: 8px 12px;
                        z-index: 1;

                        & > .dropdown-item {
                            border: 1px solid var(--tainacan-primary);

                            &:first-child {
                                border-top-left-radius: var(--tainacan-button-border-radius);
                                border-bottom-left-radius: var(--tainacan-button-border-radius);
                            }
                            &:last-child {
                                border-top-right-radius: var(--tainacan-button-border-radius);
                                border-bottom-right-radius: var(--tainacan-button-border-radius);
                            }
                        }
                    }
                    .dropdown-item {
                        padding: 0.25em 1.0em 0.25em 0.75em; 
                    }
                    .dropdown-item span{
                        vertical-align: middle;
                    }      
                    .dropdown-item-apply {
                        width: 100%;
                        border-top: 1px solid var(--tainacan-skeleton-color);
                        padding: 8px;
                        text-align: right;
                    }
                    .dropdown-item-apply .button {
                        width: 100%;
                    }
                }
            }
        }

        @media screen and (max-width: 768px) {
            height: 160px;
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
        padding-left: var(--tainacan-one-column);
        padding-right: var(--tainacan-one-column);
    }
    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        height: auto;
    }
</style>


