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
                    :label="$i18n.get('label_items_per_page')"> 
                <b-select 
                        :model-value="itemsPerPage"
                        aria-controls="items-list-results"
                        aria-labelledby="items-per-page-select"
                        @update:model-value="onChangeItemsPerPage">
                    <template 
                            v-for="(itemsPerPageOption, index) of itemsPerPageOptions"
                            :key="index">
                        <option
                                v-if="maxItemsPerPage >= 12"
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
                    :label="$i18n.get('label_go_to_page')"> 
                <b-dropdown 
                        position="is-top-right"
                        aria-role="list"
                        trap-focus
                        @change="onPageChange">
                    <template #trigger>
                        <button
                                aria-labelledby="go-to-page-dropdown"
                                class="button is-white">
                            <span>{{ page }}</span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                    </template>
                    <b-dropdown-item
                            v-for="pageNumber in totalPages"
                            :key="pageNumber" 
                            aria-controls="items-list-results"
                            role="button"
                            :value="Number(pageNumber)"
                            aria-role="listitem">
                        {{ pageNumber }}
                    </b-dropdown-item>
                </b-dropdown>
            </b-field>
        </div>
        
        <div class="pagination"> 
            <b-pagination
                    :model-value="page"
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
            const defaultItemsPerPageOptions = [12, 24, 48, this.maxItemsPerPage];
            if (!isNaN(this.itemsPerPage) && !defaultItemsPerPageOptions.includes(this.itemsPerPage))
                defaultItemsPerPageOptions.push(this.itemsPerPage);
            
            return defaultItemsPerPageOptions.sort();
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
