const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

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
        appendChildTerms,
        itemsCountStyle,
        imageSize
    } = attributes;

    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
    return <div 
                { ...blockProps }
                data-module="facets-list"
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
                image-size={ imageSize }
                append-child-terms={ (appendChildTerms === true ? 'true' : 'false') }
                link-term-facets-to-term-page={ linkTermFacetsToTermPage === false ? 'false' : 'true' }
                layout={ layout }
                items-count-style={ itemsCountStyle }
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