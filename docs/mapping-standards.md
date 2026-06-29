# Mapping Standards

Mapping Standards are declarations of standards of metadata. Once they are available and activated in your repository, you can map the metadata of your collections to them.

## Structure

A Mapping Standard has the following attributes.

### Name

```php
String $name
```

The name of the Mapping Standard.

### Metadata

```php
Array or false $metadata
```

A list of metadata, terms or attributes that this mapping has. These are the element you will be able to map your Collection's Metadata.

Each metadatum has the following attributes:

- `slug` - The metadatum name, that refers to the name of the attribute in the origin vocabulary or ontology (e.g. title)
- `label` - The human-readable name
- `URI` - The URI of this term/attribute in the origin Ontology/Vocabulary
- `metadata_type` - The preferred type for the metadatum

Array of:

```php
['slug'] => [
	'URI' => 'http://...',
	'label' => 'Label',
	'metadata_type' => 'date',
]
```

### Allow additional custom metadata

    Boolean $allow_extra_metadata

Boolean indicating whether this mapping allows additional custom metadata to be added.

### Context URL / Vocab URL

    String $context_url

The URL of the Ontology or vocabulary. For example `http://schema.org` or `http://dublincore.org/documents/dcmi-terms/`

### Type

    String $type

The Class of the ontology that this mapping refers to. For example `CreativeWork`, which is a class of Schema.org, if applied

### Header

    String $header

The header to be append to API answer, like for Dublin Core, if we need to add RDF to xml header when using Dublin Core as mapper, so:

```php
public $header = '<?xml version="1.0"?><!DOCTYPE rdf:RDF SYSTEM "http://dublincore.org/2000/12/01-dcmes-xml-dtd.dtd"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" ></rdf:RDF>';
```

### Prefix

    String $prefix

The optional prefix for the key labels, like `dc:` for Dublin Core in XML exposing.

### Registering a new mapper

For register a new mapper, the action needs to be added to `tainacan-register-mappers` hook, like:

```php
	function myNewMapper($exposers) {
		$exposers->register_mapper('Tainacan\Exposers\Mappers\NewMapper');
	}
	add_action('tainacan-register-mappers', 'myNewMapper');
```

## Examples

```php
namespace Tainacan\Exposers\Mappers;

/**
 * Support Dublin Core Mapping
 * http://purl.org/dc/elements/1.1/
 *
 */
class Dublin_Core extends Mapper {
	public $slug = 'dublin-core';
	public $name = 'Dublin Core';
	public $allow_extra_metadata = true;
	public $context_url = 'http://dublincore.org/documents/dcmi-terms/';
	public $header = '<?xml version="1.0"?><!DOCTYPE rdf:RDF SYSTEM "http://dublincore.org/2000/12/01-dcmes-xml-dtd.dtd"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" ></rdf:RDF>';
	public $prefix = 'dc:';
	public $metadata = [
		'contributor' => [
			'URI' => 'http://purl.org/dc/elements/1.1/contributor',
			'label' => 'Contributor'
		],
		'coverage' => [
			'URI' => 'http://purl.org/dc/elements/1.1/coverage',
			'label' => 'Coverage'
		],
		'creator' => [
			'URI' => 'http://purl.org/dc/elements/1.1/creator',
			'label' => 'Creator'
		],
	...
```

```php
namespace Tainacan\Exposers\Mappers;

class CreativeWorks extends Mapper {
	public $name = 'Schema.org Creative Works';
	public $slug = 'creative-works';
	public $metadata = [
		'name' => [
			'label': 'Name',
			'URI': 'http://schema.org/name'
		],
		'alternativeHeadline' => [
			'label': 'Alternative Headline',
			'URI': 'http://schema.org/alternativeHeadline'
		],
		... And so on...
	],
	public $allow_extra_fields = false;
	public $context_url = 'http://schema.org';
	public $type = 'CreativeWork';
}
```
