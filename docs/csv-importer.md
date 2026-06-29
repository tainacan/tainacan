# CSV Importer

The CSV importer allows users to import items to a collection from a CSV (comma-separated values) file.

The input file is a standard CSV file, where each line will hold one item information, and each column will hold the value for one specific metadata. Also, the first line brings the column titles.

When the user starts the importer process, he/she must choose the right file encoding the CSV was saved in (usually UTF-8), the column separator and the cell enclosure. All these options are set when the user generates the CSV file using a spreadsheet editor.

In this section user will also inform the character (or characters) used to separate values in a multi-valued cell.

After configuring the importer and choosing the target collection, the CSV file is uploaded and the user has the chance to map the columns present in the CSV to the metadata present in the target collection.

If the metadata was not created beforehand, the user can create and map metadata in this screen, or choose the "Create metadatum" option in the mapper. If this option is selected, Tainacan will automatically create a metadatum when the importer runs (see "Creating metadata on the fly" section below to learn how to tell Tainacan the type and other attributes of the metadatum that will be created).


## Special Columns

Each column of the CSV must be mapped to a metadatum in the target collection. However, there are special columns that can be used to set the value for other aspects of the item. For example, the item status can be set to say the item is public to everyone, in draft or private to editors.

The special columns that can be used are:

* **special_item_status** - Inform the item status. Possible values are draft, private or publish.
* **special_item_id** - Inform the item ID in the Tainacan database. This is useful when re-importing items and let the user decide whether to update existing items or ignore them and only add new items.
* **special_document** - let the user inform the item document. See "Importing files and attachments"
* **special_attachments** - let the user inform the attachments. See "Importing files and attachments"
* **special_comment_status** - Inform if the item is open for comments. Possible values are open or closed. The default is closed.

Example:

```csv
name, special_item_status, special_comment_status
item uno, draft,   closed
item due, private, closed
item tre, publish, open
```

### Importing files and attachments

If you also have files you want to import, that are related to the items in your CSV, you can use some special columns in your csv to do so.

There are two special columns you can use: `special_document`, which will set the Document of your item, and `special_attachments` to add one or many attachments.

The values for the special_document must be prepended with 'url:'', 'file:'' or 'text:'. This will indicate the Document Type.

Example:

```csv
name, special_document
An image,file:http://example.com/image.jpg
A youtube video,url:http://youtube.com/?w=123456
A text,text:This is a sample text
```

The values for the special_attachments is just a list of files. If you want to add many attachments, use the separator you set in the Multivalued Delimiter option.

In either case, you can point to a file using a full URL, or just a file name. In this last case, you should set the option below to tell Tainacan where to find the files in your server. You can then upload them directly (via FTP for example) and Tainacan will add them to your items.

Example:

```csv
name, special_attachments
An image,http://example.com/image.jpg
Many images,http://example.com/image.jpg||http://example.com/image2.jpg||http://example.com/image3.jpg
Images uploaded via FTP,myfolder/image.jpg||myfolder/image2.jpg
```


## Creating metadata on the fly

When the user maps the columns found in the CSV file to the metadata present in the collection, he/she has can choose the "Create metadatum" option, so the importer will automatically create the metadata as it processes the file.

By default, it will create a public text metadatum, but you can inform Tainacan the type and other features of the metadata in the header of the CSV.

In the first line, where you declare the name of each column, you can add some information that will be used by the importer to create the metadatum_id.

Each information about the metadatum will be separated by a pipe "|" character. 

The first information must be the metadata name, and the second, the metadata type.

For example:

```csv
Name,Subject|taxonomy,Date of creation|date
```

The natively supported types right now are:

* text
* textarea
* taxonomy - when this type is used, a new taxonomy will be created
* date - Values must be informed in YYYY-MM-DD format 
* numeric
* relationship - Values must be the ID of the related item

After the type, you can use keywords to inform other features:

* multiple - can have multiple values 
* required - is required
* display_yes - display in lists: yes 
* display_no - display in lists: not by default 
* display_never - display in lists: never 
* status_public - visible to everyone
* status_private - visible only to editors 
* collection_key_yes - set this meta as a collection key (there cannot be two items with the same value).

Examples combining multiple features:

```csv
Name,Subject|taxonomy|multiple|required,Internal code|numeric|required|collection_key_yes|status_private
```
