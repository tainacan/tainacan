# How to create an Exposer

Exposers are classes that implement a new Exposer that can be used by Tainacan to expose the content of a repository via API.

In order to create a new exposer you basically have to create an Exposer class and register it.

## Creating an Exposer class

Create a class that extends `\Tainacan\Exposers\Exposer`.

```php 
<?php 

class MyExposer extends \Tainacan\Exposers\Exposer {
	
}

```

In this class you will have to set up some attributes and methods:

### Attributes

#### Slug

**String $slug**

A URL friendly version of the Exposer name, to be used as a parameter to the API request informing you want to get the data using this exposer.

```php 
<?php 

class MyExposer extends \Tainacan\Exposers\Exposer {
	
	public $slug = 'my-exposer';
	
}

```

#### Supported Mapping Standard

**Array or true $mappers**

A list of mapping standards that is exposer supports. This means that whenever someone requests to receive data via this exposer, he/she will also be able to choose which mapping standard they want the content to be served. If set to `true` the exposer will accept all mapping standards.

```php 
<?php 

class MyExposer extends \Tainacan\Exposers\Exposer {
	
	public $slug = 'my-exposer';
	public $mappers = true;
	
}

```

or 

```php 
<?php 

class MyExposer extends \Tainacan\Exposers\Exposer {
	
	public $slug = 'my-exposer';
	public $mappers = ['dublin-core']; // indicates that this exposer will only serve data mapped to dublin core mapper
	
}
```

#### Accept no Mapping Standards 

**Bool $accept_no_mapper**

Indicates whether this exposer accepts to serve data in its native form, without any mapping standards.

```php 
<?php 

class MyExposer extends \Tainacan\Exposers\Exposer {
	
	public $slug = 'my-exposer';
	public $mappers = ['dublin-core']; // indicates that this exposer will only serve data mapped to dublin core mapper
	public $accept_no_mapper = true; 
}
```

### Methods

Now that you have declared the basic attributes of your Exposer, there are two methods you must implement.


#### __construct()

In this method you must call `set_name()` and `set_description()` to identify your exposer.

```php 
<?php 

class MyExposer extends \Tainacan\Exposers\Exposer {
	
	public $slug = 'my-exposer';
	public $mappers = ['dublin-core']; // indicates that this exposer will only serve data mapped to dublin core mapper
	public $accept_no_mapper = true; 
	
	public function __construct() {
		$this->set_name( __('My Exposer', 'my-test-plugin-namespace') );
		$this->set_description( __('This exposer server the data in a very different way', 'my-test-plugin-namespace') );
	}
	
}
```

**Note**: The reason Name and Description are declared this way, and not as attributes, is to allow you to localize your strings to different languages. Please refer to the WordPress documentation to learn how to internationalize your plugin.


#### rest_request_after_callbacks()

Now, this is where all the magic happens!

This method will be called right before the API returns the data to the client.

It will give you all the items it received, in the way they were about to be served in the default JSON format, and allow you to transform it.

It receives 3 parameters:

* $response: an instance of the `\WP_REST_Response` object 
* $handler: an instance of the `\WP_REST_Server` object 
* $request: an instance of the `\WP_REST_Request` object 

This method has to return the modified version of the `\WP_REST_Response` object.

```php 
<?php 

class MyExposer extends \Tainacan\Exposers\Exposer {
	
	public $slug = 'my-exposer';
	public $mappers = ['dublin-core']; // indicates that this exposer will only serve data mapped to dublin core mapper
	public $accept_no_mapper = true; 
	
	public function __construct() {
		$this->set_name( __('My Exposer', 'my-test-plugin-namespace') );
		$this->set_description( __('This exposer server the data in a very different way', 'my-test-plugin-namespace') );
	}
	
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		
		// Set the headers to another content type, if applicable
		$response->set_headers( ['Content-Type: text/plain; charset=' . get_option( 'blog_charset' )] );
		
		$items = $response->get_data();
		
		// Transform the items somehow ...
		// ...
		
		$response->set_data($items);
		
		return $response;
	}
	
}
```


### Registering a new exposer

To register a new exposer, the action need to be added to the `tainacan-register-exposers` hook, like:
```php 
<?php
	function registerMyExposer($exposers) {
		$exposers->register_exposer('MyExposer');
	}
	add_action('tainacan-register-exposers', 'registerMyExposer');
```

### Full Example

This is a full example of a plugin that implements a simple text exposer

```php 
<?php
/*
Plugin Name: Tainacan TXT Exposer
Description: This is a sample exposer class
*/

function myNewExposer($exposers) {
	
	class TxtExposer extends \Tainacan\Exposers\Exposer {
		
		public $slug = 'txt'; // type slug for url safe
		public $name = 'TXT';
		protected $mappers = true;
		public $accept_no_mapper = true;
		
		private $identation = '';
		
		function __construct() {
			$this->set_name( 'TXT' );
			$this->set_description( __('A simple TXT table', 'my-test-plugin-namespace') );
		}
		
		/**
		* 
		* {@inheritDoc}
		* @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
		*/
		public function rest_request_after_callbacks( $response, $handler, $request ) {
			$response->set_headers( ['Content-Type: text/plain; charset=' . get_option( 'blog_charset' )] );
			
			$txt = $this->array_to_txt($response->get_data(), false);
			
			$response->set_data($txt);
			return $response;
		}
		
		/**
		* Convert Array to Txt
		* @param array $data
		* @param string $txt
		* @return string
		*/
		protected function array_to_txt( $data, $addlevel = true ) {
			
			$return = '';
			
			if ($addlevel) {
				$this->identation .= '    ';
			}
			
			foreach( $data as $key => $value ) {
				
				
				$return .= $this->identation . $key . ': ';
				if (is_array($value)) {
					$return .= "\n" . $this->array_to_txt($value);
				} else {
					$return .= $value . "\n";
				}
				
			}
			
			if ($addlevel) {
				$this->identation = substr($this->identation, 0,  strlen($this->identation) - 4  );
			}
			
			return $return;
		}
	}
	
	$exposers->register_exposer('TxtExposer');
}
add_action('tainacan-register-exposers', 'myNewExposer');

```
