# Creating Compatible Themes

Tainacan plugin offers tools to manage your digital repository, but in the end, publishing it online is one of the most important features to be desired. To avoid _reinventing the wheel_, all the work of having navigation menus, home pages, etc is handled by WordPress with its ecosystem of CMS features, plugins and of course, **themes**.

> Themes are the default way that WordPress offers for tweaking the appearance of your website. They can show different layouts, font, and colors, but beyond that, they can offer customizations of different sorts.

Considering that, the development of the plugin was made _in sync_ with the production of an "official theme", planned to attend the majority of use cases, which is the [Tainacan Interface](https://wordpress.org/themes/tainacan-interface/). Nevertheless, if you are a developer and have an interest on adapting your theme to support some Tainacan features, you are free to do it by using the strategies that are described on this section.

![Exemples of an Items List on custom themes.](/_assets/images/creating-compatible-themes.png ':class=alignwide')

To give you a taste of what is possible, check out this file with three simple lines added to a _child-theme_ of WordPress famous [TwentyNineteen](https://wordpress.org/themes/twentynineteen/ ":ignore") theme:

```php
/** /tainacan/archive-items.php
 *  Custom implementation of Tainacan collection items list
 */
<?php get_header(); ?>
    <?php tainacan_the_faceted_search(); ?>
<?php get_footer(); ?>
```

With that minimal update, the items list goes from a standard _blog-posts-like_ list to a full _faceted search items list_:

![Twenty Nineteen child theme adapted to Tainacan items list.](/_assets/images/creating-compatible-themes-2.png ':class=alignwide')

We have a sample child theme made to take care of this small adjustment, that you can download here:

<div style="width: 100%; text-align: center;">
    <a style="margin: 4px; padding: 10px 16px; color: #298596; border: 2px solid #298596; border-radius: 4px;" href="https://github.com/tainacan/tainacan-wiki/raw/master/dev/_assets/some-theme-child.zip">
        Sample Child Theme
    </a>
</div>

In order to use it, you must rename the content in it's files according to your parent theme. For example, to make the _child-theme_ of WordPress _TwentyNineteen_ aforementioned, you would:

1. Rename every "Some Theme" as "Twenty Nineteen";
2. Rename every "some_theme" as "twenty_nineteen";
3. Rename every "some-theme" as "twentynineteen";

The last one is the most important, as it should correspond to the parent theme _slug_;

But there is a lot more that can be done. So if you are interested on getting to know the details, here is a summary of how you can make your theme trully compatible with Tainacan:

- [Creating custom templates](/dev/custom-templates.md) to one of [Tainacan pages](tainacan-pages.md);
- [Adapting the Vue Items List Component](/dev/the-vue-items-list-component.md);
- [Registering custom Extra View Modes](/dev/extra-view-modes.md);
