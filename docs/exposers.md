# Exposers

Exposers are declarations and methods that expose the items of your repository in a certain way.

## Structure

### Name

The name of the Exposer.

### Slug

A URL friendly version of the Exposer name, to be used as a parameter to the API request informing you want to get the data using this exposer.


### Supported Mapping Standard

A list of mapping standards that is exposer supports. This means that whenever someone makes a request to receive that via this exposer, he/she will also be able to choose in which mapping standar they want the content to be served.


### Methods

Every exposer have to implement PHP methods that will build the API response. 

Optionally, an exposer can also implement a method to print data in the `HEAD` section of the HTML when visiting an item page. For example, JSON-LD exposer can add a JSON-LD object to the head of the page of every item in your collection.