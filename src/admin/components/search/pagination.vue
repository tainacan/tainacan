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
                $i18n.get('info_of') + totalItems + '.'
            }} 
        </div> 
        <div class="items-per-page">
            <b-field 
                    id="items-per-page-select"
                    horizontal 
                    :label="$i18n.get('label_items_per_page')"> 
                <b-select 
                        :value="itemsPerPage"
                        aria-labelledby="items-per-page-select"
                        @input="onChangeItemsPerPage">
                    <option value="12">12 &nbsp;</option>
                    <option value="24">24 &nbsp;</option>
                    <option value="48">48 &nbsp;</option>
                    <option value="96">96 &nbsp;</option>
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
                        @change="onPageChange">
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
                            :key="pageNumber"
                            v-for="pageNumber in totalPages"
                            :value="Number(pageNumber)">
                        {{ pageNumber }}
                    </b-dropdown-item>
                </b-dropdown>
            </b-field>
        </div>
        
        <div class="pagination"> 
            <b-pagination
                    @change="onPageChange"
                    :total="totalItems"
                    :current.sync="page"
                    order="is-centered"
                    size="is-small"
                    :per-page="itemsPerPage"/> 
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'Pagination',
    data(){
        return {
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
            if( this.itemsPerPage == value){
                return false;
            }
            this.$eventBusSearch.setItemsPerPage(value);
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
        },
    }
}
</script>
