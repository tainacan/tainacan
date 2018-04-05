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
            <div class="header-item">
                <b-dropdown>
                    <button 
                            class="button" 
                            slot="trigger" 
                            :disabled="items.length <= 0">
                        <span>{{ $i18n.get('label_table_fields') }}</span>
                        <b-icon icon="menu-down"/>
                    </button>
                    <b-dropdown-item 
                            v-for="(column, index) in tableFields" 
                            :key="index"
                            class="control" 
                            custom>
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
                <filters-items-list />
            </aside>
            <div class="column table-container">
                <items-list
                        :collection-id="collectionId"
                        :table-fields="tableFields"
                        :pref-table-fields="prefTableFields"
                        :items="items" 
                        :is-loading="isLoading"/>
                <!-- Pagination Footer -->
                <pagination v-if="items.length > 0"/>
            </div>
        </div>      
    </div>
</template>

<script>
import ItemsList from '../../components/lists/items-list.vue';
import FiltersItemsList from '../../components/search/filters-items-list.vue';
import Pagination from '../../components/search/pagination.vue'
import { eventFilterBus } from '../../../js/event-bus-filters'
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
        onChangeTableFields(field) {
            // let prevValue = this.prefTableFields;
            // let index = this.prefTableFields.findIndex(alteredField => alteredField.slug === field.slug);
            // if (index >= 0) {
            //     prevValue[index].visible = this.prefTableFields[index].visible ? false : true;
            // }

            // for (let currentField of this.prefTableFields)
            //     this.$console.log(currentField.slug, currentField.visible);

            // for (let oldField of prevValue)
            //     this.$console.log(oldField.slug, oldField.visible);

            // this.$userPrefs.set('table_columns_' + this.collectionId, this.prefTableFields, prevValue);
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
    },
    mounted(){
        eventFilterBus.updateStoreFromURL();
        eventFilterBus.loadItems();

        this.fetchFields({ collectionId: this.collectionId, isRepositoryLevel: false, isContextEdit: false }).then((res) => {
            let rawFields = res;
            this.tableFields.push({ label: this.$i18n.get('label_thumbnail'), field: 'featured_image', slug: 'featured_image', visible: true });
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


