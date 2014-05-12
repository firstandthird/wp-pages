wp-pages
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
add_action('init', 'test_posttypes');
function test_pages() {
  do_action('ft_pages_path', '/your/path/here/');
}
```