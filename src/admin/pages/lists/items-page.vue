<template>
    <div :class="{'primary-page': isRepositoryLevel, 'page-container': isRepositoryLevel, 'page-container-small' :!isRepositoryLevel }">

        <!-- SEARCH AND FILTERS --------------------- -->
        <button 
                id="filter-menu-compress-button"
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
                <b-input
                        placeholder="Search..."
                        type="search"
                        size="is-small"
                        icon="magnify" />
            </b-field>
            <a class="is-size-7 is-secondary is-pulled-right">Busca avançada</a>

            <br>
            <br>

            <h3 class="has-text-weight-semibold">{{ $i18n.get('filters') }}</h3>
            <a
                    class="collapse-all is-size-7"
                    @click="toggleCollapseAll">
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
                        :pref-table-fields="prefTableFields"/>
            </div>
            <div 
                    :items="items"
                    id="theme-items-list" />
            <!-- LISTING RESULTS ------------------------- -->
            <div class="table-container above-subheader">
                <b-loading
                        :is-full-page="false"
                        :active.sync="isLoadingItems"/>
                <items-list
                        v-if="!isLoadingItems && items.length > 0"
                        :collection-id="collectionId"
                        :table-fields="tableFields"
                        :items="items"
                        :is-loading="isLoading"/>
                <section
                        v-if="!isLoadingItems && items.length <= 0"
                        class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <b-icon
                                    icon="inbox"
                                    size="is-large"/>
                        </p>
                        <p>{{ hasFiltered ? $i18n.get('info_no_item_found') : $i18n.get('info_no_item_created')
                            }}</p>
                        <router-link
                                v-if="!hasFiltered"
                                id="button-create-item"
                                tag="button"
                                class="button is-primary"
                                :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                            {{ $i18n.getFrom('items', 'new_item') }}
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
            toggleCollapseAll() {
                this.collapseAll = !this.collapseAll;

                for (let i = 0; i < this.fieldCollapses.length; i++)
                    this.fieldCollapses[i] = this.collapseAll;

            },
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
                isContextEdit: true
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
                        slug: 'featured_image',
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
                        }
                    }

                    this.tableFields.push({
                        name: this.$i18n.get('label_creation'),
                        field: 'row_creation',
                        field_type: undefined,
                        slug: 'creation',
                        id: 'date',
                        display: true
                    });

                    this.tableFields.push({
                        name: this.$i18n.get('label_actions'),
                        field: 'row_actions',
                        field_type: undefined,
                        slug: 'actions',
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
        } 
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .margin-1 {
        margin-bottom: 0.1rem;
    }

    .page-container, .page-container-small {
        padding: 0px;
        
    }

    .sub-header {
        min-height: $subheader-height;
        height: $subheader-height;
        padding-top: $page-small-top-padding;
        padding-left: $page-small-side-padding;
        padding-right: $page-small-side-padding;
        border-bottom: 0.5px solid #ddd;
        position: relative;

        @media screen and (max-width: 769px) {
            height: 60px;
            margin-top: -0.5em;
            padding-top: 0.90em;

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

    .filters-menu {
        position: relative;
        width: $filter-menu-width;
        max-width: $filter-menu-width;
        min-height: 100%;
        background-color: $tainacan-input-background;
        padding: $page-small-side-padding;
        float: left;
        height: 100%;
        max-height: 100%;
        overflow-y: auto;
        visibility: visible;
        display: block;
        transition: visibility ease 0.5s, display ease 0.5s;

        .label {
            font-size: 12px;
            font-weight: normal;
        }

    }

    .items-list-area {
        margin-left: 0;
        transition: margin-left ease 0.5s ;
    }
    .spaced-to-right {
        margin-left: $filter-menu-width;
    }

    .table-container {
        padding: 3em 55px;
        position: relative;
    }

    @media screen and (max-width: 769px) {
        .filters-menu {
            display: none;
        }
        .table-container {
            margin-right: 0;
            padding: .85em 0em;
        }
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
        background-color: $primary-light;
        color: $tertiary;
        padding: 0px;
        border-top-right-radius: 2px;
        border-bottom-right-radius: 2px;

        .icon {
            margin-top: -1px;
        }
    }

</style>


