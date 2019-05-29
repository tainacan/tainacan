<template>
    <div :class="className">
        <div v-if="showCollectionHeader">
            <div
                    v-if="isLoadingCollection"
                    class="facets-collection-header skeleton" 
                    :style="{ height: '165px' }"/>
            <a
                    v-else
                    target="_blank"
                    :href="collection.url ? collection.url : ''"
                    class="facets-collection-header">
                <div
                        :style="{
                            backgroundColor: collectionBackgroundColor ? collectionBackgroundColor : '', 
                            paddingRight: collection && collection.thumbnail && (collection.thumbnail['tainacan-medium'] || collection.thumbnail['medium']) ? '' : '20px',
                            paddingTop: (!collection || !collection.thumbnail || (!collection.thumbnail['tainacan-medium'] && !collection.thumbnail['medium'])) ? '1rem' : '',
                            width: collection && collection.header_image ? '' : '100%'
                        }"
                        :class="
                            'collection-name ' + 
                            ((!collection || !collection.thumbnail || (!collection.thumbnail['tainacan-medium'] && !collection.thumbnail['medium'])) && (!collection || !collection.header_image) ? 'only-collection-name' : '') 
                        ">
                    <h3 :style="{ color: collectionTextColor ? collectionTextColor : '' }">
                        <span
                                v-if="showCollectionLabel"
                                class="label">
                            {{ $root.__('Collection', 'tainacan') }}
                            <br>
                        </span>
                        {{ collection && collection.name ? collection.name : '' }}
                    </h3>
                </div>
                <div
                    v-if="collection && collection.thumbnail && (collection.thumbnail['tainacan-medium'] || collection.thumbnail['medium'])"   
                    class="collection-thumbnail"
                    :style="{ 
                        backgroundImage: 'url(' + (collection.thumbnail['tainacan-medium'] != undefined ? (collection.thumbnail['tainacan-medium'][0]) : (collection.thumbnail['medium'][0])) + ')',
                    }"/>
                <div
                        class="collection-header-image"
                        :style="{
                            backgroundImage: collection.header_image ? 'url(' + collection.header_image + ')' : '',
                            minHeight: collection && collection.header_image ? '' : '80px',
                            display: !(collection && collection.thumbnail && (collection.thumbnail['tainacan-medium'] || collection.thumbnail['medium'])) ? collection && collection.header_image ? '' : 'none' : ''  
                        }"/>
            </a>   
        </div>
        <div
                v-if="showSearchBar"
                class="facets-search-bar">
            <button
                    @click="localOrder = 'asc'; fetchFacets()"
                    :class="localOrder == 'asc' ? 'sorting-button-selected' : ''"
                    :label="$root.__('Sort ascending', 'tainacan')">
                <span class="icon">
                    <i>
                        <svg
                                width="24"
                                height="24"
                                viewBox="-2 -2 20 20">
                            <path d="M6.7,10.8l-3.3,3.3L0,10.8h2.5V0h1.7v10.8H6.7z M11.7,0.8H8.3v1.7h3.3V0.8z M14.2,5.8H8.3v1.7h5.8V5.8z M16.7,10.8H8.3v1.7	h8.3V10.8z"/>       
                        </svg>
                    </i>
                </span>
            </button>  
            <button
                    @click="localOrder = 'desc'; fetchFacets(); "
                    :class="localOrder == 'desc' ? 'sorting-button-selected' : ''"
                    :label="$root.__('Sort descending', 'tainacan')">
                <span class="icon">
                    <i>
                        <svg
                                width="24"
                                height="24"
                                viewBox="-2 -2 20 20">
                            <path
                                    d="M6.7,3.3H4.2v10.8H2.5V3.3H0L3.3,0L6.7,3.3z M11.6,2.5H8.3v1.7h3.3V2.5z M14.1,7.5H8.3v1.7h5.8V7.5z M16.6,12.5H8.3v1.7 h8.3V12.5z"/>
                        </svg>
                    </i>
                </span>
            </button>  
            <button
                    @click="fetchFacets()"
                    :label="$root.__('Search', 'tainacan')"
                    class="search-button">
                <span class="icon">
                    <i>
                        <svg    
                                width="24"
                                height="24"
                                viewBox="-2 -2 20 20">
                            <path
                                    class="st0"
                                    d="M0,5.8C0,5,0.2,4.2,0.5,3.5s0.7-1.3,1.2-1.8s1.1-0.9,1.8-1.2C4.2,0.1,5,0,5.8,0S7.3,0.1,8,0.5
                                    c0.7,0.3,1.3,0.7,1.8,1.2s0.9,1.1,1.2,1.8c0.5,1.2,0.5,2.5,0.2,3.7c0,0.2-0.1,0.4-0.2,0.6c0,0.1-0.2,0.6-0.2,0.6
                                    c0.6,0.6,1.3,1.3,1.9,1.9c0.7,0.7,1.3,1.3,2,2c0,0,0.3,0.2,0.3,0.3c0,0.3-0.1,0.7-0.3,1c-0.2,0.6-0.8,1-1.4,1.2
                                    c-0.1,0-0.6,0.2-0.6,0.1c0,0-4.2-4.2-4.2-4.2c0,0-0.8,0.3-0.8,0.4c-1.3,0.4-2.8,0.5-4.1-0.1c-0.7-0.3-1.3-0.7-1.8-1.2
                                    C1.2,9.3,0.8,8.7,0.5,8S0,6.6,0,5.8z M1.6,5.8c0,0.4,0.1,0.9,0.2,1.3C2.1,8.2,3,9.2,4.1,9.6c0.5,0.2,1,0.3,1.6,0.3
                                    c0.6,0,1.1-0.1,1.6-0.3C8.7,9,9.7,7.6,9.8,6c0.1-1.5-0.6-3.1-2-3.9c-0.9-0.5-2-0.6-3-0.4C4.6,1.8,4.4,1.9,4.1,2
                                    c-0.5,0.2-1,0.5-1.4,0.9C2,3.7,1.6,4.7,1.6,5.8z"/>       
                        </svg>
                    </i>
                </span>
            </button>
            <input
                    :value="searchString"
                    @input="(value) => applySearchString(value)"
                    type="text">
            <button
                    class="previous-button"
                    v-if="paged > 1"
                    @click="paged--; fetchFacets()"
                    :label="$root.__('Previous page', 'tainacan')">
                <span class="icon">
                    <i>
                        <svg
                                width="30"
                                height="30"
                                viewBox="0 2 20 20">
                            <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                            <path
                                    d="M0 0h24v24H0z"
                                    fill="none"/>                        
                        </svg>
                    </i>
                </span>
            </button> 
            <button
                    :style="{ marginLeft: paged <= 1 ? 'auto' : '0' }"
                    class="next-button"
                    v-if="paged < totalFacets/maxFacetsNumber && facets.length < totalFacets"
                    @click="paged++; fetchFacets()"
                    :label="$root.__('Next page', 'tainacan')">
                <span class="icon">
                    <i>
                        <svg
                                width="30"
                                height="30"
                                viewBox="0 2 20 20">
                            <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                            <path
                                    d="M0 0h24v24H0z"
                                    fill="none"/>                        
                        </svg>
                    </i>
                </span>
            </button> 
        </div>
        <ul
                v-if="isLoading"
                :style="{
                    gridTemplateColumns: layout == 'grid' ? 'repeat(auto-fill, ' + (gridMargin + (showName ? 220 : 185)) + 'px)' : 'inherit', 
                    marginTop: showSearchBar || showCollectionHeader ? '1.34rem' : '0px'
                }"
                class="facets-list"
                :class="'facets-layout-' + layout + (!showName ? ' facets-list-without-margin' : '')">
                <li
                        :key="facet"
                        v-for="facet in Number(maxFacetsNumber)"
                        class="facet-list-item skeleton"
                        :style="{ 
                            marginBottom: layout == 'grid' ? (showName ? gridMargin + 12 : gridMargin) + 'px' : '',
                            height: layout == 'grid' ? '230px' : '54px'
                        }" />      
        </ul>
        <div v-else>
            <ul 
                    v-if="facets.length > 0"
                    :style="{
                        gridTemplateColumns: layout == 'grid' ? 'repeat(auto-fill, ' + (gridMargin + (showName ? 220 : 185)) + 'px)' : 'inherit', 
                        marginTop: showSearchBar || showCollectionHeader ? '1.35rem' : '0px'
                    }"
                    class="facets-list"
                    :class="'facets-layout-' + layout + (!showName ? ' facets-list-without-margin' : '')">
                <li
                        :key="index"
                        v-for="(facet, index) of facets"
                        class="facet-list-item"
                        :style="{ marginBottom: layout == 'grid' ? (showName ? gridMargin + 12 : gridMargin) + 'px' : ''}">      
                    <a 
                            :id="isNaN(facet.id) ? facet.id : 'facet-id-' + facet.id"
                            :href="facet.url"
                            target="_blank"
                            :class="(!showName ? 'facet-without-title' : '') + ' ' + (!showImage ? 'facet-without-image' : '')">
                        <img
                            :src=" 
                                facet.thumbnail && facet.thumbnail['tainacan-medium'][0] && facet.thumbnail['tainacan-medium'][0] 
                                    ?
                                facet.thumbnail['tainacan-medium'][0] 
                                    :
                                (facet.thumbnail && facet.thumbnail['thumbnail'][0] && facet.thumbnail['thumbnail'][0]
                                    ?    
                                facet.thumbnail['thumbnail'][0] 
                                    : 
                                `${tainacanBaseUrl}/admin/images/placeholder_square.png`)
                            "
                            :alt="facet.title ? facet.title : $root.__('Thumbnail', 'tainacan')">
                        <span>{{ facet.title ? facet.title : '' }}</span>
                    </a>
                </li>
            </ul>
            <div
                    v-else
                    class="spinner-container">
                {{ $root.__('No facets found.', 'tainacan') }}
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import qs from 'qs';
import { debounce } from 'lodash';

