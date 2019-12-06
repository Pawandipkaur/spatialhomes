<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/27/2018
 * Time: 5:07 PM
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

global $wpdb;

$users = tqb_table_name( 'user_answers' );
$sqls  = array();

$sqls[]    = " ALTER TABLE {$users} ADD `answer_text` TEXT NULL DEFAULT NULL AFTER `quiz_id`;";

foreach ( $sqls as $sql ) {
	if ( $wpdb->query( $sql ) === false ) {
		return false;
	}
}

return true;
