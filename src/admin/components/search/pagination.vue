<template>
    <div 
            role="navigation"
            :aria-label="$i18n.get('label_list_pagination')"
            class="pagination-area">
        <div 
                style="flex-grow: 1;"
                class="shown-items is-hidden-mobile">
            {{ 
                $i18n.get('info_showing_items') +
                getFirstItem() +
                $i18n.get('info_to') + 
                getLastItemNumber() +
                $i18n.get('info_of')
            }} 
            <span :class="{ 'has-text-warning': isSortingByCustomMetadata }">
                {{ totalItems + '.' }}
            </span>
            <span 
                    v-tooltip="{
                        content: $i18n.get('info_items_hidden_due_sorting'),
                        autoHide: false,
                        placement: 'auto-start'
                    }"
                    style="margin-top: -3px"
                    class="icon has-text-warning"
                    v-if="isSortingByCustomMetadata">
                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-alertcircle" />
            </span>
        </div> 
        <div class="items-per-page">
            <b-field 
                    id="items-per-page-select"
                    horizontal 
                    :label="$i18n.get('label_items_per_page')"> 
                <b-select 
                        :value="itemsPerPage"
                        aria-controls="items-list-results"
                        aria-labelledby="items-per-page-select"
                        @input="onChangeItemsPerPage">
                    <option
                            v-if="maxItemsPerPage >= 12"
                            value="12">
                        12 &nbsp;
                    </option>
                    <option
                            v-if="maxItemsPerPage >= 24"
                            value="24">
                        24 &nbsp;
                    </option>
                    <option 
                            v-if="maxItemsPerPage >= 48"
                            value="48">
                        48 &nbsp;
                    </option>
                    <option 
                            v-if="maxItemsPerPage >= 96"
                            value="96">
                        96 &nbsp;
                    </option>
                </b-select>
            </b-field>
        </div>
        <div class="go-to-page items-per-page">
            <b-field 
                    horizontal 
                    id="go-to-page-dropdown"
                    :label="$i18n.get('label_go_to_page')"> 
                <b-dropdown 
                        position="is-top-right"
                        @change="onPageChange"
                        aria-role="list">
                    <button
                            aria-labelledby="go-to-page-dropdown"
                            class="button is-white"
                            slot="trigger">
                        <span>{{ page }}</span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"/>
                        </span>
                    </button>
                    <b-dropdown-item
                            aria-controls="items-list-results"
                            role="button" 
                            :key="pageNumber"
                            v-for="pageNumber in totalPages"
                            :value="Number(pageNumber)"
                            aria-role="listitem">
                        {{ pageNumber }}
                    </b-dropdown-item>
                </b-dropdown>
            </b-field>
        </div>
        
        <div class="pagination"> 
            <b-pagination
                    aria-controls="items-list-results"
                    @change="onPageChange"
                    :total="totalItems"
                    :current.sync="page"
                    order="is-centered"
                    size="is-small"
                    :per-page="itemsPerPage"
                    :aria-next-label="$i18n.get('label_next_page')"
                    :aria-previous-label="$i18n.get('label_previous_page')"
                    :aria-page-label="$i18n.get('label_page')"
                    :aria-current-label="$i18n.get('label_current_page')" /> 
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'Pagination',
    data() {
        return {
            maxItemsPerPage: tainacan_plugin.api_max_items_per_page
        }
    },
    computed: {
        totalItems(){    
            return this.getTotalItems();
        },
        page(){
            return this.getPage();
        },
        itemsPerPage(){
            return this.getItemsPerPage();
        },
        totalPages(){
            return Math.ceil(Number(this.totalItems)/Number(this.itemsPerPage));    
        }
    },
    props: {
        isSortingByCustomMetadata: Boolean
    },
    watch: {
        page( value ){
            if (value < 1)
                this.$eventBusSearch.setPage(1);
        }
    },
    methods: {
        ...mapGetters('search', [
            'getTotalItems',
            'getPage',
            'getItemsPerPage',
            'getPostQuery'
        ]),
        onChangeItemsPerPage(value) {
            if ( this.itemsPerPage == value){
                return false;
            } else if (Number(value) > Number(this.maxItemsPerPage)) {
                this.$eventBusSearch.setItemsPerPage(this.maxItemsPerPage);
            } else {
                this.$eventBusSearch.setItemsPerPage(value);
            }
        },
        onPageChange(page) {
            if(page == 0)
                return;
            this.$eventBusSearch.setPage(page);
        },
        getLastItemNumber() {
            let last = (Number(this.itemsPerPage*(this.page - 1)) + Number(this.itemsPerPage));
            
            return last > this.totalItems ? this.totalItems : last;
        },
        getFirstItem(){
            if( this.totalItems == 0 )
                return 0;
            return ( this.itemsPerPage * ( this.page - 1 ) + 1)
        }
    }
}
</script>
