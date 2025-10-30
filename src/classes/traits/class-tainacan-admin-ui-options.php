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
				'navigation' => array(
					'label' => __( 'Navigation', 'tainacan' ),
					'description' => __('Options related to the overall plugin navigation such as sidemenu and fullscreen mode.', 'tainacan'),
					'items' => array(
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
					)
				),
				'dashboard' => array(
					'label' => __( 'Dashboard', 'tainacan' ),
					'description' => __('Options related to the Dashboard page and its cards. Notice that each user may still hide the cards that remain in the screen.', 'tainacan'),
					'items' => array(
						'disableDashboardCardsSorting' => __('Disable dashboard cards sorting', 'tainacan'),
						'hideDashboardRepositoryCard' => __('Hide repository card', 'tainacan'),
						'hideDashboardRepositoryCardTaxonomiesButton' => __('Hide repository card taxonomies button', 'tainacan'),
						'hideDashboardRepositoryCardMetadataButton' => __('Hide repository card metadata button', 'tainacan'),
						'hideDashboardRepositoryCardFiltersButton' => __('Hide repository card filters button', 'tainacan'),
						'hideDashboardRepositoryCardImportersButton' => __('Hide repository card importers button', 'tainacan'),
						'showDashboardRepositoryCardExportersButton' => __('Show repository card exporters button', 'tainacan'),
						'showDashboardRepositoryCardProcessesButton' => __('Show repository card processes button', 'tainacan'),
						'showDashboardRepositoryCardActivitiesButton' => __('Show repository card activities button', 'tainacan'),
						'showDashboardRepositoryCardCapabilitiesButton' => __('Show repository card capabilities button', 'tainacan'),
						'showDashboardRepositoryCardReportsButton' => __('Show repository card reports button', 'tainacan'),
						'hideDashboardCollectionsCard' => __('Hide collections card', 'tainacan'),
						'hideDashboardCollectionsCardCollectionsListButton' => __('Hide collections card collections list button', 'tainacan'),
						'hideDashboardCollectionsCardNewCollectionButton' => __('Hide collections card new collection button', 'tainacan'),
						'hideDashboardCollectionsCardItemsListButton' => __('Hide collections card items list button', 'tainacan'),
						'hideDashboardCollectionsCardMyItemsListButton' => __('Hide collections card "My items list" button', 'tainacan'),
						'hideDashboardCollectionCards' => __('Hide collection cards', 'tainacan'),
						'showOnlyCollectionCardsThatUserCanEdit' => __('Show only collections that user can edit items', 'tainacan'),
						'showOnlyCollectionCardsAuthoredByUser' => __('Show only collections authored by the user', 'tainacan'),
						'hideDashboardCollectionCardsItemsButton' => __('Hide collection cards items button', 'tainacan'),
						'hideDashboardCollectionCardsExternalLinkButton' => __('Hide collection cards external link button', 'tainacan'),
						'showDashboardCollectionCardsMyItemsButton' => __('Show collection cards "My items" button', 'tainacan'),
						'hideDashboardCollectionCardsMetadataButton' => __('Hide collection cards metadata button', 'tainacan'),
						'showDashboardCollectionCardsFiltersButton' => __('Show collection cards filters button', 'tainacan'),
						'showDashboardCollectionCardsImportersButton' => __('Show collection cards importers button', 'tainacan'),
						'showDashboardCollectionCardsExportersButton' => __('Show collection cards exporters button', 'tainacan'),
						'showDashboardCollectionCardsActivitiesButton' => __('Show collection cards activities button', 'tainacan'),
						'showDashboardCollectionCardsCapabilitiesButton' => __('Show collection cards capabilities button', 'tainacan'),
						'showDashboardCollectionCardsReportsButton' => __('Show collection cards reports button', 'tainacan'),
						'hideDashboardInfoCard' => __('Hide info card', 'tainacan'),
						'hideDashboardInfoCardForumButton' => __('Hide info card user\'s forum button', 'tainacan'),
						'hideDashboardInfoCardFAQButton' => __('Hide info card FAQ button', 'tainacan'),
						'hideDashboardInfoCardWikiButton' => __('Hide info card wiki button', 'tainacan'),
						'hideDashboardInfoCardSourceCodeButton' => __('Hide info card source code button', 'tainacan'),
						'showDashboardInfoCardVideosButton' => __('Show info card videos button', 'tainacan'),
						'hideDashboardNewsCard' => __('Hide news card', 'tainacan'),
					)
				),
				'items-list' => array(
					'label' => __( 'Items list', 'tainacan' ),
					'description' => __('Options related to the admin pages that display the faceted search with items list.', 'tainacan'),
					'items' => array(
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
					)
				),
				'item-editing-page' => array(
					'label' => __( 'Item editing page', 'tainacan' ),
					'description' => __('Options related to the item edition form. Some of this settings may also be achieved via collection settings, but doing here will override any option.', 'tainacan'),
					'items' => array(
						'hideItemEditionPageTitle' => __('Hide page title', 'tainacan'),
						'itemEditionPublicationSectionInsideTabs' => __('Show publication section inside tabs', 'tainacan'),
						'itemEditionDocumentInsideTabs'	 => __('Show document entry inside tabs', 'tainacan'),
						'itemEditionAttachmentsInsideTabs' => __('Show attachments inside tabs', 'tainacan'),
						'hideItemEditionPublicationSection' => __('Hide publication section', 'tainacan'),
						'hideItemEditionStatusOption' => __('Hide status options', 'tainacan'),
						'hideItemEditionStatusPublishOption' => __('Hide public status option', 'tainacan'),
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
					)
				),
				'item-page' => array(
					'label' => __( 'Item page', 'tainacan' ),
					'description' => __('Options related to the item page inside the admin', 'tainacan'),
					'items' => array(
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