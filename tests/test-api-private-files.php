<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Private_Files extends TAINACAN_UnitApiTestCase {

	public $test_file;

	public function test_create_item() {
		
		$orig_file       = './tests/attachment/codeispoetrywp.jpg';
		$this->test_file = '/tmp/codeispoetrywp.jpg';
		copy( $orig_file, $this->test_file );
		
		$this->collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods',
				'status'      => 'publish'
			),
			true
		);
		
		$this->item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $this->collection,
				'status'      => 'publish'
			),
			true
		);
		
		$request = new \WP_REST_Request( 'POST', '/wp/v2/media' );
		$request->set_header( 'Content-Type', 'image/jpeg' );
		$request->set_header( 'Content-Disposition', 'attachment; filename=codeispoetrywp.jpg' );
		$request->set_param( 'post', $this->item->get_id() );
		
		global $TAINACAN_UPLOADING_ATTACHMENT_TO_POST;
		$TAINACAN_UPLOADING_ATTACHMENT_TO_POST = $this->item->get_id();
		
		$request->set_body( file_get_contents( $this->test_file ) );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		
		unset($TAINACAN_UPLOADING_ATTACHMENT_TO_POST);
		
		$this->assertEquals( 201, $response->get_status() );
		$this->assertEquals( 'image', $data['media_type'] );

		$attachment = get_post( $data['id'] );
		
		$this->assertEquals( $this->item->get_id(), $attachment->post_parent );
		
		$attachment_data = wp_get_attachment_metadata($attachment->ID);
		
		$folder = 'tainacan-items/' . $this->collection->get_id() . '/' . $this->item->get_id();
		$this->assertStringStartsWith( $folder, $attachment_data['file'] );
	}
	
	function test_internal_api() {
		$orig_file       = './tests/attachment/codeispoetrywp.jpg';
		$this->test_file = '/tmp/codeispoetrywp.jpg';
		copy( $orig_file, $this->test_file );
		
		$this->collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods',
				'status'      => 'publish'
			),
			true
		);
		
		$this->item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $this->collection,
				'status'      => 'publish'
			),
			true
		);
		
		$attachment_id = \Tainacan\Media::get_instance()->insert_attachment_from_file($this->test_file, $this->item->get_id());
		$attachment_data = wp_get_attachment_metadata($attachment_id);
		$folder = 'tainacan-items/' . $this->collection->get_id() . '/' . $this->item->get_id();
		$this->assertStringStartsWith( $folder, $attachment_data['file'] );
	}

}

/**
 * Exercise the real terminating handler over HTTP, without a WordPress server.
 *
 * @group private-files-http
 */
class TAINACAN_Private_Files_HTTP_Test extends \PHPUnit\Framework\TestCase {

	private static $directory;
	private static $server;
	private static $base_url;
	private static $fixture;

