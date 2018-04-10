<template>  
    <div 
            class="page-container-small" 
            :class="{'primary-page': isRepositoryLevel}">
        <div class="sub-header">
            <div class="header-item">
                <router-link 
                        tag="button" 
                        class="button is-secondary"
                        :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                    {{ $i18n.getFrom('items', 'new_item') }}
                </router-link>
            </div>
            <search-control
                    v-if="items.length > 0"
                    :is-repository-level="isRepositoryLevel" 
                    :collection-id="collectionId"
                    :table-fields="tableFields"
                    :pref-table-fields="prefTableFields"/>
        </div>
        <div class="columns above-subheader">
            <aside class="column filters-menu">
                <h3>{{ $i18n.get('filters') }}</h3>
                <filters-items-list />
            </aside>
            <div class="column table-container">
                <items-list
                        :collection-id="collectionId"
                        :table-fields="tableFields"
                        :items="items" 
                        :is-loading="isLoading"/>
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
import { eventSearchBus } from '../../../js/event-search-bus'
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'ItemsPage',
    data(){
        return {
            collectionId: Number,
            isRepositoryLevel: false,
            tableFields: [],
            prefTableFields: [],
            isLoading: false
        }
    },
    components: {
        SearchControl,
        ItemsList,
        FiltersItemsList,
        Pagination
    },
    methods: {
        ...mapActions('collection', [
            'fetchItems',
        ]),
        ...mapGetters('collection', [
            'getItems'
        ]),
        ...mapActions('fields', [
            'fetchFields'
        ]),
    },
    computed: {
        items(){
            return this.getItems();
        }
    },
    created() {
        this.collectionId = this.$route.params.collectionId;
        this.isRepositoryLevel  = (this.collectionId == undefined);    

        eventSearchBus.$on('isLoadingItems', isLoadingItems => {
            this.isLoading = isLoadingItems;
        });

        this.fetchFields({ collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel, isContextEdit: false }).then((res) => {
            let rawFields = res;
            this.tableFields.push({ name: this.$i18n.get('label_thumbnail'), field: 'featured_image', field_type: undefined, slug: 'featured_image', id: undefined, visible: true });
            for (let field of rawFields) {
                this.tableFields.push(
                    {name: field.name, field: field.description, slug: field.slug, field_type: field.field_type, field_type_object: field.field_type_object, id: field.id, visible: true }
                );
            }
            this.tableFields.push({ name: this.$i18n.get('label_actions'), field: 'row_actions', field_type: undefined, slug: 'actions', id: undefined, visible: true });
            //this.prefTableFields = this.tableFields;
            // this.$userPrefs.get('table_columns_' + this.collectionId)
            //     .then((value) => {
            //         this.prefTableFields = value;
            //     })
            //     .catch((error) => {
            //         this.$userPrefs.set('table_columns_' + this.collectionId, this.prefTableFields, null);
            //     });

        }).catch();
    },
    mounted(){
        eventSearchBus.updateStoreFromURL();
        eventSearchBus.loadItems();
    }

}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .sub-header {
        max-height: $header-height;
        height: $header-height;
        margin-left: -$page-small-side-padding;
        margin-right: -$page-small-side-padding;
        margin-top: -$page-small-top-padding;
        padding-top: $page-small-top-padding;
        padding-left: $page-small-side-padding;
        padding-right: $page-small-side-padding;
        border-bottom: 0.5px solid #ddd;
        
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

        .filters-menu {
            min-width: $side-menu-width;
            max-width: $side-menu-width;
            background-color: $primary-lighter;
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
    }

    

</style>


