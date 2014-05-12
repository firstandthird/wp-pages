<?php
/**
 * Plugin Name: wp-pages
 * Description: Wordpress Custom Pages
 */

if(!class_exists('Spyc')) {
  require_once('lib/spyc.php');
}

class ftPages {

  private $config = array();

  function __construct() {
    $this->config_path =   WP_CONTENT_DIR . '/pages';

    add_action('admin_menu', array($this, 'admin_menu'));

    // Allow theme to override config path
    add_action('ft_pages_path', array($this, 'set_path'));

    $this->parse();
  }

  private function parse() {
    if(!file_exists($this->config_path)) {
      return false;
    }

    foreach (glob($this->config_path . "/**/*.yaml") as $filename) {
      $config = array();

      $config = spyc_load_file($filename);

      if(!is_array($config)) continue;

      if(!array_key_exists('data', $config)) {
        foreach($config as $subconfig) {
          if(!is_array($config) || !array_key_exists('data', $subconfig)) continue;

          $this->config[] = $subconfig;
        }
      } else { 
        $this->config[] = $config;
      }
    }
  }

  function update_pages() {
    foreach($this->config as $page) {
      $existing = get_posts(array(
        'name' => $page['data']['post_name'],
        'post_type' => $page['data']['post_type'],
        'posts_per_page' => -1
      ));

      if(count($existing)) {
        $page['data']['ID'] = $existing[0]->ID;
      }

      if(isset($page['data']['post_parent'])) {
        $parent = get_posts(array(
          'name' => $page['data']['post_parent'],
          'post_type' => $page['data']['post_type'],
          'posts_per_page' => -1
        ));

        if(count($parent)) {
          $page['data']['post_parent'] = $parent[0]->ID;
        }
      }

      $inserted = wp_insert_post($page['data']);

      if(isset($page['meta'])) {
        foreach($page['meta'] as $meta_key => $meta_value) {
          $update = update_post_meta($inserted, $meta_key, $meta_value, true);
        }
      }
    }
  }

  function admin_menu() {
    add_submenu_page('tools.php', 'Update Pages', 'Update Pages', 'activate_plugins', 'update_pages', array($this, 'do_update'));
  }

  function do_update() {
    $this->update_pages();
    echo "<p>Pages updated.</p>";
  }

  function set_path($path) {
    if(!file_exists($path)) {
      return false;
    }

    $this->config_path = $path;
  }
}

$ftPages = new ftPages;