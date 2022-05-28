<?php

namespace Plugin;

use \Export\SmartExport as Export;

class SmartPlugin
{
    private static $option_prefix = 'sph_';

    /**
     * Plugin activation and preparing for installation
     *
     * @return void
     */
    public static function activate():void
    {
        update_option(self::$option_prefix . 'activated', 0);

    }

    /**
     * Plugin deactivation - removing options from wordpress options table.
     *
     * @return void
     */
    public static function deactivate():void
    {
        global $wpdb;

        $sql = sprintf(
            'DELETE FROM `%s` WHERE option_name LIKE "%s%%"'
            , $wpdb->options
            , self::$option_prefix
        );

        $wpdb->query($sql);
    }

    /**
     * Performs plugin installation
     *
     * @return void
     */
    public static function install():void
    {
        if (get_option(self::$option_prefix . 'activated', 0) != 1) {

            require PLUGIN_DIR . '/install/install.php';
            update_option(self::$option_prefix . 'activated', 1);
        }
    }

    /**
     * Performes plugin deinstallation. Removes the whole smart_price_history table from database.
     *
     * @return void
     */
    public static function uninstall():void
    {
        global $wpdb;

        $sql = "DROP TABLE `smart_price_history`";
        $wpdb->query($sql);

        self::deactivate();
    }

    /**
     * Performes products price export and cleans old entries at the end.
     *
     * @return void
     */
    public static function export():void
    {
        Export::start();
        Export::cleaner();
    }

    /**
     * Checks prices export status
     *
     * @return void
     */
    public static function checkExport()
    {
        Export::check();
    }

    /**
     * Displays lowest-price module on the product page.
     *
     * @return void
     */
    public static function renderLowestPrice()
    {
        global $product;

        if (
            sph_get_lowest_price()
            && (float) $product->get_price() != Export::calcPrice($product)
        ) {

            include PLUGIN_DIR . '/public/template/product/price-info.php';
        }

    }

    /**
     * Saves plugin configuration from Post data
     *
     * @return void
     */
    public static function savePostData()
    {
        if(
            !empty($_POST['sph_config'])
            && wp_verify_nonce($_POST['sph_config_nonce'], 'sph-config-form')
        ){
            update_option( self::$option_prefix . 'config', json_encode($_POST['sph_config']) );
        }
    }

    /**
     * Addes plugin menu to the wordpress admin menu bar.
     *
     * @return void
     */
    public static function addMenu()
    {
        $hookname = add_menu_page(
            _x('Products price history', 'menu-name', 'smart-price-history'),
            _x('Price history', 'menu-title', 'smart-price-history'),
            'manage_options',
            'smart-price-history',
            function () {

                include PLUGIN_DIR . '/admin/template/config.php';
            },
            '',
            27
        );

        add_action("load-{$hookname}", 'sph_post_data');

    }

    /**
     * Returns plugin's config object (JSON)
     *
     * @return object
     */
    public static function getConfig():object
    {
        return json_decode(get_option(self::$option_prefix . 'config', '{}'));

    }
}
