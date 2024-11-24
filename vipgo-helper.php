<?php
/**
 * WordPress VIP platform-specific code
 * 
 * This file is not included by the plugin, but is included if the plugin is run on the WordPress VIP platform.
 * 
 * @package Automattic/MaintenanceMode
 */

add_filter( 'vip_maintenance_mode_respond_503', 'vip_maintenance_mode_do_not_respond_503_for_services', 30 );
/**
 * Prevent the Maintenance Mode plugin returning a 503 HTTP status to Nagios and Jetpack.
 *
 * Maintenance Mode sets a 503 header on page requests if Maintenance Mode is enabled and this leads to Nagios
 * reporting lots of server errors and Jetpack not being able to verify connection status for sites that are just in maintenance_mode. This function sets the filter
 * response that Maintenance Mode uses to determine if it should set the 503 status header or not.
 *
 * @param bool $should_set_503 Whether Maintenance Mode should set a 503 header.
 * @return bool Indiciate whether a Maintenance Mode sets a 503 header.
 */
function vip_maintenance_mode_do_not_respond_503_for_services( $should_set_503 ): bool {
	// phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___SERVER__HTTP_USER_AGENT__, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$user_agent = empty( $_SERVER['HTTP_USER_AGENT'] ) ? '' : $_SERVER['HTTP_USER_AGENT'];

	// The request comes from Nagios so deny the 503 header being set.
	// Desktop checks use something like `check_http/v2.2.1 (nagios-plugins 2.2.1)`.
	// Mobile checks use `iphone`.
	// Utilize helper function vip_is_jetpack_request if available.
	if ( false !== strpos( $user_agent, 'check_http' ) || 'iphone' === $user_agent || ( function_exists( 'vip_is_jetpack_request' ) && vip_is_jetpack_request() ) ) {
		return false;
	}

	return $should_set_503;
}
