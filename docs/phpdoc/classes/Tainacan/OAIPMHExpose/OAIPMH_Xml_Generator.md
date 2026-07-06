# OAIPMH_Xml_Generator

Builds OAI-PMH 2.0 XML responses with `DOMDocument`. Element text is escaped via `createTextNode`.

* Full name: `\Tainacan\OAIPMHExpose\OAIPMH_Xml_Generator`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class OAIPMH_Xml_Generator {
        -dom : DOMDocument
        -root : DOMElement
        +__construct()
        -text_element(name, value)
        +init(base_url, verb, params)
        +add_error(code, message)
        +create_identify(data)
        +create_metadata_formats()
        +create_sets(sets)
        +add_set(list, set)
        +start_list(type)
        +add_record(list, data, include_metadata)
        +add_header(list, data)
        -create_header(data)
        -build_oai_dc(dc_data)
        +add_resumption_token(list, token, total, cursor, expiration)
        +output()
    }
    OAIPMH_Xml_Generator ..> OAIPMH_Xml_Generator
```

## Methods

| Method | Description |
|--------|-------------|
| `init( $base_url, $verb, $params )` | OAI-PMH envelope and request element |
| `add_error( $code, $message )` | OAI error node |
| `create_identify( $data )` | Identify verb, including oai-identifier description |
| `create_metadata_formats()` | ListMetadataFormats (oai_dc) |
| `create_sets( $sets )` | ListSets |
| `start_list( $type )` | ListRecords / ListIdentifiers / GetRecord container |
| `add_record( $list, $data, $include_metadata )` | Full record with optional metadata |
| `add_header( $list, $data )` | Header-only entry for ListIdentifiers |
| `add_resumption_token( ... )` | ResumptionToken with optional attributes |
| `output()` | Serialized XML string |
