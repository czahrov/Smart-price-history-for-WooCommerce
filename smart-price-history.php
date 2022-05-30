<?php
/*
    Plugin Name: Woocommerce Smart Price History
    Description: The plugin collects information about the lowest price of the product from the last month and presents it on the Woocommerce product page. The history of the lowest price for a product is recorded since the plug-in is enabled and is held daily at midnight.
    Version: 1.0.0
    Author: Smart Agency <kontakt@smart-agency.pl>
    Author URI: https://www.smart-agency.pl
    Text Domain: smart-price-history
    Domain Path: /languages
    Requires at least: 5.4
    Requires PHP: 5.6
    WC requires at least: 6.5.1
    WC tested up to: 6.5.1
    Licence: GPLv2
    Licence URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

define('PLUGIN_DIR', __DIR__);
define('PLUGIN_URL', plugins_url('', __FILE__));

// load translations
add_action('init', function(){
    load_plugin_textdomain(
        'smart-price-history'
        , false
        , dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );

});

require __DIR__ . '/libs/autoloader.php';

use \Plugin\SmartPlugin as Plugin;
use \Export\SmartExport as Export;

register_activation_hook(__FILE__, function(){
    Plugin::activate();
    
});

register_deactivation_hook(__FILE__, function(){
    Plugin::deactivate();

});

Plugin::install();

// actions on admin page
$file_version = time();
if (is_admin()) {

    wp_enqueue_style( 'smart-price-history', PLUGIN_URL . '/admin/css/dist/smart-price-history-admin.min.css', array(), $file_version);
} 
// actions on user page
else {

    // CSS + JS
    add_action('wp_enqueue_scripts', function () use ($file_version) {
        
        wp_enqueue_style('smart-price-history', PLUGIN_URL . '/public/css/dist/smart-price-history-public.min.css', array(), $file_version);
    });
}

// processing post data
function sph_post_data(){
    Plugin::savePostData();
    
}

/**
 * Add custom menu option
 */
add_action('admin_menu', function(){
    Plugin::addMenu();

});

// custom CRON schedules
add_filter('cron_schedules', function($schedules){

    $schedules['smart_price_history_export_interval'] = array(
        'interval'  => 24 * 60 * 60,     // 24h
        'display'   => 'Smart price history - export',
    );

    $schedules['smart_price_history_check_interval'] = array(
        'interval'  => 1 * 60,     // 1m
        'display'   => 'Smart price history - check',
    );

    return $schedules;
});

// export action hook
add_action('smart_price_history_export', function(){
    Plugin::export();

});

// CRON smart price history export
if(!wp_next_scheduled( 'smart_price_history_export' )){
    $schedule_time = (new DateTime('now'))->setTime(23, 59, 59);

    if($schedule_time->getTimestamp() < time()){
        $schedule_time->setDate(
            (int) getdate()['year'],
            (int) getdate()['mon'],
            (int) getdate()['mday'] + 1,
        );
    }

    wp_schedule_event( 
        $schedule_time->getTimestamp(), 
        'smart_price_history_export_interval', 
        'smart_price_history_export', 
        array(), 
        true 
    );
}

// check action hook
add_action('smart_price_history_check', function(){
    Plugin::checkExport();

});

// CRON smart price history check
if(!wp_next_scheduled( 'smart_price_history_check' )){
    $schedule_time = (new DateTime('now'));
    $schedule_time->setTime(
        (int) getdate($schedule_time->getTimestamp())['hours'], 
        15 * ceil(((int) getdate($schedule_time->getTimestamp())['minutes'] + 1) / 15), 
        0, 
    );

    wp_schedule_event( 
        $schedule_time->getTimestamp(), 
        'smart_price_history_check_interval', 
        'smart_price_history_check', 
        array(), 
        true 
    );
}

// Price history info in product page
add_action('woocommerce_single_product_summary', function(){
    Plugin::renderLowestPrice();

}, 11, 0);

if(!function_exists('sph_get_lowest_price')){
    function sph_get_lowest_price(){
        global $wpdb, $product;

        $query = <<<QQQ
        SELECT MIN(price)
        FROM `smart_price_history`
        WHERE product_id = '{$product->get_id()}'
        AND date >= ADDDATE(now(), INTERVAL -1 MONTH)
        QQQ;

        $result = $wpdb->get_var($query);

        return $result;
    }
}
