# tainacan_blocks_register_block


Registers a 'generic' Tainacan Block, according to the TAINACAN_BLOCKs array

***

* Full name: `tainacan_blocks_register_block`
* Defined in: `views/gutenberg-blocks/class-tainacan-gutenberg-block.php`

## Parameters

| Parameter          | Type       | Description                                                                                                                                                |
|--------------------|------------|------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$block_slug`      | **string** | The block slug                                                                                                                                             |
| `$options`         | **array**  | {
   Optional. Array of arguments.
   @type array		 $extra_editor_script_deps		Array of strings containing script dependencies of the editor side script
} |
| `$block_settings`  | **array**  | JSON array containing the block settings from the server                                                                                                   |
| `$user_settings`   | **array**  | JSON array containing the user settings from the server                                                                                                    |
| `$plugin_settings` | **array**  | JSON array containing the plugin settings from the server                                                                                                  |

## Return Value

**mixed**
