<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
* Plugin Name: Colors Dashboard Multisite
* Description: Different colors dashboard multisite
* Version: 1.0
* Author: Joel Carvalho
**/

add_action('admin_head', 'my_custom_backgrounds');

function my_custom_backgrounds() {

  $host_name = $_SERVER['SERVER_NAME'];

  /*$volei7     = strpos($_SERVER['HTTP_HOST']."".$_SERVER['REQUEST_URI'], 'volei7');
  $running7   = strpos($_SERVER['HTTP_HOST']."".$_SERVER['REQUEST_URI'], 'running7');
  $balonmano7 = strpos($_SERVER['HTTP_HOST']."".$_SERVER['REQUEST_URI'], 'balonmano7');

  if ($volei7 != false) {
    dashboard_red();
  }
  elseif ($running7 != false) {
    // Nada
  }
  elseif ($balonmano7 != false) {
    dashboard_green();
  }
  else{
    dashboard_blue();
  }*/

  // Running
  if ($host_name === 'andebol7.wp.dev' || $host_name === 'www.andebol7.com' || $host_name === 'www.andebol7.pt') {
    dashboard_blue();
  }
  elseif ($host_name === 'volei7.andebol7.wp.dev' || $host_name === 'www.volei7.pt' || $host_name === 'www.volei7.andebol7.com' || $host_name === 'www.volei7.andebol7.pt') {
    dashboard_red();
  }
  elseif ($host_name === 'running7.andebol7.wp.dev' || $host_name === 'www.running7.pt' || $host_name === 'www.running7.andebol7.com' || $host_name === 'www.running7.andebol7.pt') {
    // Nada
  }
  elseif ($host_name === 'balonmano7.andebol7.wp.dev' || $host_name === 'www.balonmano7.pt' || $host_name === 'www.balonmano7.andebol7.com' || $host_name === 'www.balonmano7.andebol7.pt') {
    dashboard_green();
  }
}

function dashboard_blue(){
  echo '<style>
        #adminmenuwrap, #adminmenu, .wp-submenu {
            background-color: #2a6496;
        }
        #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, #adminmenu .wp-has-current-submenu .wp-submenu{
            background-color: #56cff5;
        }
        #wpadminbar, #adminmenu, #adminmenu .wp-submenu, #adminmenuback, #adminmenuwrap {
            background: #2a6496;
        }
        #adminmenu li.menu-top:hover, #adminmenu li.opensub>a.menu-top, #adminmenu li>a.menu-top:focus, #adminmenu .wp-submenu, .folded #adminmenu .wp-has-current-submenu .wp-submenu, .folded #adminmenu a.wp-has-current-submenu:focus+.wp-submenu {
            background-color: #56cff5;
            color: white;
        }
        #wpadminbar .ab-top-menu>li.hover>.ab-item, #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item, #wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus, #wpadminbar .menupop .ab-sub-wrapper, #wpadminbar .shortlink-input {
            background-color: #56cff5;
            color: white;
        }
        #wpadminbar .quicklinks .menupop.hover ul li a:hover, #adminmenu a:hover, #adminmenu .wp-submenu a:hover, #adminmenu a:hover {
          color: white;
        }
        #wpadminbar .quicklinks .menupop ul.ab-sub-secondary, #wpadminbar .quicklinks .menupop ul.ab-sub-secondary .ab-submenu{
          background: #56cff5;
        }
      </style>';
}

function dashboard_red(){
  echo '<style>
        #adminmenuwrap, #adminmenu, .wp-submenu {
            background-color: #e53124;
        }
        #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, #adminmenu .wp-has-current-submenu .wp-submenu{
            background-color: #f76c6c;
        }
        #wpadminbar, #adminmenu, #adminmenu .wp-submenu, #adminmenuback, #adminmenuwrap {
            background: #e53124;
        }
        #adminmenu li.menu-top:hover, #adminmenu li.opensub>a.menu-top, #adminmenu li>a.menu-top:focus, #adminmenu .wp-submenu, .folded #adminmenu .wp-has-current-submenu .wp-submenu, .folded #adminmenu a.wp-has-current-submenu:focus+.wp-submenu {
            background-color: #f76c6c;
            color: white;
        }
        #wpadminbar .ab-top-menu>li.hover>.ab-item, #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item, #wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus, #wpadminbar .menupop .ab-sub-wrapper, #wpadminbar .shortlink-input {
            background-color: #f76c6c;
            color: white;
        }
        #wpadminbar .quicklinks .menupop.hover ul li a:hover, #adminmenu a:hover, #adminmenu .wp-submenu a:hover, #adminmenu a:hover {
          color: white;
        }
        #wpadminbar .quicklinks .menupop ul.ab-sub-secondary, #wpadminbar .quicklinks .menupop ul.ab-sub-secondary .ab-submenu{
          background: #f76c6c;
        }
      </style>';
}

function dashboard_green(){
  echo '<style>
        #adminmenuwrap, #adminmenu, .wp-submenu {
            background-color: #065838;
        }
        #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, #adminmenu .wp-has-current-submenu .wp-submenu{
            background-color: #2fbd86;
        }
        #wpadminbar, #adminmenu, #adminmenu .wp-submenu, #adminmenuback, #adminmenuwrap {
            background: #065838;
        }
        #adminmenu li.menu-top:hover, #adminmenu li.opensub>a.menu-top, #adminmenu li>a.menu-top:focus, #adminmenu .wp-submenu, .folded #adminmenu .wp-has-current-submenu .wp-submenu, .folded #adminmenu a.wp-has-current-submenu:focus+.wp-submenu {
            background-color: #2fbd86;
            color: white;
        }
        #wpadminbar .ab-top-menu>li.hover>.ab-item, #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item, #wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus, #wpadminbar .menupop .ab-sub-wrapper, #wpadminbar .shortlink-input {
            background-color: #2fbd86;
            color: white;
        }
        #wpadminbar .quicklinks .menupop.hover ul li a:hover, #adminmenu a:hover, #adminmenu .wp-submenu a:hover, #adminmenu a:hover {
          color: white;
        }
        #wpadminbar .quicklinks .menupop ul.ab-sub-secondary, #wpadminbar .quicklinks .menupop ul.ab-sub-secondary .ab-submenu{
          background: #2fbd86;
        }
      </style>';
}

?>
