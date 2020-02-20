const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { Button, ColorPicker, RangeControl, FontSizePicker, SelectControl, ToggleControl, Placeholder, PanelBody } = wp.components;

const { InspectorControls } = wp.editor;

import CollectionModal from './collection-modal.js';
import TermModal from './term-modal.js';

registerBlockType('tainacan/faceted-search', {
    title: __('Tainacan Faceted Search', 'tainacan'),
    icon:
        <svg 
                xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 24 24"
                height="24px"
                width="24px">
            <path 
                    fill="#298596"
                    d="M21.43,13.64,19.32,16a2.57,2.57,0,0,1-2,1H11a3.91,3.91,0,0,0,0-.49,5.49,5.49,0,0,0-5-5.47V9.64A2.59,2.59,0,0,1,8.59,7H17.3a2.57,2.57,0,0,1,2,1l2.11,2.38A2.59,2.59,0,0,1,21.43,13.64ZM4,3A2,2,0,0,0,2,5v7.3a5.32,5.32,0,0,1,2-1V5H16V3ZM11,21l-1,1L8.86,20.89,8,20H8l-.57-.57A3.42,3.42,0,0,1,5.5,20a3.5,3.5,0,0,1,0-7,2.74,2.74,0,0,1,.5,0A3.5,3.5,0,0,1,9,16a2.92,2.92,0,0,1,0,.51,3.42,3.42,0,0,1-.58,1.92L9,19H9l.85.85Zm-4-4.5A1.5,1.5,0,1,0,5.5,18,1.5,1.5,0,0,0,7,16.53Z"/>
        </svg>,
    category: 'tainacan-blocks',
    keywords: [ __( 'facets', 'tainacan' ), __( 'search', 'tainacan' ), __( 'items', 'tainacan' ) ],
    description: __('A full items list faceted search from either the repository, a collection or a term.', 'tainacan'),
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
            default: [ 'cards', 'masonry', 'table' ]
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
        hideGoToPageButton: {
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
        }
    },
    supports: {
        align: ['full', 'wide'],
        html: true,
        multiple: false
    },
    edit({ attributes, setAttributes, className, isSelected, clientId }){
        let {
            termId,
            taxonomyId,
            collectionId,
            defaultViewMode,
            enabledViewModes,
            hideFilters,
            hideHideFiltersButton,
            hideSearch,
            hideAdvancedSearch,
            hideSortByButton,
            hideExposersButton,
            hideItemsPerPageButton,
            hideGoToPageButton,
            startWithFiltersHidden,
            filtersAsModal,
            showInlineViewModeOptions,
            showFullscreenWithViewModes,
            listType,
            isCollectionModalOpen,
            isTermModalOpen,
            backgroundColor,
            baseFontSize,
            filtersAreaWidth
        } = attributes;

        const fontSizes = [
            {
                name: __( 'Tiny', 'tainacan' ),
                slug: 'tiny',
                size: 12,
            },
            {
                name: __( 'Small', 'tainacan' ),
                slug: 'small',
                size: 14,
            },
            {
                name: __( 'Normal', 'tainacan' ),
                slug: 'normal',
                size: 16,
            },
            {
                name: __( 'Big', 'tainacan' ),
                slug: 'big',
                size: 18,
            },
            {
                name: __( 'Huge', 'tainacan' ),
                slug: 'huge',
                size: 20,
            },
        ];

        function openCollectionModal() {
            isCollectionModalOpen = true;
            setAttributes( { 
                isCollectionModalOpen: isCollectionModalOpen
            } );
        }

        function openTermModal() {
            isTermModalOpen = true;
            setAttributes( { 
                isTermModalOpen: isTermModalOpen
            } );
        }

        return (
            <div className={className}>

                <div>
                    <InspectorControls>
                        
                        <PanelBody
                            title={__('Search Control Area', 'tainacan')}
                            initialOpen={ true }
                            >
                            <ToggleControl
                                label={__('Hide search input', 'tainacan')}
                                help={ hideSearch ? __('Do not show textual search input', 'tainacan') : __('Toggle to show textual search input', 'tainacan')}
                                checked={ hideSearch }
                                onChange={ ( isChecked ) => {
                                        hideSearch = isChecked;
                                        setAttributes({ hideSearch: isChecked });
                                    } 
                                }
                            />
                            <ToggleControl
                                label={__('Hide advanced search', 'tainacan')}
                                help={ hideAdvancedSearch || hideSearch ? __('Do not show advanced search', 'tainacan') : __('Toggle to show advanced search', 'tainacan')}
                                checked={ hideAdvancedSearch || hideSearch }
                                onChange={ ( isChecked ) => {
                                        hideAdvancedSearch = isChecked;
                                        setAttributes({ hideAdvancedSearch: isChecked });
                                    }  
                                }
                            />
                            <ToggleControl
                                label={__('Hide "Sort By" button', 'tainacan')}
                                help={ hideSortByButton ? __('Do not show "Sort By" button', 'tainacan') : __('Toggle to show "Sort By" button', 'tainacan')}
                                checked={ hideSortByButton }
                                onChange={ ( isChecked ) => {
                                        hideSortByButton = isChecked;
                                        setAttributes({ hideSortByButton: isChecked });
                                    } 
                                }
                            />
                            <ToggleControl
                                label={__('Show inline View Mode options', 'tainacan')}
                                help={ showInlineViewModeOptions ? __('Toggle to show View Mode options inline instead of a dropdown', 'tainacan') : __('Keep view mode options as a dropdown', 'tainacan')}
                                checked={ showInlineViewModeOptions }
                                onChange={ ( isChecked ) => {
                                        showInlineViewModeOptions = isChecked;
                                        setAttributes({ showInlineViewModeOptions: isChecked });
                                    } 
                                }
                            />
                            <ToggleControl
                                label={__('Show Fullscreen with other View Modes', 'tainacan')}
                                help={ showFullscreenWithViewModes ? __('Toggle to show Fullscreen view mode alongside with other View Modes', 'tainacan') : __('Keep Fullscreen view mode separated from other View Mode Options', 'tainacan')}
                                checked={ showFullscreenWithViewModes }
                                onChange={ ( isChecked ) => {
                                        showFullscreenWithViewModes = isChecked;
                                        setAttributes({ showFullscreenWithViewModes: isChecked });
                                    } 
                                }
                            />
                            <ToggleControl
                                label={__('Hide "View as..." button', 'tainacan')}
                                help={ hideExposersButton ? __('Do not show "View as...", the "Exposers" button', 'tainacan') : __('Toggle to show "View as...", the "Exposers" button', 'tainacan')}
                                checked={ hideExposersButton }
                                onChange={ ( isChecked ) => {
                                        hideExposersButton = isChecked;
                                        setAttributes({ hideExposersButton: isChecked });
                                    } 
                                }
                            />
                        </PanelBody>

                        <PanelBody
                                title={__('Filters Area', 'tainacan')}
                                initialOpen={ true }
                            >
                            <ToggleControl
                                label={__('Hide Filters', 'tainacan')}
                                help={ hideFilters ? __('Do not show Filters with the list', 'tainacan') : __('Toggle to show Filters with the list', 'tainacan')}
                                checked={ hideFilters }
                                onChange={ ( isChecked ) => {
                                        hideFilters = isChecked;
                                        setAttributes({ hideFilters: isChecked });
                                    } 
                                }
                            />
                            <ToggleControl
                                label={__('Hide "Hide Filters" button', 'tainacan')}
                                help={ hideHideFiltersButton || hideFilters ? __('Do not show "Hide Filters" button', 'tainacan') : __('Toggle to show "Hide Filters" button', 'tainacan')}
                                checked={ hideHideFiltersButton || hideFilters }
                                onChange={ ( isChecked ) => {
                                        hideHideFiltersButton = isChecked;
                                        setAttributes({ hideHideFiltersButton: isChecked });
                                    } 
                                }
                            />
                            <ToggleControl
                                label={__('Start with Filters hidden', 'tainacan')}
                                help={ startWithFiltersHidden ? __('Render the list with filters hidden at first" button', 'tainacan') : __('Toggle to render the list with filters visible at first', 'tainacan')}
                                checked={ startWithFiltersHidden }
                                onChange={ ( isChecked ) => {
                                        startWithFiltersHidden = isChecked;
                                        setAttributes({ startWithFiltersHidden: isChecked });
                                    } 
                                }
                            />
                            <ToggleControl
                                label={__('Filters as a Modal', 'tainacan')}
                                help={ filtersAsModal ? __('Render the filters area as modal instead of a collapse" button', 'tainacan') : __('Toggle to show filters list as a collapse instead of a modal', 'tainacan')}
                                checked={ filtersAsModal }
                                onChange={ ( isChecked ) => {
                                        filtersAsModal = isChecked;
                                        setAttributes({ filtersAsModal: isChecked });
                                    } 
                                }
                            />
                        </PanelBody>

                        <PanelBody
                                title={__('Pagination Area', 'tainacan')}
                                initialOpen={ true }
                            >
                            <ToggleControl
                                label={__('Hide "Items per Page" button', 'tainacan')}
                                help={ hideItemsPerPageButton ? __('Do not show "Items per Page" button', 'tainacan') : __('Toggle to show "Items per Page" button', 'tainacan')}
                                checked={ hideItemsPerPageButton }
                                onChange={ ( isChecked ) => {
                                        hideItemsPerPageButton = isChecked;
                                        setAttributes({ hideItemsPerPageButton: isChecked });
                                    } 
                                }
                            />
                            <ToggleControl
                                label={__('Hide "Go to Page" button', 'tainacan')}
                                help={ hideGoToPageButton ? __('Do not show "Go to Page" button', 'tainacan') : __('Toggle to show "Go to Page" button', 'tainacan')}
                                checked={ hideGoToPageButton }
                                onChange={ ( isChecked ) => {
                                        hideGoToPageButton = isChecked;
                                        setAttributes({ hideGoToPageButton: isChecked });
                                    } 
                                }
                            />
                        </PanelBody>

                        <PanelBody
                                title={__('Colors and Sizes', 'tainacan')}
                                initialOpen={ false }
                            >
                            <ColorPicker
                                color={ backgroundColor }
                                onChangeComplete={ (colorValue ) => {
                                    backgroundColor = colorValue.hex;
                                    setAttributes({ backgroundColor: backgroundColor });
                                }}
                            />
                            <FontSizePicker
                                fontSizes={ fontSizes }
                                value={ baseFontSize }
                                fallbackFontSize={ 16 }
                                onChange={ ( newFontSize ) => {
                                    setAttributes( { baseFontSize: newFontSize } );
                                } }
                            />
                            <RangeControl
                                label={ __('Filters Area Width (%)', 'tainacan') }
                                value={ filtersAreaWidth }
                                onChange={ ( width ) => setAttributes( { filtersAreaWidth: width } ) }
                                min={ 5 }
                                max={ 40 }
                            />
                        </PanelBody>
                       
                    </InspectorControls>
                </div>

                { isSelected ? 
                    (
                    <div>
                        <div className="block-control">
                            <p style={{ display: 'flex', alignItems: 'baseline' }}>
                                <svg 
                                        xmlns="http://www.w3.org/2000/svg" 
                                        viewBox="0 0 24 24"
                                        height="24px"
                                        width="24px">
                                    <path 
                                            fill="#298596"
                                            d="M21.43,13.64,19.32,16a2.57,2.57,0,0,1-2,1H11a3.91,3.91,0,0,0,0-.49,5.49,5.49,0,0,0-5-5.47V9.64A2.59,2.59,0,0,1,8.59,7H17.3a2.57,2.57,0,0,1,2,1l2.11,2.38A2.59,2.59,0,0,1,21.43,13.64ZM4,3A2,2,0,0,0,2,5v7.3a5.32,5.32,0,0,1,2-1V5H16V3ZM11,21l-1,1L8.86,20.89,8,20H8l-.57-.57A3.42,3.42,0,0,1,5.5,20a3.5,3.5,0,0,1,0-7,2.74,2.74,0,0,1,.5,0A3.5,3.5,0,0,1,9,16a2.92,2.92,0,0,1,0,.51,3.42,3.42,0,0,1-.58,1.92L9,19H9l.85.85Zm-4-4.5A1.5,1.5,0,1,0,5.5,18,1.5,1.5,0,0,0,7,16.53Z"/>
                                </svg>
                                {__('Show items list from: ', 'tainacan')}
                                &nbsp;
                                <SelectControl
                                    label={ __('Items list source', 'tainacan') }
                                    hideLabelFromVision
                                    value={ listType }
                                    options={ [
                                        { label: __('a Collection', 'tainacan'), value: 'collection' },
                                        { label: __('a Taxonomy Term', 'tainacan'), value: 'term' },
                                        { label: __('the Repository', 'tainacan'), value: 'repository' },
                                    ] }
                                    onChange={ ( aListType) => {
                                        listType = aListType;
                                        setAttributes({ listType: aListType });
                                    } }
                                />
                                &nbsp;
                                { 
                                    (listType == 'collection' && collectionId != undefined) || (listType == 'term' && termId != undefined) ?
                                        <Button
                                            isPrimary
                                            type="submit"
                                            onClick={ () => listType == 'term' ? openTermModal() : openCollectionModal() }>
                                            { listType == 'term' ? __('Change Term', 'tainacan') : __('Change Collection', 'tainacan') }
                                        </Button>
                                    : null
                                }
                            </p>  
                        </div>
                    </div>
                    ) : null
                }

                { ( termId == undefined && listType == 'term' ) || ( collectionId == undefined && listType == 'collection' ) ? (
                    <Placeholder
                        icon={(
                            <img
                                width={148}
                                src={ `${tainacan_blocks.base_url}/assets/images/tainacan_logo_header.svg` }
                                alt="Tainacan Logo"/>
                        )}>
                        <p>
                            <svg 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    viewBox="0 0 24 24"
                                    height="24px"
                                    width="24px">
                                <path 
                                        fill="#298596"
                                        d="M21.43,13.64,19.32,16a2.57,2.57,0,0,1-2,1H11a3.91,3.91,0,0,0,0-.49,5.49,5.49,0,0,0-5-5.47V9.64A2.59,2.59,0,0,1,8.59,7H17.3a2.57,2.57,0,0,1,2,1l2.11,2.38A2.59,2.59,0,0,1,21.43,13.64ZM4,3A2,2,0,0,0,2,5v7.3a5.32,5.32,0,0,1,2-1V5H16V3ZM11,21l-1,1L8.86,20.89,8,20H8l-.57-.57A3.42,3.42,0,0,1,5.5,20a3.5,3.5,0,0,1,0-7,2.74,2.74,0,0,1,.5,0A3.5,3.5,0,0,1,9,16a2.92,2.92,0,0,1,0,.51,3.42,3.42,0,0,1-.58,1.92L9,19H9l.85.85Zm-4-4.5A1.5,1.5,0,1,0,5.5,18,1.5,1.5,0,0,0,7,16.53Z"/>
                            </svg>
                            {__('Show a complete items list with faceted search.', 'tainacan')}
                        </p>
                        <Button
                            isPrimary
                            type="submit"
                            onClick={ () => listType == 'term' ? openTermModal() : openCollectionModal() }>
                            { listType == 'term' ? __('Select a Term', 'tainacan') : __('Select a Collection', 'tainacan') }
                        </Button>
                           
                    </Placeholder>
                    ) :
                    (
                        <div>
                            <div 
                                    style={{ fontSize: (baseFontSize - 2) + 'px' }}
                                    class="preview-warning">
                                { __('Warning: this is just a demonstration. To see the items list, either preview or publish your post.', 'tainacan') }
                            </div>
                            <div 
                                    style={{
                                        '--tainacan-background-color': backgroundColor,
                                        'font-size': baseFontSize + 'px'
                                    }}
                                    class="items-list-placeholder">
                                <div class="search-control">
                                    { 
                                        !hideSearch ?
                                        <span class="fake-searchbar">
                                            { !hideAdvancedSearch ? <span class="fake-advanced-searchbar"></span> : null }
                                        </span>
                                        : null
                                    }
                                    {
                                        !hideSortByButton ? <span class="fake-button"></span> : null
                                    }
                                    <span class="fake-button"></span>
                                    {
                                        !showInlineViewModeOptions ? 
                                            <span class="fake-button"></span> 
                                        : 
                                            <div class="fake-buttons-group">
                                                <div class="fake-button"></div>
                                                <div class="fake-button"></div>
                                                <div class="fake-button"></div>
                                                { showFullscreenWithViewModes ? <span class="fake-button"></span> : null }
                                            </div>
                                    }
                                    {
                                        !showFullscreenWithViewModes ? <span class="fake-button"></span> : null
                                    }
                                    {
                                        !hideExposersButton ? <span class="fake-button"></span> : null
                                    }
                                </div>
                                <div class="below-search-control">
                                    { !hideHideFiltersButton && !hideFilters ? <span class="fake-hide-button"></span> : null }
                                    { 
                                        !hideFilters && !filtersAsModal && !startWithFiltersHidden ?
                                            <div 
                                                    style={{
                                                        flexBasis: filtersAreaWidth + '%'
                                                    }}
                                                    class="filters">
                                                <span class="fake-text"></span>
                                                <span class="fake-text"></span>
                                                <span class="fake-text"></span>
                                                <span class="fake-text"></span>
                                                <span class="fake-text"></span>
                                                <span class="fake-text"></span>
                                                <span class="fake-text"></span>
                                            </div> 
                                        : null 
                                    }
                                    <div class="aside-filters">    
                                        <div class="items">
                                            <div class="fake-item"><span class="fake-item-header"></span></div>
                                            <div class="fake-item"><span class="fake-item-header"></span></div>
                                            <div class="fake-item"><span class="fake-item-header"></span></div>
                                            <div class="fake-item"><span class="fake-item-header"></span></div>
                                            <div class="fake-item"><span class="fake-item-header"></span></div>
                                            <div class="fake-item"><span class="fake-item-header"></span></div>
                                        </div>
                                        <div class="pagination">
                                            <span class="fake-text"></span>
                                            { !hideItemsPerPageButton ? <span class="fake-button"></span> : null }
                                            { !hideGoToPageButton ? <span class="fake-button"></span> : null }
                                            <span class="fake-searchbar"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )
                }

                { isCollectionModalOpen ? 
                    <CollectionModal
                        existingCollectionId={ collectionId } 
                        onSelectCollection={ (selectedCollectionId) => {
                            collectionId = selectedCollectionId;
                            setAttributes({
                                collectionId: collectionId, 
                                isCollectionModalOpen: false
                            });
                        }}
                        onCancelSelection={ () => setAttributes({ isCollectionModalOpen: false }) }/> 
                    : null
                }

                { isTermModalOpen ? 
                    <TermModal
                        existingTermId={ termId } 
                        existingTaxonomyId={ taxonomyId } 
                        onSelectTaxonomy={ (selectedTaxonomy) => {
                            taxonomyId = selectedTaxonomy;
                            setAttributes({ taxonomyId: selectedTaxonomy });
                        }}
                        onSelectTerm={ (selectedTermId) => {
                            termId = selectedTermId;
                            setAttributes({
                                termId: selectedTermId, 
                                isTermModalOpen: false
                            });
                        }}
                        onCancelSelection={ () => setAttributes({ isTermModalOpen: false }) }/> 
                    : null
                }

            </div>
        );
    },
    save({ attributes, className }){
        const {
            termId,
            taxonomyId,
            collectionId,
            defaultViewMode,
            enabledViewModes,
            hideFilters,
            hideHideFiltersButton,
            hideSearch,
            hideAdvancedSearch,
            hideSortByButton,
            hideExposersButton,
            hideItemsPerPageButton,
            hideGoToPageButton,
            startWithFiltersHidden,
            filtersAsModal,
            showInlineViewModeOptions,
            showFullscreenWithViewModes,
            listType,
            backgroundColor,
            baseFontSize,
            filtersAreaWidth
        } = attributes;
        
        return <div 
                    style={{
                        'font-size': baseFontSize + 'px',
                        '--tainacan-background-color': backgroundColor,
                        '--tainacan-filter-menu-width-theme': filtersAreaWidth + '%'
                    }}
                    className={ className }>
                <main 
                    term-id={ listType == 'term' ? termId : null }
                    taxonomy={ listType == 'term' ? 'tnc_tax_' + taxonomyId : null  }
                    collection-id={ listType == 'collection' ? collectionId : null }  
                    default-view-mode={ defaultViewMode }
                    enabled-view-modes={ enabledViewModes.toString() }  
                    hide-filters = { hideFilters.toString() }
                    hide-hide-filters-button= { hideHideFiltersButton.toString() }
                    hide-search = { hideSearch.toString() }
                    hide-advanced-search = { hideAdvancedSearch.toString() }
                    hide-sort-by-button = { hideSortByButton.toString() }
                    hide-exposers-button = { hideExposersButton.toString() }
                    hide-items-per-page-button = { hideItemsPerPageButton.toString() }
                    hide-go-to-page-button = { hideGoToPageButton.toString() }
                    start-with-filters-hidden = { startWithFiltersHidden.toString() }
                    filters-as-modal = { filtersAsModal.toString() }
                    show-inline-view-mode-options = { showInlineViewModeOptions.toString() }
                    show-fullscreen-with-view-modes = { showFullscreenWithViewModes.toString() }
                    id="tainacan-items-page">
                </main>
            </div>
    }
});