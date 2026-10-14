<?php

namespace Tainacan\Tests;

/** @group exporter */
class TAINACAN_Logs_Exporter extends TAINACAN_UnitTestCase {

	private $paths = [];

	public function tearDown(): void {
		foreach ( $this->paths as $path ) {
			if ( file_exists( $path ) ) {
				unlink( $path );
			}
		}
		parent::tearDown();
	}

	public function test_csv_preserves_quoted_multiline_and_structured_values() {
		$exporter = new \Tainacan\Exporter\Logs_Exporter();
		$values = [ "Descrição, com \"aspas\"\ne outra linha", [ 'name' => 'Ação' ], '=1+1', false ];
		$row = str_getcsv( $exporter->csv_row( $values ), ',', '"', '' );
		$this->assertSame( $values[0], $row[0] );
		$this->assertSame( $values[1], json_decode( $row[1], true ) );
		$this->assertSame( "'=1+1", $row[2] );
		$this->assertSame( 'false', $row[3] );
	}

	public function test_log_permission_is_required_even_for_a_tainacan_manager() {
		$user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		$user = new \WP_User( $user_id );
		$user->add_cap( 'manage_tainacan' );
		wp_set_current_user( $user_id );

		$deny_logs = function ( $allcaps, $caps, $args, $checked_user ) use ( $user_id ) {
			if (
				(int) $checked_user->ID === (int) $user_id
				&& $args[0] === 'tnc_rep_read_logs'
			) {
				$allcaps['tnc_rep_read_logs'] = false;
			}
			return $allcaps;
		};

		add_filter( 'user_has_cap', $deny_logs, PHP_INT_MAX, 4 );

		try {
			$this->assertTrue( current_user_can( 'manage_tainacan' ) );
			$this->assertFalse( current_user_can( 'tnc_rep_read_logs' ) );

			$exporter = new \Tainacan\Exporter\Logs_Exporter();
			$this->assertFalse( $exporter->export_logs() );
			$this->assertTrue( $exporter->get_abort() );
			$this->assertNull( $exporter->get_transient( 'logs_export_state' ) );
		} finally {
			remove_filter( 'user_has_cap', $deny_logs, PHP_INT_MAX );
		}
	}

	public function test_empty_history_exports_only_headers() {
		$administrator = self::factory()->user->create(
			[ 'role' => 'administrator' ]
		);
		wp_set_current_user( $administrator );

		$user = new \WP_User( $administrator );
		$user->add_cap( 'manage_tainacan' );
		$user->add_cap( 'tnc_rep_read_logs' );

		$logs = \Tainacan\Repositories\Logs::get_instance()->fetch(
			[ 'posts_per_page' => 1 ],
			'OBJECT'
		);
		$this->assertEmpty(
			$logs,
			'Este teste precisa iniciar sem atividades no banco de testes.'
		);

		$exporter = \Tainacan\Exporter_Handler::get_instance()
			->initialize_exporter( 'logscsv' );

		$iterations = 0;
		do {
			$result = $exporter->run( 123 );
			$this->assertLessThan( 20, ++$iterations );
		} while ( $result !== false );

		$this->assertFalse( $exporter->get_abort() );

		$data = $exporter->_to_Array( true );
		$file = $data['output_files'][
			\Tainacan\Exporter\Logs_Exporter::FILE_KEY
		];
		$this->paths[] = $file['filename'];

		$this->assertFileExists( $file['filename'] );

		$stream = fopen( $file['filename'], 'r' );
		$this->assertNotFalse( $stream );

		try {
			$this->assertSame(
				[
					'ID', 'date', 'user_id', 'user_name', 'action',
					'collection_id', 'item_id', 'object_type', 'object_id',
					'title', 'description', 'old_value', 'new_value',
				],
				fgetcsv( $stream, 0, ',', '"', '' )
			);

			$this->assertFalse(
				fgetcsv( $stream, 0, ',', '"', '' ),
				'O histórico vazio deve gerar somente o cabeçalho.'
			);
		} finally {
			fclose( $stream );
		}
	}

