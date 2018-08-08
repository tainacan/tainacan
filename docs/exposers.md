# Exposers

Exposers are declarations and methods that expose the items of your repository in a certain way.

## Structure

### Name

String $name

The name of the Exposer.

### Slug

String $slug

A URL friendly version of the Exposer name, to be used as a parameter to the API request informing you want to get the data using this exposer.


### Supported Mapping Standard

Array or true $mappers

A list of mapping standards that is exposer supports. This means that whenever someone makes a request to receive that via this exposer, he/she will also be able to choose in which mapping standar they want the content to be served, or true for serve all mappers.


### Methods

Every exposer have to implement PHP methods that will build the API response and have to be parent of Tainacan\Exposers\Types\Type

####callback rest response

\WP_REST_Response rest_request_after_callbacks will receve a array of elements to be exposed, and api request, server and reponse.

	/**
		 * Change response after api callbacks
		 * @param \WP_REST_Response $response
		 * @param \WP_REST_Server $handler
		 * @param \WP_REST_Request $request
		 * @return \WP_REST_Response
		 */
		public function rest_request_after_callbacks( $response, $handler, $request ); 

Optionally, an exposer can also implement a method to print data in the `HEAD` section of the HTML when visiting an item page. For example, JSON-LD exposer can add a JSON-LD object to the head of the page of every item in your collection.