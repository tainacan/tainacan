const { useBlockProps } = wp.blockEditor;

export default function({ attributes }) {
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
        appendChildTerms,
        itemsCountStyle,
        imageSize
    } = attributes;

    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = useBlockProps.save();
    return <div 
                { ...blockProps }
                data-module="facets-list"
                data-metadatum-id={ metadatumId }
                data-metadatum-type={ metadatumType }
                data-collection-id={ collectionId }  
                data-collection-slug={ collectionSlug }
                data-parent-term-id={ parentTerm ? parentTerm.id : undefined }  
                data-show-image={ '' + showImage }
                data-name-inside-image={ nameInsideImage === true ? 'true' : 'false' }
                data-show-items-count={ '' + showItemsCount }
                data-show-search-bar={ '' + showSearchBar }
                data-show-load-more={ '' + showLoadMore }
                data-image-size={ imageSize }
                data-append-child-terms={ (appendChildTerms === true ? 'true' : 'false') }
                data-link-term-facets-to-term-page={ linkTermFacetsToTermPage === false ? 'false' : 'true' }
                data-layout={ layout }
                data-items-count-style={ itemsCountStyle }
                data-cloud-rate={ cloudRate }
                data-grid-margin={ gridMargin }
                data-max-facets-number={ maxFacetsNumber }
                data-max-columns-count={ maxColumnsCount }
                data-tainacan-api-root={ tainacan_blocks.root }
                data-tainacan-site-url={ tainacan_blocks.site_url }
                id={ 'wp-block-tainacan-facets-list_' + blockId }>
                    { content }
            </div>
};