<?php

namespace Tainacan;

class Dashboard extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

	protected function get_page_slug() : string {
        return 'tainacan_dashboard';
    }
	private $vue_component_page_slug = 'tainacan_admin';
	private $tainacan_dashboard_cards = [];

	function add_admin_menu() {
		// Main Page, Dashboard
		$dashboard_page_suffix = add_menu_page(
			__( 'Tainacan', 'tainacan' ),
			__( 'Tainacan', 'tainacan' ),
			'read',
			$this->get_page_slug(),
			array( &$this, 'render_page' ),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iNC40bW0iIGhlaWdodD0iNC4zbW0iIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDE3LjUxIDE3LjU1NCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+CjxtZXRhZGF0YT4KPHJkZjpSREY+CjxjYzpXb3JrIHJkZjphYm91dD0iIj4KPGRjOmZvcm1hdD5pbWFnZS9zdmcreG1sPC9kYzpmb3JtYXQ+CjxkYzp0eXBlIHJkZjpyZXNvdXJjZT0iaHR0cDovL3B1cmwub3JnL2RjL2RjbWl0eXBlL1N0aWxsSW1hZ2UiLz4KPC9jYzpXb3JrPgo8L3JkZjpSREY+CjwvbWV0YWRhdGE+CjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKC03NS45MTEgLTExOC44OSkiPgo8ZyB0cmFuc2Zvcm09Im1hdHJpeCguMzUyNzggMCAwIC0uMzUyNzggOTIuMjg3IDEzMy45NCkiIGZpbGw9IiNmZmYiPgo8cGF0aCBkPSJtMCAwYy0xZS0zIC0xZS0zIC0yZS0zIC0yZS0zIC0yZS0zIC0zZS0zIC0xZS0zIC0xZS0zIC0xZS0zIC0yZS0zIC0yZS0zIC00ZS0zIC0yLjQwNi01LjEyNC04Ljk4NC02LjAzOC0xOC4xNjgtMS43MzggMC41NjIgMi43MDcgMS4xNzMgNi4zMTggMS4yNzEgOC44NjUgMWUtMyA2ZS0zIDAgMC4wMSAwIDAuMDE2IDIuODE0IDEuODE5IDUuNzI0IDQuMDk2IDguNjM4IDYuOTQzLTAuMTI2IDAuMDY5LTAuMTA0IDAuMDU4IDAgMCA3LjY3Mi00LjE2OCAxMC40ODYtOS4zNDYgOC4yNjMtMTQuMDc5bS0xMC44MTMgMTUuMzQ2cy0xZS0zIDAtMWUtMyAxZS0zYy0yLjA2NS0xLjk5Mi00LjEwNi0zLjY3My02LjA4NS01LjA5Mi0wLjA3OSAyLjM3Mi0wLjM1IDQuOTY5LTAuOTExIDcuNzU5IDIuMjE0LTAuNjczIDQuNTUxLTEuNTQ3IDYuOTk2LTIuNjY3IDAuMDUxIDAuMDQ5IDAuMDM2IDAuMDM1IDAgMCAwLTFlLTMgMWUtMyAtMWUtMyAxZS0zIC0xZS0zbS0xMS4xNCAxNS4xMWM2LjcxIDYuMjA0IDEyLjczMSA3LjI0MiAxNi41MjYgMy40NTkgMWUtMyAwIDJlLTMgLTFlLTMgM2UtMyAtMmUtM3MxZS0zIC0yZS0zIDJlLTMgLTNlLTNjMy44MDktMy43OTUgMi43NzMtOS44NDctMy40NjMtMTYuNjA0LTAuMDMtMC4wMzMtMC4wMzctMC4wMzkgMCAwLTAuMDM4IDAuMDE4LTMuNjM2IDEuODg4LTkuNTc3IDMuNTMzLTAuNDQ2IDIuNDY1LTMuMDY3IDguOTk0LTMuNDkxIDkuNjE3bTIuNjEyLTIxLjg0NmMtMy4yMzktMi4wNC02LjI1OS0zLjM5NS04Ljg3Ni00LjI5Ny0wLjkzNiAwLjcyMS0xLjgwNCAxLjQ0NS0yLjYxIDIuMTY1djFlLTNjLTEuMzQxIDEuMzEtMi43MiAyLjgzNC00LjA5IDQuNjA0IDAuOTA1IDIuNjQzIDIuMjcgNS42OTEgNC4zMjcgOC45NjIgMi44MjYgMC4yMjEgMTAuMDA3LTEuMTM5IDEwLjAyNi0xLjI3OCAwLjIwOS0wLjAzMSAxLjM2Ni03LjI3OSAxLjIyMy0xMC4xNTdtLTEuMTQ3LTkuMjA3Yy0wLjMwNC0wLjAyNS00LjA1IDIuMDcxLTUuMzUgMy4xODl2MWUtM2MyLjAxIDAuNzYxIDYuMzczIDIuOTkyIDYuMzczIDIuOTkyczdlLTMgNGUtMyAwLjAxIDZlLTNjMC01ZS0zIC0wLjYwNy00LjIxMS0xLjAzMy02LjE4OG0tMTYuMjA1IDMuMTMzYzAuMDU5IDEuMDU2IDAuMjQgMi45MTkgMC44MzYgNS4zNTIgMC42MzktMC43NzUgMy43MDQtMy44MjYgNC40OTYtNC41MDUtMi40MTMtMC41OTctNC4yNjctMC43ODItNS4zMzItMC44NDdtLTMuMjEyIDE2LjM4M2MyLjAyOCAwLjQzNSA0LjU2OCAwLjkwOCA2LjMwOCAxLjAzMiAwIDFlLTMgMWUtMyAyZS0zIDFlLTMgM2UtMyAwLTFlLTMgLTFlLTMgLTJlLTMgLTFlLTMgLTNlLTMgLTEuMjkyLTIuMjQ2LTIuMjk4LTQuNDEtMy4wODEtNi40NDEtMS4xIDEuNjE2LTMuMjIxIDUuMzk4LTMuMjI3IDUuNDA5bTAuNTkgMjAuNTg0IDJlLTMgMmUtMyAzZS0zIDFlLTNjNC43ODggMi4yMzQgMTAuMDE1LTAuNjc0IDE0LjE4NS04LjUzMy0yLjgyOC0yLjg2Ny01LjEtNS43My02LjkyMS04LjUwMSAwLTFlLTMgMC0yZS0zIC0xZS0zIC0yZS0zIDFlLTMgMCAxZS0zIDFlLTMgMWUtMyAyZS0zIC0yLjU4MS0wLjA3OC01LjgyNy0wLjc0My04Ljk5Mi0xLjI2NS00LjQ0MSA5LjM5Ni0zLjQxNyAxNS44OTkgMS43MjMgMTguMjk2bTE4LjAxNy0xNy45OTNjLTIuNzU1IDAuNTY3LTUuMzIyIDAuODUzLTcuNjcyIDAuOTQ2IDEuNDA5IDEuOTcgMy4wNzUgNCA1LjA0NiA2LjA1My00ZS0zIDhlLTMgMS45NjYtNC43ODUgMi42MjYtNi45OTltMTQuOTA5LTUuNDkzYzYuOTY2IDcuODI1IDcuNjYyIDE0Ljc1OCAyLjg1NSAxOS41OTJsLTZlLTMgNmUtM2MtNC44MTIgNC44MzgtMTEuOTY5IDQuMDkyLTE5LjYyOS0yLjc2NnYtMWUtMyAxZS0zYy00Ljc2NSA4LjYwOC0xMS4wNiAxMS41ODItMTcuMTA4IDguNzgxbC00ZS0zIC0yZS0zYy0xZS0zIDAtM2UtMyAtMWUtMyAtNGUtMyAtMmUtMyAtOC4xNTItMy40MTgtOC45OTYtMTQuODI2IDIuNDgzLTMxLjQxNmgxZS0zYy0xZS0zIDAtMWUtMyAtMWUtMyAtMWUtMyAtMWUtMyAtMS40MDMtNC43NDctMS41OTItOC40NDgtMS41OTYtMTAuMjY1IDEuNzk0LTVlLTMgNS41MDUgMC4xNjUgMTAuMjggMS41NTEgMTguNzA5LTEyLjcyNCAyOC40MjgtOS4zNDMgMzEuMzI1LTIuNTkyIDAgMWUtMyAxZS0zIDJlLTMgMmUtMyA0ZS0zIDAgMWUtMyAxZS0zIDNlLTMgMmUtMyA0ZS0zIDIuODIyIDYuMDExLTAuMzY2IDEyLjY3OC04LjYgMTcuMTA2IiBmaWxsPSIjZmZmIi8+CjwvZz4KPC9nPgo8L3N2Zz4K'
		);
		add_action( 'load-' . $dashboard_page_suffix, array( &$this, 'load_page' ) );
	}

	function admin_enqueue_css() {
		global $TAINACAN_BASE_URL;
		wp_admin_css( 'dashboard' );
		wp_enqueue_style( 'tainacan-dashboard-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-dashboard.css', [], TAINACAN_VERSION );
	}

	function admin_enqueue_js() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_script( 'dashboard' );

	}

	public function render_page_content() {
		require_once('page.php');
	}

	/**
	 * Registers the deafult dashboard cards to be displayed
	 */
	function register_cards() {

		/**
		 * Option that stores the user enabled cards
		 */
		$enabled_cards = get_option(
			'tainacan_dashboard_enabled_cards',
			array(
				'tainacan-dashboard-repository-card',
				'tainacan-dashboard-collections-card',
				'tainacan-dashboard-info-card',
				// 'tainacan-dashboard-collection-card-267' // Temporary example of collection card
			)
		);
		
		/**
		 * Filling the array containing the default cards
		 * based on user capabilities
		 */
		if (
			current_user_can( 'manage_tainacan' ) ||
			current_user_can( 'tnc_rep_edit_taxonomies') ||
			current_user_can( 'tnc_rep_edit_metadata') ||
			current_user_can( 'tnc_rep_edit_filters')
		) {
			$tainacan_dashboard_cards[] = array(
				'id' => 'tainacan-dashboard-repository-card',
				'title' => __( 'Repository', 'tainacan' ),
				'description' => __('Area responsible for gathering all the structural settings that affect the collections of your repository.', 'tainacan'),
				'content' => [$this, 'tainacan_repository_dashboard_card'],
				'icon' => $this->get_svg_icon( 'repository' ),
				'color' => 'blue'
			);
		}


		$tainacan_dashboard_cards[] = array(
			'id' => 'tainacan-dashboard-collections-card',
			'title' => __( 'Collections', 'tainacan' ),
			'description' => __('Collections are groups of items in the repository that share the same set of metadata.', 'tainacan'),
			'content' => [$this, 'tainacan_collections_dashboard_card'],
			'icon' => $this->get_svg_icon( 'collections' ),
			'color' => 'turquoise',
			'position' => 'side'
		);

		$tainacan_dashboard_cards[] = array(
			'id' => 'tainacan-dashboard-info-card',
			'title' => __( 'Help content and tutorials', 'tainacan' ),
			'description' => __('The Tainacan community provides some help resources. Below we list the main ones for you to clear your doubts.', 'tainacan'),
			'content' => array( $this, 'tainacan_help_dashboard_card' ),
			'icon' => $this->get_svg_icon( 'info' ),
			'color' => 'gray',
			'position' => 'column3'
		);

		$collections = tainacan_collections()->fetch(array(), 'OBJECT');
		foreach( $collections as $collection ) {
			$tainacan_dashboard_cards[] = array(
				'id' => 'tainacan-dashboard-collection-card-' . $collection->get_id(),
				'title' => $collection->get_name(),
				'description' => $collection->get_description(),
				'content' => array( $this, 'tainacan_collection_dashboard_card' ),
				'content_args' => array( 'collection_id' => $collection->get_id() ),
				'icon' => $this->get_svg_icon( 'collection' ),
				'color' => 'turquoise',
				'position' => 'normal'
			);
		}

		/**
		 * Use this filter to add or remove dashboard cards.
		 * Remeber to return an array containing the card objects
		 * with a structure that contains the id, title, description,
		 * content (a callback), icon (an svg or img html tag), color
		 * (one of gray, blue and turoquoise) and position (normal, side, column3, column4)
		 * 
		 * If you remove any card from the array, users won't be able to add it anyway.
		 * If you just remove its id from the 'tainacan_dashboard_enabled_cards' wp option, 
		 * users will be able to add it again.
		 */
		$tainacan_dashboard_cards = apply_filters( 'tainacan-dashboard-cards', $tainacan_dashboard_cards );

		foreach ($tainacan_dashboard_cards as $card) {
			if ( !in_array( $card['id'], $enabled_cards ) )
				continue;
			
			$this->add_dashboard_card(
				$card['id'],
				$card['title'],
				$card
			);
		}
	}

	/**
	 * Wrapper for the wp_add_dashboard_widget function
	 * that also accepts an icon and do not expects controle_callback or control_callback_args
	 * 
	 * @param string $id
	 * @param string $title
	 * @param array $args {
	 *    Optional. Array of arguments for adding a dashboard card.
	 * 		@type string description Summary or small description for the card.
	 * 		@type callable callback function to return HTML content inside the card.
	 * 		@type array $content_args Arguments to be passed to the content callback.
	 * 	 	@type string $icon Icon to be displayed on the card.
	 * 		@type string $color Color of the card. One of 'gray', 'blue', 'turquoise'.
	 * 		@type string $position Position of the card. One of 'normal', 'side', 'column3', 'column4'.
	 * }
	 */
	function add_dashboard_card( $id, $title, $args = array() ) {

		$defaults = array(
			'description' => '',
			'content' => null,
			'content_args' => null,
			'icon' => '',
			'color' => 'gray',
			'position' => 'normal'
		);

		$args = wp_parse_args( $args, $defaults );

		$widget_name = '<span class="tainacan-dashboard-card-title">' . $title . '</span>';
		$widget_name = $args['icon'] ? ('<span class="icon" style="background-color: var(--tainacan-' . $args['color'] . '5);">' . $args['icon'] . '</span>' . $widget_name) : $widget_name;

		$content_callback = $args['content'];
		$callback_args = $args['content_args'];
		$private_callback_args = array( '__widget_basename' => $widget_name );

		if ( is_null( $callback_args ) )
			$callback_args = $private_callback_args;
		elseif ( is_array( $callback_args ) )
			$callback_args = array_merge( $callback_args, $private_callback_args );

		if ( $args['description'] )
			$content_callback = function () use ($args, $callback_args) {
				echo '<p class="tainacan-dashboard-card-description">' . $args['description'] . '</p><hr>';
				call_user_func($args['content'], $callback_args);
			};
		
		wp_add_dashboard_widget(
			$id,
			$widget_name,
			$content_callback,
			null,
			$callback_args,
			$args['position']
		);

		return $id;
	}


	/**
	 * Creates the display code for the repository card
	 */
	function tainacan_repository_dashboard_card($args = null) {
		?>
		<ul class="tainacan-dashboard-card-list" data-color-scheme="blue">
			<?php if ( current_user_can( 'manage_tainacan' ) ||	current_user_can( 'tnc_rep_edit_taxonomies') ) : ?>
				<li>
					<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/taxonomies'); ?>">
						<span class="icon">
							<?php echo $this->get_svg_icon('taxonomies'); ?>
						</span>
						<span class="text"><?php _e('Taxonomies', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_tainacan' ) ||	current_user_can( 'tnc_rep_edit_metadata') ) : ?>
				<li>
					<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/metadata'); ?>">
						<span class="icon">
							<?php echo $this->get_svg_icon('metadata'); ?>
						</span>
						<span class="text"><?php _e('Metadata', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_tainacan' ) ||	current_user_can( 'tnc_rep_edit_filters') ) : ?>
				<li>
					<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/filters'); ?>">
						<span class="icon">
							<?php echo $this->get_svg_icon('filters'); ?>
						</span>
						<span class="text"><?php _e('Filters', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_tainacan' ) ) : ?>
				<li>
					<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/importers'); ?>">
						<span class="icon">
							<?php echo $this->get_svg_icon('importers'); ?>
						</span>
						<span class="text"><?php _e('Importers', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
		<?php
	}

	/**
	 * Creates the display code for the collections card
	 */
	function tainacan_collections_dashboard_card($args = null) {
		?>
		<ul class="tainacan-dashboard-card-list">
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('collections'); ?>
					</span>
					<span class="text"><?php _e('Collections list', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/items'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('items'); ?>
					</span>
					<span class="text"><?php _e('Items list', 'tainacan'); ?></span>
				</a>
			</li>
			<?php if ( current_user_can('manage_tainacan') || current_user_can('tnc_rep_edit_collections') ) : ?>
				<li>
					<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/new'); ?>">
						<span class="icon">
							<?php echo $this->get_svg_icon('add'); ?>
						</span>
						<span class="text"><?php _e('New collection', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/my-items?' . http_build_query(['authorid' => get_current_user_id()])  ); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('item'); ?>
					</span>
					<span class="text"><?php _e('My Items list', 'tainacan'); ?></span>
				</a>
			</li>
		</ul>
		<?php
	}

	/**
	 * Creates the display code for the info and help card
	 */
	function tainacan_help_dashboard_card($args = null) {
		?>
		<ul class="tainacan-dashboard-card-list" data-color-scheme="gray">
			<li>
				<a href="https://tainacan.discourse.group" target="_blank">
					<span class="icon">
						<?php echo $this->get_svg_icon('discourse'); ?>
					</span>
					<span class="text"><?php _e('User\'s forum', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php _e('https://tainacan.github.io/tainacan-wiki/#/faq', 'tainacan'); ?>" target="_blank">
					<span class="icon">
						<?php echo $this->get_svg_icon('help'); ?>
					</span>
					<span class="text"><?php _e('F.A.Q.', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="https://tainacan.github.io/tainacan-wiki/#/" target="_blank">
					<span class="icon">
						<?php echo $this->get_svg_icon('info'); ?>
					</span>
					<span class="text"><?php _e('Wiki', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="https://github.com/tainacan/tainacan" target="_blank">
					<span class="icon">
						<?php echo $this->get_svg_icon('github'); ?>
					</span>
					<span class="text"><?php _e('GitHub', 'tainacan'); ?></span>
				</a>
			</li>
		</ul>
		<?php
	}
	
	/**
	 * Creates the display code for a collection card
	 */
	function tainacan_collection_dashboard_card($args = null) {
		$collection_id = isset($args['collection_id']) ? $args['collection_id'] : null;

		if ( is_null($collection_id) )
			return;
	
	?>
		<ul class="tainacan-dashboard-card-list">
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/items'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('items'); ?>
					</span>
					<span class="text"><?php _e('Items list', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/my-items?' . http_build_query(['authorid' => get_current_user_id()]) ); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('item'); ?>
					</span>
					<span class="text"><?php _e('My Items list', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/metadata'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('metadata'); ?>
					</span>
					<span class="text"><?php _e('Metadata', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/settings'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('settings'); ?>
					</span>
					<span class="text"><?php _e('Settings', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/filters'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('filters'); ?>
					</span>
					<span class="text"><?php _e('Filters', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/reports'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('reports' ); ?>
					</span>
					<span class="text"><?php _e('Reports', 'tainacan'); ?></span>
				</a>
			</li>
		</ul>
	<?php
	}

}
