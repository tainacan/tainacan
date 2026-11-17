<?php
/**
 * Semantic search console markup.
 *
 * Included from Rag_Console::render_page_content(), so `$this` is the page object and
 * every value below is already gathered. Nothing here queries anything.
 *
 * @var \Tainacan\Rag_Console $this
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$report      = isset( $this->report ) && is_array( $this->report ) ? $this->report : [];
$preview     = $this->preview;
$collections = isset( $preview['collections'] ) ? $preview['collections'] : [];
$connector   = isset( $preview['connector'] ) ? (string) $preview['connector'] : '';
$models      = isset( $preview['models'] ) && is_array( $preview['models'] ) ? $preview['models'] : [];

$environment = isset( $report['environment'] ) ? $report['environment'] : [];
$spaces      = isset( $report['spaces'] ) ? $report['spaces'] : [];
$queue       = isset( $report['queue'] ) ? $report['queue'] : [];
$failed      = isset( $report['failed'] ) ? $report['failed'] : [];
$paused      = isset( $report['paused'] ) ? $report['paused'] : [];
$active      = isset( $report['active'] ) ? $report['active'] : [];

$notice      = isset( $_GET[ \Tainacan\Rag_Console::NOTICE_ARG ] ) ? \Tainacan\Rag_Console::notice_text( wp_unslash( $_GET[ \Tainacan\Rag_Console::NOTICE_ARG ] ) ) : '';
$providers   = \Tainacan\Rag\Provider_Registry::get_instance()->get_providers();
$status      = isset( $environment['status'] ) ? $environment['status'] : 'unknown';
$ready       = 'ready' === $status;
?>
<style>
	.tainacan-rag-console { max-width: 1100px; }
	.tainacan-rag-console .tainacan-rag-panel {
		background: #fff;
		border: 1px solid #c3c4c7;
		border-radius: 4px;
		margin-block-end: 20px;
		padding: 4px 16px 16px;
	}
	.tainacan-rag-console .tainacan-rag-state { display: inline-block; padding: 1px 8px; border-radius: 10px; font-size: 12px; }
	.tainacan-rag-console .tainacan-rag-state--ready { background: #d1f0d1; color: #1a4a1a; }
	.tainacan-rag-console .tainacan-rag-state--degraded { background: #fdf0c8; color: #664d00; }
	.tainacan-rag-console .tainacan-rag-state--unsupported { background: #f8d0d0; color: #6b1111; }
	.tainacan-rag-console .tainacan-rag-state--unknown { background: #e5e5e5; color: #333; }
	.tainacan-rag-console .tainacan-rag-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-block-start: 8px; }
	.tainacan-rag-console .tainacan-rag-error { color: #8a1f11; font-family: monospace; font-size: 12px; word-break: break-word; }
	.tainacan-rag-console .tainacan-rag-snippet { max-width: 520px; }
	.tainacan-rag-console td, .tainacan-rag-console th { vertical-align: top; }
</style>

<div class="wrap tainacan-page-container-content tainacan-rag-console">
	<div class="tainacan-fixed-subheader">
		<h1 class="tainacan-page-title">
			<?php esc_html_e( 'Semantic search', 'tainacan' ); ?>
		</h1>
	</div>

	<?php if ( '' !== $notice ) : ?>
		<div class="notice notice-info is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
	<?php endif; ?>

	<p>
		<?php esc_html_e( 'Everything below reflects stored vectors and their visibility rules. A search returns items through the item repository, so private items and collections the reader cannot open never reach a result, even if their vectors are still in the table.', 'tainacan' ); ?>
	</p>

	<!-- Requirements -->
	<div class="tainacan-rag-panel">
		<h2><?php esc_html_e( 'Requirements', 'tainacan' ); ?></h2>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Environment', 'tainacan' ); ?></th>
					<td>
						<span class="tainacan-rag-state tainacan-rag-state--<?php echo esc_attr( in_array( $status, [ 'ready', 'degraded', 'unsupported' ], true ) ? $status : 'unknown' ); ?>">
							<?php echo esc_html( $status ); ?>
						</span>
						<?php if ( ! $ready ) : ?>
							<p class="description">
								<?php esc_html_e( 'Until the environment is ready, a space cannot be created because the table would be rejected by the server. Re-run the environment report before acting on anything else on this page.', 'tainacan' ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Database', 'tainacan' ); ?></th>
					<td><?php echo esc_html( '' !== $environment['db_version'] ? $environment['db_version'] : __( 'unknown', 'tainacan' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Largest dimension accepted', 'tainacan' ); ?></th>
					<td><?php echo esc_html( (string) (int) $environment['max_dimensions'] ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Active spaces', 'tainacan' ); ?></th>
					<td>
						<?php
						printf(
							/* translators: 1: text space id or "none", 2: image space id or "none" */
							esc_html__( 'Text: %1$s · Image: %2$s', 'tainacan' ),
							esc_html( null === $active['text'] ? __( 'none', 'tainacan' ) : (string) $active['text'] ),
							esc_html( null === $active['image'] ? __( 'none', 'tainacan' ) : (string) $active['image'] )
						);
						?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- Spaces -->
	<div class="tainacan-rag-panel">
		<h2><?php esc_html_e( 'Spaces', 'tainacan' ); ?></h2>

		<?php if ( empty( $spaces ) ) : ?>
			<p><?php esc_html_e( 'No space exists yet. A space is one provider, one model, one modality and one dimension, and every search runs inside exactly one of them.', 'tainacan' ); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Space', 'tainacan' ); ?></th>
						<th><?php esc_html_e( 'Provider / model', 'tainacan' ); ?></th>
						<th><?php esc_html_e( 'Modality', 'tainacan' ); ?></th>
						<th><?php esc_html_e( 'Dims', 'tainacan' ); ?></th>
						<th><?php esc_html_e( 'Vector index', 'tainacan' ); ?></th>
						<th><?php esc_html_e( 'Chunks / items', 'tainacan' ); ?></th>
						<th><?php esc_html_e( 'Est. vector size', 'tainacan' ); ?></th>
						<th><?php esc_html_e( 'Queue (pending / failed)', 'tainacan' ); ?></th>
						<th><?php esc_html_e( 'State', 'tainacan' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'tainacan' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $spaces as $space ) : ?>
						<tr>
							<td>
								<strong>#<?php echo esc_html( (string) $space['id'] ); ?></strong><br />
								<span class="description"><?php echo esc_html( $space['key'] ); ?></span>
							</td>
							<td><?php echo esc_html( $space['provider'] . ' / ' . $space['model'] ); ?></td>
							<td><?php echo esc_html( $space['modality'] ); ?></td>
							<td><?php echo esc_html( (string) $space['dimensions'] ); ?></td>
							<td>
								<?php if ( null === $space['has_index'] ) : ?>
									<span class="tainacan-rag-error"><?php esc_html_e( 'table missing', 'tainacan' ); ?></span>
								<?php elseif ( $space['has_index'] ) : ?>
									<?php esc_html_e( 'present', 'tainacan' ); ?>
								<?php else : ?>
									<span class="tainacan-rag-error"><?php esc_html_e( 'missing', 'tainacan' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<?php echo esc_html( (string) $space['storage']['chunks'] . ' / ' . (string) $space['storage']['items'] ); ?>
							</td>
							<td><?php echo esc_html( \Tainacan\Rag_Console::format_bytes( $space['storage']['vector_bytes'] ) ); ?></td>
							<td>
								<?php echo esc_html( (string) $space['queue']['pending'] . ' / ' . (string) $space['queue']['failed'] ); ?>
							</td>
							<td>
								<?php echo esc_html( $space['is_active'] ? __( 'active', 'tainacan' ) : __( 'inactive', 'tainacan' ) ); ?>
								<br /><span class="description"><?php echo esc_html( $space['status'] ); ?></span>
							</td>
							<td>
								<?php if ( ! $space['is_active'] ) : ?>
									<form method="post">
										<?php wp_nonce_field( \Tainacan\Rag_Console::NONCE_ACTION ); ?>
										<input type="hidden" name="tainacan_rag_action" value="activate_space" />
										<input type="hidden" name="space_id" value="<?php echo esc_attr( (string) $space['id'] ); ?>" />
										<button type="submit" class="button button-small"><?php esc_html_e( 'Activate', 'tainacan' ); ?></button>
									</form>
								<?php endif; ?>
								<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this space and every vector stored in it?', 'tainacan' ) ); ?>');">
									<?php wp_nonce_field( \Tainacan\Rag_Console::NONCE_ACTION ); ?>
									<input type="hidden" name="tainacan_rag_action" value="delete_space" />
									<input type="hidden" name="space_id" value="<?php echo esc_attr( (string) $space['id'] ); ?>" />
									<button type="submit" class="button button-small button-link-delete"><?php esc_html_e( 'Delete', 'tainacan' ); ?></button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

		<?php endif; ?>

		<h3><?php esc_html_e( 'Create a space', 'tainacan' ); ?></h3>

		<?php $connector_label = isset( $providers[ $connector ] ) ? $providers[ $connector ]->get_label() : $connector; ?>

		<form method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( $this->get_page_slug() ); ?>" />
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="rag-connector"><?php esc_html_e( 'Connector', 'tainacan' ); ?></label></th>
						<td>
							<select id="rag-connector" name="rag_connector" onchange="this.form.submit()">
								<?php foreach ( $providers as $provider_id => $provider ) : ?>
									<option value="<?php echo esc_attr( $provider_id ); ?>" <?php selected( $connector, $provider_id ); ?>>
										<?php
										echo esc_html(
											sprintf(
												/* translators: 1: connector label, 2: configured or not */
												__( '%1$s (%2$s)', 'tainacan' ),
												$provider->get_label(),
												$provider->is_configured() ? __( 'configured', 'tainacan' ) : __( 'not configured', 'tainacan' )
											)
										);
										?>
									</option>
								<?php endforeach; ?>
							</select>
							<noscript><button type="submit" class="button"><?php esc_html_e( 'Show models', 'tainacan' ); ?></button></noscript>
							<p class="description">
								<?php
								printf(
									/* translators: %s: link to the connectors screen */
									esc_html__( 'The list is the AI connectors installed on the site; keys and vendor choice live there, not here: %s. Choosing one reloads the page so the model list can be read from that connector.', 'tainacan' ),
									'<a href="' . esc_url( admin_url( 'options-connectors.php' ) ) . '">' . esc_html__( 'Settings → Connectors', 'tainacan' ) . '</a>'
								);
								?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</form>

		<form method="post">
			<?php wp_nonce_field( \Tainacan\Rag_Console::NONCE_ACTION ); ?>
			<input type="hidden" name="tainacan_rag_action" value="create_space" />
			<input type="hidden" name="provider" value="<?php echo esc_attr( $connector ); ?>" />
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Connector', 'tainacan' ); ?></th>
						<td><strong><?php echo esc_html( $connector_label ); ?></strong></td>
					</tr>
					<tr>
						<th scope="row"><label for="rag-model"><?php esc_html_e( 'Model', 'tainacan' ); ?></label></th>
						<td>
							<?php if ( ! empty( $models ) ) : ?>
								<select id="rag-model" name="model">
									<?php foreach ( $models as $model_id => $model_label ) : ?>
										<option value="<?php echo esc_attr( $model_id ); ?>"><?php echo esc_html( $model_label ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Read from this connector. Embeddings from different models are not comparable, so a space is bound to the one chosen here.', 'tainacan' ); ?></p>
							<?php else : ?>
								<input type="text" id="rag-model" name="model" class="regular-text" placeholder="<?php esc_attr_e( 'an embedding model id your connector accepts', 'tainacan' ); ?>" />
								<p class="description">
									<?php
									echo esc_html(
										sprintf(
											/* translators: %s: connector id */
											__( '%s reports no embedding model to WordPress AI, so this is free text: enter the model id it accepts. A connector can be given a model list with the tainacan_rag_connector_models filter.', 'tainacan' ),
											$connector_label
										)
									);
									?>
								</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="rag-modality"><?php esc_html_e( 'Modality', 'tainacan' ); ?></label></th>
						<td>
							<select id="rag-modality" name="modality">
								<option value="text"><?php esc_html_e( 'Text', 'tainacan' ); ?></option>
								<option value="image"><?php esc_html_e( 'Image', 'tainacan' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="rag-dimensions"><?php esc_html_e( 'Dimensions', 'tainacan' ); ?></label></th>
						<td>
							<input type="number" min="1" id="rag-dimensions" name="dimensions" class="small-text" />
							<p class="description"><?php esc_html_e( 'The table is built for this dimension. It cannot be changed later without rebuilding the space, so check the model documentation first.', 'tainacan' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
			<p><button type="submit" class="button button-primary" <?php disabled( ! $ready ); ?>><?php esc_html_e( 'Create space', 'tainacan' ); ?></button></p>
		</form>
	</div>

	<!-- Indexing -->
	<div class="tainacan-rag-panel">
		<h2><?php esc_html_e( 'Indexing', 'tainacan' ); ?></h2>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Queue', 'tainacan' ); ?></th>
					<td>
						<?php
						printf(
							/* translators: 1: pending, 2: failed, 3: done */
							esc_html__( 'pending %1$d · failed %2$d · done %3$d', 'tainacan' ),
							(int) $queue['pending'],
							(int) $queue['failed'],
							(int) $queue['done']
						);
						?>
						<div class="tainacan-rag-actions">
							<form method="post">
								<?php wp_nonce_field( \Tainacan\Rag_Console::NONCE_ACTION ); ?>
								<input type="hidden" name="tainacan_rag_action" value="run_queue" />
								<button type="submit" class="button button-small"><?php esc_html_e( 'Process 5 items now', 'tainacan' ); ?></button>
							</form>
							<form method="post">
								<?php wp_nonce_field( \Tainacan\Rag_Console::NONCE_ACTION ); ?>
								<input type="hidden" name="tainacan_rag_action" value="retry_queue" />
								<button type="submit" class="button button-small" <?php disabled( 0 === (int) $queue['failed'] ); ?>><?php esc_html_e( 'Requeue failed rows', 'tainacan' ); ?></button>
							</form>
							<?php if ( ! empty( $paused ) ) : ?>
								<form method="post">
									<?php wp_nonce_field( \Tainacan\Rag_Console::NONCE_ACTION ); ?>
									<input type="hidden" name="tainacan_rag_action" value="clear_breaker" />
									<button type="submit" class="button button-small"><?php esc_html_e( 'Clear provider pauses', 'tainacan' ); ?></button>
								</form>
							<?php endif; ?>
						</div>
						<p class="description"><?php esc_html_e( 'Processing runs inline and is capped, so it is a way to drain a few rows, not a substitute for the scheduled worker. A row that keeps failing keeps its attempt count and backs off.', 'tainacan' ); ?></p>
					</td>
				</tr>

				<?php if ( ! empty( $paused ) ) : ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Paused providers', 'tainacan' ); ?></th>
						<td>
							<ul>
								<?php foreach ( $paused as $pause ) : ?>
									<li>
										<code><?php echo esc_html( $pause['key'] ); ?></code> —
										<?php
										printf(
											/* translators: %d: seconds remaining */
											esc_html__( '%d seconds left', 'tainacan' ),
											(int) $pause['seconds_remaining']
										);
										?>
										<br /><span class="tainacan-rag-error"><?php echo esc_html( $pause['error'] ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ( ! empty( $failed ) ) : ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Recent failures', 'tainacan' ); ?></th>
						<td>
							<table class="widefat striped">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Row', 'tainacan' ); ?></th>
										<th><?php esc_html_e( 'Space', 'tainacan' ); ?></th>
										<th><?php esc_html_e( 'Item', 'tainacan' ); ?></th>
										<th><?php esc_html_e( 'Attempts', 'tainacan' ); ?></th>
										<th><?php esc_html_e( 'Error', 'tainacan' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $failed as $row ) : ?>
										<tr>
											<td><?php echo esc_html( (string) $row['id'] ); ?></td>
											<td><?php echo esc_html( (string) $row['space_id'] ); ?></td>
											<td><?php echo esc_html( (string) $row['item_id'] ); ?></td>
											<td><?php echo esc_html( (string) $row['attempts'] ); ?></td>
											<td class="tainacan-rag-error"><?php echo esc_html( $row['error'] ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<!-- Coverage -->
	<div class="tainacan-rag-panel">
		<h2><?php esc_html_e( 'Coverage', 'tainacan' ); ?></h2>

		<?php if ( empty( $spaces ) ) : ?>
			<p><?php esc_html_e( 'No space to measure against yet.', 'tainacan' ); ?></p>
		<?php else : ?>
			<?php foreach ( $spaces as $space ) : ?>
				<h3>
					<?php
					printf(
						/* translators: 1: space id, 2: provider/model */
						esc_html__( 'Space #%1$d — %2$s', 'tainacan' ),
						(int) $space['id'],
						esc_html( $space['provider'] . ' / ' . $space['model'] )
					);
					?>
				</h3>

				<?php if ( empty( $space['coverage'] ) ) : ?>
					<p><?php esc_html_e( 'No collection has published items yet.', 'tainacan' ); ?></p>
				<?php else : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Collection', 'tainacan' ); ?></th>
								<th><?php esc_html_e( 'Published items', 'tainacan' ); ?></th>
								<th><?php esc_html_e( 'Indexed', 'tainacan' ); ?></th>
								<th><?php esc_html_e( 'Missing', 'tainacan' ); ?></th>
								<th><?php esc_html_e( 'Backfill', 'tainacan' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $space['coverage'] as $row ) : ?>
								<tr>
									<td><?php echo esc_html( $row['name'] . ' (#' . $row['collection_id'] . ')' ); ?></td>
									<td><?php echo esc_html( (string) $row['published'] ); ?></td>
									<td><?php echo esc_html( (string) $row['indexed'] ); ?></td>
									<td>
										<?php if ( $row['missing'] > 0 ) : ?>
											<span class="tainacan-rag-error"><?php echo esc_html( (string) $row['missing'] ); ?></span>
										<?php else : ?>
											<?php echo esc_html( '0' ); ?>
										<?php endif; ?>
									</td>
									<td>
										<div class="tainacan-rag-actions">
											<form method="post">
												<?php wp_nonce_field( \Tainacan\Rag_Console::NONCE_ACTION ); ?>
												<input type="hidden" name="tainacan_rag_action" value="backfill_start" />
												<input type="hidden" name="space_id" value="<?php echo esc_attr( (string) $space['id'] ); ?>" />
												<input type="hidden" name="collection_id" value="<?php echo esc_attr( (string) $row['collection_id'] ); ?>" />
												<button type="submit" class="button button-small"><?php esc_html_e( 'Start', 'tainacan' ); ?></button>
											</form>
											<form method="post">
												<?php wp_nonce_field( \Tainacan\Rag_Console::NONCE_ACTION ); ?>
												<input type="hidden" name="tainacan_rag_action" value="backfill_step" />
												<input type="hidden" name="space_id" value="<?php echo esc_attr( (string) $space['id'] ); ?>" />
												<input type="hidden" name="collection_id" value="<?php echo esc_attr( (string) $row['collection_id'] ); ?>" />
												<button type="submit" class="button button-small"><?php esc_html_e( 'Queue next 50', 'tainacan' ); ?></button>
											</form>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<!-- Search preview -->
	<div class="tainacan-rag-panel">
		<h2><?php esc_html_e( 'Search preview', 'tainacan' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Runs the same code the search ability runs, with the same visibility filter. The candidate count and the number of dropped candidates are the useful part: they separate "nothing matched" from "matches were hidden".', 'tainacan' ); ?>
		</p>

		<form method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( $this->get_page_slug() ); ?>" />
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="rag-query"><?php esc_html_e( 'Query', 'tainacan' ); ?></label></th>
						<td>
							<input type="search" id="rag-query" name="rag_query" class="regular-text" value="<?php echo esc_attr( $preview['query'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="rag-collection"><?php esc_html_e( 'Collection', 'tainacan' ); ?></label></th>
						<td>
							<select id="rag-collection" name="rag_collection">
								<option value="0"><?php esc_html_e( 'All collections', 'tainacan' ); ?></option>
								<?php foreach ( $collections as $collection_id => $collection_name ) : ?>
									<option value="<?php echo esc_attr( (string) $collection_id ); ?>" <?php selected( (int) $preview['collection_id'], (int) $collection_id ); ?>>
										<?php echo esc_html( $collection_name . ' (#' . (int) $collection_id . ')' ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php
								echo esc_html(
									empty( $collections )
										? __( 'No collection exists yet, so there is nothing to scope to.', 'tainacan' )
										: __( 'Scopes the preview to one collection. The space is searched either way; this only narrows the item set.', 'tainacan' )
								);
								?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Search', 'tainacan' ); ?></button></p>
		</form>

		<?php if ( $preview['ran'] ) : ?>
			<?php if ( is_wp_error( $preview['result'] ) ) : ?>
				<div class="notice notice-error inline">
					<p>
						<strong><?php echo esc_html( $preview['result']->get_error_code() ); ?></strong> —
						<?php echo esc_html( $preview['result']->get_error_message() ); ?>
					</p>
				</div>
			<?php else : ?>
				<?php $result = $preview['result']; ?>
				<p>
					<?php
					printf(
						/* translators: 1: returned items, 2: candidates scanned, 3: candidates dropped, 4: space id */
						esc_html__( '%1$d result(s), %2$d candidate(s) scanned, %3$d dropped by visibility, in space #%4$d.', 'tainacan' ),
						(int) $result['count'],
						(int) $result['candidates'],
						(int) $result['filtered_out'],
						(int) $result['space']['id']
					);
					?>
				</p>

				<?php if ( empty( $result['results'] ) ) : ?>
					<p><?php esc_html_e( 'No item survived the filter for this query.', 'tainacan' ); ?></p>
				<?php else : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Item', 'tainacan' ); ?></th>
								<th><?php esc_html_e( 'Score', 'tainacan' ); ?></th>
								<th><?php esc_html_e( 'Snippets', 'tainacan' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $result['results'] as $hit ) : ?>
								<tr>
									<td>
										<a href="<?php echo esc_url( get_edit_post_link( (int) $hit['item_id'] ) ); ?>">
											<?php echo esc_html( (string) $hit['item_id'] ); ?>
										</a>
										<br /><span class="description"><?php echo esc_html( get_the_title( (int) $hit['item_id'] ) ); ?></span>
									</td>
									<td><?php echo esc_html( (string) round( (float) $hit['score'], 4 ) ); ?></td>
									<td class="tainacan-rag-snippet">
										<?php foreach ( $hit['snippets'] as $snippet ) : ?>
											<p>
												<code><?php echo esc_html( $snippet['source'] . '#' . $snippet['chunk_index'] ); ?></code>
												<?php echo esc_html( wp_trim_words( $snippet['content'], 40 ) ); ?>
											</p>
										<?php endforeach; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
