<?php

namespace Tainacan;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Tainacan\Tools\Tools_Registry;

/**
 * Management Tools admin page: run CLI-backed operations from the UI.
 * Cards and forms are rendered in PHP; JS handles run and status.
 *
 * @since 1.1.0
 */
class Tools extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Cached collections list for tool form selects (fetched once per request).
	 *
	 * @var \Tainacan\Entities\Collection[]|null
	 */
	private $collections_for_tools = null;

	/**
	 * @return string
	 */
	protected function get_page_slug(): string {
		return 'tainacan_tools';
	}

	public function init() {
		parent::init();
	}

	/**
	 * Get all collections for tool form selects. Fetched once per request and reused.
	 * Uses the same query pattern as the dashboard (optionally filter by current user).
	 *
	 * @return \Tainacan\Entities\Collection[]
	 */
	private function get_collections_for_tools() {
		if ( $this->collections_for_tools !== null ) {
			return $this->collections_for_tools;
		}
		$collections_query = [
			'posts_per_page' => -1,
		];
		$this->collections_for_tools = tainacan_collections()->fetch( $collections_query, 'OBJECT' );
		return $this->collections_for_tools;
	}

	/**
	 * Tools to display on the page (exposed to REST only).
	 *
	 * @return \Tainacan\Tools\Tool[]
	 */
	public function get_tools_for_page() {
		$all = Tools_Registry::get_all_tools();
		$list = [];
		foreach ( $all as $tool ) {
			if ( $tool->is_exposed_to_rest() ) {
				$list[] = $tool;
			}
		}
		return $list;
	}

	/**
	 * Output one tool card: title, description, form fields, Run button, output div.
	 *
	 * @param \Tainacan\Tools\Tool $tool
	 */
	public function render_tool_card( $tool ) {
		$id = $tool->get_id();
		$destructive = $tool->is_destructive();
		?>
		<div class="tainacan-tools-card" data-tool-id="<?php echo esc_attr( $id ); ?>"<?php echo $destructive ? ' data-destructive="1"' : ''; ?> data-required-params="<?php echo esc_attr( wp_json_encode( $this->get_required_param_names( $tool->get_params() ) ) ); ?>">
			<div class="tainacan-tools-card-main">
				<h2 class="tainacan-tools-card-title"><?php echo esc_html( $tool->get_name() ); ?></h2>
				<?php if ( $tool->get_description() ) : ?>
					<p class="tainacan-tools-card-description"><?php echo esc_html( $tool->get_description() ); ?></p>
				<?php endif; ?>
				<div class="tainacan-tools-card-form">
					<?php echo $this->render_tool_form_fields( $tool->get_params(), $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="tainacan-tools-card-run">
					<button type="button" class="button button-primary tainacan-tools-run-btn"><?php esc_html_e( 'Run', 'tainacan' ); ?></button>
				</div>
			</div>
			<div class="tainacan-tools-card-output" aria-live="polite"><div class="tainacan-tools-card-output-placeholder"><?php esc_html_e( 'Output messages will appear here.', 'tainacan' ); ?></div></div>
		</div>
		<?php
	}

	/**
	 * @param array<int, array{name: string, type?: string, required?: bool, label?: string, default?: mixed, description?: string}> $params
	 * @return string[]
	 */
	private function get_required_param_names( array $params ) {
		$names = [];
		foreach ( $params as $p ) {
			if ( ! empty( $p['required'] ) && ! empty( $p['name'] ) ) {
				$names[] = $p['name'];
			}
		}
		return $names;
	}

	/**
	 * HTML for form fields from tool param definitions.
	 *
	 * @param array<int, array{name: string, type?: string, required?: bool, label?: string, default?: mixed, description?: string}> $params
	 * @param string $tool_id
	 * @return string
	 */
	public function render_tool_form_fields( array $params, $tool_id ) {
		if ( empty( $params ) ) {
			return '';
		}
		$html = '<div class="tainacan-tools-form">';
		foreach ( $params as $p ) {
			$name = isset( $p['name'] ) ? $p['name'] : '';
			if ( ! $name ) {
				continue;
			}
			$type = isset( $p['type'] ) ? strtolower( (string) $p['type'] ) : 'text';
			$required = ! empty( $p['required'] );
			$default = array_key_exists( 'default', $p ) ? $p['default'] : null;
			$label = isset( $p['label'] ) ? (string) $p['label'] : '';
			$description = isset( $p['description'] ) ? $p['description'] : '';
			$input_id = 'tainacan-tool-' . $tool_id . '-' . $name;
			$label_text = $label ? $label : str_replace( '_', ' ', $name );
			if ( $required ) {
				$label_text .= ' *';
			}
			$html .= '<div class="tainacan-tools-field">';
			$is_boolean = ( $type === 'boolean' );
			if ( ! $is_boolean ) {
				$html .= '<label class="tainacan-tools-field__label" for="' . esc_attr( $input_id ) . '">' . esc_html( $label_text ) . '</label>';
			}

			if ( $is_boolean ) {
				$html .= '<label class="tainacan-tools-field__checkbox" for="' . esc_attr( $input_id ) . '">';
				$html .= '<input type="checkbox" id="' . esc_attr( $input_id ) . '" name="' . esc_attr( $name ) . '" value="1"' . ( $default ? ' checked="checked"' : '' ) . '>';
				$html .= '<span class="tainacan-tools-field__checkbox-label">' . esc_html( $label_text ) . '</span>';
				$html .= '</label>';
			} elseif ( $name === 'collection' || $name === 'collection_id' ) {
				$collections = $this->get_collections_for_tools();
				$html .= '<select id="' . esc_attr( $input_id ) . '" name="' . esc_attr( $name ) . '"';
				if ( $required ) {
					$html .= ' required="required"';
				}
				$html .= '>';
				if ( $name === 'collection' ) {
					$html .= '<option value="all"' . ( $default === 'all' ? ' selected="selected"' : '' ) . '>' . esc_html__( 'All collections', 'tainacan' ) . '</option>';
				}
				foreach ( $collections as $collection ) {
					$coll_id = $collection->get_id();
					$coll_name = $collection->get_name();
					$selected = ( $default !== null && (string) $default === (string) $coll_id ) ? ' selected="selected"' : '';
					$html .= '<option value="' . esc_attr( (string) $coll_id ) . '"' . $selected . '>' . esc_html( $coll_name ) . '</option>';
				}
				$html .= '</select>';
			} else {
				$input_type = ( $type === 'number' ) ? 'number' : 'text';
				$html .= '<input type="' . esc_attr( $input_type ) . '" id="' . esc_attr( $input_id ) . '" name="' . esc_attr( $name ) . '"';
				if ( $default !== null && $default !== '' ) {
					$html .= ' value="' . esc_attr( (string) $default ) . '"';
				}
				if ( $required ) {
					$html .= ' required="required"';
				}
				$html .= '>';
			}

			if ( $description ) {
				$html .= '<p class="description">' . esc_html( $description ) . '</p>';
			}
			$html .= '</div>';
		}
		$html .= '</div>';
		return $html;
	}

	public function add_admin_menu() {
		if ( ! $this->has_admin_ui_option( 'hideNavigationToolsButton' ) ) {
			$tools_page_suffix = add_submenu_page(
				! $this->has_admin_ui_option( 'hideNavigationOtherMenu' ) ? $this->tainacan_other_links_slug : $this->tainacan_root_menu_slug,
				__( 'Management Tools', 'tainacan' ),
				'<span class="icon" aria-hidden="true">' . $this->get_svg_icon( 'settings' ) . '</span><span class="menu-text">' . __( 'Management Tools', 'tainacan' ) . '</span>',
				'manage_options',
				$this->get_page_slug(),
				[ &$this, 'render_page' ]
			);
			add_action( 'load-' . $tools_page_suffix, [ &$this, 'load_page' ] );
		}
	}

	public function admin_enqueue_css() {
		global $TAINACAN_BASE_URL;
		wp_enqueue_style( 'tainacan-tools-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-tools.css', [], TAINACAN_VERSION );
	}

	public function admin_enqueue_js() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_script(
			'tainacan-tools-scripts',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_tools.js',
			[ 'tainacan-admin-navigation-menu' ],
			TAINACAN_VERSION,
			true
		);

		// Provide i18n strings for the tools page script.
		$i18n = [
			'run'                 => __( 'Run', 'tainacan' ),
			'running'             => __( 'Running…', 'tainacan' ),
			'no_output'           => __( 'No output yet.', 'tainacan' ),
			'confirm_destructive' => __( 'This action cannot be undone. Continue?', 'tainacan' ),
			'required_field'      => __( 'Please fill required fields.', 'tainacan' ),
		];

		// Preferred namespace.
		wp_localize_script( 'tainacan-tools-scripts', 'tainacan_tools', [ 'i18n' => $i18n ] );
	}

	public function render_page_content() {
		require_once __DIR__ . '/page.php';
	}
}
