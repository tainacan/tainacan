<?php

namespace Tainacan\Traits;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class for getting Admin UI options passed either via query string in
 * the URL or via the 'tainacan-admin-ui-options' filter.
 */
trait Admin_UI_Options {

	protected static $admin_ui_options = [];

	/**
	 * Lists a translatable and grouped version of the available admin ui options
	 * 
	 * @return array of available admin ui options
	 */
	public function get_available_admin_ui_options() {
		
		return apply_filters(
			'tainacan-available-admin-ui-options',
			array(
				__( 'Navigation', 'tainacan' ) => array(
					'forceFullscreenAdminMode' => __('Force Tainacan to always overlap WordPress admin menu and sidebar', 'tainacan'),
					
					'hideBreadcrumbs' => __('Hide breadcrumbs', 'tainacan'),
					'hideWordPressShorcutButton' => __('Hide WordPress shortcut button', 'tainacan'),
					'hideSiteShorcutButton' => __('Hide site shortcut button', 'tainacan'),
					'hideFullscreenTogglerButton' => __('Hide fullscreen toggler button', 'tainacan'),
					'hideMenuCollapserButton' => __('Hide menu collapser button', 'tainacan'),
					'hideNavigationSidebar' => __('Hide entire navigation side menu', 'tainacan'),
					'hideNavigationHomeButton' => __('Hide home button in side menu', 'tainacan'),
					
					'hideNavigationRepositoryMenu' => __('Hide "Repository" menu button in side menu', 'tainacan'),
					'hideNavigationTaxonomiesButton' => __('Hide taxonomies button in repository submenu', 'tainacan'),
					'hideNavigationMetadataButton' => __('Hide metadata button in repository submenu', 'tainacan'),
					'hideNavigationFiltersButton' => __('Hide filters button in repository submenu', 'tainacan'),
					'hideNavigationImportersButton' => __('Hide importers button in repository submenu', 'tainacan'),
					'hideNavigationExportersButton' => __('Hide exporters button in repository submenu', 'tainacan'),
					'hideNavigationActivitiesButton' => __('Hide activities button in repository submenu', 'tainacan'),
					'hideNavigationCapabilitiesButton' => __('Hide permissions button in repository submenu', 'tainacan'),
					'hideNavigationProcessesButton' => __('Hide processes button in repository submenu', 'tainacan'),
					'hideNavigationReportsButton' => __('Hide reports button in repository submenu', 'tainacan'),
					
					'hideNavigationCollectionsMenu' => __('Hide "Collections" menu button in side menu', 'tainacan'),
					'hideNavigationCollectionsButton' => __('Hide collections list button in collections submenu', 'tainacan'),
					'hideNavigationItemsButton' => __('Hide "All items" button in collections submenu', 'tainacan'),
					'hideNavigationMyItemsButton' => __('Hide "My items" button in collections submenu', 'tainacan'),
					
					'hideNavigationCollectionName' => __('Hide collection name in current collection submenu', 'tainacan'),
					'hideNavigationCollectionItemsButton' => __('Hide "All items" button in current collection submenu', 'tainacan'),
					'hideNavigationCollectionMyItemsButton' => __('Hide "My items" button in current collection submenu', 'tainacan'),
					'hideNavigationCollectionSettingsButton' => __('Hide settings button in current collection submenu', 'tainacan'),
					'hideNavigationCollectionMetadataButton' => __('Hide metadata button in current collection submenu', 'tainacan'),
					'hideNavigationCollectionFiltersButton' => __('Hide filters button in current collection submenu', 'tainacan'),
					'hideNavigationCollectionExportersButton' => __('Hide exporters button in current collection submenu', 'tainacan'),
					'hideNavigationCollectionActivitiesButton' => __('Hide activities button in current collection submenu', 'tainacan'),
					'hideNavigationCollectionCapabilitiesButton' => __('Hide permissions button in current collection submenu', 'tainacan'),
					'hideNavigationCollectionReportsButton' => __('Hide reports button in current collection submenu', 'tainacan'),
					
					'hideExternalEntityLinks' => __('Hide external site links for item, collection, taxonomies and other public pages', 'tainacan'),
				
					'hideNavigationOtherMenu' => __('Hide "Other" menu button in side menu', 'tainacan'),
					'hideNavigationSettingsButton' => __('Hide "Settings" button in side menu', 'tainacan'),
					'hideNavigationRolesButton' => __('Hide "Roles" button in side menu', 'tainacan'),
					'hideNavigationSystemCheckButton' => __('Hide "System Check" button in side menu', 'tainacan'),

				),
				__( 'Dashboard (experimental)', 'tainacan' ) => array(
					'hideHomeRepositorySection' => __('Hide repository section', 'tainacan'),
					'hideHomeThemeCollectionsButton' => __('Hide collections button in theme', 'tainacan'),
					'hideHomeThemeItemsButton' => __('Hide items button in theme', 'tainacan'),
					'hideHomeTaxonomiesButton' => __('Hide taxonomies button', 'tainacan'),
					'hideHomeMetadataButton' => __('Hide repository level metadata button', 'tainacan'),
					'hideHomeFiltersButton' => __('Hide repository level filters button', 'tainacan'),
					'hideHomeImportersButton' => __('Hide importers button', 'tainacan'),
					'hideHomeExportersButton' => __('Hide exporters button', 'tainacan'),
					'hideHomeActivitiesButton' => __('Hide activities button', 'tainacan'),
					'hideHomeCollectionsButton' => __('Hide collections button', 'tainacan'),
					'hideHomeCollectionMyItemsButton' => __('Hide "My items"settings button in collections section', 'tainacan'),
					'hideHomeCollectionSettingsButton' => __('Hide settings button in collections section', 'tainacan'),
					'hideHomeCollectionMetadataButton' => __('Hide metadata button in collections section', 'tainacan'),
					'hideHomeCollectionFiltersButton' => __('Hide filters button in collections section', 'tainacan'),
					'hideHomeCollectionActivitiesButton' => __('Hide activities button in collections section', 'tainacan'),
					'hideHomeCollectionThemeCollectionButton' => __('Hide "view in theme" button in collections section', 'tainacan'),
					'showHomeCollectionCreateItemButton' => __('Show create item button in collections section', 'tainacan')
				),
				__( 'Items list', 'tainacan' ) => array(
					'hideItemsListPageTitle' => __('Hide page title', 'tainacan'),
					'hideItemsListBulkActionsButton' => __('Hide bulk actions button', 'tainacan'),
					'hideItemsListMultipleSelection' => __('Hide multiple item selection', 'tainacan'),
					'hideItemsListSelection' => __('Hide individual item selection', 'tainacan'),
					'hideItemsListExposersButton' => __('Hide "View as..." button', 'tainacan'),
					'hideItemsListViewModesButton' => __('Hide view mode selector button', 'tainacan'),
					'hideDisplayedMetadataDropdown' => __('Hide displayed metadata dropdown', 'tainacan'),
					'hideItemsListAdvancedSearch' => __('Hide advanced search', 'tainacan'),
					'hideItemsListStatusTabs' => __('Hide status tabs', 'tainacan'),
					'hideItemsListStatusTabsTotalItems' => __('Hide total items in status tabs', 'tainacan'),
					'hideItemsListCreationDropdownBulkAdd' => __('Hide bulk add button in creation dropdown', 'tainacan'),
					'hideItemsListCreationDropdownImport' => __('Hide import button in creation dropdown', 'tainacan'),
					'hideItemsListContextMenu' => __('Hide right-click context menu', 'tainacan'),
					'hideItemsListFilterCreationButton' => __('Hide create filters button', 'tainacan'),
					'hideItemsListGoToPageButton' => __('Hide "Go to page" button', 'tainacan'),
					'hideItemsListItemsPerPageButton' => __('Hide "Items per page" button', 'tainacan')
				),
				__( 'Item editing page', 'tainacan' ) => array(
					'hideItemEditionPageTitle' => __('Hide page title', 'tainacan'),
					'itemEditionPublicationSectionInsideTabs' => __('Show publication section inside tabs', 'tainacan'),
					'itemEditionDocumentInsideTabs'	 => __('Show document entry inside tabs', 'tainacan'),
					'itemEditionAttachmentsInsideTabs' => __('Show attachments inside tabs', 'tainacan'),
					'hideItemEditionPublicationSection' => __('Hide publication section', 'tainacan'),
					'hideItemEditionStatusOption' => __('Hide status options', 'tainacan'),
					'hideItemEditionStatusPublishOption' => __('Hide publish status option', 'tainacan'),
					'hideItemEditionStatusPrivateOption' => __('Hide private status option', 'tainacan'),
					'hideItemEditionStatusPendingOption' => __('Hide pending status option', 'tainacan'),
					'hideItemEditionCommentsToggle' => __('Hide comments option', 'tainacan'),
					'hideItemEditionDocument' => __('Hide document entry completely', 'tainacan'),
					'hideItemEditionDocumentFileInput' => __('Hide file type document entry', 'tainacan'),
					'hideItemEditionDocumentTextInput' => __('Hide text type document entry', 'tainacan'),
					'hideItemEditionDocumentUrlInput' => __('Hide URL type document entry', 'tainacan'),
					'hideItemEditionThumbnail' => __('Hide thumbnail', 'tainacan'),
					'hideItemEditionAttachments' => __('Hide attachments', 'tainacan'),
					'itemEditionStatusOptionOnFooterDropdown' => __('Show status option in footer dropdown', 'tainacan'),
					'allowItemEditionModalInsideModal' => __('Allow item creation modal inside another modal (experimental)', 'tainacan')
				),
				__( 'Item page', 'tainacan' ) => array(
					'hideItemSinglePageTitle' => __('Hide page title', 'tainacan'),
					'hideItemSingleCurrentStatus' => __('Hide status', 'tainacan'),
					'hideItemSingleCurrentVisibility' => __('Hide visibility status', 'tainacan'),
					'hideItemSingleCommentsOpen' => __('Hide comments condition', 'tainacan'),
					'hideItemSingleDocument' => __('Hide document', 'tainacan'),
					'hideItemSingleThumbnail' => __('Hide thumbnail', 'tainacan'),
					'hideItemSingleAttachments' => __('Hide attachments', 'tainacan'),
					'hideItemSingleActivities' => __('Hide activities', 'tainacan'),
					'hideItemSingleExposers' => __('Hide "View as..." button', 'tainacan')
				)
			)
		);
	}

	/**
	 * 
	 * @return string option value for the given setting
	 */
	public function has_admin_ui_option($option) {
		// Get Admin Options to tweak which components will be displayed
		return isset(self::$admin_ui_options[$option]) && ( self::$admin_ui_options[$option] === 'true' || self::$admin_ui_options[$option] === true || self::$admin_ui_options[$option] === 1 || self::$admin_ui_options[$option] === '1' );
	}
}