const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { BaseControl, RangeControl, Spinner, Button, SelectControl, ToggleControl, Tooltip, Placeholder, Toolbar, PanelBody } = wp.components;

const { InspectorControls, BlockControls } = wp.editor;

import tainacan from '../../js/axios.js';
import axios from 'axios';
import qs from 'qs';

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
        content: {
            type: 'array',
            source: 'children',
            selector: 'div'
        },
        termId: {
            type: String,
            default: undefined
        },
        taxonomy: {
            type: String,
            default: undefined
        },
        collectionId: {
            type: String,
            default: undefined
        },
        defaultViewMode: {
            type: String,
            default: undefined
        },
        enabledViewModes: {
            type: Array,
            default: undefined
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
            taxonomy,
            collectionId,
            defaultViewMode,
            enabledViewModes,
            hideFilters,
            hideHideFiltersButton,
            hideSearch,
            hideAdvancedSearch,
            hideSortByButton,
            hideItemsPerPageButton,
            hideGoToPageButton,
            startWithFiltersHidden,
            filtersAsModal,
            showInlineViewModeOptions,
            showFullscreenWithViewModes,
            listType
        } = attributes;
 
        function setContent(){

            facets = [];
            isLoading = true;
            
            if (facetsRequestSource != undefined && typeof facetsRequestSource == 'function')
                facetsRequestSource.cancel('Previous facets search canceled.');

            facetsRequestSource = axios.CancelToken.source();
            
            setAttributes({
                isLoading: isLoading
            });
            
            let endpoint = '/facets/' + metadatumId;
            let query = endpoint.split('?')[1];
            let queryObject = qs.parse(query);

            // Set up max facets to be shown
            if (maxFacetsNumber != undefined && maxFacetsNumber > 0)
                queryObject.number = maxFacetsNumber;
            else if (queryObject.number != undefined && queryObject.number > 0)
                setAttributes({ maxFacetsNumber: queryObject.number });
            else {
                queryObject.number = 12;
                setAttributes({ maxFacetsNumber: 12 });
            }

            // Set up searching string
            if (searchString != undefined)
                queryObject.search = searchString;
            else if (queryObject.search != undefined)
                setAttributes({ searchString: queryObject.search });
            else {
                delete queryObject.search;
                setAttributes({ searchString: undefined });
            }

            // Set up parentTerm for taxonomies
            if (parentTerm && parentTerm.id !== undefined && parentTerm.id !== null && parentTerm.id !== '' && metadatumType == 'Taxonomy')
                queryObject.parent = parentTerm.id;
            else {
                delete queryObject.parent;
                setAttributes({ parentTerm: null });
            }

            // Parameter fo tech entity object with image and url
            queryObject['context'] = 'extended';
            
            endpoint = endpoint.split('?')[0] + '?' + qs.stringify(queryObject);
            
            tainacan.get(endpoint, { cancelToken: facetsRequestSource.token })
                .then(response => {
                    facetsObject = [];

                    if (metadatumType == 'Taxonomy') {
                        for (let facet of response.data.values) {
                            facetsObject.push(Object.assign({ 
                                url: facet.entity && facet.entity.url ? facet.entity.url : tainacan_blocks.site_url + '/' + collectionSlug + '/#/?taxquery[0][compare]=IN&taxquery[0][taxonomy]=' + facet.taxonomy + '&taxquery[0][terms][0]=' + facet.value
                            }, facet));
                        }
                    } else {
                        for (let facet of response.data.values) {
                            facetsObject.push(Object.assign({ 
                                url: tainacan_blocks.site_url + '/' + collectionSlug + '/#/?metaquery[0][key]=' + metadatumId + '&metaquery[0][value]=' + facet.value
                            }, facet));
                        }
                    }
                    
                    for (let facet of facetsObject)
                        facets.push(prepareFacet(facet));

                    setAttributes({
                        content: <div></div>,
                        facets: facets,
                        facetsObject: facetsObject,
                        isLoading: false,
                        facetsRequestSource: facetsRequestSource
                    });
                });
        }

        function updateContent() {

            setAttributes({
                content: <div></div>
            });
        }

        function openMetadataModal() {
            isModalOpen = true;
            setAttributes( { 
                isModalOpen: isModalOpen
            } );
        }
        
        function openParentTermModal() {
            isParentTermModalOpen = true;
            setAttributes( { 
                isParentTermModalOpen: isParentTermModalOpen
            } );
        }

        // Executed only on the first load of page
        if(content && content.length && content[0].type)
            setContent();

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
                                help={ hideAdvancedSearch ? __('Do not show advanced search', 'tainacan') : __('Toggle to show advanced search', 'tainacan')}
                                checked={ hideAdvancedSearch }
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
                                help={ hideHideFiltersButton ? __('Do not show "Hide Filters" button', 'tainacan') : __('Toggle to show "Hide Filters" button', 'tainacan')}
                                checked={ hideHideFiltersButton }
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
                                {__('Render an items list with faceted search from: ', 'tainacan')}
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
                            {__('Render a complete items list with faceted search.', 'tainacan')}
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
                            <div class="preview-warning">
                                { __('Warning: this is just a demonstration. To see the items list, either preview or publish your post.', 'tainacan') }
                            </div>
                            <div class="items-list-placeholder">
                                <div class="search-control"></div>
                                <div class="filters"></div>
                                <div class="items"></div>
                                <div class="pagination"></div>
                            </div>
                        </div>
                    )
                }

            </div>
        );
    },
    save({ attributes, className }){
        const {
            termId,
            taxonomy,
            collectionId,
            defaultViewMode,
            enabledViewModes,
            hideFilters,
            hideHideFiltersButton,
            hideSearch,
            hideAdvancedSearch,
            hideSortByButton,
            hideItemsPerPageButton,
            hideGoToPageButton,
            startWithFiltersHidden,
            filtersAsModal,
            showInlineViewModeOptions,
            showFullscreenWithViewModes
        } = attributes;
        return <main 
                    className={ className }
                    term-id={ termId }
                    taxonomy={ taxonomy }
                    collection-id={ '67472' /* collectionId */ }  
                    default-view-mode={ defaultViewMode }
                    enabled-view-modes={ enabledViewModes }  
                    hide-filters = { '' +  hideFilters }
                    hide-hide-filters-button= { hideHideFiltersButton }
                    hide-search = { '' +  hideSearch }
                    hide-advanced-search = { '' +  hideAdvancedSearch }
                    hide-sort-by-button = { '' +  hideSortByButton }
                    hide-items-per-page-button = { '' +  hideItemsPerPageButton }
                    hide-go-to-page-button = { '' +  hideGoToPageButton }
                    start-with-filters-hidden = { '' +  startWithFiltersHidden }
                    filters-as-modal = { '' +  filtersAsModal }
                    show-inline-view-mode-options = { '' +  showInlineViewModeOptions }
                    show-fullscreen-with-view-modes = { '' +  showFullscreenWithViewModes }
                    id="tainacan-items-page">
                </main>
    }
});