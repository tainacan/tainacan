<template>
    <div 
            :class="{'primary-page': isRepositoryLevel}">

        <!-- SEARCH AND FILTERS --------------------- -->
        <!-- Filter menu compress button -->
        <button 
                id="filter-menu-compress-button"
                :class="{'filter-menu-compress-button-top-repo': isRepositoryLevel}"
                :style="{ top: isHeaderShrinked ? '125px' : '152px'}"
                @click="isFiltersMenuCompressed = !isFiltersMenuCompressed">
            <b-icon :icon="isFiltersMenuCompressed ? 'menu-right' : 'menu-left'" />
        </button>
        <!-- Side bar with search and filters -->
        <aside 
                v-show="!isFiltersMenuCompressed"
                class="filters-menu">
            <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingFilters"/>

            <b-field> 
                <div class="control is-small is-clearfix">
                    <input
                        class="input is-small"
                        :placeholder="$i18n.get('instruction_search')"
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

            <h3 class="has-text-weight-semibold">{{ $i18n.get('filters') }}</h3>
            <a
                    v-if="!isLoadingFilters &&
                    ((filters.length >= 0 &&
                     isRepositoryLevel) || filters.length > 0)"
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
                    v-if="!isLoadingFilters &&
                    ((filters.length >= 0 &&
                     isRepositoryLevel) || filters.length > 0)"
                    :filters="filters"
                    :collapsed="collapseAll"
                    :is-repository-level="isRepositoryLevel"/>

            <section
                    v-else
                    class="is-grouped-centered section">
                <div class="content has-text-gray has-text-centered">
                    <p>
                        <b-icon
                                icon="filter"
                                size="is-large"/>
                    </p>
                    <p>{{ $i18n.get('info_there_is_no_filter' ) }}</p>
                    <router-link
                            v-if="!isOnTheme"
                            id="button-create-filter"
                            :to="isRepositoryLevel ? $routerHelper.getNewFilterPath() : $routerHelper.getNewCollectionFilterPath(collectionId)"
                            tag="button"
                            class="button is-secondary is-centered">
                        {{ $i18n.getFrom('filters', 'new_item') }}
                    </router-link>
                </div>
            </section>
        </aside>
        
        <!-- ITEMS LIST AREA (ASIDE THE ASIDE) ------------------------- -->
        <div 
                id="items-list-area"
                class="items-list-area"
                :class="{ 'spaced-to-right': !isFiltersMenuCompressed }">
            <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingItems"/>

            <!-- SEARCH CONTROL ------------------------- -->
            <div class="search-control">
                <b-loading
                        :is-full-page="false"
                        :active.sync="isLoadingFields"/>
                <!-- Item Creation Dropdown, only on Admin -->
                <div 
                        class="search-control-item"
                        v-if="!isOnTheme">
                    <b-dropdown 
                            :mobile-modal="false"
                            id="item-creation-options-dropdown">
                        <button
                                class="button is-secondary"
                                slot="trigger">
                            <span>{{ $i18n.getFrom('items','add_new') }}</span>
                            <b-icon icon="menu-down"/>
                        </button>

                        <b-dropdown-item>
                            <router-link
                                    id="a-create-item"
                                    tag="div"
                                    :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                                {{ $i18n.get('add_one_item') }}
                            </router-link>
                        </b-dropdown-item>
                        <b-dropdown-item disabled>
                            {{ $i18n.get('add_items_bulk') + ' (Not ready)' }}
                        </b-dropdown-item>
                        <b-dropdown-item disabled>
                            {{ $i18n.get('add_items_external_source') + ' (Not ready)' }}
                        </b-dropdown-item>
                    </b-dropdown>

                </div>
                <!-- Displayed Fields Dropdown -->
                <div    
                        v-if="!isOnTheme || registeredViewModes[viewMode].dynamic_metadata"
                        class="search-control-item">
                    <b-dropdown
                            ref="displayedFieldsDropdown"
                            :mobile-modal="false"
                            :disabled="totalItems <= 0"
                            class="show">
                        <button
                                class="button is-white"
                                slot="trigger">
                            <span>{{ $i18n.get('label_table_fields') }}</span>
                            <b-icon icon="menu-down"/>
                        </button>
                        <div class="metadata-options-container">
                        <b-dropdown-item
                                v-for="(column, index) in localTableFields"
                                :key="index"
                                class="control"
                                custom>
                            <b-checkbox
                                    v-model="column.display"
                                    :native-value="column.display">
                                {{ column.name }}
                            </b-checkbox>
                        </b-dropdown-item>   
                        </div>
                        <div class="dropdown-item-apply">
                            <button 
                                    @click="onChangeDisplayedFields()"
                                    class="button is-success">
                                {{ $i18n.get('label_apply_changes') }}
                            </button>
                        </div>  
                    </b-dropdown>
                </div>

                <!-- Change OrderBy Select and Order Button-->
                <div class="search-control-item">
                    <b-field>
                        <b-select
                                :disabled="totalItems <= 0"
                                @input="onChangeOrderBy($event)"
                                :placeholder="$i18n.get('label_sorting')">
                            <option
                                    v-for="field in tableFields"
                                    v-if="
                                        field.id === 'creation_date' || 
                                        field.id === 'author_name' || (
                                            field.id !== undefined &&
                                            field.field_type_object.related_mapped_prop !== 'description' &&
                                            field.field_type_object.primitive_type !== 'term' &&
                                            field.field_type_object.primitive_type !== 'item' &&
                                            field.field_type_object.primitive_type !== 'compound'
                                    )"
                                    :value="field"
                                    :key="field.id">
                                {{ field.name }}
                            </option>
                        </b-select>
                        <button
                                :disabled="totalItems <= 0"
                                class="button is-white is-small"
                                @click="onChangeOrder()">
                            <b-icon :icon="order === 'ASC' ? 'sort-ascending' : 'sort-descending'"/>
                        </button>
                    </b-field>
                </div>

                <!-- View Modes Dropdown -->
                <div 
                        v-if="isOnTheme"
                        class="search-control-item">
                    <b-field>
                    <b-dropdown 
                                @change="onChangeViewMode($event)"
                                :mobile-modal="false"
                                position="is-bottom-left"
                                :aria-label="$i18n.get('label_view_mode')">
                            <button 
                                    class="button is-white" 
                                    slot="trigger">
                                <span 
                                        v-if="registeredViewModes[viewMode] != undefined"
                                        v-html="registeredViewModes[viewMode].icon"/>
                                <b-icon icon="menu-down" />
                            </button>
                            <b-dropdown-item 
                                    v-for="(viewModeOption, index) of enabledViewModes"
                                    :key="index"
                                    :value="viewModeOption"
                                    v-if="registeredViewModes[viewModeOption] != undefined">
                                <span v-html="registeredViewModes[viewModeOption].icon"/>
                                {{ registeredViewModes[viewModeOption].label }}
                            </b-dropdown-item>
                        </b-dropdown>
                    </b-field>
                </div>

            </div>

            <!-- STATUS TABS, only on Admin -------- -->
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

            <!-- ITEMS LISTING RESULTS ------------------------- -->
            <div class="above-search-control">

                <!-- Admin Table -->
                <items-list
                        v-if="!isOnTheme && 
                              !isLoadingItems && 
                              totalItems > 0"
                        :collection-id="collectionId"
                        :table-fields="tableFields"
                        :items="items"
                        :is-loading="isLoadingItems"
                        :is-on-trash="status == 'trash'"/>     
                
                <!-- Theme View Modes -->
                <div 
                        v-if="isOnTheme &&
                              !isLoadingItems &&
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'template'"
                        v-html="itemsListTemplate"/>
                <component
                        v-if="isOnTheme && 
                              !isLoadingItems && 
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'component'"
                        :collection-id="collectionId"
                        :table-fields="tableFields"
                        :items="items"
                        :is-loading="isLoadingItems"
                        :is="'table-view-mode'"/>     

                <!-- Empty Placeholder (only used in Admin) -->
                <section
                        v-if="!isOnTheme && !isLoadingItems && totalItems <= 0"
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

                <!-- Pagination -->
                <pagination v-if="totalItems > 0 && (!isOnTheme || registeredViewModes[viewMode].show_pagination)"/>
            </div>
        </div>
        
    </div>
