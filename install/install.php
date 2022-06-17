<?php
global $wpdb;

update_option( 'smartexport_progress', 0 );

$sql = file_get_contents(__DIR__ . '/install.sql');
$wpdb->query( $wpdb->prepare( $sql ) );

wp_schedule_single_event( time(), 'smart_price_history_export', array(), true );
