# Mapping Standards

Mapping Standards are declarations of standards of metadata. Once they are available and activated in your repository, you can map the metadata of your collections to them.

## Structure

A Mapping Standard has the following attributes.

### Name

The name of the Mapping Standard.

### Metadata

A list of metadata, terms or attributes that this mapping have. These are the element you will be able to map your Collection's Metadata.

Each metadatum has the following attributes:

* Name - The metadatum name, that refers to the name of the attribute in the origin vocabulary or ontology (e.g. title)
* Label - The human readable name
* URI - The URI of this term/attribute in the origin Ontoloy/Vocabulary


### Allow additional custom metadata

Boolen indicating wether this mapping allows additional custom metadata to be added.

### Context URL / Vocab URL

The URL of the Ontology or vocabulary. For example `http://schema.org` or  `http://dublincore.org/documents/dcmi-terms/`

### Type

The Class of the ontology that this mapping refers to. For example `CreativeWork`, which is a class of Schema.org.

## Examples

```
{
	'name': 'Dublin Core',
	'metadata': {
		{
			'name': 'title',
			'label': 'Title',
			'URI': 'http://purl.org/dc/terms/title'
		},
		{
			'name': 'publisher',
			'label': 'Publisher',
			'URI': 'http://purl.org/dc/terms/publisher'
		},
		... And so on...
	},
	'allow_extra_fields': true,
	'context_url': 'http://dublincore.org/documents/dcmi-terms/'
}
```

```
{
	'name': 'Schema.org Creative Works',
	'metadata': {
		{
			'name': 'name',
			'label': 'Name',
			'URI': 'http://schema.org/name'
		},
		{
			'name': 'alternativeHeadline',
			'label': 'Alternative Headline',
			'URI': 'http://schema.org/alternativeHeadline'
		},
		... And so on...
	},
	'allow_extra_fields': false,
	'context_url': 'http://schema.org',
	'type': 'CreativeWork'
}
```
