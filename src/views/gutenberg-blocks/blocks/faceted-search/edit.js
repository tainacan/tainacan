const { __ } = wp.i18n;

const { 
    Button,
    BaseControl,
    CheckboxControl,
    ColorPalette,
    RangeControl,
    FontSizePicker,
    HorizontalRule,
    SelectControl,
    ToggleControl,
    Placeholder,
    PanelBody,
    ToolbarGroup,
    Dropdown,
    ToolbarButton,
    ToolbarItem,
    MenuGroup,
    MenuItemsChoice
} = wp.components;

const { InspectorControls, BlockControls, useBlockProps } = wp.blockEditor;

import CollectionModal from './collection-modal.js';
import TermModal from './term-modal.js';

export default function({ attributes, setAttributes, isSelected, clientId }) {
    let {
        termId,
        taxonomyId,
        collectionId,
        defaultViewMode,
        enabledViewModes,
        collectionDefaultViewMode,
        collectionEnabledViewModes,
        hideFilters,
        hideHideFiltersButton,
        hideSearch,
        hideAdvancedSearch,
        hideDisplayedMetadataButton,
        hideSortingArea,
        hideSortByButton,
        hideItemsThumbnail,
        hideExposersButton,
        hideItemsPerPageButton,
        hidePaginationArea,
        defaultItemsPerPage,
        hideGoToPageButton,
        showFiltersButtonInsideSearchControl,
        startWithFiltersHidden,
        filtersAsModal,
        showInlineViewModeOptions,
        showFullscreenWithViewModes,
        listType,
        isTermModalOpen,
        isCollectionModalOpen,
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
        shouldNotHideFiltersOnMobile,
        displayFiltersHorizontally
    } = attributes;

    // Gets blocks props from hook
    const blockProps = useBlockProps();
    const className = blockProps.className;

    let registeredViewModesEntries = [];
    let registeredViewModesKeys = [];
    updateAvailableViewModes(hideItemsThumbnail);

    if ( enabledViewModes === null || !enabledViewModes.length )
        enabledViewModes = Object.keys(tainacan_plugin.registered_view_modes);

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

    const listTypeChoices = [
        {
            value: 'collection',
            label: __('a Collection', 'tainacan'),
        },
        {
            value: 'term',
            label: __('a Taxonomy Term', 'tainacan'),
        },
        {
            value: 'repository',
            label: __('the Repository', 'tainacan'),
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

    function checkIfViewModeIsEnabled(viewMode) {
        return enabledViewModes.includes(viewMode);
    }

    function updateAvailableViewModes(isItemThumbnailHidden) {
        if (isItemThumbnailHidden != true) {
            registeredViewModesEntries = Object.entries(tainacan_plugin.registered_view_modes);
            registeredViewModesKeys = Object.keys(tainacan_plugin.registered_view_modes);
        } else {
            const validViewModes = {};
            Object.keys(tainacan_plugin.registered_view_modes).forEach((viewModeKey) => {
                if (!tainacan_plugin.registered_view_modes[viewModeKey]['requires_thumbnail']) 
                    validViewModes[viewModeKey] = tainacan_plugin.registered_view_modes[viewModeKey];
            });
            registeredViewModesEntries = Object.entries(validViewModes);
            registeredViewModesKeys = Object.keys(validViewModes);

            const availableViewModes = JSON.parse(JSON.stringify(enabledViewModes)).filter((aViewMode) => registeredViewModesKeys.includes(aViewMode) );
            if (JSON.stringify(availableViewModes) != JSON.stringify(enabledViewModes)) {
                enabledViewModes = availableViewModes;

                // Puts a valid view mode as default if the current one is not in the list anymore.
                if (!checkIfViewModeIsEnabled(defaultViewMode)) {
                    const validViewModeIndex = enabledViewModes.findIndex((aViewMode) => registeredViewModesKeys.includes(aViewMode));
                    if (validViewModeIndex >= 0)
                        defaultViewMode = enabledViewModes[validViewModeIndex];
                }

                setAttributes({
                    enabledViewModes: enabledViewModes,
                    defaultViewMode: defaultViewMode 
                });
            }
        }
    }

    function onUpdateListType( aListType, props) {
        listType = aListType;

        if (listType != 'collection') {
            enabledViewModes = registeredViewModesKeys;
            defaultViewMode = hideItemsThumbnail ? 'table' : 'masonry';
        }

        setAttributes({
            listType: aListType,
            enabledViewModes: enabledViewModes,
            defaultViewMode: defaultViewMode
        });

        if (listType == 'term')
            openTermModal();
        else if (listType == 'collection')
            openCollectionModal();
        else
            return;
    }
    
    return ( listType == 'preview' ? 
            <div className={className}>
                <img
                        width="100%"
                        src={ `${tainacan_blocks.base_url}/assets/images/faceted-search.png` } />
            </div>
        : (
        <div { ...blockProps }>

            <div>
                <BlockControls>
                    { !( termId == undefined && listType == 'term' ) && !( collectionId == undefined && listType == 'collection' ) ?
                        <ToolbarGroup>
                            <ToolbarItem>
                                { () => (
                                    <Dropdown
                                        contentClassName="wp-block-tainacan__dropdown"
                                        renderToggle={ ( { isOpen, onToggle } ) => (
                                                <ToolbarButton
                                                    onClick={ onToggle }
                                                    aria-expanded={ isOpen }>
                                                        { __('Items list source', 'tainacan')  }
                                                        <span className="components-dropdown-menu__indicator"></span>  
                                                </ToolbarButton>
                                        ) }
                                        renderContent={ ( { onToggle } ) => (
                                            <MenuGroup>
                                                <MenuItemsChoice
                                                    choices={ listTypeChoices }
                                                    value={ listType } 
                                                    onSelect={ (value) => {
                                                        onUpdateListType(value);
                                                        onToggle(); 
                                                    }}>
                                                </MenuItemsChoice>
                                            </MenuGroup>
                                        ) }
                                    />
                                ) }
                            </ToolbarItem>
                        </ToolbarGroup>
                    :null }
                </BlockControls>
            </div>

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
                            label={__('Hide "Displayed metadata" button', 'tainacan')}
                            help={ hideDisplayedMetadataButton ? __('Do not show "Displayed metadata" dropdown even if the view mode allows it', 'tainacan') : __('Toggle to show "Displayed metadata" dropdown if the view mode allows it', 'tainacan')}
                            checked={ hideDisplayedMetadataButton }
                            onChange={ ( isChecked ) => {
                                    hideDisplayedMetadataButton = isChecked;
                                    setAttributes({ hideDisplayedMetadataButton: isChecked });
                                } 
                            }
                        />
                        <ToggleControl
                            label={__('Hide sorting area', 'tainacan')}
                            help={ hideSortingArea ? __('Do not show search sorting options', 'tainacan') : __('Toggle to show search sorting options', 'tainacan')}
                            checked={ hideSortingArea }
                            onChange={ ( isChecked ) => {
                                    hideSortingArea = isChecked;
                                    setAttributes({ hideSortingArea: isChecked });
                                }  
                            }
                        />
                        <ToggleControl
                            label={__('Hide "Sort By" button', 'tainacan')}
                            help={ hideSortingArea || hideSortByButton ? __('Do not show "Sort By" button', 'tainacan') : __('Toggle to show "Sort By" button', 'tainacan')}
                            checked={ hideSortingArea || hideSortByButton }
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
                            label={__('Show fullscreen Slides with other View Modes', 'tainacan')}
                            help={ showFullscreenWithViewModes ? __('Toggle to show fullscreen Slides view mode alongside with other View Modes', 'tainacan') : __('Keep fullscreen Slides view mode separated from other View Mode Options', 'tainacan')}
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
                            <ToggleControl
                            label={__('Show "Filters" button inside the Search Control', 'tainacan')}
                            help={ showFiltersButtonInsideSearchControl ? __('Toggle to show Filters button inside the Search Control, instead of floating left', 'tainacan') : __('Keep Filters button as a floating arrow on the left', 'tainacan')}
                            checked={ showFiltersButtonInsideSearchControl }
                            onChange={ ( isChecked ) => {
                                    showFiltersButtonInsideSearchControl = isChecked;
                                    setAttributes({ showFiltersButtonInsideSearchControl: isChecked });
                                } 
                            }
                        />

                        <ToggleControl
                            label={__('Hide Items Thumbnail', 'tainacan')}
                            help={ hideItemsThumbnail ? __('Do not show the items Thumbnail on the list', 'tainacan') : __('Toggle to show the items thumbnail on the list', 'tainacan')}
                            checked={ hideItemsThumbnail }
                            onChange={ ( isChecked ) => {
                                    hideItemsThumbnail = isChecked;
                                    setAttributes({ hideItemsThumbnail: isChecked });
                                    updateAvailableViewModes(isChecked);
                                } 
                            }
                        />

                        <BaseControl
                                id="defaulOrder"
                                label={ __('Default order', 'tainacan')}
                                help={ __('The default sorting direction', 'tainacan') }>
                            <SelectControl
                                    label={ __('Default order', 'tainacan') }
                                    hideLabelFromVision
                                    value={ order }
                                    options={
                                        [
                                            { value: 'ASC', label: __('Ascending', 'tainacan') },
                                            { value: 'DESC', label: __('Descending', 'tainacan') }
                                        ]
                                    }
                                    onChange={ (anOrder) => {
                                        order = anOrder;
                                        setAttributes({ order: anOrder });
                                    } }
                                />
                        </BaseControl>

                        { listType != 'collection' ?
                            <BaseControl
                                    id="defaulOrderBy"
                                    label={ __('Default order by', 'tainacan')}
                                    help={ __('The default metadata by which the sorting will be applied', 'tainacan') }>
                                <SelectControl
                                        label={ __('Default order by', 'tainacan') }
                                        hideLabelFromVision
                                        value={ orderBy }
                                        options={
                                            [
                                                { value: 'date', label: __('Creation date', 'tainacan') },
                                                { value: 'title', label: __('Title', 'tainacan') }
                                            ]
                                        }
                                        onChange={ (anOrderBy) => {
                                            orderBy = anOrderBy;
                                            setAttributes({ orderBy: anOrderBy });
                                        } }
                                    />
                            </BaseControl>
                        : null }

                        <BaseControl
                                id="defaultViewModeSelect"
                                label={ __('Forced default view mode', 'tainacan')}
                                help={ __('The default view mode to be forced against the one setted on the repository', 'tainacan') }>
                            <SelectControl
                                    label={ __('Default view mode', 'tainacan') }
                                    hideLabelFromVision
                                    value={ defaultViewMode }
                                    options={
                                        [{ value: 'none', label: __('Use current view mode settings', 'tainacan') }]
                                        .concat(registeredViewModesEntries
                                            .map(aViewMode => { return { label: aViewMode[1].label, value: aViewMode[0], disabled: !checkIfViewModeIsEnabled(aViewMode[0]) || aViewMode[1].full_screen }})
                                        )
                                    }
                                    onChange={ (aViewMode) => {
                                        defaultViewMode = aViewMode;
                                        setAttributes({ defaultViewMode: aViewMode });
                                    } }
                                />
                        </BaseControl>
                        
                        <BaseControl
                                id="enabledViewModeCheckboxesList"
                                label={ __('Forced enabled view modes', 'tainacan') }
                                help={ __('Select the view modes that you wish to be available for user selection on the items list.', 'tainacan') }>
                            
                            { 
                                registeredViewModesEntries.map(aRegisteredViewMode => {
                                    return  (
                                    <CheckboxControl
                                            label={ aRegisteredViewMode[1].label }
                                            checked={ checkIfViewModeIsEnabled(aRegisteredViewMode[0]) }
                                            disabled={ checkIfViewModeIsEnabled(aRegisteredViewMode[0]) && enabledViewModes.filter((aViewMode) => tainacan_plugin.registered_view_modes[aViewMode] && !tainacan_plugin.registered_view_modes[aViewMode].full_screen).length <= 1 }
                                            onChange={ () => {
                                                let index = enabledViewModes.findIndex(aViewMode => aViewMode == aRegisteredViewMode[0]);
                                                if (index > -1)
                                                    enabledViewModes.splice(index, 1);
                                                else    
                                                    enabledViewModes.push(aRegisteredViewMode[0]);
                                                
                                                    // Puts a valid view mode as default if the current one is not in the list anymore.
                                                if (defaultViewMode != 'none' && !enabledViewModes.includes(defaultViewMode)) {
                                                    const validViewModeIndex = enabledViewModes.findIndex((aViewMode) => (tainacan_plugin.registered_view_modes[aViewMode] && !tainacan_plugin.registered_view_modes[aViewMode].full_screen));
                                                    if (validViewModeIndex >= 0)
                                                        defaultViewMode = enabledViewModes[validViewModeIndex];
                                                }

                                                setAttributes({ 
                                                    enabledViewModes: JSON.parse(JSON.stringify(enabledViewModes)),
                                                    defaultViewMode: defaultViewMode 
                                                });
                                            } }
                                        /> 
                                    )
                                })
                            }
                        </BaseControl>
                        
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
                            help={ startWithFiltersHidden ? __('Render the list with filters hidden at first', 'tainacan') : __('Toggle to render the list with filters visible at first', 'tainacan')}
                            checked={ startWithFiltersHidden }
                            onChange={ ( isChecked ) => {
                                    startWithFiltersHidden = isChecked;
                                    setAttributes({ startWithFiltersHidden: isChecked });
                                } 
                            }
                        />
                         <ToggleControl
                            label={__('Should not hide filters even on mobile', 'tainacan')}
                            help={ shouldNotHideFiltersOnMobile || shouldNotHideFiltersOnMobile === undefined ? __('Toggle to keep filters area visible even on small screen sizes', 'tainacan') : __('Automatically hide filters area on small screen sizes inside a modal', 'tainacan') }
                            checked={ shouldNotHideFiltersOnMobile && !filtersAsModal }
                            onChange={ ( isChecked ) => {
                                    shouldNotHideFiltersOnMobile = isChecked;
                                    setAttributes({ shouldNotHideFiltersOnMobile: isChecked });
                                } 
                            }
                        />
                        <ToggleControl
                            label={__('Filters as a Modal', 'tainacan')}
                            help={ filtersAsModal ? __('Render the filters area as modal instead of a side panel', 'tainacan') : __('Toggle to show filters list as a side panel instead of a modal', 'tainacan')}
                            checked={ filtersAsModal }
                            onChange={ ( isChecked ) => {
                                    filtersAsModal = isChecked;
                                    setAttributes({ filtersAsModal: isChecked });
                                } 
                            }
                        />
                        <ToggleControl
                            label={__('Display filters horizontally', 'tainacan')}
                            checked={ displayFiltersHorizontally }
                            onChange={ ( isChecked ) => {
                                    displayFiltersHorizontally = isChecked;
                                    setAttributes({ displayFiltersHorizontally: isChecked });
                                } 
                            }
                        />
                    </PanelBody>

                    <PanelBody
                            title={__('Pagination Area', 'tainacan')}
                            initialOpen={ true }
                        >
                        <ToggleControl
                            label={__('Hide pagination area', 'tainacan')}
                            help={ hidePaginationArea ? __('Do not show search pagination options', 'tainacan') : __('Toggle to show search pagination options', 'tainacan')}
                            checked={ hidePaginationArea }
                            onChange={ ( isChecked ) => {
                                    hidePaginationArea= isChecked;
                                    setAttributes({ hidePaginationArea: isChecked });
                                }  
                            }
                        />
                        <ToggleControl
                            label={__('Hide "Items per Page" button', 'tainacan')}
                            help={ hidePaginationArea || hideItemsPerPageButton ? __('Do not show "Items per Page" button', 'tainacan') : __('Toggle to show "Items per Page" button', 'tainacan')}
                            checked={ hidePaginationArea || hideItemsPerPageButton }
                            onChange={ ( isChecked ) => {
                                    hideItemsPerPageButton = isChecked;
                                    setAttributes({ hideItemsPerPageButton: isChecked });
                                } 
                            }
                        />
                        <ToggleControl
                            label={__('Hide "Go to Page" button', 'tainacan')}
                            help={ hidePaginationArea || hideGoToPageButton ? __('Do not show "Go to Page" button', 'tainacan') : __('Toggle to show "Go to Page" button', 'tainacan')}
                            checked={ hidePaginationArea || hideGoToPageButton }
                            onChange={ ( isChecked ) => {
                                    hideGoToPageButton = isChecked;
                                    setAttributes({ hideGoToPageButton: isChecked });
                                } 
                            }
                        />
                            <RangeControl
                            label={__('Default number of items per page', 'tainacan')}
                            value={ defaultItemsPerPage ? defaultItemsPerPage : 12 }
                            onChange={ ( itemsPerPage ) => {
                                defaultItemsPerPage = itemsPerPage;
                                setAttributes( { defaultItemsPerPage: itemsPerPage } );
                            }}
                            min={ 1 }
                            max={ tainacan_plugin.api_max_items_per_page ? tainacan_plugin.api_max_items_per_page : 96 }
                        />
                    </PanelBody>
                </InspectorControls>
                <InspectorControls group="styles">
                    <PanelBody
                            title={__('Dimensions', 'tainacan')}
                            initialOpen={ true }
                        >
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
                            min={ 10 }
                            max={ 40 }
                        />
                    </PanelBody>
                    <PanelBody
                            title={__('Colors', 'tainacan')}
                            initialOpen={ true }
                        >
                        <BaseControl
                                id="backgroundColorPicker"
                                label={ __('Background color', 'tainacan')}
                                help={ __('The background color of the entire items list', 'tainacan') }>
                            <ColorPalette
                                value={ backgroundColor }
                                onChange={ ( colorValue ) => {
                                    backgroundColor = colorValue;
                                    setAttributes({ backgroundColor: backgroundColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <HorizontalRule />
                        <BaseControl
                                id="secondaryColorPicker"
                                label={ __('Link and Active Main color', 'tainacan')}
                                help={ __('The text color links and other action or active state elements, such as select arrows, tooltip contents, etc', 'tainacan') }>
                            <ColorPalette
                                value={ secondaryColor }
                                onChange={ (colorValue ) => {
                                    secondaryColor = colorValue;
                                    setAttributes({ secondaryColor: secondaryColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <HorizontalRule />
                        <BaseControl
                                id="primaryColorPicker"
                                label={ __('Tooltips background color', 'tainacan')}
                                help={ __('The tooltips background color and other elements, such as the hide filters button', 'tainacan') }>
                            <ColorPalette
                                value={ primaryColor }
                                onChange={ (colorValue ) => {
                                    primaryColor = colorValue;
                                    setAttributes({ primaryColor: primaryColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <HorizontalRule />
                        <BaseControl
                                id="inputBackgroundColorPicker"
                                label={ __('Input Background color', 'tainacan')}
                                help={ __('The background color for input fields', 'tainacan') }>
                            <ColorPalette
                                value={ inputBackgroundColor }
                                onChange={ (colorValue ) => {
                                    inputBackgroundColor = colorValue;
                                    setAttributes({ inputBackgroundColor: inputBackgroundColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <HorizontalRule />
                        <BaseControl
                                id="inputColorPicker"
                                label={ __('Input Text color', 'tainacan')}
                                help={ __('The text color for input fields, including dropdowns and buttons', 'tainacan') }>
                            <ColorPalette
                                value={ inputColor }
                                onChange={ (colorValue ) => {
                                    inputColor = colorValue;
                                    setAttributes({ inputColor: inputColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <HorizontalRule />
                        <BaseControl
                                id="inputBorderColorPicker"
                                label={ __('Input Border color', 'tainacan')}
                                help={ __('The border color for input fields', 'tainacan') }>
                            <ColorPalette
                                value={ inputBorderColor }
                                onChange={ (colorValue ) => {
                                    inputBorderColor = colorValue;
                                    setAttributes({ inputBorderColor: inputBorderColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <HorizontalRule />
                        <BaseControl
                                id="labelColorPicker"
                                label={ __('Label Text color', 'tainacan')}
                                help={ __('The text color for field labels', 'tainacan') }>
                            <ColorPalette
                                value={ labelColor }
                                onChange={ (colorValue ) => {
                                    labelColor = colorValue;
                                    setAttributes({ labelColor: labelColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <HorizontalRule />
                        <BaseControl
                                id="headingColorPicker"
                                label={ __('Headings Text color', 'tainacan')}
                                help={ __('The text color for headings such as items title and filters menu header', 'tainacan') }>
                            <ColorPalette
                                value={ headingColor }
                                onChange={ (colorValue ) => {
                                    headingColor = colorValue;
                                    setAttributes({ headingColor: headingColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <HorizontalRule />
                        <BaseControl
                                id="infoColorPicker"
                                label={ __('General Info Text color', 'tainacan')}
                                help={ __('The text color for other information such as item metadata, icons, number of pages, etc', 'tainacan') }>
                            <ColorPalette
                                value={ infoColor }
                                onChange={ (colorValue ) => {
                                    infoColor = colorValue;
                                    setAttributes({ infoColor: infoColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <BaseControl
                                id="itemBackgroundColorPicker"
                                label={ __('Item Background color', 'tainacan')}
                                help={ __('The background color for an item on the list', 'tainacan') }>
                            <ColorPalette
                                value={ itemBackgroundColor }
                                onChange={ (colorValue ) => {
                                    itemBackgroundColor = colorValue;
                                    setAttributes({ itemBackgroundColor: itemBackgroundColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <HorizontalRule />
                        <BaseControl
                                id="itemHoverBackgroundColorPicker"
                                label={ __('Item Hover Background color', 'tainacan')}
                                help={ __('The background color for an item on the list, when hovered', 'tainacan') }>
                            <ColorPalette
                                value={ itemHoverBackgroundColor }
                                onChange={ (colorValue ) => {
                                    itemHoverBackgroundColor = colorValue;
                                    skeletonColor = colorValue;
                                    setAttributes({ 
                                        itemHoverBackgroundColor: itemHoverBackgroundColor,
                                        skeletonColor: skeletonColor
                                    });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                        <HorizontalRule />
                        <BaseControl
                                id="itemHeadingHoverBackgroundColorPicker"
                                label={ __('Item Heading Hover Background color', 'tainacan')}
                                help={ __('The background color for the item heading (where the title is), when hovered', 'tainacan') }>
                            <ColorPalette
                                value={ itemHeadingHoverBackgroundColor }
                                onChange={ (colorValue ) => {
                                    itemHeadingHoverBackgroundColor = colorValue;
                                    setAttributes({ itemHeadingHoverBackgroundColor: itemHeadingHoverBackgroundColor });
                                }}
                                disableAlpha
                            />
                        </BaseControl>
                    </PanelBody>
                    
                </InspectorControls>
            </div>

            { listType == '' || ( termId == undefined && listType == 'term' ) || ( collectionId == undefined && listType == 'collection' ) ? (
                <Placeholder
                    className="tainacan-block-placeholder"
                    icon={(
                        <img
                            width={148}
                            src={ `${tainacan_blocks.base_url}/assets/images/tainacan_logo_header.svg` }
                            alt="Tainacan Logo"/>
                    )}>
                    <p>
                        <svg 
                                xmlns="http://www.w3.org/2000/svg" 
                                viewBox="0 0 6.3499998 6.3499998"
                                height="24px"
                                width="24px">
                            <g transform="matrix(0.2891908,0,0,0.2891908,-30.465367,-38.43427)">
                                <path 
                                        transform="matrix(0.26458333,0,0,0.26458333,104.32258,131.88168)"
                                        fill="#187181"
                                        d="M 16.662109,14.712891 V 24.927734 H 84.753906 V 14.712891 Z m 6.810547,17.021484 v 9.748047 c 6.857764,2.272819 11.798639,8.605281 11.798828,16.078125 -7.56e-4,2.313298 -0.496344,4.586348 -1.421875,6.693359 l 8.375,8.375 -3.365234,3.367188 H 77.945312 V 31.734375 Z m 17.019532,10.216797 h 20.429687 v 10.21289 H 40.492188 Z m -22.835938,3.84375 a 9.1779065,9.1779065 0 0 0 -0.916016,0.03906 11.753475,11.753475 0 0 0 1.671875,23.445313 11.455635,11.455635 0 0 0 6.466797,-1.910156 l 1.908203,1.910156 2.88086,2.982422 3.820312,3.716797 3.347657,-3.347657 -3.851563,-3.855468 -2.847656,-2.845703 -1.941407,-1.941407 a 11.455635,11.455635 0 0 0 1.941407,-6.433593 11.723603,11.723603 0 0 0 -6.697266,-10.583985 11.422139,11.422139 0 0 0 -5.027344,-1.136719 9.1779065,9.1779065 0 0 0 -0.755859,-0.03906 z m 0.755859,6.736328 a 5.0244015,5.0244015 0 0 1 5.027344,5.025391 v 0.101562 a 5.0244015,5.0244015 0 0 1 -0.269531,1.570313 4.9574094,4.9574094 0 0 1 -4.757813,3.351562 5.0244015,5.0244015 0 0 1 -1.671875,-9.746094 4.6559456,4.6559456 0 0 1 1.671875,-0.302734 z m 6.376953,20.601562 c -0.431168,0.183308 -0.872311,0.335613 -1.316406,0.484376 v 2.378906 h 4.179688 z "/>
                            </g>
                        </svg>
                        {__('Show a complete items list with faceted search from: ', 'tainacan')}
                    </p>
                    <Dropdown
                            contentClassName="wp-block-tainacan__dropdown"
                            renderToggle={ ( { isOpen, onToggle } ) => (
                                <Button
                                    isPrimary
                                    onClick={ onToggle }
                                    aria-expanded={ isOpen }>
                                        { __('Items list source', 'tainacan') }
                                </Button>
                            ) }
                            renderContent={ ( { onToggle } ) => (
                                <MenuGroup>
                                    <MenuItemsChoice
                                        choices={ listTypeChoices }
                                        value={ listType } 
                                        onSelect={ (value) => {
                                            onUpdateListType(value);
                                            onToggle(); 
                                        }}>
                                    </MenuItemsChoice>
                                </MenuGroup>
                            ) }
                        />
                        
                </Placeholder>
                ) :
                (
                    <div style={{ fontSize: (baseFontSize - 2) + 'px' }}>
                        <div className="preview-warning">
                            { __('Warning: this is just a demonstration. To see the items list, either preview or publish your post.', 'tainacan') }
                        </div>
                        <div 
                                style={{
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
                                className="items-list-placeholder">
                            <div className="search-control">
                                { 
                                    !hideSearch ?
                                    <span className="fake-searchbar">
                                        { !hideAdvancedSearch ? <span className="fake-advanced-searchbar"></span> : null }
                                    </span>
                                    : null
                                }
                                {
                                    showFiltersButtonInsideSearchControl && !hideHideFiltersButton ? <span className="fake-button"><div className="fake-icon"></div><div className="fake-text"></div></span> : null
                                }
                                { 
                                    !hideDisplayedMetadataButton ?
                                    <span className="fake-button"><div className="fake-text"></div></span>
                                    :null
                                }
                                { 
                                    !hideSortingArea ?
                                    <span className="fake-button"> { !hideSortByButton ? <div className="fake-text"></div> : null }<div className="fake-icon"></div><div className="fake-text"></div></span>
                                :null 
                                }
                                {
                                    !showInlineViewModeOptions ? 
                                        <span className="fake-button"><div className="fake-icon"></div><div className="fake-text"></div></span> 
                                    : 
                                        <div className="fake-buttons-group">
                                            { Array(3).fill().map( () => <div className="fake-button"><div className="fake-icon"></div></div> )}
                                            { showFullscreenWithViewModes ? <span className="fake-button"><div className="fake-icon"></div></span> : null }
                                        </div>
                                }
                                {
                                    !showFullscreenWithViewModes ? <span className="fake-button"><div className="fake-icon"></div><div className="fake-text"></div></span> : null
                                }
                                {
                                    !hideExposersButton ? <span className="fake-button"><div className="fake-icon"></div><div className="fake-text"></div></span> : null
                                }
                            </div>
                            <div className="below-search-control">
                                { !showFiltersButtonInsideSearchControl & !hideHideFiltersButton && !hideFilters ? <span className="fake-hide-button"><div className="fake-icon"></div></span> : null }
                                { 
                                    !hideFilters && !filtersAsModal && !startWithFiltersHidden ?
                                        <div 
                                                style={{
                                                    flexBasis: filtersAreaWidth + '%'
                                                }}
                                                className="filters">
                                            <div className="fake-filters-heading"></div>
                                            { Array(2).fill().map( () => {
                                                return <div className="fake-filter">
                                                    <span className="fake-text"></span>
                                                    <span className="fake-searchbar"></span>
                                                </div>
                                            } )}
                                            <div className="fake-filter">
                                                <span className="fake-text"></span>
                                                <div className="fake-checkbox-list">
                                                    { Array(4).fill().map( () => {
                                                        return <div>
                                                            <span className="fake-checkbox"></span>
                                                            <span className="fake-text"></span>
                                                        </div>
                                                    } ) }
                                                    <div className="fake-link"></div>
                                                </div>
                                            </div>
                                            <div className="fake-filter">
                                                <span className="fake-text"></span>
                                                <span className="fake-searchbar"></span>
                                            </div>
                                            <div className="fake-filter">
                                                <span className="fake-text"></span>
                                                <div className="fake-checkbox-list">
                                                    { Array(2).fill().map( () => {
                                                        return <div>
                                                            <span className="fake-checkbox"></span>
                                                            <span className="fake-text"></span>
                                                        </div>
                                                    } ) }
                                                    <div className="fake-link"></div>
                                                </div>
                                            </div>
                                        </div> 
                                    : null 
                                }
                                <div className="aside-filters">    
                                    <div className={ 'items' + (hideItemsThumbnail ? ' items-without-thumbnail' : '') }>
                                        { Array(5).fill().map( () => {
                                            return <div className="fake-item">
                                                <div className="fake-item-header">
                                                    <div className="fake-text"></div>
                                                </div>
                                                <div 
                                                        style={{ 
                                                            backgroundImage: tainacan_plugin ? 'url("' + tainacan_plugin.base_url + '/assets/images/placeholder_square.png")' : '' 
                                                        }}
                                                        className="fake-item-thumb"></div>
                                                { Array(3).fill().map( () => <div className="fake-item-description"></div> ) }
                                            </div>
                                        } ) }
                                        <div className="fake-item fake-item-hovered">
                                            <div className="fake-item-header">
                                                <div className="fake-tooltip"><div className="fake-link"></div></div>
                                                <div className="fake-text"></div>
                                            </div>
                                            <div 
                                                    style={{ 
                                                        backgroundImage: tainacan_plugin ? 'url("' + tainacan_plugin.base_url + '/assets/images/placeholder_square.png")' : '' 
                                                    }}
                                                    className="fake-item-thumb"></div>
                                            { Array(3).fill().map( () => <div className="fake-item-description"></div> ) }
                                        </div>
                                        { Array(2).fill().map( () => {
                                            return <div className="fake-item">
                                                <div className="fake-item-header">
                                                    <div className="fake-text"></div>
                                                </div>
                                                <div 
                                                        style={{ 
                                                            backgroundImage: tainacan_plugin ? 'url("' + tainacan_plugin.base_url + '/assets/images/placeholder_square.png")' : '' 
                                                        }}
                                                        className="fake-item-thumb"></div>
                                                { Array(3).fill().map( () => <div className="fake-item-description"></div> ) }
                                            </div>
                                        } ) }
                                    </div>
                                    { !hidePaginationArea ?
                                    <div className="pagination">
                                        <span className="fake-text"></span>
                                        { !hideItemsPerPageButton ? <span className="fake-button"><div className="fake-text"></div></span> : null }
                                        { !hideGoToPageButton ? <span className="fake-button"><div className="fake-text"></div></span> : null }
                                        <div className="fake-buttons-group">
                                            { Array(6).fill().map( () => <div className="fake-link"></div> ) }
                                        </div>
                                    </div>
                                    : null }
                                </div>
                            </div>
                        </div>
                    </div>
                )
            }

            { isCollectionModalOpen ? 
                <CollectionModal
                    existingCollectionId={ collectionId }  
                    existingCollectionDefaultOrder={ order } 
                    existingCollectionDefaultOrderBy={ collectionOrderBy }
                    existingCollectionDefaultOrderByMeta={ collectionOrderByMeta }
                    existingCollectionDefaultOrderByType={ collectionOrderByType } 
                    existingCollectionDefaultViewMode={ collectionDefaultViewMode } 
                    existingCollectionEnabledViewModes={ collectionEnabledViewModes }
                    onSelectCollection={ ({ collectionId, collectionDefaultViewMode, collectionEnabledViewModes, collectionDefaultOrder, collectionDefaultOrderBy, collectionDefaultOrderByMeta, collectionDefaultOrderByType }) => {
                        collectionId = collectionId;
                        collectionDefaultViewMode = collectionDefaultViewMode ? collectionDefaultViewMode : defaultViewMode;
                        collectionEnabledViewModes = collectionEnabledViewModes && collectionEnabledViewModes.length ? collectionEnabledViewModes : enabledViewModes;
                        order = collectionDefaultOrder ? collectionDefaultOrder : 'ASC';
                        collectionOrderBy = collectionDefaultOrderBy ? collectionDefaultOrderBy : 'date';
                        collectionOrderByMeta = collectionDefaultOrderByMeta ? collectionDefaultOrderByMeta + '' : '';
                        collectionOrderByType = collectionDefaultOrderByType ? collectionDefaultOrderByType : '';
                        setAttributes({
                            collectionId: collectionId, 
                            collectionDefaultViewMode: collectionDefaultViewMode,
                            defaultViewMode: collectionDefaultViewMode,
                            collectionEnabledViewModes: collectionEnabledViewModes,
                            enabledViewModes: collectionEnabledViewModes,
                            order: order,
                            collectionOrderBy, collectionOrderBy,
                            collectionOrderByMeta: collectionOrderByMeta,
                            collectionOrderByType: collectionOrderByType,
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
                        enabledViewModes = registeredViewModesKeys;
                        
                        setAttributes({
                            termId: selectedTermId, 
                            isTermModalOpen: false,
                            enabledViewModes: enabledViewModes
                        });
                    }}
                    onCancelSelection={ () => setAttributes({ isTermModalOpen: false }) }/> 
                : null
            }

        </div>
        )
    );
};