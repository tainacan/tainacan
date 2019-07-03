<template>
    <div :class="className">
        <div
                v-if="showSearchBar"
                class="facets-search-bar"> 
            <button
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
        </div>
        <ul
                v-if="isLoading"
                :style="{
                    gridTemplateColumns: layout == 'grid' ? 'repeat(auto-fill, ' + (gridMargin + 185) + 'px)' : 'inherit', 
                    marginTop: showSearchBar ? '1.5rem' : '0px'
                }"
                class="facets-list"
                :class="'facets-layout-' + layout + (!showName ? ' facets-list-without-margin' : '')">
                <li
                        :key="facet"
                        v-for="facet in Number(maxFacetsNumber)"
                        class="facet-list-item skeleton"
                        :style="{ 
                            marginBottom: layout == 'grid' && ((metadatumType == 'Relationship' || metadatumType == 'Taxonomy') && showImage) ? (showName ? gridMargin + 12 : gridMargin) + 'px' : '',
                            height: getSkeletonHeight()
                        }" />      
        </ul>
        <div v-else>
            <ul 
                    v-if="facets.length > 0"
                    :style="{
                        gridTemplateColumns: layout == 'grid' ? 'repeat(auto-fill, ' + (gridMargin + 185) + 'px)' : 'inherit', 
                        marginTop: showSearchBar ? '1.5rem' : '0px'
                    }"
                    class="facets-list"
                    :class="'facets-layout-' + layout">
                <li
                        :key="index"
                        v-for="(facet, index) of facets"
                        class="facet-list-item"
                        :class="(!showImage ? 'facet-without-image' : '')"
                        :style="{ marginBottom: layout == 'grid' ? gridMargin + 'px' : ''}">      
                    <a 
                            :id="isNaN(facet.id) ? facet.id : 'facet-id-' + facet.id"
                            :href="facet.url"
                            target="_blank"
                            :style="{ fontSize: layout == 'cloud' && facet.total_items ? + (1 + (cloudRate/4) * Math.log(facet.total_items)) + 'rem' : ''}">
                        <img
                            v-if="metadatumType == 'Taxonomy'"
                            :src=" 
                                facet.entity && facet.entity['header_image']
                                    ?    
                                facet.entity['header_image']
                                    : 
                                `${tainacanBaseUrl}/admin/images/placeholder_square.png`
                            "
                            :alt="facet.title ? facet.title : $root.__('Thumbnail', 'tainacan')">
                        <img
                            v-if="metadatumType == 'Relationship'"
                            :src=" 
                                facet.entity.thumbnail && facet.entity.thumbnail['tainacan-medium'][0] && facet.entity.thumbnail['tainacan-medium'][0] 
                                    ?
                                facet.entity.thumbnail['tainacan-medium'][0] 
                                    :
                                (facet.entity.thumbnail && facet.entity.thumbnail['thumbnail'][0] && facet.entity.thumbnail['thumbnail'][0]
                                    ?    
                                facet.entity.thumbnail['thumbnail'][0] 
                                    : 
                                `${tainacanBaseUrl}/admin/images/placeholder_square.png`)
                            "
                            :alt="facet.title ? facet.title : $root.__('Thumbnail', 'tainacan')">
                        <span>{{ facet.label ? facet.label : '' }}</span>
                        <span 
                                v-if="facet.total_items"
                                class="facet-item-count"
                                :style="{ display: !showItemsCount ? 'none' : '' }">
                            &nbsp;({{ facet.total_items }})
                        </span>
                    </a>
                </li>
            </ul>

            <button
                    v-if="showLoadMore && facets.length > 0 && (facets.length < totalFacets || lastTerm != '')"
                    @click="loadMore()"
                    class="show-more-button"
                    :label="$root.__('Show more', 'tainacan')">
                <span class="icon">
                    <i>
                        <svg
                                width="24"
                                height="24"
                                viewBox="4 3 24 24">
                            <path d="M 7.41,8.295 6,9.705 l 6,6 6,-6 -1.41,-1.41 -4.59,4.58 z"/>
                            <path
                                    d="M0 0h24v24H0z"
                                    fill="none"/>                        
                        </svg>
                    </i>
                </span>
            </button>
            <div
                    v-else
                    class="spinner-container">
                {{ facets.length > 0 ? '' : $root.__('Nothing found.', 'tainacan') }}
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
            offset: undefined,
            totalFacets: 0,
            lastTerm: undefined
        }
    },
    props: {
        metadatumId: String,  
        metadatumType: String,  
        collectionId: String,  
        collectionSlug: String,  
        showImage: Boolean,
        showItemsCount: Boolean,
        showSearchBar: Boolean,
        showLoadMore: Boolean,
        layout: String,
        cloudRate: Number,
        gridMargin: Number,
        maxFacetsNumber: Number,
        tainacanApiRoot: String,
        tainacanBaseUrl: String,
        tainacanSiteUrl: String,
        className: String
    },
    methods: {
        applySearchString: debounce(function(event) { 

            let value = event.target.value;

            if (this.searchString != value) {
                this.searchString = value;
                this.offset = 0;
                this.lastTerm = '';
                this.fetchFacets();
            }
        }, 500),
        loadMore() {
            this.offset += Number(this.maxFacetsNumber);
            this.fetchFacets();
        },
        fetchFacets() {
            if (this.offset == 0)
                this.facets = [];

            this.isLoading = true;
            
            if (this.facetsRequestSource != undefined && typeof this.facetsRequestSource == 'function')
                this.facetsRequestSource.cancel('Previous facets search canceled.');

            this.facetsRequestSource = axios.CancelToken.source();

            let endpoint = '/facets/' + this.metadatumId;
            let query = endpoint.split('?')[1];
            let queryObject = qs.parse(query);

            // Set up max facets to be shown
            if (this.maxFacetsNumber != undefined && Number(this.maxFacetsNumber) > 0)
                queryObject.number = this.maxFacetsNumber;
            else if (queryObject.number != undefined && queryObject.number > 0)
                this.localMaxFacetsNumber = queryObject.number;
            else {
                queryObject.number = 12;
                this.localMaxFacetsNumber = 12;
            }

            // Set up searching string
            if (this.searchString != undefined)
                queryObject.search = this.searchString;
            else if (queryObject.search != undefined)
                this.searchString = queryObject.search;
            else {
                delete queryObject.search;
                this.searchString = undefined;
            }

            // Set up paging
            queryObject.offset = this.offset;
            if (this.lastTerm != undefined)
                queryObject.last_term = this.lastTerm;
            
            // Parameter fo tech entity object with image and url
            queryObject['context'] = 'extended';

            endpoint = endpoint.split('?')[0] + '?' + qs.stringify(queryObject);
            
            this.tainacanAxios.get(endpoint, { cancelToken: this.facetsRequestSource.token })
                .then(response => {

                    if (this.metadatumType == 'Taxonomy') {
                        for (let facet of response.data.values) {
                            this.facets.push(Object.assign({ 
                                url: facet.entity && facet.entity.url ? facet.entity.url : this.tainacanSiteUrl + '/' + this.collectionSlug + '/#/?taxquery[0][compare]=IN&taxquery[0][taxonomy]=' + facet.taxonomy + '&taxquery[0][terms][0]=' + facet.value
                            }, facet));
                        }
                    } else {
                        for (let facet of response.data.values) {
                            this.facets.push(Object.assign({ 
                                url: this.tainacanSiteUrl + '/' + this.collectionSlug + '/#/?metaquery[0][key]=' + this.metadatumId + '&metaquery[0][value]=' + facet.value
                            }, facet));
                        }
                    }
 
                    this.isLoading = false;
                    this.totalFacets = Number(response.headers['x-wp-total']);
                    this.lastTerm = response.data.values.length > 0 ? response.data.last_term : '';

                }).catch(() => { 
                    this.isLoading = false;
                    // console.log(error);
                });
        },
        getSkeletonHeight() {
            switch(this.layout) {
                case 'grid':
                    if ((this.metadatumType == 'Relationship' || this.metadatumType == 'Taxonomy') && this.showImage)
                        return '230px';
                    else
                        return '24px'
                case 'list':
                    if ((this.metadatumType == 'Relationship' || this.metadatumType == 'Taxonomy') && this.showImage)
                        return '54px';
                    else
                        return '24px'
                default: return '54px';
            }
        }
    },
    created() {
        this.tainacanAxios = axios.create({ baseURL: this.tainacanApiRoot });
        this.offset = 0;
        this.fetchFacets();
    },
}
</script>

<style lang="scss">

    @import './facets-list.scss';

</style>
