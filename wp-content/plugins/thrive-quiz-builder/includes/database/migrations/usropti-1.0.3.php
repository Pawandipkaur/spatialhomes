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

$users = tqb_table_name( 'users' );
$sqls  = array();

$sqls[] = "ALTER TABLE {$users} CHANGE `quiz_id` `quiz_id` BIGINT NOT NULL;";
$sqls[] = "ALTER TABLE {$users} ADD INDEX(`quiz_id`);";
$sqls[] = "ALTER TABLE {$users} ADD INDEX(`completed_quiz`);";

foreach ( $sqls as $sql ) {
	if ( $wpdb->query( $sql ) === false ) {
		return false;
	}
}

return true;