</template>

<script>
    import ItemsList from '../../components/lists/items-list.vue';
    import FiltersItemsList from '../../components/search/filters-items-list.vue';
    import Pagination from '../../components/search/pagination.vue'
    import { mapActions, mapGetters } from 'vuex';

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
                collapseAll: true,
                isOnTheme: false,
                futureSearchQuery: '',
                isHeaderShrinked: false,
                localTableFields: [],
                registeredViewModes: tainacan_plugin.registered_view_modes
            }
        },
        props: {
            collectionId: Number,
            defaultViewMode: String, // Used only on theme
            enabledViewModes: Object // Used only on theme
        },
        computed: {
            items() {
                return this.getItems();
            },
            itemsListTemplate() {
                return this.getItemsListTemplate();
            },
            totalItems() {
                return this.getTotalItems();
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
            },
            viewMode() {
                return this.getViewMode();
            },
            orderBy() {
                return this.getOrderBy();
            },
            order() {
                return this.getOrder();
            }
        },
        components: {
            ItemsList,
            FiltersItemsList,
            Pagination
        },
        watch: {
            tableFields() {
                this.localTableFields = JSON.parse(JSON.stringify(this.tableFields));
            }
        },
        methods: {
            ...mapGetters('collection', [
                'getItems',
                'getItemsListTemplate'
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
                'getStatus',
                'getOrderBy',
                'getOrder',
                'getViewMode',
                'getTotalItems'
            ]),
            updateSearch() {
                this.$eventBusSearch.setSearchQuery(this.futureSearchQuery);
            },  
            onChangeOrderBy(field) {
                this.$eventBusSearch.setOrderBy(field);
            },
            onChangeOrder() {
                this.order == 'DESC' ? this.$eventBusSearch.setOrder('ASC') : this.$eventBusSearch.setOrder('DESC');
            },
            onChangeTab(status) {
                this.$eventBusSearch.setStatus(status);
            },
            onChangeViewMode(viewMode) {
                this.$eventBusSearch.setViewMode(viewMode);
            },
            onChangeDisplayedFields() {
                let fetchOnlyFieldIds = [];

                for (let i = 0; i < this.localTableFields.length; i++) {

                    this.tableFields[i].display = this.localTableFields[i].display;
                    if (this.tableFields[i].id != undefined) {
                        if (this.tableFields[i].display) {
                            fetchOnlyFieldIds.push(this.tableFields[i].id);                      
                        }
                    }
                }
                this.$eventBusSearch.addFetchOnly({
                    '0': 'thumbnail',
                    'meta': fetchOnlyFieldIds,
                    '1': 'creation_date',
                    '2': 'author_name'
                });
                this.$refs.displayedFieldsDropdown.toggle();
            },
            prepareFieldsAndFilters() {

                this.isLoadingFilters = true;

                this.fetchFilters({
                    collectionId: this.collectionId,
                    isRepositoryLevel: this.isRepositoryLevel,
                    isContextEdit: !this.isOnTheme,
                    includeDisabled: 'no',
                })
                    .then(() => this.isLoadingFilters = false)
                    .catch(() => this.isLoadingFilters = false);

                this.isLoadingFields = true;
                this.tableFields = [];

                this.fetchFields({
                    collectionId: this.collectionId,
                    isRepositoryLevel: this.isRepositoryLevel,
                    isContextEdit: !this.isOnTheme
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

                        let fetchOnlyFieldIds = [];

                        for (let field of this.fields) {
                            if (field.display !== 'never') {

                                let display;

                                if (field.display == 'no')
                                    display = false;
                                else if (field.display == 'yes')
                                    display = true;

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
                                if (display)
                                    fetchOnlyFieldIds.push(field.id);                      
                            }
                        }

                        this.tableFields.push({
                            name: this.$i18n.get('label_creation_date'),
                            field: 'row_creation',
                            field_type: undefined,
                            slug: 'creation_date',
                            id: undefined,
                            display: true
                        });
                        this.tableFields.push({
                            name: this.$i18n.get('label_created_by'),
                            field: 'row_author',
                            field_type: undefined,
                            slug: 'author_name',
                            id: undefined,
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
                        this.$eventBusSearch.addFetchOnly({
                            '0': 'thumbnail',
                            'meta': fetchOnlyFieldIds,
                            '1': 'creation_date',
                            '2': 'author_name'
                        });
                        this.isLoadingFields = false;

                    })
                    .catch(() => {
                        this.isLoadingFields = false;
                    });
            }
        },
        created() {

            this.isOnTheme = (this.$route.name === null);

            this.isRepositoryLevel = (this.collectionId === undefined);

            this.$eventBusSearch.setCollectionId(this.collectionId);

            this.$eventBusSearch.$on('isLoadingItems', isLoadingItems => {
                this.isLoadingItems = isLoadingItems;
            });

            this.$eventBusSearch.$on('hasFiltered', hasFiltered => {
                this.hasFiltered = hasFiltered;
            });

            this.$eventBusSearch.$on('hasToPrepareFieldsAndFilters', (to) => {
                /* This condition is to prevent a incorrect fetch by filter or fields when we come from items
                 * at collection level to items page at repository level
                 */
                if(this.collectionId === to.params.collectionId) {
                    this.prepareFieldsAndFilters();
                }
            });

            this.$eventBusSearch.setViewMode(this.defaultViewMode);

        },
        mounted() {
            
            this.prepareFieldsAndFilters();
            this.localTableFields = JSON.parse(JSON.stringify(this.tableFields));

            // Watch Scroll for shrinking header, only on Admin at collection level
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

    .page-container {
        padding: 0px;   
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
        margin-bottom: -0.1rem;

        h3 {
            font-size: 100%;
            margin-top: 48px;
        }

        #collection-search-button {
            border-radius: 0 !important;
            padding: 0 8px !important;
            border-color: $tainacan-input-background;
            &:focus, &:active {
                border-color: none !important;
            }
        }

        .label {
            font-size: 12px;
            font-weight: normal;
        }

    }
    .filter-menu-compress-button-top-repo {
         top: 123px !important;
    }
    #filter-menu-compress-button {
        position: absolute;
        z-index: 9;
        top: 152px;
        left: 0;
        max-width: 23px;
        height: 21px;
        width: 23px;
        border: none;
        background-color: $primary-lighter;
        color: $tertiary;
        padding: 0;
        border-top-right-radius: 2px;
        border-bottom-right-radius: 2px;
        cursor: pointer;
        transition: top 0.3s;

        .icon {
            margin-top: -1px;
        }
    }

    .spaced-to-right {
        margin-left: $filter-menu-width;
    }

    .search-control {
        min-height: $subheader-height;
        height: $subheader-height;
        padding-top: $page-small-top-padding;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        border-bottom: 0.5px solid #ddd;
        display: flex;
        justify-content: space-between;

        @media screen and (max-width: 769px) {
            height: 60px;
            margin-top: 0;

            .search-control-item {
                padding-right: 0.5em;
            }
        }
    }

    .search-control-item {
        display: inline-block;
        
        .field {
            align-items: center;
        }
        
        #item-creation-options-dropdown {
            margin-right: 80px;
        }
        .dropdown-menu {
            display: block;

            div.dropdown-content {
                padding: 0;

                .metadata-options-container {
                    max-height: 240px;
                    overflow: auto;
                }
                            
                .dropdown-item-apply {
                    width: 100%;
                    border-top: 1px solid #efefef;
                    padding: 8px 12px;
                    text-align: right;
                }
                .dropdown-item-apply .button {
                    width: 100%;
                }
            }
        }
    }

    .above-search-control {
        margin-bottom: 0;
        margin-top: 0;
        height: calc(100% - 184px);
    }

    .tabs {
        padding-top: 20px;
        margin-bottom: 20px;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
    }

    .items-list-area {
        margin-left: 0;
        transition: margin-left ease 0.5s;
        height: 100%;
        overflow: auto;
        position: relative;
    }

    .table-container {
        padding-left: 8.333333%;
        padding-right: 8.333333%;
        min-height: 200px;
        //height: calc(100% - 82px);
    }

    .pagination-area {
        margin-left: $page-side-padding;
        margin-right: $page-side-padding;
    }


</style>


