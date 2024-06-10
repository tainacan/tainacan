const { useBlockProps } = wp.blockEditor;

export default function({ attributes }) {
    const {
        termId,
        taxonomyId,
        collectionId,
        defaultViewMode,
        enabledViewModes,
        collectionDefaultViewMode,
        collectionEnabledViewModes,
        hideDisplayedMetadataButton,
        hideSortingArea,
        hideFilters,
        hideHideFiltersButton,
        hideSearch,
        hideAdvancedSearch,
        hideSortByButton,
        hideItemsThumbnail,
        hidePaginationArea,
        hideExposersButton,
        hideItemsPerPageButton,
        defaultItemsPerPage,
        hideGoToPageButton,
        showFiltersButtonInsideSearchControl,
        startWithFiltersHidden,
        filtersAsModal,
        showInlineViewModeOptions,
        showFullscreenWithViewModes,
        listType,
        backgroundColor,
        baseFontSize,
        filtersAreaWidth,
        inputColor,
        inputBackgroundColor,
        inputBorderColor,
        labelColor,
        infoColor,
        headingColor,
        skeletonColor,
        itemBackgroundColor,
        itemHoverBackgroundColor,
        itemHeadingHoverBackgroundColor,
        primaryColor,
        secondaryColor,
        order,
        orderBy,
        orderByMeta,
        orderByType,
        collectionOrderBy,
        collectionOrderByMeta,
        collectionOrderByType,
        shouldNotHideFiltersOnMobile
    } = attributes;
    
    let updatedListType = '' + listType;

    if (updatedListType === '' && collectionId)
        updatedListType = 'collection';
    else if (updatedListType === '' && termId && taxonomyId)
        updatedListType = 'term';
    
    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = useBlockProps.save();

    return <div 
            style={{
                'font-size': baseFontSize + 'px',
                '--tainacan-base-font-size': baseFontSize + 'px',
                '--tainacan-background-color': backgroundColor,
                '--tainacan-filter-menu-width-theme': filtersAreaWidth + '%',
                '--tainacan-input-color': inputColor,
                '--tainacan-input-background-color': inputBackgroundColor,
                '--tainacan-input-border-color': inputBorderColor,
                '--tainacan-label-color': labelColor,
                '--tainacan-info-color': infoColor,
                '--tainacan-heading-color': headingColor,
                '--tainacan-skeleton-color': skeletonColor,
                '--tainacan-item-background-color': itemBackgroundColor,
                '--tainacan-item-hover-background-color': itemHoverBackgroundColor,
                '--tainacan-item-heading-hover-background-color': itemHeadingHoverBackgroundColor,
                '--tainacan-primary': primaryColor,
                '--tainacan-secondary': secondaryColor
            }}
            { ...blockProps }>
        <main 
                id="tainacan-items-page"
                data-module="faceted-search"
                data-term-id={ updatedListType == 'term' ? termId : null }
                data-taxonomy={ updatedListType == 'term' ? 'tnc_tax_' + taxonomyId : null  }
                data-collection-id={ updatedListType == 'collection' ? collectionId : null }  
                data-default-view-mode={ defaultViewMode != 'none' ? defaultViewMode : (updatedListType == 'collection' ? collectionDefaultViewMode : (hideItemsThumbnail ? 'table' : 'masonry') ) }
                data-is-forced-view-mode={ defaultViewMode == 'none' ? 'true' : 'false' }
                data-enabled-view-modes={ enabledViewModes ? enabledViewModes.toString() : '' }  
                data-hide-filters = { hideFilters.toString() }
                data-hide-hide-filters-button= { hideHideFiltersButton.toString() }
                data-hide-search = { hideSearch.toString() }
                data-hide-advanced-search = { hideAdvancedSearch.toString() }
                data-hide-displayed-metadata-button = { hideDisplayedMetadataButton.toString() }
                data-hide-pagination-area = { hidePaginationArea.toString() }
                data-hide-sorting-area = { hideSortingArea.toString() }
                data-hide-items-thumbnail = { hideItemsThumbnail ? hideItemsThumbnail.toString() : 'false' }
                data-hide-sort-by-button = { hideSortByButton.toString() }
                data-hide-exposers-button = { hideExposersButton.toString() }
                data-hide-items-per-page-button = { hideItemsPerPageButton.toString() }
                data-default-items-per-page = { defaultItemsPerPage }
                data-hide-go-to-page-button = { hideGoToPageButton.toString() }
                data-show-filters-button-inside-search-control = { showFiltersButtonInsideSearchControl.toString() }
                data-start-with-filters-hidden = { startWithFiltersHidden.toString() }
                data-filters-as-modal = { filtersAsModal.toString() }
                data-show-inline-view-mode-options = { showInlineViewModeOptions.toString() }
                data-show-fullscreen-with-view-modes = { showFullscreenWithViewModes.toString() } 
                data-default-order = { order ? order : 'ASC' }
                data-default-orderby = { updatedListType == 'collection' ? (collectionOrderBy ? collectionOrderBy : 'date') : (orderBy ? orderBy : 'date') }
                data-default-orderby-meta = { updatedListType == 'collection' ? (collectionOrderByMeta ? collectionOrderByMeta : '') : (orderByMeta ? orderByMeta : '') }
                data-default-orderby-type = { updatedListType == 'collection' ? (collectionOrderByType ? collectionOrderByType : '') : (orderByType ? orderByType : '') }
                data-should-not-hide-filters-on-mobile = { shouldNotHideFiltersOnMobile ? shouldNotHideFiltersOnMobile.toString() : 'false' } >
        </main>
    </div>
};