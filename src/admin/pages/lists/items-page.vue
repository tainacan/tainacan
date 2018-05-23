<template>
    <div 
            :class="{'primary-page': isRepositoryLevel}">

        <!-- SEARCH AND FILTERS --------------------- -->
        <button 
                id="filter-menu-compress-button"
                :style="{ top: isHeaderShrinked ? '125px' : '152px'}"
                @click="isFiltersMenuCompressed = !isFiltersMenuCompressed">
            <b-icon :icon="isFiltersMenuCompressed ? 'menu-right' : 'menu-left'" />
        </button>
        <aside 
                v-show="!isFiltersMenuCompressed"
                class="filters-menu">
            <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingFilters"/>

            <b-field class="margin-1"> 
                <div class="control is-small is-clearfix">
                    <input
                        class="input is-small"
                        :placeholder=" $i18n.get('instruction_search_collection') "
                        type="search"
                        autocomplete="on"
                        :value="searchQuery"
                        @input="futureSearchQuery = $event.target.value"
                        @keyup.enter="updateSearch()">
                </div>

                <p class="control">
                    <button                             
                            id="collection-search-button"
                            type="submit"
                            class="button"
                            @click="updateSearch()">
                        <b-icon 
                                icon="magnify" 
                                size="is-small"/>
                    </button>
                </p>
            </b-field>
            <!-- <a class="is-size-7 is-secondary is-pulled-right">Busca avançada</a> -->

            <br>
            <br>

            <h3 class="has-text-weight-semibold">{{ $i18n.get('filters') }}</h3>
            <a
                    v-if="!isLoadingFilters && filters.length > 0"
                    class="collapse-all is-size-7"
                    @click="collapseAll = !collapseAll">
                {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                <b-icon
                        type="is-secondary"
                        size="is-small"
                        :icon=" collapseAll ? 'menu-down' : 'menu-right'" />
            </a>

            <br>
            <br>

            <filters-items-list
                    v-if="!isLoadingFilters && filters.length > 0"
                    :filters="filters"
                    :collapsed="collapseAll"/>

            <section
                    v-else
                    class="is-grouped-centered section">
                <div class="content has-text-gray has-text-centered">
                    <p>
                        <b-icon
                                icon="filter-outline"
                                size="is-large"/>
                    </p>
                    <p>{{ $i18n.get('info_there_is_no_filter' ) }}</p>
                    <router-link
                            id="button-create-filter"
                            :to="isRepositoryLevel ? $routerHelper.getNewFilterPath() : $routerHelper.getNewCollectionFilterPath(collectionId)"
                            tag="button"
                            class="button is-secondary is-centered">
                        {{ $i18n.getFrom('filters', 'new_item') }}
                    </router-link>
                </div>
            </section>
        </aside>
        
        <div 
                id="items-list-area"
                class="items-list-area"
                :class="{ 'spaced-to-right': !isFiltersMenuCompressed }">
            <!-- SEARCH CONTROL ------------------------- -->
            <div class="sub-header">
                <b-loading
                        :is-full-page="false"
                        :active.sync="isLoadingFields"/>
                <search-control
                        v-if="fields.length > 0 && (items.length > 0 || isLoadingItems)"
                        :is-repository-level="isRepositoryLevel"
                        :collection-id="collectionId"
                        :table-fields="tableFields"
                        :pref-table-fields="prefTableFields"
                        :is-on-theme="isOnTheme"/>
            </div>
            <div 
                    v-if="!isOnTheme"
                    class="tabs">
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
            
            <!-- <div 
                    :items="items"
                    id="theme-items-list" /> -->
            <!-- LISTING RESULTS ------------------------- -->
            <div class="above-subheader">
                <b-loading
                        :is-full-page="false"
                        :active.sync="isLoadingItems"/>
                <items-list
                        v-if="!isLoadingItems && items.length > 0"
                        :collection-id="collectionId"
                        :table-fields="tableFields"
                        :items="items"
                        :is-loading="isLoading"
                        :is-on-theme="isOnTheme"/>
                <section
                        v-if="!isLoadingItems && items.length <= 0"
                        class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <b-icon
                                    icon="inbox"
                                    size="is-large"/>
                        </p>
                        <p v-if="status == undefined || status == ''">{{ hasFiltered ? $i18n.get('info_no_item_found') : $i18n.get('info_no_item_created') }}</p>
                        <p v-if="status == 'draft'">{{ $i18n.get('info_no_item_draft') }}</p>
                        <p v-if="status == 'trash'">{{ $i18n.get('info_no_item_trash') }}</p>

                        <router-link
                                v-if="!hasFiltered && (status == undefined || status == '')"
                                id="button-create-item"
                                tag="button"
                                class="button is-primary"
                                :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                            {{ $i18n.getFrom('items', 'add_new') }}
                        </router-link>
                    </div>
                </section>
                <!-- Pagination Footer -->
                <pagination v-if="items.length > 0"/>
            </div>
        </div>
        
    </div>
</template>

<script>
    import SearchControl from '../../components/search/search-control.vue'
    import ItemsList from '../../components/lists/items-list.vue';
    import FiltersItemsList from '../../components/search/filters-items-list.vue';
    import Pagination from '../../components/search/pagination.vue'
    import {mapActions, mapGetters} from 'vuex';

    export default {
        name: 'ItemsPage',
        data() {
            return {
                isRepositoryLevel: false,
                tableFields: [],
                prefTableFields: [],
                isLoadingItems: false,
                isLoadingFilters: false,
                isLoadingFields: false,
                hasFiltered: false,
                isFiltersMenuCompressed: false,
                collapseAll: false,
                isOnTheme: false,
                futureSearchQuery: '',
                isHeaderShrinked: false
            }
        },
        props: {
            collectionId: Number
        },
        components: {
            SearchControl,
            ItemsList,
            FiltersItemsList,
            Pagination
        },
        methods: {
            ...mapGetters('collection', [
                'getItems'
            ]),
            ...mapActions('fields', [
                'fetchFields'
            ]),
            ...mapGetters('fields', [
                'getFields'
            ]),
            ...mapActions('filter', [
                'fetchFilters'
            ]),
            ...mapGetters('filter', [
                'getFilters'
            ]),
            ...mapGetters('search', [
                'getSearchQuery',
                'getStatus'
            ]),
            updateSearch() {
                this.$eventBusSearch.setSearchQuery(this.futureSearchQuery);
            },  
            onChangeTab(status) {
                this.$eventBusSearch.setStatus(status);
            }
        },
        computed: {
            items() {
                return this.getItems();
            },
            filters() {
                return this.getFilters();
            },
            fields() {
                return this.getFields();
            },
            searchQuery() {
                return this.getSearchQuery();
            },
            status() {
                return this.getStatus();
            }
        },
        created() {
           /*  
            document.addEventListener('tainacan-items-change', () => {
                var themeList = document.getElementById('theme-items-list');
                var items = themeList.attributes.items.value;

                var e = document.createElement('p');
                e.innerHTML = items;

                themeList.appendChild(e);
            }); */

            this.isOnTheme = (this.$route.name == null);
            this.isRepositoryLevel = (this.collectionId == undefined);

            this.$eventBusSearch.$on('isLoadingItems', isLoadingItems => {
                this.isLoadingItems = isLoadingItems;
            });

            this.$eventBusSearch.$on('hasFiltered', hasFiltered => {
                this.hasFiltered = hasFiltered;
            });

            this.isLoadingFilters = true;
            this.fetchFilters({
                collectionId: this.collectionId,
                isRepositoryLevel: this.isRepositoryLevel,
                isContextEdit: true,
                includeDisabled: 'no',
            })
                .then(() => this.isLoadingFilters = false)
                .catch(() => this.isLoadingFilters = false);

            this.isLoadingFields = true;

            this.fetchFields({
                collectionId: this.collectionId,
                isRepositoryLevel: this.isRepositoryLevel,
                isContextEdit: false
            })
                .then(() => {

                    this.tableFields.push({
                        name: this.$i18n.get('label_thumbnail'),
                        field: 'row_thumbnail',
                        field_type: undefined,
                        slug: 'thumbnail',
                        id: undefined,
                        display: true
                    });

                    for (let field of this.fields) {
                        if (field.display !== 'never') {
                            // Will be pushed on array

                            let display = true;

                            if (field.display === 'no') {
                                display = false;
                            }

                            this.tableFields.push(
                                {
                                    name: field.name,
                                    field: field.description,
                                    slug: field.slug,
                                    field_type: field.field_type,
                                    field_type_object: field.field_type_object,
                                    id: field.id,
                                    display: display
                                }
                            );    
                            //this.$eventBusSearch.addFetchOnlyMeta(field.id);                       
                        }
                    }
                    this.$eventBusSearch.loadItems();

                    this.tableFields.push({
                        name: this.$i18n.get('label_creation'),
                        field: 'row_creation',
                        field_type: undefined,
                        slug: 'creation',
                        id: 'date',
                        display: true
                    });

                    // this.prefTableFields = this.tableFields;
                    // this.$userPrefs.get('table_columns_' + this.collectionId)
                    //     .then((value) => {
                    //         this.prefTableFields = value;
                    //     })
                    //     .catch((error) => {
                    //         this.$userPrefs.set('table_columns_' + this.collectionId, this.prefTableFields, null);
                    //     });

                    this.isLoadingFields = false;

                })
                .catch(() => {
                    this.isLoadingFields = false;
                });
        },
        mounted() {
            this.$eventBusSearch.setCollectionId(this.collectionId);
            this.$eventBusSearch.updateStoreFromURL();
            this.$eventBusSearch.loadItems();

            if (!this.isRepositoryLevel && !this.isOnTheme) {
                document.getElementById('items-list-area').addEventListener('scroll', ($event) => {
                    this.isHeaderShrinked = ($event.originalTarget.scrollTop > 53);
                    this.$emit('onShrinkHeader', this.isHeaderShrinked); 
                });
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .margin-1 {
        margin-bottom: 0.1rem;
    }

    .page-container {
        padding: 0px;
        
    }

    .sub-header {
        min-height: $subheader-height;
        height: $subheader-height;
        padding-top: $page-small-top-padding;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        border-bottom: 0.5px solid #ddd;
        position: relative;

        @media screen and (max-width: 769px) {
            height: 60px;
            margin-top: 0;

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
        height: calc(100% - 184px);
    }
    .pagination-area {
        margin-left: $page-side-padding;
        margin-right: $page-side-padding;
    }

    .table-container {
        padding-left: 8.333333%;
        padding-right: 8.333333%;
        //height: calc(100% - 82px);
    }

    #collection-search-button {
        border-radius: 0px !important;
        padding: 0px 8px !important;
        border-color: $tainacan-input-background;
        &:focus, &:active {
            border-color: none !important;
        }
    }

    .filters-menu {
        position: relative;
        width: $filter-menu-width;
        max-width: $filter-menu-width;
        min-height: 100%;
        height: 100%;
        background-color: $tainacan-input-background;
        padding: $page-small-side-padding;
        float: left;
        overflow-y: auto;
        visibility: visible;
        display: block;
        transition: visibility ease 0.5s, display ease 0.5s;

        h3 {
            font-size: 100%;
        }

        .label {
            font-size: 12px;
            font-weight: normal;
        }

    }

    .items-list-area {
        margin-left: 0;
        transition: margin-left ease 0.5s;
        height: 100%;
        overflow: auto;
    }
    .spaced-to-right {
        margin-left: $filter-menu-width;
    }

    #filter-menu-compress-button {
        position: absolute;
        z-index: 9;
        top: 152px;
        left: 0px;
        max-width: 23px;
        height: 21px;
        width: 23px;
        border: none;
        background-color: $primary-lighter;
        color: $tertiary;
        padding: 0px;
        border-top-right-radius: 2px;
        border-bottom-right-radius: 2px;
        cursor: pointer;
        transition: top 0.3s;

        .icon {
            margin-top: -1px;
        }
    }

</style>


