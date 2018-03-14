<template>  
    <div class="page-container-small" :class="{'primary-page': isRepositoryLevel}">
        <div class="sub-header">
            <div class="header-item">
                <router-link tag="button" class="button is-secondary"
                        :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                    {{ $i18n.getFrom('items', 'new_item') }}
                </router-link>
            </div>
            <div class="header-item">
                <b-dropdown>
                    <button class="button" slot="trigger" :disabled="items.length <= 0">
                        <span>{{$i18n.get('label_table_fields')}}</span>
                        <b-icon icon="menu-down"></b-icon>
                    </button>
                    <b-dropdown-item v-for="(column, index) in tableFields" 
                        :key="index"
                        class="control" custom>
                        <b-checkbox
                            @input="onChangeTableFields(column)"
                            v-model="column.visible" 
                            :native-value="column.field">
                            {{ column.label }}
                        </b-checkbox>
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>
        <div class="columns above-subheader">
            <aside class="column filters-menu">
                <h3>{{ $i18n.get('filters') }}</h3>
                <filters-items-list></filters-items-list>
            </aside>
            <div class="column table-container">
                <items-list
                    :collection-id="collectionId"
                    :tableFields="tableFields"
                    :prefTableFields="prefTableFields"
                    :totalItems="totalItems"
                    :page="page"
                    :items="items" 
                    :isLoading="isLoading"
                    :itemsPerPage="itemsPerPage">
                </items-list>
                <!-- Footer -->
                <div class="table-footer">
                    <div class="shown-items"> 
                        {{ 
                            $i18n.get('info_showing_items') + 
                            (itemsPerPage*(page - 1) + 1) + 
                            $i18n.get('info_to') + 
                            getLastItemNumber() + 
                            $i18n.get('info_of') + totalItems + '.'
                        }} 
                    </div> 
                    <div class="items-per-page">
                        <b-field horizontal :label="$i18n.get('label_items_per_page')"> 
                            <b-select 
                                    :value="itemsPerPage"
                                    @input="onChangeItemsPerPage" 
                                    :disabled="items.length <= 0">
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
                            :total="totalItems"
                            :current.sync="page"
                            order="is-centered"
                            size="is-small"
                            :per-page="itemsPerPage">
                        </b-pagination> 
                    </div>
                </div>
            </div>
        </div>      
    </div>
</template>

<script>
import ItemsList from '../../components/lists/items-list.vue';
import FiltersItemsList from '../../components/lists/filters-items-list.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'ItemsPage',
    data(){
        return {
            collectionId: Number,
            isRepositoryLevel: false,
            tableFields: [],
            prefTableFields: [],
            totalItems: 0,
            page: 1,
            itemsPerPage: 12,
            isLoading: false
        }
    },
    components: {
        ItemsList,
        FiltersItemsList
    },
    methods: {
        ...mapActions('collection', [
            'fetchItems',
            'fetchFields'
        ]),
        ...mapGetters('collection', [
            'getItems',
            'getFields'
        ]),
        ...mapActions('fields', [
            'fetchFields'
        ]),
        ...mapGetters('fields', [
            'getFields'
        ]),
        ...mapActions('filter', [
            'set_postquery',
            'search_by_collection'
        ]),
        onChangeTableFields(field) {
            let prevValue = this.prefTableFields;
            let index = this.prefTableFields.findIndex(alteredField => alteredField.slug === field.slug);
            if (index >= 0) {
                console.log(prevValue[index].visible);
                console.log(this.prefTableFields[index].visible);
                //prevValue[index].visible = this.prefTableFields[index].visible ? false : true;
            }
            

            // for (let currentField of this.prefTableFields)
            //     console.log(currentField.slug, currentField.visible);
            


            // for (let oldField of prevValue)
            //     console.log(oldField.slug, oldField.visible);

            //this.$userPrefs.set('table_columns_' + this.collectionId, this.prefTableFields, prevValue);
        },
        onChangeItemsPerPage(value) {
            let prevValue = this.itemsPerPage;
            this.itemsPerPage = value;
            this.$userPrefs.set('items_per_page', value, prevValue);
            this.loadItems();
        },
        onPageChange(page) {
            this.page = page;
            this.loadItems();
        },
        loadItems() {
            this.isLoading = true;
            let promisse = null;

            if( Object.keys( this.$route.query ).length > 0 ) {
                this.page = ( this.$route.query.page ) ? this.$route.query.page : this.page;
                this.itemsPerPage = ( this.$route.query.itemsPerPage ) ? this.$route.query.itemsPerPage : this.itemsPerPage;

                this.set_postquery(this.$route.query);
                if (this.$route.params && this.$route.params.collectionId) {
                    promisse = this.search_by_collection(this.$route.params.collectionId);
                }
            }

            if(!promisse)
                promisse = this.fetchItems({ 'collectionId': this.collectionId, 'page': this.page, 'itemsPerPage': this.itemsPerPage });

            promisse.then((res) => {
                this.isLoading = false;
                this.totalItems = res.total;
            })
            .catch((error) => {
                this.isLoading = false;
            });
        },
        getLastItemNumber() {
            let last = (Number(this.itemsPerPage*(this.page - 1)) + Number(this.itemsPerPage));
            
            return last > this.totalItems ? this.totalItems : last;
        }
    },
    computed: {
        items(){
            return this.getItems();
        }
    },
    created() {
        this.collectionId = this.$route.params.collectionId;
        this.isRepositoryLevel  = (this.collectionId == undefined);
        
        this.$userPrefs.get('items_per_page')
        .then((value) => {
            this.itemsPerPage = value;
        })
        .catch((error) => {
            this.$userPrefs.set('items_per_page', 12, null);
        });     
    },
    mounted(){
        this.loadItems();
        this.fetchFields({ collectionId: this.collectionId, isRepositoryLevel: false }).then((res) => {
            let rawFields = res;
            for (let field of rawFields) {
                this.tableFields.push(
                    { label: field.name, field: field.description, slug: field.slug,  visible: true }
                );
            }
            this.tableFields.push({ label: this.$i18n.get('label_actions'), field: 'row_actions', slug: 'actions', visible: true });
            
            this.prefTableFields = this.tableFields;
            // this.$userPrefs.get('table_columns_' + this.collectionId)
            //     .then((value) => {
            //         this.prefTableFields = value;
            //     })
            //     .catch((error) => {
            //         this.$userPrefs.set('table_columns_' + this.collectionId, this.prefTableFields, null);
            //     });

        }).catch();
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

        .header-item {
            display: inline-block;
            padding-right: 8em;
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
            padding-left: $page-small-side-padding
        }

        .table-container {
            margin-right: -$page-small-side-padding;
            padding: 3em 2.5em;
        }
    }

    

</style>


