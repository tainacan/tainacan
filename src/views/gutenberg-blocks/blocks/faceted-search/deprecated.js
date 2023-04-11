export default [
    /* Deprecated in version 0.19.4 due to add of orderby, orderbymeta and orderbykey */
    {
        attributes: {
            "termId": {
                "type": "String",
                "default": null
            },
            "taxonomyId": {
                "type": "String",
                "default": null
            },
            "collectionId": {
                "type": "String",
                "default": null
            },
            "defaultViewMode": {
                "type": "String",
                "default": "masonry"
            },
            "enabledViewModes": {
                "type": "Array",
                "default": null
            },
            "collectionDefaultViewMode": {
                "type": "String",
                "default": "masonry"
            },
            "collectionEnabledViewModes": {
                "type": "Array",
                "default": []
            },
            "hideFilters": {
                "type": "Boolean",
                "default": false
            },
            "hideHideFiltersButton": {
                "type": "Boolean",
                "default": false
            },
            "hideSearch": {
                "type": "Boolean",
                "default": false
            },
            "hideAdvancedSearch": {
                "type": "Boolean",
                "default": false
            },
            "hideDisplayedMetadataButton": {
                "type": "Boolean",
                "default": false
            },
            "hideSortingArea": {
                "type": "Boolean",
                "default": false
            },
            "hideSortByButton": {
                "type": "Boolean",
                "default": false
            },
            "hideItemsThumbnail": {
                "type": "Boolean",
                "default": false
            },
            "hideExposersButton": {
                "type": "Boolean",
                "default": false
            },
            "hideItemsPerPageButton": {
                "type": "Boolean",
                "default": false
            },
            "defaultItemsPerPage": {
                "type": "Number",
                "default": 12
            },
            "hideGoToPageButton": {
                "type": "Boolean",
                "default": false
            },
            "hidePaginationArea": {
                "type": "Boolean",
                "default": false
            },
            "showFiltersButtonInsideSearchControl": {
                "type": "Boolean",
                "default": false
            },
            "startWithFiltersHidden": {
                "type": "Boolean",
                "default": false
            },
            "filtersAsModal": {
                "type": "Boolean",
                "default": false
            },
            "showInlineViewModeOptions": {
                "type": "Boolean",
                "default": false
            },
            "showFullscreenWithViewModes": {
                "type": "Boolean",
                "default": false
            },
            "listType": {
                "type": "String",
                "default": ""
            },
            "isCollectionModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "isTermModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "backgroundColor": {
                "type": "String",
                "default": "#ffffff"
            },
            "baseFontSize": {
                "type": "Number",
                "default": 16
            },
            "filtersAreaWidth": {
                "type": "Number",
                "default": 20
            },
            "inputColor": {
                "type": "String",
                "default": "#1d1d1d"
            },
            "inputBackgroundColor": {
                "type": "String",
                "default": "#ffffff"
            },
            "inputBorderColor": {
                "type": "String",
                "default": "#dbdbdb"
            },
            "labelColor": {
                "type": "String",
                "default": "#454647"
            },
            "infoColor": {
                "type": "String",
                "default": "#555758"
            },
            "headingColor": {
                "type": "String",
                "default": "#000000"
            },
            "skeletonColor": {
                "type": "String",
                "default": "#eeeeee"
            },
            "itemBackgroundColor": {
                "type": "String",
                "default": "#ffffff"
            },
            "itemHoverBackgroundColor": {
                "type": "String",
                "default": "#f2f2f2"
            },
            "itemHeadingHoverBackgroundColor": {
                "type": "String",
                "default": "#dbdbdb"
            },
            "primaryColor": {
                "type": "String",
                "default": "#d9eced"
            },
            "secondaryColor": {
                "type": "String",
                "default": "#298596"
            }
        },
        supports: {
            align: ['full', 'wide'],
            html: true,
            multiple: false
        },
        save({ attributes, className }) {
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
                secondaryColor
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
                        hide-pagination-area = { hidePaginationArea.toString() }
                        hide-exposers-button = { hideExposersButton.toString() }
                        hide-items-per-page-button = { hideItemsPerPageButton.toString() }
                        default-items-per-page = { defaultItemsPerPage }
                        hide-go-to-page-button = { hideGoToPageButton.toString() }
                        show-filters-button-inside-search-control = { showFiltersButtonInsideSearchControl.toString() }
                        start-with-filters-hidden = { startWithFiltersHidden.toString() }
                        filters-as-modal = { filtersAsModal.toString() }
                        show-inline-view-mode-options = { showInlineViewModeOptions.toString() }
                        show-fullscreen-with-view-modes = { showFullscreenWithViewModes.toString() } >
                </main>
            </div>
        }
    },
    /* Deprecated in version 0.18.4 due to WP 5.8 support */
    {
        attributes: {
            termId: {
                type: String,
                default: undefined
            },
            taxonomyId: {
                type: String,
                default: undefined
            },
            collectionId: {
                type: String,
                default: undefined
            },
            defaultViewMode: {
                type: String,
                default: 'masonry'
            },
            enabledViewModes: {
                type: Array,
                default: Object.keys(tainacan_plugin.registered_view_modes)
            },
            collectionDefaultViewMode: {
                type: String,
                default: 'masonry'
            },
            collectionEnabledViewModes: {
                type: Array,
                default: []
            },
            hideFilters: {
                type: Boolean,
                default: false
            },
            hideHideFiltersButton: {
                type: Boolean,
                default: false
            },
            hideSearch: {
                type: Boolean,
                default: false
            },
            hideAdvancedSearch: {
                type: Boolean,
                default: false
            },
            hideDisplayedMetadataButton: {
                type: Boolean,
                default: false
            },
            hideSortingArea: {
                type: Boolean,
                default: false
            },
            hideSortByButton: {
                type: Boolean,
                default: false
            },
            hideItemsThumbnail: {
                type: Boolean,
                default: false
            },
            hideExposersButton: {
                type: Boolean,
                default: false
            },
            hideItemsPerPageButton: {
                type: Boolean,
                default: false
            },
            defaultItemsPerPage: {
                type: Number,
                default: 12
            },
            hideGoToPageButton: {
                type: Boolean,
                default: false
            },
            hidePaginationArea: {
                type: Boolean,
                default: false
            },
            showFiltersButtonInsideSearchControl: {
                type: Boolean,
                default: false
            },
            startWithFiltersHidden: {
                type: Boolean,
                default: false
            },
            filtersAsModal: {
                type: Boolean,
                default: false
            },
            showInlineViewModeOptions: {
                type: Boolean,
                default: false
            },
            showFullscreenWithViewModes: {
                type: Boolean,
                default: false
            },
            listType: {
                type: String,
                default: ''
            },
            isCollectionModalOpen: {
                type: Boolean,
                default: false
            },
            isTermModalOpen: {
                type: Boolean,
                default: false
            },
            backgroundColor: {
                type: String,
                default: '#ffffff'
            },
            baseFontSize: {
                type: Number,
                default: 16
            },
            filtersAreaWidth: {
                type: Number,
                default: 20
            },
            inputColor: {
                type: String,
                default: '#1d1d1d'
            },
            inputBackgroundColor: {
                type: String,
                default: '#ffffff'
            },
            inputBorderColor: {
                type: String,
                default: '#dbdbdb'
            },
            labelColor: {
                type: String,
                default: '#454647'
            },
            infoColor: {
                type: String,
                default: '#555758'
            },
            headingColor: {
                type: String,
                default: '#000000'
            },
            skeletonColor: {
                type: String,
                default: '#eeeeee'
            },
            itemBackgroundColor: {
                type: String,
                default: '#ffffff'
            },
            itemHoverBackgroundColor: {
                type: String,
                default: '#f2f2f2'
            },
            itemHeadingHoverBackgroundColor: {
                type: String,
                default: '#dbdbdb'
            },
            primaryColor: {
                type: String,
                default: '#d9eced'
            },
            secondaryColor: {
                type: String,
                default: '#298596'
            }
        },
        supports: {
            align: ['full', 'wide'],
            html: true,
            multiple: false
        },
        save({ attributes, className }) {
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
                secondaryColor
            } = attributes;
            
            let updatedListType = '' + listType;
    
            if (updatedListType === '' && collectionId)
                updatedListType = 'collection';
            else if (updatedListType === '' && termId && taxonomyId)
                updatedListType = 'term' 
    
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
                            term-id={ updatedListType == 'term' ? termId : null }
                            taxonomy={ updatedListType == 'term' ? 'tnc_tax_' + taxonomyId : null  }
                            collection-id={ updatedListType == 'collection' ? collectionId : null }  
                            default-view-mode={ defaultViewMode != 'none' ? defaultViewMode : (updatedListType == 'collection' ? collectionDefaultViewMode : (hideItemsThumbnail ? 'table' : 'masonry') ) }
                            is-forced-view-mode={ defaultViewMode == 'none' ? true : false }
                            enabled-view-modes={ enabledViewModes.toString() }  
                            hide-filters = { hideFilters.toString() }
                            hide-hide-filters-button= { hideHideFiltersButton.toString() }
                            hide-search = { hideSearch.toString() }
                            hide-advanced-search = { hideAdvancedSearch.toString() }
                            hide-displayed-metadata-button = { hideDisplayedMetadataButton.toString() }
                            hide-pagination-area = { hidePaginationArea.toString() }
                            hide-sorting-area = { hideSortingArea.toString() }
                            hide-items-thumbnail = { hideItemsThumbnail ? hideItemsThumbnail.toString() : 'false' }
                            hide-sort-by-button = { hideSortByButton.toString() }
                            hide-pagination-area = { hidePaginationArea.toString() }
                            hide-exposers-button = { hideExposersButton.toString() }
                            hide-items-per-page-button = { hideItemsPerPageButton.toString() }
                            default-items-per-page = { defaultItemsPerPage }
                            hide-go-to-page-button = { hideGoToPageButton.toString() }
                            show-filters-button-inside-search-control = { showFiltersButtonInsideSearchControl.toString() }
                            start-with-filters-hidden = { startWithFiltersHidden.toString() }
                            filters-as-modal = { filtersAsModal.toString() }
                            show-inline-view-mode-options = { showInlineViewModeOptions.toString() }
                            show-fullscreen-with-view-modes = { showFullscreenWithViewModes.toString() }
                            id="tainacan-items-page">
                    </main>
                </div>
        }
    },
    {
        attributes: {
            termId: {
                type: String,
                default: undefined
            },
            taxonomyId: {
                type: String,
                default: undefined
            },
            collectionId: {
                type: String,
                default: undefined
            },
            defaultViewMode: {
                type: String,
                default: 'masonry'
            },
            enabledViewModes: {
                type: Array,
                default: Object.keys(tainacan_plugin.registered_view_modes)
            },
            collectionDefaultViewMode: {
                type: String,
                default: 'masonry'
            },
            collectionEnabledViewModes: {
                type: Array,
                default: []
            },
            hideFilters: {
                type: Boolean,
                default: false
            },
            hideHideFiltersButton: {
                type: Boolean,
                default: false
            },
            hideSearch: {
                type: Boolean,
                default: false
            },
            hideAdvancedSearch: {
                type: Boolean,
                default: false
            },
            hideDisplayedMetadataButton: {
                type: Boolean,
                default: false
            },
            hideSortingArea: {
                type: Boolean,
                default: false
            },
            hideSortByButton: {
                type: Boolean,
                default: false
            },
            hideExposersButton: {
                type: Boolean,
                default: false
            },
            hideItemsPerPageButton: {
                type: Boolean,
                default: false
            },
            defaultItemsPerPage: {
                type: Number,
                default: 12
            },
            hideGoToPageButton: {
                type: Boolean,
                default: false
            },
            hidePaginationArea: {
                type: Boolean,
                default: false
            },
            showFiltersButtonInsideSearchControl: {
                type: Boolean,
                default: false
            },
            startWithFiltersHidden: {
                type: Boolean,
                default: false
            },
            filtersAsModal: {
                type: Boolean,
                default: false
            },
            showInlineViewModeOptions: {
                type: Boolean,
                default: false
            },
            showFullscreenWithViewModes: {
                type: Boolean,
                default: false
            },
            listType: {
                type: String,
                default: 'collection'
            },
            isCollectionModalOpen: {
                type: Boolean,
                default: false
            },
            isTermModalOpen: {
                type: Boolean,
                default: false
            },
            backgroundColor: {
                type: String,
                default: '#ffffff'
            },
            baseFontSize: {
                type: Number,
                default: 16
            },
            filtersAreaWidth: {
                type: Number,
                default: 20
            },
            inputColor: {
                type: String,
                default: '#1d1d1d'
            },
            inputBackgroundColor: {
                type: String,
                default: '#ffffff'
            },
            inputBorderColor: {
                type: String,
                default: '#dbdbdb'
            },
            labelColor: {
                type: String,
                default: '#454647'
            },
            infoColor: {
                type: String,
                default: '#555758'
            },
            headingColor: {
                type: String,
                default: '#000000'
            },
            skeletonColor: {
                type: String,
                default: '#eeeeee'
            },
            itemBackgroundColor: {
                type: String,
                default: '#ffffff'
            },
            itemHoverBackgroundColor: {
                type: String,
                default: '#f2f2f2'
            },
            itemHeadingHoverBackgroundColor: {
                type: String,
                default: '#dbdbdb'
            },
            primaryColor: {
                type: String,
                default: '#d9eced'
            },
            secondaryColor: {
                type: String,
                default: '#298596'
            }
        },
        save({ attributes, className }){
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
                secondaryColor
            } = attributes;
            
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
                            term-id={ listType == 'term' ? termId : null }
                            taxonomy={ listType == 'term' ? 'tnc_tax_' + taxonomyId : null  }
                            collection-id={ listType == 'collection' ? collectionId : null }  
                            default-view-mode={ defaultViewMode != 'none' ? defaultViewMode : (listType == 'collection' ? collectionDefaultViewMode : 'masonry') }
                            is-forced-view-mode={ defaultViewMode == 'none' ? true : false }
                            enabled-view-modes={ enabledViewModes.toString() }  
                            hide-filters = { hideFilters.toString() }
                            hide-hide-filters-button= { hideHideFiltersButton.toString() }
                            hide-search = { hideSearch.toString() }
                            hide-advanced-search = { hideAdvancedSearch.toString() }
                            hide-displayed-metadata-button = { hideDisplayedMetadataButton.toString() }
                            hide-pagination-area = { hidePaginationArea.toString() }
                            hide-sorting-area = { hideSortingArea.toString() }
                            hide-sort-by-button = { hideSortByButton.toString() }
                            hide-pagination-area = { hidePaginationArea.toString() }
                            hide-exposers-button = { hideExposersButton.toString() }
                            hide-items-per-page-button = { hideItemsPerPageButton.toString() }
                            default-items-per-page = { defaultItemsPerPage }
                            hide-go-to-page-button = { hideGoToPageButton.toString() }
                            show-filters-button-inside-search-control = { showFiltersButtonInsideSearchControl.toString() }
                            start-with-filters-hidden = { startWithFiltersHidden.toString() }
                            filters-as-modal = { filtersAsModal.toString() }
                            show-inline-view-mode-options = { showInlineViewModeOptions.toString() }
                            show-fullscreen-with-view-modes = { showFullscreenWithViewModes.toString() }
                            id="tainacan-items-page">
                    </main>
                </div>
        }
    },
    {
        attributes: {
            termId: {
                type: String,
                default: undefined
            },
            taxonomyId: {
                type: String,
                default: undefined
            },
            collectionId: {
                type: String,
                default: undefined
            },
            defaultViewMode: {
                type: String,
                default: 'masonry'
            },
            enabledViewModes: {
                type: Array,
                default: Object.keys(tainacan_plugin.registered_view_modes)
            },
            collectionDefaultViewMode: {
                type: String,
                default: 'masonry'
            },
            collectionEnabledViewModes: {
                type: Array,
                default: []
            },
            hideFilters: {
                type: Boolean,
                default: false
            },
            hideHideFiltersButton: {
                type: Boolean,
                default: false
            },
            hideSearch: {
                type: Boolean,
                default: false
            },
            hideAdvancedSearch: {
                type: Boolean,
                default: false
            },
            hideDisplayedMetadataButton: {
                type: Boolean,
                default: false
            },
            hideSortingArea: {
                type: Boolean,
                default: false
            },
            hideSortByButton: {
                type: Boolean,
                default: false
            },
            hideExposersButton: {
                type: Boolean,
                default: false
            },
            hideItemsPerPageButton: {
                type: Boolean,
                default: false
            },
            defaultItemsPerPage: {
                type: Number,
                default: 12
            },
            hideGoToPageButton: {
                type: Boolean,
                default: false
            },
            hidePaginationArea: {
                type: Boolean,
                default: false
            },
            showFiltersButtonInsideSearchControl: {
                type: Boolean,
                default: false
            },
            startWithFiltersHidden: {
                type: Boolean,
                default: false
            },
            filtersAsModal: {
                type: Boolean,
                default: false
            },
            showInlineViewModeOptions: {
                type: Boolean,
                default: false
            },
            showFullscreenWithViewModes: {
                type: Boolean,
                default: false
            },
            listType: {
                type: String,
                default: ''
            },
            isCollectionModalOpen: {
                type: Boolean,
                default: false
            },
            isTermModalOpen: {
                type: Boolean,
                default: false
            },
            backgroundColor: {
                type: String,
                default: '#ffffff'
            },
            baseFontSize: {
                type: Number,
                default: 16
            },
            filtersAreaWidth: {
                type: Number,
                default: 20
            },
            inputColor: {
                type: String,
                default: '#1d1d1d'
            },
            inputBackgroundColor: {
                type: String,
                default: '#ffffff'
            },
            inputBorderColor: {
                type: String,
                default: '#dbdbdb'
            },
            labelColor: {
                type: String,
                default: '#454647'
            },
            infoColor: {
                type: String,
                default: '#555758'
            },
            headingColor: {
                type: String,
                default: '#000000'
            },
            skeletonColor: {
                type: String,
                default: '#eeeeee'
            },
            itemBackgroundColor: {
                type: String,
                default: '#ffffff'
            },
            itemHoverBackgroundColor: {
                type: String,
                default: '#f2f2f2'
            },
            itemHeadingHoverBackgroundColor: {
                type: String,
                default: '#dbdbdb'
            },
            primaryColor: {
                type: String,
                default: '#d9eced'
            },
            secondaryColor: {
                type: String,
                default: '#298596'
            }
        },
        save({ attributes, className }) {
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
                secondaryColor
            } = attributes;
            
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
                            term-id={ listType == 'term' ? termId : null }
                            taxonomy={ listType == 'term' ? 'tnc_tax_' + taxonomyId : null  }
                            collection-id={ listType == 'collection' ? collectionId : null }  
                            default-view-mode={ defaultViewMode != 'none' ? defaultViewMode : (listType == 'collection' ? collectionDefaultViewMode : 'masonry') }
                            is-forced-view-mode={ defaultViewMode == 'none' ? true : false }
                            enabled-view-modes={ enabledViewModes.toString() }  
                            hide-filters = { hideFilters.toString() }
                            hide-hide-filters-button= { hideHideFiltersButton.toString() }
                            hide-search = { hideSearch.toString() }
                            hide-advanced-search = { hideAdvancedSearch.toString() }
                            hide-displayed-metadata-button = { hideDisplayedMetadataButton.toString() }
                            hide-pagination-area = { hidePaginationArea.toString() }
                            hide-sorting-area = { hideSortingArea.toString() }
                            hide-sort-by-button = { hideSortByButton.toString() }
                            hide-pagination-area = { hidePaginationArea.toString() }
                            hide-exposers-button = { hideExposersButton.toString() }
                            hide-items-per-page-button = { hideItemsPerPageButton.toString() }
                            default-items-per-page = { defaultItemsPerPage }
                            hide-go-to-page-button = { hideGoToPageButton.toString() }
                            show-filters-button-inside-search-control = { showFiltersButtonInsideSearchControl.toString() }
                            start-with-filters-hidden = { startWithFiltersHidden.toString() }
                            filters-as-modal = { filtersAsModal.toString() }
                            show-inline-view-mode-options = { showInlineViewModeOptions.toString() }
                            show-fullscreen-with-view-modes = { showFullscreenWithViewModes.toString() }
                            id="tainacan-items-page">
                    </main>
                </div>
        },
    }
];