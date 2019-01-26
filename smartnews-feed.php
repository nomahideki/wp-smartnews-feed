<?php
/*
  Plugin Name: Smartnews Feed
  Description: スマートニュース用のフィードを生成するプラグインです。
  Version: 1.0.0
  Author: Hideki Noma
  Author URI: http://logitoy.jp/
  Text Domain: smartnews-feed
  Domain Path: /languages
 */
/*  Copyright 2016  Hideki Noma  (email : r-wp@logitoy.jp)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1noref  USA
 */


load_plugin_textdomain('smartnews_feed', false, dirname(plugin_basename(__FILE__)) . '/languages/');


/* * *************************************************************
 * Define
 * ************************************************************* */


if (!defined('SNF_USER_NAME')) {
    define('SNF_USER_NAME', basename(dirname(__FILE__)));
}
if (!defined('SNF_USER_PLUGIN_DIR')) {
    define('SNF_USER_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . SNF_USER_NAME);
}
if (!defined('SNF_USER_PLUGIN_URL')) {
    define('SNF_USER_PLUGIN_URL', WP_PLUGIN_URL . '/' . SNF_USER_NAME);
}



/* * *************************************************************
 * Install and uninstall
 * ************************************************************* */

/**
 * Hooks for install
 */
if (function_exists('register_uninstall_hook')) {
    register_deactivation_hook(__FILE__, 'snf_uninstall');
}


/**
 * Hooks for uninstall
 */
if (function_exists('register_activation_hook')) {
    register_activation_hook(__FILE__, 'snf_install');
}

/**
 * Install this plugin
 */
function snf_install() {
// Initialise the Copyright / logo and save it
    $textarea = '(C) Company name';
    add_option('snf_copyright', $textarea);
    $logo = get_header_image();
    add_option('snf_logo_url', $logo);
}

/**
 * Uninstall this plugin
 */
function snf_uninstall() {
// Unregister options
    delete_option('snf_copyright');
    delete_option('snf_logo_url');
    delete_option('snf_rss_update_frequency');
    delete_option('snf_rss_update_period');

    unregister_setting('smartnews_feed', 'snf_copyright', 'snf_logo_url', 'snf_rss_update_frequency', 'snf_rss_update_period');
}

/* * *************************************************************
 * Menu + settings page
 * ************************************************************* */

/**
 * Add menu on the Back-Office for the plugin
 */
function snf_add_options_page() {
    if (function_exists('add_options_page')) {
        $page_title = __('Smartnews Feed', 'smartnews_feed');
        $menu_title = __('Smartnews Feed', 'smartnews_feed');
        $capability = 'administrator';
        $menu_slug = 'smartnews-feed';
        $function = 'snf_settings_page'; // function that contain the page
        add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
    }
}

add_action('admin_menu', 'snf_add_options_page');

/**
 * Display form of admin settings page
 */
function snf_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Smartnews Feed', 'smartnews_feed'); ?></h1>
        <p><?php _e('Please write your own copyright and Logo URL for your Smartnews feed.', 'smartnews_feed'); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields('smartnews-feed'); ?>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row">
                            <label for="snf_copyright">
                                <?php _e('Copyright', 'smartnews_feed'); ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            // determine the code to place in the textarea
                            $snf_copyright = get_option('snf_copyright');
                            if ($snf_copyright === false) {
                                // this option does not exists
                                $textarea = '(C) Company name';
                                // save this option
                                add_option('snf_copyright', $textarea);
                            } else {
                                // this option exists, display it in the textarea
                                $textarea = $snf_copyright;
                            }
                            ?>
                            <textarea name="snf_copyright" id="snf_copyright"
                                      rows="10" cols="50"
                                      class="large-text code"><?php
                                          echo $textarea;
                                          ?></textarea>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="snf_logo_url">
                                <?php _e('Logo URL', 'smartnews_feed'); ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            // determine the code to place in the textarea
                            $snf_logo_url = get_option('snf_logo_url');
                            if ($snf_logo_url === false) {
                                // this option does not exists
                                $logo = get_header_image();

                                // save this option
                                add_option('snf_logo_url', $logo);
                            } else {
                                // this option exists, display it in the textarea
                                $logo = $snf_logo_url;
                            }
                            ?>
                            <input name="snf_logo_url" id="snf_logo_url" size="50" value="<?php echo $logo; ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="snf_rss_update_frequency">
                                <?php _e('Update frequency', 'smartnews_feed'); ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            // determine the code to place in the textarea
                            $rss_update_frequency = get_option('snf_rss_update_frequency');
                            if ($rss_update_frequency === false) {
                                $rss_update_frequency = 1;
                                add_option('snf_rss_update_frequency', $rss_update_frequency);
                            } else {

                            }
                            $rss_update_period = get_option('snf_rss_update_period');
                            if ($rss_update_period === false) {
                                $rss_update_period = "hourly";
                                add_option('snf_rss_update_period', $rss_update_period);
                            } else {

                            }
                            ?>
                            <input name="snf_rss_update_frequency" id="snf_rss_update_frequency" size="6" value="<?php echo $rss_update_frequency; ?>" />
                            <select name="snf_rss_update_period" id="snf_rss_update_period">
                                <?php
                                $list = array('minutes', 'hourly', 'daily', 'weekly', 'monthly', 'yearly');
                                foreach ($list as $option):
                                    ?>
                                    <option value="<?php echo $option; ?>"<?php
                                    if ($option == $rss_update_period)
                                        echo " selected";
                                    ?>><?php echo _e($option, 'smartnews_feed'); ?></option>
                                        <?php endforeach; ?>
                            </select><?php echo _e('Suggested periods: 1-15 minutes.', 'smartnews_feed'); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
            // idea to evolve : button to restaure initial code
            ?>
            <?php submit_button(); ?>
        </form>
    </div>

    <?php
}

/**
 * Manage the option when we submit the form
 */
function snf_save_settings() {
    register_setting('smartnews-feed', 'snf_copyright');
    register_setting('smartnews-feed', 'snf_logo_url');
    register_setting('smartnews-feed', 'snf_rss_update_frequency');
    register_setting('smartnews-feed', 'snf_rss_update_period');
}

add_action('admin_init', 'snf_save_settings');

function snf_rss_update_frequency() {
    return get_option('snf_rss_update_frequency');
}

function snf_rss_update_period() {
    return get_option('snf_rss_update_period');
}

add_filter('rss_update_frequency', 'snf_rss_update_frequency');
add_filter('rss_update_period', 'snf_rss_update_period');
/* * *************************************************************
 * Core function
 * ************************************************************* */

/**
 * Main code for the RSS footer
 *
 * @param $content content of the post
 */
function do_feed_smartnews() {
    $feed_template = dirname(__FILE__) . '/feed.php';
    load_template($feed_template);
}

add_action('do_feed_smartnews', 'do_feed_smartnews');
