<template>  
    <div 
            class="page-container-small" 
            :class="{'primary-page': isRepositoryLevel}">
        <div class="sub-header">
            <b-loading 
                    :is-full-page="false" 
                    :active.sync="isLoadingFields"/>
            <div class="header-item">
                <router-link 
                        id="button-create-item"
                        tag="button" 
                        class="button is-secondary"
                        :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                    {{ $i18n.getFrom('items', 'new_item') }}
                </router-link>
            </div>
            <search-control
                    v-if="fields.length > 0 && (items.length != 0 || isLoadingItems)"
                    :is-repository-level="isRepositoryLevel" 
                    :collection-id="collectionId"
                    :table-fields="tableFields"
                    :pref-table-fields="prefTableFields"/>
        </div>
        <div class="columns">
            <aside class="column filters-menu">
                <b-loading 
                        :is-full-page="false" 
                        :active.sync="isLoadingFilters"/>
                <h3>{{ $i18n.get('filters') }}</h3>
                <filters-items-list 
                        v-if="!isLoadingFilters && filters.length > 0" 
                        :filters="filters"/>
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
                            {{ $i18n.getFrom('filters', 'new_item') }}</router-link>
                    </div>
                </section>
            </aside>
            <div class="column">
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
                            <p>{{ hasFiltered ? $i18n.get('info_no_item_found') : $i18n.get('info_no_item_created') }}</p>
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
    </div>
</template>

<script>
import SearchControl from '../../components/search/search-control.vue'
import ItemsList from '../../components/lists/items-list.vue';
import FiltersItemsList from '../../components/search/filters-items-list.vue';
import Pagination from '../../components/search/pagination.vue'
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'ItemsPage',
    data(){
        return {
            isRepositoryLevel: false,
            tableFields: [],
            prefTableFields: [],
            isLoadingItems: false,
            isLoadingFilters: false,
            isLoadingFields: false,
            hasFiltered: false
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
        ...mapActions('filter',[
            'fetchFilters'
        ]),
        ...mapGetters('filter', [
            'getFilters'
        ])
    },
    computed: {
        items(){
            return this.getItems();
        },
        filters(){
            return this.getFilters();
        },
        fields() {
            return this.getFields();
        }
    },
    created() {

        this.isRepositoryLevel = (this.collectionId == undefined);    

        this.$eventBusSearch.$on('isLoadingItems', isLoadingItems => {
            this.isLoadingItems = isLoadingItems;
        });

        this.$eventBusSearch.$on('hasFiltered', hasFiltered => {
            this.hasFiltered = hasFiltered;
        });

        this.isLoadingFilters = true;
        this.fetchFilters( { collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel, isContextEdit: true })
            .then(() => this.isLoadingFilters = false) 
            .catch(() => this.isLoadingFilters = false);

        this.isLoadingFields = true;
        this.fetchFields({ collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel, isContextEdit: false }).then(() => {

            this.tableFields.push({ name: this.$i18n.get('label_thumbnail'), field: 'row_thumbnail', field_type: undefined, slug: 'featured_image', id: undefined, visible: true });
            for (let field of this.fields) {
                this.tableFields.push(
                    {name: field.name, field: field.description, slug: field.slug, field_type: field.field_type, field_type_object: field.field_type_object, id: field.id, visible: true }
                );
            }
            
            this.tableFields.push({ name: this.$i18n.get('label_creation'), field: 'row_creation', field_type: undefined, slug: 'creation', id: 'date', visible: true});
            this.tableFields.push({ name: this.$i18n.get('label_actions'), field: 'row_actions', field_type: undefined, slug: 'actions', id: undefined, visible: true });
            
            //this.prefTableFields = this.tableFields;
            // this.$userPrefs.get('table_columns_' + this.collectionId)
            //     .then((value) => {
            //         this.prefTableFields = value;
            //     })
            //     .catch((error) => {
            //         this.$userPrefs.set('table_columns_' + this.collectionId, this.prefTableFields, null);
            //     });
            this.isLoadingFields = false;

        }).catch(() => {
            this.isLoadingFields = false;
        });
    },
    mounted(){
        this.$eventBusSearch.setCollectionId(this.collectionId);
        this.$eventBusSearch.updateStoreFromURL();
        this.$eventBusSearch.loadItems();
    }

}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .page-container-small>.columns {
        margin-top: 0;
     
    }

    .sub-header {
        min-height: $subheader-height;
        height: $subheader-height;
        margin-left: -$page-small-side-padding;
        margin-right: -$page-small-side-padding;
        margin-top: -$page-small-top-padding;
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
        min-width: $side-menu-width;
        max-width: $side-menu-width;
        background-color: $tainacan-input-color;
        margin-left: -$page-small-side-padding;
        padding: $page-small-side-padding;
        
        .label {
            font-size: 12px;
            font-weight: normal;
        }
        
    }

    .table-container {
        margin-right: -$page-small-side-padding;
        padding: 3em 2.5em;
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

    

</style>


