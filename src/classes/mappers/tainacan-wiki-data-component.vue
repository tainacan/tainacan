<template>
    <div :class="{ 'is-flex is-flex-wrap-wrap': itemMetadatum.metadatum.multiple != 'yes' || maxtags != undefined }">
        <b-tabs
            v-if="wikidataPropertyId"
                size="is-small"
                animated
                v-model="activeTab">
            <b-tab-item :label="$i18n.get('label_insert_items')">
                <b-taginput
                        expanded
                        :disabled="disabled"
                        :id="wikiDataInputId"
                        size="is-small"
                        icon="magnify"
                        :value="selected"
                        @input="onInput"
                        @blur="onBlur"
                        @add="onAdd"
                        @remove="onRemove"
                        :data="options"
                        :maxtags="maxtags != undefined ? maxtags : (itemMetadatum.metadatum.multiple == 'yes' || allowNew === true ? (maxMultipleValues !== undefined ? maxMultipleValues : null) : '1')"
                        autocomplete
                        :remove-on-keys="[]"
                        :dropdown-position="isLastMetadatum ? 'top' :'auto'"
                        attached
                        :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : $i18n.get('instruction_type_existing_item')"
                        :loading="isLoading"
                        :aria-close-label="$i18n.get('remove_value')"
                        :class="{'has-selected': selected != undefined && selected != []}"
                        field="label"
                        @typing="search"
                        check-infinite-scroll
                        @infinite-scroll="searchMore"
                        :has-counter="false"
                        @focus="onMobileSpecialFocus">
                    <template slot-scope="props">
                        <div class="media">
                            <div 
                                    class="media-content"
                                    style="display: block;">
                                {{ props.option.label }}
                                <br v-if="props.option.description">
                                <small v-if="props.option.description">{{ props.option.description }}</small>
                            </div>
                            <div class="has-text-gray media-right">
                                ({{ props.option.value }})
                            </div>
                        </div>
                    </template>
                    <template 
                            v-if="!isLoading"
                            slot="empty">
                        {{ $i18n.get('info_no_item_found') }}
                    </template>
                    <!-- <template
                            v-if="currentUserCanEditItems && (!$adminOptions.itemEditionMode || $adminOptions.allowItemEditionModalInsideModal)" 
                            slot="footer">
                        <a @click="editItemModalOpen = true">
                            {{ $i18n.get('label_create_new_item') + ' "' + searchQuery + '"' }}
                        </a>
                    </template> -->
                </b-taginput>
            </b-tab-item>
            <b-tab-item
                    v-if="itemMetadatum && itemMetadatum.value !== undefined"
                    style="min-height: 56px;"
                    :label="( itemMetadatum.value.length == 1 || itemMetadatum.metadatum.multiple != 'yes' ) ? $i18n.get('label_selected_item') : ( $i18n.get('label_selected_items') + ' (' + itemMetadatum.value.length + ')' )">
                <div class="tainacan-wiki-data-results-container">
                    <div 
                            v-if="itemMetadatum.value && itemMetadatum.value.length"
                            class="tainacan-wiki-data-group">
                        <div 
                                v-for="(itemValue, index) of selected"
                                :key="index"
                                style="position: relative;">
                            <div class="media">
                                <div 
                                        class="media-content"
                                        style="display: block;">
                                    {{ itemValue.label }}
                                    <br v-if="itemValue.description">
                                    <small v-if="itemValue.description">{{ itemValue.description }}</small>
                                </div>
                                <div class="has-text-gray media-right">
                                    ({{ itemValue.value }})
                                </div>
                            </div>
                            <a 
                                    @click="removeFromSelected(itemValue.value)"
                                    class="wiki-data-value-button--remove">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-close" />
                                </span>
                            </a>
                            <span
                                    v-if="index < selected.length - 1"
                                    class="multivalue-separator"> | </span>
                        </div>
                    </div>
                    <div v-else>
                        <p
                                class="has-text-gray"
                                style="font-size: 0.875em;">
                            {{ $i18n.get('info_no_item_found') }}
                        </p>
                    </div>
                </div>
            </b-tab-item>
        </b-tabs>
        <section 
                v-else
                class="field is-grouped-centered section">
            <div class="content has-text-gray has-text-centered">
                <p>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-processes tainacan-icon-rotate-90"/>
                    </span>
                </p>
            </div>
        </section>
    </div>
