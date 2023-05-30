<template>
    <li 
            class="facet-list-item"
            :class="(!showImage ? 'facet-without-image' : '') + (nameInsideImage ? ' facet-with-name-inside-image' : '') + (isCollapseInsteadOfLink(facet) ? ' facet-term-with-children': '')">
        <a 
                :id="isNaN(facetId) ? facetId : 'facet-id-' + facetId"
                :href="isCollapseInsteadOfLink(facet) ? null : ((linkTermFacetsToTermPage && isMetadatumTypeTaxonomy) ? facet.term_url : facet.url)"
                @click="() => { isCollapseInsteadOfLink(facet) ? displayChildTerms(facetId) : null }"
                :style="{ fontSize: layout == 'cloud' && facet.total_items ? + (1 + (cloudRate/4) * Math.log(facet.total_items)) + 'em' : ''}">
            <img
                :src=" 
                    facet.entity.thumbnail && facet.entity.thumbnail[imageSize][0] && facet.entity.thumbnail[imageSize][0] 
                        ?
                    facet.entity.thumbnail[imageSize][0] 
                        :
                    (facet.entity.thumbnail && facet.entity.thumbnail['thumbnail'][0] && facet.entity.thumbnail['thumbnail'][0]
                        ?    
                    facet.entity.thumbnail['thumbnail'][0] 
                        : 
                    `${tainacanBaseUrl}/assets/images/placeholder_square.png`)
                "
                :alt="facet.thumbnail_alt ? facet.thumbnail_alt : (facet.label ? facet.label : $root.__('Thumbnail', 'tainacan'))">
            <div :class=" 'facet-label-and-count' + (itemsCountStyle === 'below' ? ' is-style-facet-label-and-count--below' : '')">
                <span>{{ facet.label ? facet.label : '' }}</span>
                <span 
                        v-if="facet.total_items"
                        class="facet-item-count"
                        :style="{ display: !showItemsCount ? 'none' : '' }">
                    <template v-if="itemsCountStyle === 'below'">
                        {{ facet.total_items != 1 ? (facet.total_items + ' ' + $root.__('items', 'tainacan' )) : (facet.total_items + ' ' + $root.__('item', 'tainacan' )) }}
                    </template>
                    <template v-else>
                        &nbsp;({{ facet.total_items }})
                    </template>
                </span>
            </div>
            <template v-if="appendChildTerms && facet.total_children > 0 && (!childFacetsObject[facet.id != undefined ? facet.id : facet.value] || childFacetsObject[facet.id != undefined ? facet.id : facet.value].facets.length)">
                <svg 
                        v-if="childFacetsObject[facetId] && childFacetsObject[facetId].visible"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="-4 -3 16 16"
                        height="32px"
                        width="32px">
                    <g transform="translate(-69.294715,-148.68529)">
                        <path d="m 71.096008,154.8776 2.43204,-2.4257 2.4257,2.4257 0.7493,-0.74294 -3.175,-3.175 -3.175,3.175 z" />
                    </g>
                </svg>
                <svg 
                        v-else
                        xmlns="http://www.w3.org/2000/svg" 
                        viewBox="-4 -3 16 16"
                        height="32px"
                        width="32px">
                    <g transform="translate(-69.294715,-148.68529)">
                        <path d="m 71.096008,150.95966 2.43204,2.4257 2.4257,-2.4257 0.7493,0.74294 -3.175,3.175 -3.175,-3.175 z" />
                    </g>
                </svg>
            </template>
        </a>
        <template v-if="appendChildTerms && facet.total_children > 0">
            <ul
                    v-if="isLoadingChildTerms == (facet.id != undefined ? facet.id : facet.value)"
                    :style="{
                        gridGap: layout == 'grid' ? (gridMargin + 'px') : 'inherit',
                        marginTop: showSearchBar ? '1.5em' : '4px'
                    }"
                    class="facets-list"
                    :class="'facets-layout-' + layout + (!showName ? ' facets-list-without-margin' : '') + (maxColumnsCount ? ' max-columns-count-' + maxColumnsCount : '')">
                <li
                        :key="childFacet"
                        v-for="childFacet in Number(facet.total_children)"
                        class="facet-list-item skeleton"
                        :style="{ 
                            minHeight: getSkeletonHeight(),
                            marginTop: layout == 'list' ? '4px' : '',
                            marginLeft: layout == 'list' ? '7px' : '',
                            marginBottom: layout == 'grid' && showImage ? (showName ? gridMargin + 12 : gridMargin) + 'px' : ''
                        }" />
            </ul>
            <template v-else>
                <transition name="child-reveal">
                    <ul 
                            v-if="childFacetsObject[facet.id != undefined ? facet.id : facet.value] && childFacetsObject[facet.id != undefined ? facet.id : facet.value].visible"
                            class="child-term-facets">
                        <template v-if="childFacetsObject[facet.id != undefined ? facet.id : facet.value].facets.length">
                            <facets-list-theme-unit
                                    v-for="(aChildTermFacet, childFacetIndex) of childFacetsObject[facet.id != undefined ? facet.id : facet.value].facets"
                                    :key="childFacetIndex"
                                    :show-image="showImage"
                                    :name-inside-image="nameInsideImage"
                                    :child-facets-object="childFacetsObject"
                                    :facet="aChildTermFacet"
                                    :cloud-rate="cloudRate"
                                    :tainacan-base-url="tainacanBaseUrl"
                                    :layout="layout"
                                    :append-child-terms="appendChildTerms"
                                    :metadatum-type="metadatumType"
                                    :show-items-count="showItemsCount"
                                    :is-loading-child-terms="isloadingChildTerms"
                                    :link-term-facets-to-term-page="linkTermFacetsToTermPage"
                                    :is-metadatum-type-taxonomy="isMetadatumTypeTaxonomy"
                                    :is-metadatum-type-relationship="isMetadatumTypeRelationship"
                                    @on-display-child-terms="displayChildTerms" />
                        </template>
                        <p 
                                v-else 
                                class="no-child-facet-found">
                            {{ $root.__( 'The child terms of this facet do not contain items.', 'tainacan' ) }}
                        </p>
                    </ul>
                </transition>
            </template>
        </template>
    </li>
