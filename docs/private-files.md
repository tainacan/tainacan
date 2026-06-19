# Protecting private files

This page explains how Tainacan protects access to attachments made to private items.

## How it works

Tainacan changes the default folders WordPress uses to save attachments for items. Instead of saving attachments in folders and subfolders representing the year and month, Tainacan will save them in folders representing the collection and items ids.

Every time an attachment or a document is uploaded to an item, the file is saved under a special directory inside the uploads folder. There is a base directory for all items files, `tainacan-items` by default, and, inside this folder a `$collection_id/$item_id` folder structure.

```
uploads/2019/02 <-- WordPress default. Stores uploads for february, 2019
uploads/tainacan-items/123/456/ <-- Stores uploads for item 456 that belongs to collection 123
```

The URL for the files will always remain the plain structure, for example, `site.com/wp-content/uploads/tainacan-items/1234/2345/file.jpg`. However, in the file system, collections and items folders can be prefixed, indicating they are protected folders.

So if the item with ID `2345` is private, the folder will be called, for example, `_x_2345`.

Since the URL and the folder in the filesystem don't match, trying to access this file would result in a 404 error. But then Tainacan will detect this and will into the prefixed folders for it.

If it finds, it will check whether the current user can read the item and then serve it for authorized users, or leave a 404 response for non-authorized users.

A special `.htaccess` rule will block access to any URL that has a folder starting with the `_x_` prefix, so the only way to access these files will be using the plain URL and passing through this permission check.

Every time you edit an item or a collection, or even bulk edit items, the folders will be renamed accordingly.

> [!WARNING]
> For now, thumbnails are not considered private files and remain in the default WordPress upload folder.

## Protecting folders

You need to add a rule to your `.htaccess` to block access to all prefixed folders inside your uploads directory. This could be done with a rule like this:

```
RewriteRule ^wp-content/uploads/tainacan-items/.*_x_\d+/.+$ - [F,L]
```

This line will go right below the `RewriteEngine On` line.

Even if a person manages to find out the real file path, with the prefixed folder name, he/she will not be able to access it, only via the clean URL that will go through the permission check.


## Changing base folder and prefixes

The base folder for items attachments can be changed setting the `TAINACAN_ITEMS_UPLOADS_DIR` constant in `wp-config.php`. The default value is `tainacan-items`.

The prefix for private folders can be changed setting the `TAINACAN_PRIVATE_FOLDER_PREFIX` constant in `wp-config.php`. The default value is `_x_`.

> [!WARNING]
> These constants must be set in a fresh install. If they are changed after there are uploads, all the links to the existing files will break.


## Moving attachments from versions before 0.11

If you are upgrading from an older version of Tainacan and already have files attached to items everything will keep working as usual. The changes will only be applied to new files uploaded from this point on. Old files will still be in the default WordPress folder structure.

If you want to move old files to the new structure there is a Command-Line command you can run to do this, using WP CLI.

```
wp tainacan move-attachments-to-items-folder
```

> [!WARNING]
> Make a backup of your site before running this and check if everything is in place afterward, if they are not, recover your backup.

It is a good idea to do a dry run before just to check if the command runs till the end and have an idea of the files that will be affected:

```
wp tainacan move-attachments-to-items-folder --dry-run
```

