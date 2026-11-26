<?php

namespace Tainacan\Tests;

/**
 * PDF covers report why they could not be created.
 *
 * @see https://github.com/tainacan/tainacan/issues/1113
 *
 * @group media
 */
class Media_PDF_Cover extends TAINACAN_UnitTestCase {

	/**
	 * Files created by the tests.
	 *
	 * @var string[]
	 */
	private $files = array();

	/**
	 * Failures received by the tainacan-pdf-cover-failed action.
	 *
	 * @var array[]
	 */
	private $failures = array();

	/**
	 * The error_log ini value before the test.
	 *
	 * @var string|false
	 */
	private $error_log_setting;

	public function setUp(): void {
		parent::setUp();
		$this->failures = array();
		add_action( 'tainacan-pdf-cover-failed', array( $this, 'record_failure' ), 10, 3 );
		// Keeps the expected failures out of the test output.
		$this->error_log_setting = ini_set( 'error_log', '/dev/null' );
	}

	public function tearDown(): void {
		remove_action( 'tainacan-pdf-cover-failed', array( $this, 'record_failure' ), 10 );
		ini_set( 'error_log', (string) $this->error_log_setting );
		foreach ( $this->files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
		parent::tearDown();
	}

	public function record_failure( $reason, $filepath, $exception ) {
		$this->failures[] = compact( 'reason', 'filepath', 'exception' );
	}

	/**
	 * Writes a file in the temp folder.
	 *
	 * @param string $name     File name.
	 * @param string $contents File contents.
	 *
	 * @return string The file path.
	 */
	private function temp_file( $name, $contents ) {
		$path = get_temp_dir() . wp_unique_filename( get_temp_dir(), $name );
		file_put_contents( $path, $contents );
		$this->files[] = $path;
		return $path;
	}

	private function valid_pdf() {
		return \Tainacan\System_Check::get_minimal_pdf();
	}

	private function require_imagick_with_pdf() {
		if ( ! class_exists( '\Imagick' ) || empty( \Imagick::queryFormats( 'PDF' ) ) ) {
			$this->markTestSkipped( 'Imagick with PDF support is not available.' );
		}
	}

	/**
	 * Skips the test when Imagick can not render a PDF here, for example
	 * when Ghostscript is not installed, as in the GitHub runners.
	 */
	private function require_pdf_rendering() {
		$this->require_imagick_with_pdf();
		$path = $this->temp_file( 'probe.pdf', $this->valid_pdf() );
		try {
			$probe = new \Imagick();
			$probe->readImage( $path . '[0]' );
			$probe->clear();
		} catch ( \Throwable $e ) {
			$this->markTestSkipped( 'Imagick can not render PDF files here (is Ghostscript installed?): ' . $e->getMessage() );
		}
	}

	public function test_cover_of_a_valid_pdf_is_created_without_errors() {
		$this->require_pdf_rendering();
		$media = \Tainacan\Media::get_instance();

		$cover = $media->get_pdf_cover( $this->temp_file( 'valid.pdf', $this->valid_pdf() ) );

		$this->assertInstanceOf( \Imagick::class, $cover );
		$this->assertNull( $media->get_last_pdf_cover_error() );
		$this->assertEmpty( $this->failures );
	}

	public function test_a_broken_pdf_reports_the_render_error() {
		$this->require_imagick_with_pdf();
		$media = \Tainacan\Media::get_instance();
		$path  = $this->temp_file( 'broken.pdf', "%PDF-1.4\nthis is not a real pdf body\n" );

		$this->assertNull( $media->get_pdf_cover( $path ) );

		$this->assertStringStartsWith( 'Imagick could not render the first page', $media->get_last_pdf_cover_error() );
		$this->assertCount( 1, $this->failures );
		$this->assertSame( $path, $this->failures[0]['filepath'] );
		$this->assertInstanceOf( \Throwable::class, $this->failures[0]['exception'] );

		// The fatal error guard is turned off again, so a later shutdown does not throw.
		$guard = new \ReflectionProperty( \Tainacan\Media::class, 'THROW_EXCPTION_ON_FATAL_ERROR' );
		$guard->setAccessible( true );
		$this->assertFalse( $guard->getValue( $media ) );
	}

	public function test_a_missing_pdf_is_reported_but_other_missing_files_are_not() {
		$media = \Tainacan\Media::get_instance();

		$this->assertNull( $media->get_pdf_cover( get_temp_dir() . 'tainacan-missing-document.pdf' ) );
		$this->assertSame( 'The file could not be read.', $media->get_last_pdf_cover_error() );
		$this->assertCount( 1, $this->failures );

		// A missing audio file never had a cover to create.
		$this->assertNull( $media->get_pdf_cover( get_temp_dir() . 'tainacan-missing-document.mp3' ) );
		$this->assertNull( $media->get_last_pdf_cover_error() );
		$this->assertCount( 1, $this->failures );
	}

	public function test_a_file_that_is_not_a_pdf_is_not_a_failure() {
		$media = \Tainacan\Media::get_instance();

		$this->assertNull( $media->get_pdf_cover( $this->temp_file( 'notes.txt', 'Just some text.' ) ) );

		$this->assertNull( $media->get_last_pdf_cover_error() );
		$this->assertEmpty( $this->failures );
	}

	public function test_the_extract_filter_still_replaces_the_cover() {
		$media = \Tainacan\Media::get_instance();
		$blob  = function () {
			return 'blob from a filter';
		};
		add_filter( 'tainacan-extract-pdf-cover', $blob );

		$this->assertSame( 'blob from a filter', $media->get_pdf_cover( get_temp_dir() . 'tainacan-missing-document.pdf' ) );
		$this->assertEmpty( $this->failures );

		remove_filter( 'tainacan-extract-pdf-cover', $blob );
	}

	public function test_system_check_renders_a_pdf_cover_now() {
		$this->require_pdf_rendering();

		$status = \Tainacan\System_Check::get_instance()->get_pdf_cover_status();

		$this->assertSame( 'good', $status['status'], $status['message'] );
	}

	public function test_system_check_shows_why_pdf_covers_fail() {
		if ( ! class_exists( '\Imagick' ) ) {
			$this->markTestSkipped( 'Imagick is not available.' );
		}

		// Makes the next render fail the way an outdated Ghostscript in memory would.
		$fail = function ( $blob, $filepath ) {
			file_put_contents( $filepath, "%PDF-1.4\nbroken\n" );
			return $blob;
		};
		add_filter( 'tainacan-extract-pdf-cover', $fail, 10, 2 );

		$status = \Tainacan\System_Check::get_instance()->get_pdf_cover_status();

		remove_filter( 'tainacan-extract-pdf-cover', $fail, 10 );

		$this->assertSame( 'error', $status['status'] );
		$this->assertStringContainsString( 'Covers for PDF documents are not being created', $status['message'] );
		$this->assertStringContainsString( 'stop and start PHP', $status['message'] );
	}
}
