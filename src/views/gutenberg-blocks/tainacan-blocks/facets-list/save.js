export default function({ attributes, className }) {
    const {
        content, 
        blockId,
        collectionId,  
        collectionSlug,
        parentTerm,  
        showImage,
        nameInsideImage,
        showItemsCount,
        showLoadMore,
        layout,
        cloudRate,
        gridMargin,
        metadatumId,
        metadatumType,
        maxFacetsNumber,
        maxColumnsCount,
        showSearchBar,
        linkTermFacetsToTermPage,
        appendChildTerms
    } = attributes;
    return <div 
                data-module="facets-list"
                className={ className }
                metadatum-id={ metadatumId }
                metadatum-type={ metadatumType }
                collection-id={ collectionId }  
                collection-slug={ collectionSlug }
                parent-term-id={ parentTerm ? parentTerm.id : undefined }  
                show-image={ '' + showImage }
                name-inside-image={ nameInsideImage === true ? 'true' : 'false' }
                show-items-count={ '' + showItemsCount }
                show-search-bar={ '' + showSearchBar }
                show-load-more={ '' + showLoadMore }
                append-child-terms={ (appendChildTerms === true ? 'true' : 'false') }
                link-term-facets-to-term-page={ linkTermFacetsToTermPage === false ? 'false' : 'true' }
                layout={ layout }
                cloud-rate={ cloudRate }
                grid-margin={ gridMargin }
                max-facets-number={ maxFacetsNumber }
                max-columns-count={ maxColumnsCount }
                tainacan-api-root={ tainacan_blocks.root }
                tainacan-base-url={ tainacan_blocks.base_url }
                tainacan-site-url={ tainacan_blocks.site_url }
                id={ 'wp-block-tainacan-facets-list_' + blockId }>
                    { content }
            </div>
};