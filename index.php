<?php

/**
 * Plugin Name: Tavakal - Destroy user sessions
 * Description: Free light plugin to kick out user after being afk, or inactive for long time even if the browser is closed. You can change the time and users roles anytime in option page
 * Author: Tavakal4devs
 * Version: 1.0.0
 * Donate link: https://paypal.me/MohAsly
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 */


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require dirname( __FILE__ ) .'/includes/TavakalDestroySessionSettings.php';
require dirname( __FILE__ ) .'/includes/TavakalDestroyUserSession.php';


new TavakalDestroySessionSettings();
new TavakalDestroyUserSession();


// deactivation

register_deactivation_hook( __FILE__, 'tavakal_deactivate');

function tavakal_deactivate(){

    delete_option('tavakal_time_before_destroying_sessions');
    delete_option('tavakal_included_roles');
    delete_option('tavakal_time_type');

    global $wpdb;
    $wpdb->delete(
        $wpdb->usermeta,
        array(
            'meta_key' => 'tavakal_last_user_activity',
        )
    );

    wp_clear_scheduled_hook('tavakal_destroy_expired_sessions');
}