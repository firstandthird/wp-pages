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
  private $page_ids = array();

  function __construct() {
    $this->config_path =   WP_CONTENT_DIR . '/pages';

    add_action('admin_menu', array($this, 'admin_menu'));

    // Allow theme to override config path
    add_action('ft_pages_path', array($this, 'set_path'));

    $this->parse();
    $this->get_page_ids();

    // Remove pages from admin
    add_filter('parse_query', array($this, 'remove_from_admin'));

    // Add data to templates
    add_action('template_redirect', array($this, 'add_data'));
  }

  private function parse() {
    if(!file_exists($this->config_path)) {
      return false;
    }

    foreach (glob($this->config_path . "/*.yaml") as $filename) {
      $config = array();

      $config = spyc_load_file($filename);

      if(!is_array($config)) continue;

      if(!array_key_exists('post_name', $config)) {
        foreach($config as $subconfig) {
          if(!is_array($config) || !array_key_exists('post_name', $subconfig)) continue;

          $this->config[] = $subconfig;
        }
      } else { 
        $this->config[] = $config;
      }
    }
  }

  private function get_page_ids() {
    $pages = array();

    foreach($this->config as $page) {
      $existing = get_posts(array(
        'name' => $page['post_name'],
        'post_type' => $page['post_type'],
        'posts_per_page' => -1
      ));

      if(count($existing)) {
        $pages[] = $existing[0]->ID;
      }
    }

    $this->page_ids = $pages;
  }

  function remove_from_admin($query) {
    global $pagenow, $post_type;

    if(!is_admin()) return $query;

    if($pagenow === 'edit.php' && $post_type === 'page') {
      $query->query_vars['post__not_in'] = $this->page_ids;
    }
  }

  function add_data() {
    global $pagename;
    
    if(isset($pagename)) {
      foreach($this->config as $page) {
        if($page['post_name'] === $pagename && isset($page['data'])) {
          $this->load_data($page);
          break;
        }
      }
    }
  }

  function load_data($page) {
    $GLOBALS[$page['post_name']] = spyc_load_file($this->config_path . '/data/' . $page['data']);
  }

  function update_pages() {
    foreach($this->config as $page) {
      $existing = get_posts(array(
        'name' => $page['post_name'],
        'post_type' => $page['post_type'],
        'posts_per_page' => -1
      ));

      if(count($existing)) {
        $page['ID'] = $existing[0]->ID;
      }

      if(isset($page['post_parent'])) {
        $parent = get_posts(array(
          'name' => $page['post_parent'],
          'post_type' => $page['post_type'],
          'posts_per_page' => -1
        ));

        if(count($parent)) {
          $page['post_parent'] = $parent[0]->ID;
        }
      }

      $inserted = wp_insert_post($page);

      if(isset($page['template'])) {
        $update = update_post_meta($inserted, '_wp_page_template', $page['template'], true);
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