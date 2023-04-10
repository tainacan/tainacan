export default function({ attributes, className }) {
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
        collectionOrderByType
    } = attributes;
    
    let updatedListType = '' + listType;

    if (updatedListType === '' && collectionId)
        updatedListType = 'collection';
    else if (updatedListType === '' && termId && taxonomyId)
        updatedListType = 'term';
        
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
            className={ className }>
        <main 
                id="tainacan-items-page"
                data-module="faceted-search"
                term-id={ updatedListType == 'term' ? termId : null }
                taxonomy={ updatedListType == 'term' ? 'tnc_tax_' + taxonomyId : null  }
                collection-id={ updatedListType == 'collection' ? collectionId : null }  
                default-view-mode={ defaultViewMode != 'none' ? defaultViewMode : (updatedListType == 'collection' ? collectionDefaultViewMode : (hideItemsThumbnail ? 'table' : 'masonry') ) }
                is-forced-view-mode={ defaultViewMode == 'none' ? 'true' : 'false' }
                enabled-view-modes={ enabledViewModes ? enabledViewModes.toString() : '' }  
                hide-filters = { hideFilters.toString() }
                hide-hide-filters-button= { hideHideFiltersButton.toString() }
                hide-search = { hideSearch.toString() }
                hide-advanced-search = { hideAdvancedSearch.toString() }
                hide-displayed-metadata-button = { hideDisplayedMetadataButton.toString() }
                hide-pagination-area = { hidePaginationArea.toString() }
                hide-sorting-area = { hideSortingArea.toString() }
                hide-items-thumbnail = { hideItemsThumbnail ? hideItemsThumbnail.toString() : 'false' }
                hide-sort-by-button = { hideSortByButton.toString() }
                hide-exposers-button = { hideExposersButton.toString() }
                hide-items-per-page-button = { hideItemsPerPageButton.toString() }
                default-items-per-page = { defaultItemsPerPage }
                hide-go-to-page-button = { hideGoToPageButton.toString() }
                show-filters-button-inside-search-control = { showFiltersButtonInsideSearchControl.toString() }
                start-with-filters-hidden = { startWithFiltersHidden.toString() }
                filters-as-modal = { filtersAsModal.toString() }
                show-inline-view-mode-options = { showInlineViewModeOptions.toString() }
                show-fullscreen-with-view-modes = { showFullscreenWithViewModes.toString() } 
                default-order = { order ? order : 'ASC' }
                default-orderby = { updatedListType == 'collection' ? (collectionOrderBy ? collectionOrderBy : 'date') : (orderBy ? orderBy : 'date') }
                default-orderby-meta = { updatedListType == 'collection' ? (collectionOrderByMeta ? collectionOrderByMeta : '') : (orderByMeta ? orderByMeta : '') }
                default-orderby-type = { updatedListType == 'collection' ? (collectionOrderByType ? collectionOrderByType : '') : (orderByType ? orderByType : '') } >
        </main>
    </div>
};