</template>

<script>
import qs from 'qs';

export default {
    name: "TainacanWikiData",
    props: {
        itemMetadatum: Object,
        value: [String, Number, Array],
        maxtags: undefined,
        disabled: false,
        allowNew: true,
        isLastMetadatum: false,
        isMobileScreen: false
    },
    data() {
        return {
            selected:[],
            options: [],
            isLoading: false,
            searchQuery: '',
            totalItems: 0,
            page: 1,
            activeTab: 0,
        }
    },
    computed: {
        wikidataPropertyId() {
            return this.itemMetadatum.metadatum &&
                   this.itemMetadatum.metadatum.exposer_mapping &&
                   this.itemMetadatum.metadatum.exposer_mapping['wiki-data'] &&
                   this.itemMetadatum.metadatum.exposer_mapping['wiki-data']['wikidataPropertyId'] ? this.itemMetadatum.metadatum.exposer_mapping['wiki-data']['wikidataPropertyId'] : false;
        },
        wikidataEndpoint() {
            return 'https://www.wikidata.org/w/api.php?origin=*&action=wbsearchentities&format=json&type=item&language=pt-br&uselang=pt-br&claim=' + this.wikidataPropertyId + '&';
        },
        maxMultipleValues() {
            return (
                this.itemMetadatum &&
                this.itemMetadatum.metadatum &&
                this.itemMetadatum.metadatum.cardinality &&
                !isNaN(this.itemMetadatum.metadatum.cardinality) &&
                this.itemMetadatum.metadatum.cardinality > 1
            ) ? this.itemMetadatum.metadatum.cardinality : undefined;
        },
        wikiDataInputId() {
            if (this.itemMetadatum && this.itemMetadatum.metadatum)
                return 'tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '');
            else
                return '';
        },
    },
    created() {
        if (
            this.itemMetadatum.metadatum &&
            this.itemMetadatum.metadatum.exposer_mapping &&
            this.itemMetadatum.metadatum.exposer_mapping['wiki-data'] &&
            this.itemMetadatum.metadatum.exposer_mapping['wiki-data']['wikidataPropertyId'] &&
            this.itemMetadatum.value &&
            ( Array.isArray( this.itemMetadatum.value ) ? this.itemMetadatum.value.length > 0 : true ) 
        ) {
            const originalValues = Array.isArray( this.itemMetadatum.value ) ? this.itemMetadatum.value : [ this.itemMetadatum.value ]
            const originalValuesAsObjects = originalValues.map(aValue => JSON.parse(aValue));

            this.fetchFromWikidata('https://www.wikidata.org/w/api.php?origin=*&action=wbgetentities&format=json&ids=' + originalValuesAsObjects.map(aValue => aValue.value).join('|')  )
                .then( res => {
                    if (res.entities && Object.keys(res.entities).length > 0) {
                        for (let item of Object.values(res.entities)) {
                            this.selected.push({
                                label: item.labels && item.labels['pt-br'] && item.labels['pt-br']['value'] ? item.labels['pt-br']['value'] : item.title,
                                value: item.id,
                                description: item.descriptions && item.descriptions['pt-br'] && item.descriptions['pt-br']['value'] ? item.descriptions['pt-br']['value'] : ''
                            });
                        }
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });
        }
    },
    methods: {
        async fetchFromWikidata(url)  {

            try {
                const requestHeaders = new Headers({
                    'Content-Type': 'application/json'
                });
                const request = new Request(url, requestHeaders);
                const res = await fetch(request)
                const data = await res.json();
                return data;
            } catch(e) {
                return e;
            }
        },
        onInput(newSelected) {
            // First we reset the input
            this.search('');

            this.selected = newSelected;
            this.$emit('input', newSelected.map((valueObject) => JSON.stringify(valueObject)));
        },
        onBlur() {
            this.$emit("blur");
        },
        search: _.debounce(function(query) {

            // String update
            if (query != this.searchQuery) {
                this.searchQuery = query;
                this.options = [];
                this.page = 1;
            } 

            // String cleared
            if (!query.length) {
                this.searchQuery = query;
                this.options = [];
                this.page = 1;
            }

            // No need to load more
            if (this.page > 1 && this.options.length > this.totalItems*12)
                return;

            // There is already one value set and is not multiple
            if (this.selected.length > 0 && this.itemMetadatum.metadatum.multiple === 'no')
                return;

            if (this.searchQuery !== '') {
                this.isLoading = true;

                this.fetchFromWikidata(this.wikidataEndpoint + this.getQueryString(this.searchQuery))
                    .then( res => {
                        if (res.search) {
                            for (let item of res.search) {
                                this.options.push({
                                    label: item.label,
                                    value: item.id,
                                    description: item.description,
                                });
                            }
                        }
                        if (res['search-continue'])
                            this.totalItems = Number(res['search-continue']) * 6;
                        
                        this.page++;

                        this.isLoading = false;
                    })
                    .catch(error => {
                        this.isLoading = false;
                        this.$console.log(error);
                    });
            }

        }, 500),
        searchMore: _.debounce(function () {
            this.search(this.searchQuery)
        }, 250),
        getQueryString( search ) {
            let query = [];
            query['search'] = search;
            query['perpage'] = 12;
            query['paged'] = this.page;
            query['order'] = 'asc';

            return qs.stringify(query);
        },
        removeFromSelected(itemId) {
            const indexOfRemovedItem = this.selected.findIndex(itemValue => itemValue.value == itemId);

            if (indexOfRemovedItem >= 0) {
                this.selected.splice(indexOfRemovedItem, 1);
                this.onInput(this.selected);
            }
        },
        onMobileSpecialFocus() {
            this.$emit('mobileSpecialFocus');
        }
    }
};
</script>

<style lang="scss" scoped>
    div.is-flex {
        justify-content: flex-start;
    }
    .add-link {
        font-size: 0.75em;
    }
    .b-tabs {
        margin-bottom: 0;
        width: 100%;
    }
    /deep/ .b-tabs .tab-content {
        padding: 0.5em 0px !important;
    }
    /deep/ .tabs {
        margin-bottom: 0 !important;
    }
    /deep/ .tabs ul {
        padding: 0;
    }
    /deep/ .tainacan-wiki-data-results-container {
        border: 1px solid var(--tainacan-gray1);
        background-color: var(--tainacan-white);
        margin-top: calc(-1 * (0.5em + 1px));
        margin-bottom: calc(-1 * (0.5em + 1px));
        display: flex;
        overflow: auto;
        padding: 12px;
        max-height: 40vh;
        transition: height 0.5s ease, min-height 0.5s ease;
    }
    /deep/ .tainacan-wiki-data-results-container .tainacan-wiki-data-group {
        padding-bottom: 12px;
    }
    /deep/ .tainacan-wiki-data-results-container .tainacan-wiki-data-group .tainacan-wiki-data-metadatum .tainacan-metadatum .label {
        font-size: 0.75em;
    }
    /deep/ .tainacan-wiki-data-results-container .tainacan-wiki-data-group .tainacan-wiki-data-metadatum  a {
        pointer-events: auto;
    }
    /deep/ .tainacan-wiki-data-results-container .tainacan-wiki-data-group .tainacan-wiki-data-metadatum .tainacan-wiki-data-metadatum-header {
        padding-right: 64px;
    }
    /deep/ .tainacan-wiki-data-results-container .tainacan-wiki-data-group .tainacan-wiki-data-metadatum .tainacan-wiki-data-metadatum-header .label{
        font-size: 0.875em;
    }

    /deep/ .tainacan-wiki-data-results-container .tainacan-wiki-data-group .tainacan-wiki-data-metadatum >div>.multivalue-separator {
        display: block;
        max-height: 1px;
        width: calc(100% - 40px);
        background: var(--tainacan-gray2);
        content: none;
        color: transparent;
        margin: 0.5em 0 0.5em 40px;
    }
    /deep/ .tainacan-wiki-data-group {
        width: 100%;
    }
    /deep/ .tainacan-wiki-data-group .tainacan-wiki-data-metadatum .label {
        font-size: 1em;
    }
    /deep/ .tainacan-wiki-data-group .tainacan-wiki-data-metadatum a {
        pointer-events: none;
    }
    /deep/ .tainacan-wiki-data-group .tainacan-wiki-data-metadatum p {
        font-size: 0.875em;
        margin-bottom: 0;
        margin-top: 0;
    }
    
</style>