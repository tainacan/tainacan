# OAIPMH_Data_Provider

OAI-PMH data provider: maps Tainacan collections, items, and Dublin Core metadata to structures consumed by `OAIPMH_Xml_Generator`.

* Full name: `\Tainacan\OAIPMHExpose\OAIPMH_Data_Provider`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class OAIPMH_Data_Provider {
        -identifier_prefix : string
        +__construct()
        +get_base_url()
        +get_identifier_prefix()
        +build_identifier(item_id)
        +extract_item_id(identifier)
        +get_identify()
        +get_repository_identifier()
        +get_earliest_datestamp()
        +get_sets()
        +get_sets_page(query)
        +set_exists(set_spec)
        +item_exists(identifier)
        +get_item(identifier)
        +get_records(query)
        -build_date_query(from, until)
        -format_item(post)
        -get_item_dc(item)
        -is_set_oai_readable(collection)
        -is_item_oai_readable(item)
    }
```

## Methods

| Method | Description |
|--------|-------------|
| `get_base_url()` | REST base URL advertised on Identify |
| `get_identify()` | Identify response field map |
| `get_earliest_datestamp()` | Cached MIN(post_modified_gmt) for published/trashed items |
| `get_sets()` | Collections as OAI sets |
| `get_records( $query )` | Paginated harvest query (set, from, until) |
| `get_item( $identifier )` | Single record by OAI identifier |
| `build_identifier( $item_id )` | Host-based OAI identifier |
| `extract_item_id( $identifier )` | Parses host-based and legacy reversed-domain identifiers |

Dublin Core fields are built from item core properties plus each metadatum's `exposer_mapping['dublin-core']`.
