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

$questions = tge_table_name( 'questions' );
$sqls      = array();
$sqls[]    = " ALTER TABLE {$questions} ADD `settings` TEXT NULL DEFAULT NULL AFTER `description`;";

foreach ( $sqls as $sql ) {
	if ( $wpdb->query( $sql ) === false ) {
		return false;
	}
}

return true;
