<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 16-Apr-19
 * Time: 10:59 AM
 */

/**
 * Class TVA_Membermouse_Integration
 * - implements TVA_Integration methods
 */
class TVA_Membermouse_Integration extends TVA_Integration {

	protected function init_items() {

		$items = array();

		if ( class_exists( 'MM_MembershipLevel' ) ) {

			$membership_levels = MM_MembershipLevel::getMembershipLevelsList();

			if ( ! empty( $membership_levels ) && is_array( $membership_levels ) ) {

				foreach ( $membership_levels as $id => $name ) {
					try {
						$items[] = new TVA_Integration_Item( $id, $name );
					} catch ( Exception $e ) {

					}
				}
			}
		}

		$this->set_items( $items );
	}

	protected function _get_item_from_membership( $key, $value ) {

		$membership = new MM_MembershipLevel( $value );

		return new TVA_Integration_Item( (int) $membership->getId(), $membership->getName() );
	}

	public function is_rule_applied( $rule ) {

		$applied = false;

		if ( function_exists( 'mm_member_decision' ) ) {

			foreach ( $rule['items'] as $item ) {

				$applied = mm_member_decision( array( 'membershipId' => $item['id'] ) );

				if ( $applied ) {
					break;
				}
			}
		}

		return $applied;
	}

	public function trigger_no_access() {

		$url = MM_CorePageEngine::getUrl( MM_CorePageType::$ERROR, MM_Error::$ACCESS_DENIED );

		if ( ! empty( $url ) ) {
			wp_redirect( $url );
		}
	}
}
