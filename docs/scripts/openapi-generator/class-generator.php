<?php

namespace OpenAPIGenerator;

class Generator {

	protected $namespace;
	protected $routes;
	protected $components;

	public $extractCommonTypes = false;

	public function __construct( $namespace, $routes, $extractCommonTypes ) {
		$this->namespace          = $namespace;
		$this->routes             = $routes;
		$this->extractCommonTypes = $extractCommonTypes;

		$this->components = [
			'schemas' => [
				'WPError' => [
					'type'       => 'object',
					'properties' => [
						'code' => [
							'description' => esc_html__( 'The error code', 'document-generator-for-openapi' ),
							'type'        => 'string',
						],
						'message' => [
							'description' => esc_html__( 'The description of the error', 'document-generator-for-openapi' ),
							'type'        => 'string',
						],
						'data' => [
							'description' => esc_html__( 'Data of the error', 'document-generator-for-openapi' ),
							'type'        => 'any',
						],
						'additional_data' => [
							'description' => esc_html__( 'Additional data of the error', 'document-generator-for-openapi' ),
							'type'        => 'any',
						],
						'additional_errors' => [
							'description' => esc_html__( 'More errors', 'document-generator-for-openapi' ),
							'type'        => 'array',
							'items'       => [
								'$ref' => '#/components/schemas/WPError',
							],
						],
					],
				],
			],
		];
	}

	public function generateDocument() {
		return apply_filters( 'openapi_generator_v3_1', $this->generateRoot(), $this );
	}

	public function generateRoot() {
		$result = [
			'openapi'           => '3.1.0',
			'info'              => $this->generateInfo(),
			'jsonSchemaDialect' => 'http://json-schema.org/draft-04/schema#',
			'servers'           => $this->generateServers(),
			'paths'             => $this->generatePaths(),
			'security'          => $this->generateSecurity(),
		];

		if ( ! empty( $this->components ) ) {
			$result['components'] = $this->components;
		}

		return $result;
	}

	public function generateInfo() {
		return [
			'title'   => $this->namespace,
			'summary' => esc_html(
				sprintf(
					esc_html__(
						'Generated OpenAPI document of the namespace %s on %s.',
						'document-generator-for-openapi'
					),
					$this->namespace,
					get_option( 'blogname' )
				)
			),
			'version' => '1',
		];
	}

	public function generateServers() {
		return [
			[ 'url' => rest_url( $this->namespace ) ],
		];
	}

	public function generatePaths() {

		$result = [];

		foreach ( $this->routes as $url => $spec ) {
			// remove namespace portion from url
			$url = preg_replace( '#' . $this->namespace . '/?#', '', $url );

			$substitutions = $this->getSubstitutions( $url );

			// replace all regex substituions with OpenAPI substitutions
			$url = preg_replace( '/\(\?P\<(.*?)\>.*?\)(\/|$)/', '{$1}$2', $url );

			$name = '';

			// try to get a fallback entity name for this path by getting the last part of the url
			// TODO we need to ignore all substitution names
			$matches = [];
			if ( preg_match_all( '/\/(.*?)(\/|$)/', $url, $matches, PREG_SET_ORDER ) ) {
				$name = $matches[ count( $matches ) - 1 ][1];
			}

			// it is better to use the name of the defined WP schema result if it exists
			if ( isset( $spec['schema'] ) && isset( $spec['schema']['title'] ) ) {
				$name = $spec['schema']['title'];
			}

			$result[ $url ] = $this->generatePathItem( $name, $spec, $substitutions );
		}

		return $result;
	}

	public function getSubstitutions( $url ) {
		// create OpenAPI style substitutions by replacing regex named capture grouping used in WordPress
		// url/<?P<paramname>[regex]+)/further/url
		// to
		// url/{paramname}/further/url

		$substitutions = [];
		$found         = preg_match_all( '/\(\?P\<(.*?)\>(.*?)\)(\/|$)/', $url, $matches, PREG_SET_ORDER );
		if ( $found && $found > 0 ) {
			// for each found substituion, store the given regex
			foreach ( $matches as $foundSubstitution ) {
				$substitutions[ $foundSubstitution[1] ] = $foundSubstitution[2];
			}
		}

		return $substitutions;
	}