	public static function setUpBeforeClass(): void {
		self::$directory = sys_get_temp_dir() . '/tainacan-range-' . uniqid();
		mkdir(self::$directory);
		self::$fixture = str_repeat(implode('', array_map('chr', range(0, 255))), 512);
		foreach (['101/_x_201', '_x_102/202', '_x_103/_x_203'] as $folder) {
			$directory = self::$directory . '/uploads/tainacan-items/' . $folder;
			mkdir($directory, 0777, true);
			file_put_contents($directory . '/fixture.mp4', self::$fixture);
			file_put_contents($directory . '/empty.mp4', '');
		}

		$router = <<<'PHP'
<?php
namespace Tainacan\Entities {
	class Item {
		public function can_read() { return ($_SERVER['HTTP_X_TEST_AUTHORIZED'] ?? '') === 'yes'; }
	}
}
namespace Tainacan\Repositories {
	class Items {
		public static function get_instance() { return new self; }
		public function fetch($item, $collection) { return new \Tainacan\Entities\Item; }
	}
}
namespace Tainacan {
	class Media {
		public static function get_instance() { return new self; }
		public function get_mime_content_type($file) { return 'video/mp4'; }
	}
}
namespace {
	define('ABSPATH', __DIR__ . '/');
	function is_404() { return true; }
	function status_header($status) { header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status . ' Test'); }
	function wp_unslash($value) { return stripslashes($value); }
	function sanitize_text_field($value) { return trim(strip_tags($value)); }
	function wp_get_upload_dir() {
		return ['baseurl' => 'http://' . $_SERVER['HTTP_HOST'] . '/uploads', 'basedir' => __DIR__ . '/uploads'];
	}
	require TAINACAN_ROOT . '/src/classes/traits/class-tainacan-singleton-instance.php';
	require TAINACAN_ROOT . '/src/classes/class-tainacan-private-files.php';
	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
	$handler = (new \ReflectionClass(\Tainacan\Private_Files::class))->newInstanceWithoutConstructor();
	$handler->dir_separator = '/';
	$handler->template_redirect();
	echo 'not found';
}
PHP;
		file_put_contents(self::$directory . '/router.php', str_replace('TAINACAN_ROOT', var_export(dirname(__DIR__), true), $router));
		$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
		self::assertNotFalse($socket, $error);
		$address = stream_socket_get_name($socket, false);
		fclose($socket);
		self::$base_url = 'http://' . $address;
		self::$server = proc_open(
			[PHP_BINARY, '-d', 'output_buffering=0', '-S', $address, self::$directory . '/router.php'],
			[0 => ['pipe', 'r'], 1 => ['file', self::$directory . '/server.log', 'a'], 2 => ['file', self::$directory . '/server.log', 'a']],
			$pipes
		);
		self::assertTrue(is_resource(self::$server));
		fclose($pipes[0]);
		for ($attempt = 0; $attempt < 100; $attempt++) {
			$connection = @stream_socket_client('tcp://' . $address, $errno, $error, 0.05);
			if ($connection) {
				fclose($connection);
				return;
			}
			usleep(20000);
		}
		$log = file_get_contents(self::$directory . '/server.log');
		self::tearDownAfterClass();
		self::fail('HTTP fixture server did not start: ' . $log);
	}

	public static function tearDownAfterClass(): void {
		if (is_resource(self::$server)) {
			proc_terminate(self::$server);
			proc_close(self::$server);
		}
		if (is_dir(self::$directory)) {
			$files = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator(self::$directory, \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ($files as $file) {
				$file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
			}
			rmdir(self::$directory);
		}
	}

	private function request($folder, $method = 'GET', $headers = [], $filename = 'fixture.mp4', $authorized = true) {
		$context = stream_context_create(['http' => [
			'method' => $method,
			'header' => implode("\r\n", array_merge(['X-Test-Authorized: ' . ($authorized ? 'yes' : 'no')], $headers)),
			'ignore_errors' => true,
			'timeout' => 5,
		]]);
		$response = fopen(self::$base_url . '/uploads/tainacan-items/' . $folder . '/' . $filename, 'rb', false, $context);
		$this->assertNotFalse($response);
		$metadata = stream_get_meta_data($response);
		$body = stream_get_contents($response);
		fclose($response);
		$this->assertNotFalse($body);
		$response_headers = [];
		foreach ($metadata['wrapper_data'] as $header) {
			if (strpos($header, ':') !== false) {
				list($name, $value) = explode(':', $header, 2);
				$response_headers[strtolower($name)] = trim($value);
			}
		}
		return [(int) explode(' ', $metadata['wrapper_data'][0])[1], $response_headers, $body];
	}

	public static function byte_ranges() {
		return [
			'first bytes' => ['bytes=0-1', 0, 2],
			'nonzero offset' => ['bytes=31-63', 31, 33],
			'last byte' => ['bytes=131071-131071', 131071, 1],
			'across chunks' => ['bytes=7-100006', 7, 100000],
			'open ended' => ['bytes=131056-', 131056, 16],
			'suffix' => ['bytes=-16', 131056, 16],
			'clamped end' => ['bytes=131056-999999', 131056, 16],
			'oversized suffix' => ['bytes=-999999', 0, 131072],
			'whole file range' => ['bytes=0-', 0, 131072],
			'case insensitive unit' => ['BYTES=0-1', 0, 2],
			'leading zeroes' => ['bytes=000000000000000000001-000000000000000000003', 1, 3],
			'huge end' => ['bytes=131056-999999999999999999999999999999', 131056, 16],
			'huge suffix' => ['bytes=-999999999999999999999999999999', 0, 131072],
		];
	}

	/** @dataProvider byte_ranges */
	public function test_byte_ranges($range, $start, $length) {
		foreach (['101/201', '102/202', '103/203'] as $folder) {
			list($status, $headers, $body) = $this->request($folder, 'GET', ['Range: ' . $range]);
			$this->assertSame(206, $status, $folder . ': ' . $range);
			$this->assertSame('bytes', ($headers['accept-ranges'] ?? null));
			$this->assertSame('video/mp4', ($headers['content-type'] ?? null));
			$this->assertSame('bytes ' . $start . '-' . ($start + $length - 1) . '/131072', ($headers['content-range'] ?? null));
			$this->assertSame((string) $length, ($headers['content-length'] ?? null));
			$this->assertSame(substr(self::$fixture, $start, $length), $body);
		}
	}

	public static function full_responses() {
		return [
			'ordinary GET' => [[]],
			'multiple ranges' => [['Range: bytes=0-1,8-9']],
			'unknown unit' => [['Range: items=0-1']],
			'malformed' => [['Range: bytes=oops']],
			'missing bounds' => [['Range: bytes=-']],
			'reversed bounds' => [['Range: bytes=9-2']],
			'huge reversed bounds' => [['Range: bytes=999999999999999999999-999999999999999999998']],
			'negative start' => [['Range: bytes=-1-3']],
			'conditional tag' => [['Range: bytes=0-1', 'If-Range: "unknown"']],
			'conditional date' => [['Range: bytes=0-1', 'If-Range: Wed, 21 Oct 2015 07:28:00 GMT']],
			'other method' => [['Range: bytes=0-1'], 'POST'],
		];
	}

	/** @dataProvider full_responses */
	public function test_full_responses($request_headers, $method = 'GET') {
		list($status, $headers, $body) = $this->request('101/201', $method, $request_headers);
		$this->assertSame(200, $status);
		$this->assertSame('bytes', ($headers['accept-ranges'] ?? null));
		$this->assertSame('video/mp4', ($headers['content-type'] ?? null));
		$this->assertSame('131072', ($headers['content-length'] ?? null));
		$this->assertArrayNotHasKey('content-range', $headers);
		$this->assertSame(self::$fixture, $body);
	}

	public function test_unsatisfiable_ranges() {
		foreach (['101/201', '102/202', '103/203'] as $folder) {
			foreach (['bytes=131072-', 'bytes=131073-131074', 'bytes=-0', 'bytes=999999999999999999999999-'] as $range) {
				list($status, $headers, $body) = $this->request($folder, 'GET', ['Range: ' . $range]);
				$this->assertSame(416, $status, $range);
				$this->assertSame('bytes */131072', ($headers['content-range'] ?? null));
				$this->assertSame('0', ($headers['content-length'] ?? null));
				$this->assertSame('', $body);
			}
		}
	}

	public function test_head_ignores_ranges_and_has_no_body() {
		foreach (['101/201', '102/202', '103/203'] as $folder) {
			foreach ([[], ['Range: bytes=0-1'], ['Range: bytes=131072-']] as $request_headers) {
				list($status, $headers, $body) = $this->request($folder, 'HEAD', $request_headers);
				$this->assertSame(200, $status);
				$this->assertSame('bytes', ($headers['accept-ranges'] ?? null));
				$this->assertSame('131072', ($headers['content-length'] ?? null));
				$this->assertArrayNotHasKey('content-range', $headers);
				$this->assertSame('', $body);
			}
		}
	}

	public function test_empty_file() {
		foreach ([[], ['Range: bytes=0-'], ['Range: bytes=-1']] as $request_headers) {
			list($status, $headers, $body) = $this->request('101/201', 'GET', $request_headers, 'empty.mp4');
			$this->assertSame($request_headers ? 416 : 200, $status);
			$this->assertSame('0', ($headers['content-length'] ?? null));
			$this->assertSame('', $body);
			if ($request_headers) {
				$this->assertSame('bytes */0', ($headers['content-range'] ?? null));
			} else {
				$this->assertArrayNotHasKey('content-range', $headers);
			}
		}
	}

	public function test_denied_requests_do_not_expose_file_content_or_size() {
		foreach (['101/201', '102/202', '103/203'] as $folder) {
			foreach (['GET', 'HEAD'] as $method) {
				foreach ([[], ['Range: bytes=0-1'], ['Range: bytes=131072-']] as $range) {
					list($status, $headers, $body) = $this->request($folder, $method, $range, 'fixture.mp4', false);
					$this->assertSame(404, $status);
					$this->assertSame($method === 'HEAD' ? '' : 'not found', $body);
					$this->assertArrayNotHasKey('content-range', $headers);
					$this->assertArrayNotHasKey('content-length', $headers);
					$this->assertArrayNotHasKey('accept-ranges', $headers);
				}
			}
		}
	}
}