</template>

<script>
export default {
    props: {
        appendChildTerms: Boolean,
        facet: Object,
        tainacanBaseUrl: String,
        showImage: Boolean,
        showItemsCount: Boolean,
        showSearchBar: Boolean,
        nameInsideImage: Boolean,
        isLoadingChildTerms: Boolean,
        linkTermFacetsToTermPage: Boolean,
        layout: String,
        cloudRate: Number,
        metadatumType: String,
        childFacetsObject: Object,
        isMetadatumTypeTaxonomy: Boolean,
        isMetadatumTypeRelationship: Boolean,
        itemsCountStyle: String,
        imageSize: String
    },
    computed:{
        facetId() {
            return (this.facet.id != undefined ? this.facet.id : this.facet.value);
        }
    },
    methods: {
        displayChildTerms(facetId) {
            this.$emit('on-display-child-terms', facetId)
        },
        isCollapseInsteadOfLink(facet) {
            return (this.appendChildTerms && facet.total_children > 0 && (!this.childFacetsObject[facet.id != undefined ? facet.id : facet.value] || this.childFacetsObject[facet.id != undefined ? facet.id : facet.value].facets.length) );
        },
        getSkeletonHeight() {
            switch(this.layout) {
                case 'grid':
                    if ((this.isMetadatumTypeRelationship || this.isMetadatumTypeTaxonomy) && this.showImage)
                        return '230px';
                    else
                        return '24px'
                case 'list':
                    if ((this.isMetadatumTypeRelationship || this.isMetadatumTypeTaxonomy) && this.showImage)
                        return '54px';
                    else
                        return '24px'
                default: return '54px';
            }
        }
    }
}
</script>