	public function generatePathItem( $name, $spec, $substitutions ) {

		$result = [];

		foreach ( $spec['endpoints'] as $endpoint ) {
			$parameters             = [];
			$contentProperties        = [];
			$substitutionParameters   = [];

			// create parameters for all the following methods of this endpoint
			// this means, yes, currently those parameters are duplicated in the OpenAPI document
			// because we don't use refs yet.
			foreach ( $endpoint['args'] as $argumentName => $argument ) {
				$paramSchema  = $this->generateParameterObject( $argumentName, $argument, $substitutions );
				$parameters[] = $paramSchema;

				// if this is not a path parameter, add it to the content schema
				if ( ! array_key_exists( $argumentName, $substitutions ) ) {
					$contentProperties[ $argumentName ] = $this->generateSchemaObject(
						$argument,
						[
							'currentKey' => $argumentName,
						]
					);
				} else {
					$substitutionParameters[] = $paramSchema;
				}
			}
			foreach ( $endpoint['methods'] as $methodName ) {

				$method = [
					'responses' => [
						'200' => [ 'description' => 'OK' ],
						'400' => [
							'description' => 'Bad Request',
							'content'     => [
								'application/json' => [
									'schema' => [
										'$ref' => '#/components/schemas/WPError',
									],
								],
							],
						],
						'404' => [ 'description' => 'Not Found' ],
					],
				];

				if ( isset( $spec['schema']['tags'] ) ) {
					$method['tags'] = $spec['schema']['tags'];
				} else {
					$method['tags'] = [ 'root' ];
				}

				// for all methods with possible request body content,
				// we move the parameters to its requestbody
				// all substitutions parameters should be kept in the query parameter list
				if ( in_array( strtolower( $methodName ), [ 'post', 'put', 'patch' ], true ) ) {

					if ( count( $contentProperties ) ) {
						$method['requestBody'] = [
							'required' => true,
							'content'  => [
								'application/json' => [
									'schema' => [
										'type'       => 'object',
										'title'      => $name,
										'properties' => $contentProperties,
									],
								],
							],
						];
					}

					if ( count( $substitutionParameters ) ) {
						$method['parameters'] = $substitutionParameters;
					}
				} else {
					$method['parameters'] = $parameters;
				}

				// if a schema is defined for the reponse of the current route add it.
				if ( isset( $spec['schema'] ) && ! empty( $spec['schema'] ) ) {
					$method['responses']['200']['content'] = $this->generateResponseSchema( $spec['schema'] );
				}

				// create operation object for path item with the specific method
				$result[ strtolower( $methodName ) ] = $method;
			}
		}

		return $result;
	}

	public function generateParameterObject( $argumentName, $argument, $substitutions ) {
		$in = array_key_exists( $argumentName, $substitutions ) ? 'path' : 'query';

		$result = [
			'name'        => $argumentName,
			'in'          => $in,
			'description' => isset( $argument['description'] ) ? $argument['description'] : '',
			'required'    => $in === 'path' ? true : ( isset( $argument['required'] ) ? $argument['required'] : false ),
			'schema'      => $this->generateSchemaObject(
				$argument,
				[ 'currentKey' => $argumentName ]
			),
		];

		return $result;
	}

	public function generateResponseSchema( $schema ) {

		$schemaName = $schema['title'];

		// add schema to the current schema pool to add it to the components part of the document later on.
		$this->components['schemas'][ $schemaName ] = $this->generateSchemaObject(
			$schema,
			[
				'currentKey' => null,
			]
		);

		return [
			'application/json' => [
				'schema' => [
					'$ref' => '#/components/schemas/' . $schemaName,
				],
			],
		];
	}

	public function generateSchemaObject( $schemaObject, $context ) {

		if ( isset( $schemaObject['type'] ) ) {
			if ( is_array( $schemaObject['type'] ) &&
				isset( $schemaObject['oneOf'] ) &&
				is_array( $schemaObject['oneOf'] ) ) {

				$result['oneOf'] = [];

				foreach ( $schemaObject['oneOf'] as $type ) {
					$result['oneOf'][] = $this->generateSchemaObject(
						$type,
						array_merge( $context, [ 'currentKey' => null ] )
					);
				}
			} else {
				$result['type'] = $schemaObject['type'];

				if ( $schemaObject['type'] === 'object' && isset( $schemaObject['properties'] ) ) {
					$requiredProperties = [];

					foreach ( $schemaObject['properties'] as $key => $parameter ) {
						$result['properties'][ $key ] = $this->generateSchemaObject(
							$parameter,
							array_merge( $context, [ 'currentKey' => $key ] )
						);

						if ( isset( $schemaObject['properties'][ $key ]['required'] ) &&
							$schemaObject['properties'][ $key ]['required'] === true ) {
							$requiredProperties[] = $key;
						}
					}

					if ( ! empty( $requiredProperties ) ) {
						$result['required'] = $requiredProperties;
					}
				}

				if ( $schemaObject['type'] === 'array' && isset( $schemaObject['items'] ) ) {
					// TODO Is it safe to always pass context with same currentKey of parent?
					$result['items'] = $this->generateSchemaObject( $schemaObject['items'], $context );
				}
			}
		} else {
			$result = [ 'type' => 'string' ];
		}

		if ( isset( $schemaObject['format'] ) ) {
			$result['format'] = $schemaObject['format'];
		}

		if ( isset( $schemaObject['description'] ) ) {
			$result['description'] = $schemaObject['description'];
		}

		if ( isset( $schemaObject['enum'] ) ) {
			$result['enum'] = array_values( $schemaObject['enum'] );
		}

		if ( $this->extractCommonTypes &&
			( ( isset( $result['type'] ) && $result['type'] === 'object' && $context['currentKey'] ) ||
				( isset( $result['enum'] ) && is_array( $result['enum'] ) ) ) ) {

			$uriKey = $context['currentKey'];

			// TODO Improve collission handling
			$i = 1;
			while ( isset( $this->components['schemas'][ $uriKey ] ) &&
				$this->components['schemas'][ $uriKey ] !== $result ) {
				$uriKey = $context['currentKey'] . '_' . $i++;
			}

			$uri = '#/components/schemas/' . $uriKey;

			$this->components['schemas'][ $uriKey ] = $result;

			return [
				'$ref' => $uri,
			];
		}

		return $result;
	}

	public function generateSecurity() {
		return [];
	}
}
