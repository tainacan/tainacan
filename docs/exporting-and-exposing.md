# Exporting and Exposing your Repository

When you buid a digital repository with Tainacan, you gain the ability to show it to the world in many different ways thanks to the power and flexibility of WordPress.

But sometimes you dont want just to have your collections browsable via web, you want to download a spreadsheet to work with or you want to make it availabe via APIs so it can be consumed by other applications or harvested by an aggregator. This page describe how Tainacan handle with these situations.

## Mapping

With Tainacan you have the possibility to map your collection structure to one or more known standards you may want to be compatible with. So even if you use a custom set of fields to describe your collection, you may be compatible and interoperate with other repositories.

You do it by informing, for each field you create, what is it relative in each format you want to map your collection to. You may say for example, that you "Name" Field is the equivalent to the dc:Title attribute in Dublin Core and some another attribute in other format you choose.

Tainacan is shipped with some Mapping standards that implement popular metadata standards. And it will be easy to create new standards.  See more [details about mapping standards](mapping-standards.md). 

You can also use these mapping standards as a pre-set when you create a new Collection.

## Exporting

Exporting allows you to download the content of your repository to a file - or to multiple files. The format of the package you will download depends on the exporter you will use. Tainacan ships with a simple CSV exporter and a Tainacan-Package exporter, that allows you to export all the content of your collections, including the attachments, to import in another Tainacan instance.

Whatever exporter you choose to you use, you will be able to choose wether you want to download the collection as it is, which means, with the fields the way the were created in Tainacan, or wether you want to download it in a mapped version. For example, if you mapped your collection to Dublin Core, you can download a CSV file either in Dublin Core format or in the original format.

Tainacan makes it very easy to developers to create new exporters and publish them as plugins anyone can use.

## Exposing

Tainacan is powered with an API that allows other applications to search and consume the content of your repository. By default, this API serves the content in JSON format, preserving the fields in the collections the way you created them.

In the same way you can choose the format of the file when you export your collection, one can choose the format he/she want to consume yout content in. This is what exposers are for.

Each exposer implements a different way of presenting your data in the API response, and may support one or many mappings. See more [details about exposers](exposers.md).

For example, the default JSON exposer supports any mapping and can serve your content exposing any metadata standard you mapped your content to. The decision is in the hands of the application that makes the request to your API.

On the other hand, OAI-PMH exposer only supports Dublin Core mapping and will allways serve content this way.

Exposers are also really easy to develop and can be added to your Tainacan instance via plugins.