export default {
    name: "FacetsListTheme",
    data() {
        return {
            facets: [],
            collection: undefined,
            facetsRequestSource: undefined,
            searchString: '',
            isLoading: false,
            isLoadingCollection: false,
            localMaxFacetsNumber: undefined,
            localOrder: undefined,
            tainacanAxios: undefined,
            paged: undefined,
            totalFacets: 0
        }
    },
    props: {
        collectionId: String,  
        showImage: Boolean,
        showName: Boolean,
        layout: String,
        gridMargin: Number,
        searchURL: String,
        maxFacetsNumber: Number,
        order: String,
        showSearchBar: Boolean,
        showCollectionHeader: Boolean,
        showCollectionLabel: Boolean,
        collectionBackgroundColor: String,
        collectionTextColor: String,
        tainacanApiRoot: String,
        tainacanBaseUrl: String,
        className: String
    },
    methods: {
        applySearchString: debounce(function(event) { 

            let value = event.target.value;

            if (this.searchString != value) {
                this.searchString = value;
                this.paged = 1;
                this.fetchFacets();
            }
        }, 500),
        fetchFacets() {

            this.facets = [];
            this.isLoading = true;
            
            if (this.facetsRequestSource != undefined && typeof this.facetsRequestSource == 'function')
                this.facetsRequestSource.cancel('Previous facets search canceled.');

            this.facetsRequestSource = axios.CancelToken.source();

            let endpoint = '/collection' + this.searchURL.split('#')[1].split('/collections')[1];
            let query = endpoint.split('?')[1];
            let queryObject = qs.parse(query);

            // Set up max facets to be shown
            if (this.maxFacetsNumber != undefined && Number(this.maxFacetsNumber) > 0)
                queryObject.perpage = this.maxFacetsNumber;
            else if (queryObject.perpage != undefined && queryObject.perpage > 0)
                this.localMaxFacetsNumber = queryObject.perpage;
            else {
                queryObject.perpage = 12;
                this.localMaxFacetsNumber = 12;
            }

            // Set up sorting order
            if (this.localOrder != undefined)
                queryObject.order = this.localOrder;
            else if (queryObject.order != undefined)
                this.localOrder = queryObject.order;
            else {
                queryObject.order = 'asc';
                this.localOrder = 'asc';
            }

            // Set up sorting order
            if (this.searchString != undefined)
                queryObject.search = this.searchString;
            else if (queryObject.search != undefined)
                this.searchString = queryObject.search;
            else {
                delete queryObject.search;
                this.searchString = undefined;
            }

            // Set up paging
            if (this.paged != undefined)
                queryObject.paged = this.paged;
            else if (queryObject.paged != undefined)
                this.paged = queryObject.paged;
            else
                this.paged = 1;

            // Remove unecessary queries
            delete queryObject.readmode;
            delete queryObject.iframemode;
            delete queryObject.admin_view_mode;
            delete queryObject.fetch_only_meta;
            
            endpoint = endpoint.split('?')[0] + '?' + qs.stringify(queryObject) + '&fetch_only=title,url,thumbnail';
            
            this.tainacanAxios.get(endpoint, { cancelToken: this.facetsRequestSource.token })
                .then(response => {

                    for (let facet of response.data.facets)
                        this.facets.push(facet);

                    this.isLoading = false;
                    this.totalFacets = response.headers['x-wp-total'];

                }).catch(() => { 
                    this.isLoading = false;
                    // console.log(error);
                });
        },
        fetchCollectionForHeader() {
            if (this.showCollectionHeader) {

                this.isLoadingCollection = true;             

                this.tainacanAxios.get('/collections/' + this.collectionId + '?fetch_only=name,thumbnail,header_image')
                    .then(response => {
                        this.collection = response.data;
                        this.isLoadingCollection = false;      
                    });
            }
        }
    },
    created() {
        this.tainacanAxios = axios.create({ baseURL: this.tainacanApiRoot });
        this.localOrder = this.order;
  
        if (this.showCollectionHeader)
            this.fetchCollectionForHeader();

        this.fetchFacets();
    },
}
</script>

<style lang="scss">

    @import './facets-list.scss';

</style>
