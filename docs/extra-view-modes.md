# Extra View Modes

Extra View Modes are a way of creating your own templates for items list visualization. By default, Tainacan offers the following view modes:

- Table
- Cards
- Records
- Masonry
- Grid
- Slideshow

Each has it's specificities, but in case you're not satisfied with them, a developer can easily create a plugin to offer a custom way of displaying items list.

## Creating your extra view mode

As shown in [our post for extra view modes](http://tainacan.org/2018/06/13/custom-view-modes-how-will-the-world-see-your-collection/), we created [a sample plugin](https://github.com/tainacan/tainacan-extra-viewmodes) with some inspirational ideas for custom view modes. We here describe the process to create such plugin. You will basically need three files:

1. The .php file for registering the plugin and view mode;
2. The .php file with them items list template;
3. The .css file with the styling for your template;

### Registering your plugin

As in any WordPress Plugin, you'll first need to create a php header as follows:

```
<?php
/*
Plugin Name: Name of Your Extra View Mode Plugin
Plugin URI: A URL for a related link
Description: An extra view modes plugin for Tainacan
Author: Your Name Here
Version: 0.1
Text Domain: tainacan-extra-viewmodes
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
```

Only Plugin Name is obligatory. Save the file with a name unique to your plugin, a good practice is to use the plugin name, such as `name-of-your-extra-view-mode-plugin.php`.

The rest of the file will contain the register function for your view mode. We discuss it in details ahead:

```
add_action( 'after_setup_theme', function() {

	tainacan_register_view_mode('MyViewMode', [
		'label' => 'My View Mode',
		'description' => 'This is my view mode. It looks great the way it is.',
		'icon' => '<span class="icon"><i class="mdi mdi-view-quilt mdi-24px"></i></span>',
		'dynamic_metadata' => false,
		'template' => __DIR__ . '/my-view-mode.php',
	]);

} );

?> /* End of file */
```

The function `tainacan_register_view_mode` is part of Tainacan's plugin. It's first paramether is a unique slug that will be used to identify your view-mode. Then follows and array paramethers:

| Type   | Name             | Description | Default                                   |
|--------|------------------|-------------|-------------------------------------------|
| string | label            | Label, visible to users.            | Same of the variable $slug                |
| string | description      | Description, visible only to editors in the admin.            | None                                      |
| string | type             | Type. Accepted values are 'template' or 'component'.             | 'template'                                |
| string | template         | Full path  to the template file to be used. Required if $type is set to template.             | 'theme-path/tainacan/view-mode-{$slug}.php' |
| string | component        | Component tag name. The web component js must be included and must accept two props: 1) items: the list of items to be rendered ; 2) displayed-metadata: list of metadata to be displayed;            | 'view-mode-{$slug}'                         |
| string | thumbnail        | Full URL to an thumbnail that represents the view mode. Displayed only in Admin.             | None                                      |
| string | icon             | HTML that outputs an icon that represents the view mode.            | None                                      |
| string   | skeleton_template | HTML that outputs a preview of the items to be loaded, such as gray blocks, representing the items. | None |
| bool   | show_pagination  | Wether to display or not pagination controls.            | true                                      |
| bool   | full-screen      | Wether the view mode will display full screen or not.             | false                                     |
| bool   | dynamic_metadata | Wether to display or not (and use or not) the "displayed metadata" selector.            | false                                     |
| bool   | implements_skeleton | Wheter the view modes taks care of showing it's own Skeleton/Ghost css classes for loading items. | false |

The *type* parameter is one of the most relevants here. When registering view modes, you can either create a simple PHP `template`, using WordPress functions, as the ones in our sample plugin, or more complex Vue.js `component`. When passing a template, the file path should be provided, while for components the name of previously loaded .vue component must be provided. Vue components must also have two props, one for receiving the items list as a parsed JSON Object and other for an array of metadata that will be displayed.

View modes as Cards and Grid do not allow users to choose which metadata should be displayed, but rather decide that only certain will be visible. For this kind of view mode, it is used the *dynamic_metadata* parameter as `false`.

## Making a Template extra view mode

The file indicated on the register above should point to the .php file where your template lives. It will probably have some structure similar to the following sample:

```
<?php if ( have_posts() ) : ?>

	<div class="my-view-mode-container">

		<?php while ( have_posts() ) : the_post(); ?>
			
			<a class="my-view-mode-item" href="<?php the_permalink(); ?>">
				<p class="my-view-mode-title">     
					<?php the_title(); ?>           
				</p>
				<?php if ( has_post_thumbnail() ) : ?>
					<div 
							style="background-image: url(<?php the_post_thumbnail_url( 'tainacan-medium' ) ?>)"
							class="my-view-mode-thumbnail">
						<?php the_post_thumbnail( 'tainacan-medium' ); ?>
						<div class="skeleton"></div> 
					</div>
				<?php else : ?>
					<div 
							style="background-image: url(<?php echo get_template_directory_uri() . '/assets/images/thumbnail_placeholder.png'?>)"
							class="grid-item-thumbnail">
						<?php echo '<img alt="Thumbnail placeholder" src="' . get_template_directory_uri() . '/assets/images/thumbnail_placeholder.png">'?>
						<div class="skeleton"></div> 
					</div>
				<?php endif; ?>  
			</a>	
		
		<?php endwhile; ?>
	
	</div>

<?php else : ?>

	<div class="my-view-mode-container">
		<section class="section">
			<div class="content has-text-gray4 has-text-centered">
				<p>
					<span class="icon is-large">
						<i class="mdi mdi-48px mdi-file-multiple"></i>
					</span>
				</p>
				<p>'No item was found.','tainacan-interface'</p>
			</div>
		</section>
	</div>

<?php endif; ?>
```

The classes `my-view-mode-container`, `my-view-mode-item`, `my-view-mode-title`, `my-view-mode-thumbnail` and so forth should be implemented by you on your style file. Other classes as the `skeleton`, `section` are part of Tainacan's plugin CSS.