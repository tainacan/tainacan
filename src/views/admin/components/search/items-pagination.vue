<template>
    <div 
            role="navigation"
            :aria-label="$i18n.get('label_list_pagination')"
            class="pagination-area">
        <div 
                style="flex-grow: 1;"
                class="shown-items is-hidden-mobile"
                role="status"
                aria-live="polite"
                aria-atomic="false"
                aria-relevant="text"
                :aria-label="$i18n.get('label_list_pagination')">
            <span v-html="showingItemsText"></span>
            <span 
                    v-if="isSortingByCustomMetadata"
                    v-tooltip="{
                        content: $i18n.get('info_items_hidden_due_sorting'),
                        autoHide: false,
                        placement: 'auto-start',
                        popperClass: ['tainacan-tooltip', 'tooltip']
                    }"
                    style="margin-top: -3px"
                    class="icon has-text-warning">
                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-alertcircle" />
            </span>
        </div> 
        <div 
                v-if="!hideItemsPerPageButton"
                id="tainacanItemsPerPageButton"
                class="items-per-page">
            <b-field 
                    id="items-per-page-select"
                    horizontal 
                    :label="$i18n.get('label_items_per_page')"
                    label-for="items-per-page-select-input"> 
                <b-select 
                        id="items-per-page-select-input"
                        :model-value="itemsPerPage"
                        aria-controls="items-list-results"
                        :disabled="itemsPerPageOptions.length <= 1"
                        :compat-fallthrough="false"
                        @update:model-value="onChangeItemsPerPage">
                    <template 
                            v-for="(itemsPerPageOption, index) of itemsPerPageOptions"
                            :key="index">
                        <option
                                :value="itemsPerPageOption">
                            {{ itemsPerPageOption }} &nbsp;
                        </option>
                    </template>
                </b-select>
            </b-field>
        </div>
        <div 
                v-if="!hideGoToPageButton"
                id="tainacanGoToPageButton"
                class="go-to-page items-per-page">
            <b-field 
                    id="go-to-page-dropdown"
                    horizontal
                    :label="$i18n.get('label_go_to_page')"
                    label-for="go-to-page-select-input">
                <b-select 
                        id="go-to-page-select-input"
                        :model-value="page"
                        aria-controls="items-list-results"
                        :compat-fallthrough="false"
                        @update:model-value="onPageChange">
                    <option
                            v-for="pageNumber in totalPages"
                            :key="pageNumber"
                            :value="Number(pageNumber)">
                        {{ pageNumber }}
                    </option>
                </b-select>
            </b-field>
        </div>
        
        <div class="pagination"> 
            <b-pagination
                    :model-value="page"
                    :aria-label="$i18n.get('label_pagination')"
                    aria-controls="items-list-results"
                    :total="totalItems"
                    order="is-centered"
                    size="is-small"
                    :per-page="itemsPerPage"
                    :aria-next-label="$i18n.get('label_next_page')"
                    :aria-previous-label="$i18n.get('label_previous_page')"
                    :aria-page-label="$i18n.get('label_page')"
                    :aria-current-label="$i18n.get('label_current_page')"
                    @change="onPageChange" /> 
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'ItemsPagination',
    props: {
        isSortingByCustomMetadata: Boolean,
        hideItemsPerPageButton: false,
        hideGoToPageButton: false
    },
    data() {
        return {
            maxItemsPerPage: tainacan_plugin.api_max_items_per_page ? Number(tainacan_plugin.api_max_items_per_page) : 96
        }
    },
    computed: {
        ...mapGetters('search', {
            'totalItems': 'getTotalItems',
            'page': 'getPage',
            'itemsPerPage': 'getItemsPerPage'
        }),
        totalPages(){
            return Math.ceil(Number(this.totalItems)/Number(this.itemsPerPage));    
        },
        itemsPerPageOptions() {
            const defaultItemsPerPageOptions = [];
            
            if ( 12 <= this.maxItemsPerPage )
                defaultItemsPerPageOptions.push(12);
            
            if ( 24 <= this.maxItemsPerPage )
                defaultItemsPerPageOptions.push(24);
            
            if ( 48 <= this.maxItemsPerPage )
                defaultItemsPerPageOptions.push(48);

            if ( !defaultItemsPerPageOptions.includes(this.maxItemsPerPage) )
                defaultItemsPerPageOptions.push(this.maxItemsPerPage);

            if (!isNaN(this.itemsPerPage) && !defaultItemsPerPageOptions.includes(this.itemsPerPage))
                defaultItemsPerPageOptions.push(Number(this.itemsPerPage));
            
            return defaultItemsPerPageOptions.sort((a,b) => a - b);
        },
        showingItemsText() {
            const first = this.getFirstItem();
            const last = this.getLastItemNumber();
            const total = this.totalItems;
            
            let text = this.$i18n.getWithVariables('info_showing_items_range', [first, last, total]);
            
            // Wrap the total number in a span with conditional warning class
            // Replace "of X." with "of <span>X</span>." to wrap just the number
            if (this.isSortingByCustomMetadata) {
                const totalStr = String(total);
                const regex = new RegExp('( of )(' + totalStr + ')(\\.)', 'g');
                text = text.replace(regex, '$1<span class="has-text-warning">$2</span>$3');
            }
            
            return text;
        }
    },
    watch: {
        page( value ){
            if (value < 1)
                this.$eventBusSearch.setPage(1);
        }
    },
    methods: {
        onChangeItemsPerPage(value) {
            if ( this.itemsPerPage == value )
                return false;
            else if ( Number(value) > Number(this.maxItemsPerPage) )
                this.$eventBusSearch.setItemsPerPage(this.maxItemsPerPage);
            else
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
        }
    }
}
</script>
