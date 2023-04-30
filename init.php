<?php
/**
 * Troms カスタム投稿
 *
 * Plugin Name: Troms Security
 *
 * Description: ログインページのURLを変更します
 * Version: 1.0.1
 * Author: ZATY
 * Author URI: https://zaty.jp
 *
 */
if (!class_exists('Login_Security')) :

  class Login_Security
  {
    const DEFAULT_LOGIN_NAME = 'wp-login.html';

    public function __construct()
    {
      $this->init();
    }

    private function init()
    {
      $this->hooks();
    }

    private function hooks()
    {
      add_action('template_redirect', array($this, 'login_redirect'));
      add_action('login_init', array($this, 'return_error_default_login_url'));
      add_filter('site_url', array($this, 'login_change_site_url'), 10, 2);
      add_filter('wp_redirect', array($this, 'login_wp_redirect'));
    }

    public function return_error_default_login_url()
    {
      if (!defined('LOGIN_CHANGE') || sha1('page_changed') !== LOGIN_CHANGE) {
          global $wp_query;
          $wp_query->set_404();
          status_header(404);
          get_template_part(404);
          exit;
      }
    }

    public function login_change_site_url($url, $path)
    {
      $require_login_name = apply_filters('login_endpoint_name', self::DEFAULT_LOGIN_NAME);
      if (strpos($path, 'wp-login.php') !== false
          && (is_user_logged_in()
              || strpos($_SERVER['REQUEST_URI'], $require_login_name) !== false)) {
          $url = str_replace('wp-login.php', $require_login_name, $url);
      }
      return $url;
    }

    public function login_wp_redirect($location)
    {
      $require_login_name = apply_filters('login_endpoint_name', self::DEFAULT_LOGIN_NAME);

      if (strpos($_SERVER['REQUEST_URI'], $require_login_name) !== false) {
          $location = str_replace('wp-login.php', $require_login_name, $location);
      }
      return $location;
    }

    public function login_redirect()
    {
      $full_uri = (is_ssl() ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
      $full_uri = explode('?', $full_uri)[0];

      $require_login_name = apply_filters('login_endpoint_name', self::DEFAULT_LOGIN_NAME);
      if ($full_uri === home_url('/') . $require_login_name) {
          header("HTTP/1.1 200 LOGIN PAGE");
          define('LOGIN_CHANGE', sha1('page_changed'));
          require_once(ABSPATH . '/wp-login.php');
          exit;
      }
    }
  }

  new Login_Security;
  
endif;