First+Third Pages
============

Wordpress Custom Pages

## Usage

Install to plugin directory.

Create a new directory under `wp-content` called `pages`. This will store all the yaml files.

## Creating a page

Copy one of the example yaml files from the `example-conf` directory and place it into the `pages` directory you created.

After you make changes go to the wordpress admin and use the settings->Update Pages link.

## Configuring config path

In your plugin or functions.php

```php
add_action('init', 'test_pages');
function test_pages() {
  do_action('ft_pages_path', '/your/path/here/');
}
```

## Data usage

You can pass a data file in which will be loaded when the page is rendered.

Example yaml:

```yaml
post_title: Data
post_name: data-example #slug
post_status: publish
post_type: page
template: page-templates/data-example.php
data: example-data.yaml
```

Example data config:

```yaml
headline: This is a headline
copy: 
  - "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
  - "Test paragraph"
```

Data config files go into a directory named data inside of the page config directory. By default this is wp-content/pages/data.

See the example below for usage in a template.

```php
<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

// This is how you get the data.
$page_data = isset($GLOBALS[$pagename]) ? $GLOBALS[$pagename] : array();

get_header(); ?>

  <div id="primary" class="site-content">
    <div id="content" role="main">

      <?php if(isset($page_data['headline'])): ?>
        <h1><?php echo $page_data['headline'] ?></h1>
      <?php endif; ?>
      
      <?php if(isset($page_data['copy'])): ?>
        <?php foreach($page_data['copy'] as $copy): ?>
          <p><?php echo $copy ?></p>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if(isset($page_data['image'])): ?>
        This shouldn't run.
        <img src="<?php echo $page_data['image'] ?>">
      <?php endif; ?>

    </div><!-- #content -->
  </div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
```