	public function test_export_preserves_log_with_missing_user() {
		$administrator = self::factory()->user->create(
			[ 'role' => 'administrator' ]
		);
		wp_set_current_user( $administrator );

		$user = new \WP_User( $administrator );
		$user->add_cap( 'manage_tainacan' );
		$user->add_cap( 'tnc_rep_read_logs' );

		$author_id = self::factory()->user->create(
			[
				'role' => 'subscriber',
				'display_name' => 'Autor removido',
			]
		);

		$log = $this->tainacan_entity_factory->create_entity(
			'log',
			[
				'title' => 'Atividade com autor removido',
				'user_id' => $author_id,
			],
			true
		);

		$this->assertSame( $author_id, (int) $log->get_user_id() );
		$this->assertSame( 'Autor removido', $log->get_user_name() );

		// Simula um usuário ausente sem apagar ou reatribuir suas atividades.
		// Estas operações ficam exclusivamente no banco isolado de testes.
		global $wpdb;
		$this->assertSame(
			1,
			$wpdb->delete( $wpdb->users, [ 'ID' => $author_id ], [ '%d' ] )
		);
		$wpdb->delete(
			$wpdb->usermeta,
			[ 'user_id' => $author_id ],
			[ '%d' ]
		);
		clean_user_cache( $author_id );

		$this->assertFalse( get_userdata( $author_id ) );

		$exporter = \Tainacan\Exporter_Handler::get_instance()
			->initialize_exporter( 'logscsv' );

		$iterations = 0;
		do {
			$result = $exporter->run( 123 );
			$this->assertLessThan( 20, ++$iterations );
		} while ( $result !== false );

		$this->assertFalse( $exporter->get_abort() );

		$data = $exporter->_to_Array( true );
		$path = $data['output_files'][
			\Tainacan\Exporter\Logs_Exporter::FILE_KEY
		]['filename'];
		$this->paths[] = $path;

		$stream = fopen( $path, 'r' );
		$this->assertNotFalse( $stream );
		$matching_rows = [];

		try {
			$header = fgetcsv( $stream, 0, ',', '"', '' );
			$this->assertCount( 13, $header );

			while ( ( $row = fgetcsv( $stream, 0, ',', '"', '' ) ) !== false ) {
				if ( $row[0] === (string) $log->get_id() ) {
					$matching_rows[] = array_combine( $header, $row );
				}
			}
		} finally {
			fclose( $stream );
		}

		$this->assertCount( 1, $matching_rows );
		$this->assertSame(
			(string) $author_id,
			$matching_rows[0]['user_id']
		);
		$this->assertSame( '', $matching_rows[0]['user_name'] );
		$this->assertSame(
			'Atividade com autor removido',
			$matching_rows[0]['title']
		);
	}
	public function test_completion_prepares_expiration_and_email_link() {
		$administrator = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $administrator );
		$user = new \WP_User( $administrator );
		$user->add_cap( 'manage_tainacan' );
		$user->add_cap( 'tnc_rep_read_logs' );
		$exporter = \Tainacan\Exporter_Handler::get_instance()->initialize_exporter( 'logscsv' );
		$before = time();
		$iterations = 0;
		do {
			$result = $exporter->run( 123 );
			$this->assertLessThan( 20, ++$iterations );
		} while ( $result !== false );
		$data = $exporter->_to_Array( true );
		$file = $data['output_files'][\Tainacan\Exporter\Logs_Exporter::FILE_KEY];
		$this->paths[] = $file['filename'];
		$period = \Tainacan\Exporter_Files::get_instance()->get_expiration_period();
		$this->assertGreaterThanOrEqual( $before + $period, $file['expires_at'] );
		$this->assertLessThanOrEqual( time() + $period, $file['expires_at'] );
		$this->assertStringContainsString( 'process_id=123', $file['url'] );
		$this->assertStringContainsString( '#/processes', $exporter->get_output( true ) );
		$this->assertStringNotContainsString( '[nonce]', $exporter->get_output( true ) );
	}
	public function test_batches_resume_without_including_later_logs() {
		$administrator = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $administrator );
		$user = new \WP_User( $administrator );
		$user->add_cap( 'manage_tainacan' );
		$user->add_cap( 'tnc_rep_read_logs' );
		$expected = [];
		for ( $i = 0; $i < 105; $i++ ) {
			$log = $this->tainacan_entity_factory->create_entity( 'log', [ 'title' => 'CSV log ' . $i ], true );
			$expected[] = (string) $log->get_id();
		}
		$exporter = \Tainacan\Exporter_Handler::get_instance()->initialize_exporter( 'logscsv' );
		$this->assertInstanceOf( \Tainacan\Exporter\Logs_Exporter::class, $exporter );
		$this->assertGreaterThan( 0, $exporter->run() );
		$data = $exporter->_to_Array( true );
		$path = $data['output_files'][\Tainacan\Exporter\Logs_Exporter::FILE_KEY]['filename'];
		$this->paths[] = $path;
		file_put_contents( $path, "uncommitted retry tail\n", FILE_APPEND );
		$late = $this->tainacan_entity_factory->create_entity( 'log', [ 'title' => 'Created after export started' ], true );
		$iterations = 0;
		do {
			$exporter = new \Tainacan\Exporter\Logs_Exporter( $data );
			$result = $exporter->run();
			$data = $exporter->_to_Array( true );
			$this->assertLessThan( 20, ++$iterations );
		} while ( $result !== false );
		$stream = fopen( $path, 'r' );
		$header = fgetcsv( $stream, 0, ',', '"', '' );
		$this->assertCount( 13, $header );
		$ids = [];
		while ( ( $row = fgetcsv( $stream, 0, ',', '"', '' ) ) !== false ) {
			$ids[] = $row[0];
		}
		fclose( $stream );
		$this->assertSame( $ids, array_values( array_unique( $ids ) ) );
		foreach ( $expected as $id ) {
			$this->assertContains( $id, $ids );
		}
		$this->assertNotContains( (string) $late->get_id(), $ids );
		$this->assertNotContains( 'uncommitted retry tail', $ids );
	}
}
