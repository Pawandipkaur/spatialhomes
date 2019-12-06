<?php
/**
 * Thrive Themes  https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! defined( 'TGE_DB_UPGRADING' ) ) {
	return;
}

global $wpdb;

$answers   = tge_table_name( 'answers' );
$sqls      = array();
$sqls[]    = " ALTER TABLE {$answers} ADD `feedback` TEXT NULL DEFAULT NULL AFTER `result_id`;";

foreach ( $sqls as $sql ) {
	if ( $wpdb->query( $sql ) === false ) {
		return false;
	}
}

return true;
