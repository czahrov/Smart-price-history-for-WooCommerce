<?php

namespace Export;

/**
 * Exporting new set of products price to database
 */
class SmartExport
{

    /**
     * Exporting products info to database
     *
     * @return void
     */
    public static function start():void
    {
        $status = self::getStatus();

        if( $status->is_active ) return;   // abort when already running

        global $wpdb;

        $progress   = self::getProgress();
        $products   = self::getProducts();
        $products   = array_slice($products, floor($progress/100 * count($products)));

        foreach($products as $product_num => $product_id){
            $wc_product = wc_get_product( $product_id );
            
            $wpdb->insert(
                'smart_price_history'
                ,array(
                    'product_id'    => $product_id,
                    'product_sku'   => $wc_product->get_sku(),
                    'price'         => self::calcPrice($wc_product),
                )
                ,array(
                    '%d',
                    '%s',
                    '%f',
                )
            );

            self::setProgress($progress + (100 - $progress) * ($product_num + 1) / count($products));
        }

        self::setProgress(0);
    }

    /**
     * Returns an array of products
     *
     * @return int[]
     */
    public static function getProducts():array
    {
        $products = null;

        if(is_null($products)){
            $products = wc_get_products(array(
                'status' => ['publish', 'draft'],
                'limit' => -1,
                // 'sku' => '3058286',
                'return' => 'ids',
            ));

        }

        return $products;
    }

    /**
     * Calculates product's price
     *
     * @param \WC_Product $product
     * @return float
     */
    public static function calcPrice($product):float
    {
        if( is_numeric($product) ){
            $product = wc_get_product( (int) $product );
        }
        elseif( !$product instanceof \WC_Product ){
            return false;
        }

        $product_price = (float) $product->get_price();

        foreach($product->get_category_ids() as $cat_id){

            if(
                isset(self::getCategoryRules()["set_{$cat_id}"])
                && is_array(($set = self::getCategoryRules()["set_{$cat_id}"]))
                && !empty($rules = $set['rules'])
            ){
                $rule = $rules[0];
                $amount = $rule['amount'];

                if(
                    !empty(($type = $rule['type']))
                    && isset($set['collector']['args']['cats'])
                    && in_array($cat_id, $set['collector']['args']['cats'])
                ){

                    switch ($type) {
                        case 'percent_product':
                            
                            $product_price = $product_price * (1 - $amount / 100);
                            break;
                        case 'fixed_product':
                            
                            $product_price = $product_price - $amount;
                            break;
                        default:
                        
                    }

                    break;
                }
            }
        }

        return $product_price;
    }

    /**
     * Returns an array of category pricing rules
     *
     * @return array
        /*  example Array(
            [set_17483] => Array
            (
                [conditions_type] => all
                [conditions] => Array
                    (
                        [0] => Array
                            (
                                [type] => apply_to
                                [args] => Array
                                    (
                                        [applies_to] => everyone
                                    )

                            )

                    )

                [collector] => Array
                    (
                        [type] => cats
                    )

                [rules] => Array
                    (
                        [0] => Array
                            (
                                [type] => percent_product
                                [amount] => 3
                            )

                    )

            )
        )
     */
    public static function getCategoryRules():array
    {
        $rules = null;

        if(is_null($rules)){
            $rules = get_option('_s_category_pricing_rules', array());
        }

        return $rules;
    }

    /**
     * Sets export progress
     *
     * @param float $progress
     * @return void
     */
    public static function setProgress(float $progress):void
    {
        update_option( 'sph_last_activity', time() );
        update_option( 'sph_export_progress', $progress );
    }

    /**
     * Returns an export progress
     *
     * @return float
     */
    public static function getProgress():float
    {
        return get_option( 'sph_export_progress', 0 );
    }

    /**
     * Returns last export activity timestamp
     *
     * @return integer
     */
    public static function getLastActivity():int
    {
        return (int) get_option('sph_last_activity', 0);
    }

    /**
     * Returns an export status object
     *
     * @return object
     * object{
     *  is_active:bool
     *  is_running:bool
     *  is_complete:bool
     *  is_dead:bool
     * }
     */
    public static function getStatus():object
    {
        $last_activity  = self::getLastActivity();
        $progress       = self::getProgress();

        $is_active      = time() - $last_activity < (1 * 60);
        $is_running     = ($progress > 0) && ($progress < 100);
        $is_complete    = $progress == 100;

        return (object) array(
            'is_active'     => $is_active,
            'is_running'    => $is_running,
            'is_complete'   => $is_complete,
            'is_dead'       => $is_running && !$is_active,
        );
    }

    /**
     * Checks export status. Is it activ and is it running
     *
     * @return void
     */
    public static function check():void
    {

        if(self::getStatus()->is_dead){
            self::start();
        }
    }

    /**
     * Deletes entries older than 1 month
     *
     * @return void
     */
    public static function cleaner():void
    {
        global $wpdb;

        $sql = <<<QQQ
        DELETE FROM `smart_price_history`
        WHERE date < ADDDATE(now(), INTERVAL -1 MONTH)
        QQQ;

        $wpdb->query($sql);
    }
}
