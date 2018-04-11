<template>
    <div class="pagination-area">
        <div class="shown-items">
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
                    horizontal 
                    :label="$i18n.get('label_items_per_page')"> 
                <b-select 
                        :value="itemsPerPage"
                        @input="onChangeItemsPerPage">
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
                    :per-page="itemsPerPage"/> 
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import { eventBusSearch } from '../../../js/event-bus-search';

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
        }
    },
    watch: {
        page( value ){
            if (value < 1)
                eventBusSearch.setPage(1);
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
            
            let prevValue = this.itemsPerPage;
            eventBusSearch.setItemsPerPage(value);
            this.$userPrefs.set('items_per_page', value, prevValue);
        },
        onPageChange(page) {
            if(page == 0)
                return;
            eventBusSearch.setPage(page);
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
    },
}
</script>

<style scoped>

</style>
