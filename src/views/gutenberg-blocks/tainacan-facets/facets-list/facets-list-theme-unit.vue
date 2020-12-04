<template>
    <li 
            class="facet-list-item"
            :class="(!showImage ? 'facet-without-image' : '') + (nameInsideImage ? ' facet-with-name-inside-image' : '') + ((appendChildTerms && facet.total_children > 0) ? ' facet-term-with-children': '')"
            :style="{ marginBottom: layout == 'grid' ? gridMargin + 'px' : ''}">
        <a 
                :id="isNaN(facetId) ? facetId : 'facet-id-' + facetId"
                :href="(appendChildTerms && facet.total_children > 0) ? null : ((linkTermFacetsToTermPage && isMetadatumTypeTaxonomy) ? facet.term_url : facet.url)"
                @click="() => { (appendChildTerms && facet.total_children > 0) ? displayChildTerms(facetId) : null }"
                target="_blank"
                :style="{ fontSize: layout == 'cloud' && facet.total_items ? + (1 + (cloudRate/4) * Math.log(facet.total_items)) + 'em' : ''}">
            <img
                v-if="isMetadatumTypeTaxonomy"
                :src=" 
                    facet.entity && facet.entity['header_image']
                        ?    
                    facet.entity['header_image']
                        : 
                    `${tainacanBaseUrl}/assets/images/placeholder_square.png`
                "
                :alt="facet.label ? facet.label : $root.__('Thumbnail', 'tainacan')">
            <img
                v-if="isMetadatumTypeRelationship"
                :src=" 
                    facet.entity.thumbnail && facet.entity.thumbnail['tainacan-medium'][0] && facet.entity.thumbnail['tainacan-medium'][0] 
                        ?
                    facet.entity.thumbnail['tainacan-medium'][0] 
                        :
                    (facet.entity.thumbnail && facet.entity.thumbnail['thumbnail'][0] && facet.entity.thumbnail['thumbnail'][0]
                        ?    
                    facet.entity.thumbnail['thumbnail'][0] 
                        : 
                    `${tainacanBaseUrl}/assets/images/placeholder_square.png`)
                "
                :alt="facet.label ? facet.label : $root.__('Thumbnail', 'tainacan')">
            <span>{{ facet.label ? facet.label : '' }}</span>
            <span 
                    v-if="facet.total_items"
                    class="facet-item-count"
                    :style="{ display: !showItemsCount ? 'none' : '' }">
                &nbsp;({{ facet.total_items }})
            </span>
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
                            marginBottom: layout == 'grid' && showImage ? (showName ? gridMargin + 12 : gridMargin) + 'px' : '',
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
                            {{ $root.__( 'This facet children terms do not contain items.', 'tainacan' ) }}
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
        isMetadatumTypeRelationship: Boolean
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