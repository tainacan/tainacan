# Terms

A term is a WordPress term. It belongs to the WordPress taxonomy created for a Tainacan taxonomy, `tnc_tax_{id}`. It is not a post, and `fetch()` does not take `WP_Query` arguments. The repository is `tainacan_terms()`. The vocabulary itself is a [taxonomy](repository-taxonomies.md).

## Create

`set_taxonomy()` receives the WordPress taxonomy slug from `$taxonomy->get_db_identifier()`. `set_name()` is the term name. `set_parent()` receives the parent term ID when the taxonomy is hierarchical.

```php
$term = new \Tainacan\Entities\Term();
$term->set_name( 'Drama' );
$term->set_taxonomy( $taxonomy->get_db_identifier() );

if ( $term->validate() ) {
	$term = tainacan_terms()->insert( $term );
}
```

`term_exists()` checks the vocabulary before inserting. Pass the term name and the taxonomy ID, slug, or entity.

`delete()` removes the term. The permanent flag has no effect: terms are always removed, not moved to the trash.

## Read

The first argument follows [`get_terms()`](https://developer.wordpress.org/reference/functions/get_terms/ ":ignore"). The second argument is the taxonomy: an ID, a taxonomy entity, or a list of either. The result is an array of term entities. A numeric first argument is a term ID and returns one term.

```php
$terms = tainacan_terms()->fetch(
	array(
		'hide_empty' => false,
		'name'       => 'Drama',
	),
	$taxonomy
);
```

To assign terms to an item, save their IDs through a taxonomy metadatum. See [Item metadata](repository-item-metadata.md#multiple-values-and-terms).

## Class reference

[Repository](phpdoc/classes/Tainacan/Repositories/Terms.md) · [Entity](phpdoc/classes/Tainacan/Entities/Term.md) · [Internal API](internal-api.md)
