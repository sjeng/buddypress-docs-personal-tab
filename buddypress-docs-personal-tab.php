<?php
/*
Plugin Name: BuddyPress Docs Personal Tab
Version: 0.1-alpha
Description: Personal Tab for BuddyPress Docs - for Shands Healthcare
Author: Boone Gorges
Text Domain: buddypress-docs-personal-tab
Domain Path: /languages
*/

// The slug used for the Personal tab
if ( ! defined( 'BP_DOCS_PERSONAL_SLUG' ) ) {
	define( 'BP_DOCS_PERSONAL_SLUG', 'personal' );
}

/**
 * Set up Personal nav item.
 */
function bpdpt_setup_nav() {
	bp_core_new_subnav_item( array(
		'name'            => _x( 'Personal', 'Personal Docs tab name', 'bp-docs' ),
		'slug'            => BP_DOCS_PERSONAL_SLUG,
		'parent_url'      => bp_displayed_user_domain() . bp_docs_get_docs_slug() . '/',
		'parent_slug'     => bp_docs_get_docs_slug(),
		'screen_function' => array( buddypress()->bp_docs, 'template_loader' ),
		'position'        => 50,
		'user_has_access' => bp_is_my_profile(),
	) );
}
add_action( 'bp_bp_docs_setup_nav', 'bpdpt_setup_nav' );

/**
 * Ensure that Personal tab shows folders from the current user, and don't show
 * folders on Started and Edited pages.
 */
function bpdpt_filter_bp_docs_get_folders_args( $r ) {
	if ( bp_is_user() && bp_is_current_action( BP_DOCS_PERSONAL_SLUG ) ) {
		$r['user_id'] = bp_displayed_user_id();
	}

	if ( ! bp_docs_is_folder_manage_view() && ( bp_docs_is_started_by() || bp_docs_is_edited_by() ) ) {
		$r['include'] = array( 0 );
	}

	return $r;
}
add_filter( 'bp_before_bp_docs_get_folders_parse_args', 'bpdpt_filter_bp_docs_get_folders_args' );

/**
 * Ensure that Personal tab shows Docs from the current user.
 */
function bpdpt_filter_bp_docs_has_docs_args( $r ) {
	if ( bp_is_user() && bp_is_current_action( BP_DOCS_PERSONAL_SLUG ) ) {
		$r['author_id'] = bp_displayed_user_id();
		$r['group_id'] = array();
	}

	return $r;
}
add_filter( 'bp_before_bp_docs_has_docs_parse_args', 'bpdpt_filter_bp_docs_has_docs_args' );

/**
 * Remove the Group column from the Personal page.
 */
function bpdpt_remove_group_column() {
	if ( bp_is_user() && bp_is_current_action( BP_DOCS_PERSONAL_SLUG ) ) {
		remove_filter( 'bp_docs_loop_additional_th', array( buddypress()->bp_docs->groups_integration, 'groups_th' ), 5 );
		remove_filter( 'bp_docs_loop_additional_td', array( buddypress()->bp_docs->groups_integration, 'groups_td' ), 5 );
	}
}
add_action( 'bp_screens', 'bpdpt_remove_group_column' );

/**
 * Remove the Group column from the Personal page.
 */
function bpdpt_remove_author_column() {
	if ( bp_is_user() && bp_is_current_action( BP_DOCS_PERSONAL_SLUG ) ) {
		remove_filter( 'bp_docs_loop_additional_th', array( buddypress()->bp_docs->groups_integration, 'groups_th' ), 5 );
		remove_filter( 'bp_docs_loop_additional_td', array( buddypress()->bp_docs->groups_integration, 'groups_td' ), 5 );
	}
}
add_action( 'bp_screens', 'bpdpt_remove_author_column' );

/**
 * Enqueue assets
 */
function bpdpt_enqueue_assets() {
	if ( bp_is_user() && bp_is_current_action( BP_DOCS_PERSONAL_SLUG ) ) {
		wp_enqueue_style( 'bpdpt', plugins_url( 'buddypress-docs-personal-tab/bpdpt.css' ) );
		wp_enqueue_script( 'bpdpt', plugins_url( 'buddypress-docs-personal-tab/bpdpt.js' ), array( 'bp-docs-folders' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'bpdpt_enqueue_assets' );

/**
 * Add Personal information to directory breadcrumbs.
 *
 * @since 1.9.0
 *
 * @param array $crumbs
 * @return array
 */
function bp_docs_personal_directory_breadcrumb( $crumbs ) {
	if ( bp_is_user() && bp_is_current_action( BP_DOCS_PERSONAL_SLUG ) ) {
		$user_crumbs = array(
			sprintf(
				'<a href="%s">%s</a>',
				bp_displayed_user_domain() . bp_docs_get_docs_slug() . '/' . BP_DOCS_PERSONAL_SLUG . '/',
				__( 'Personal', 'bp-docs' )
			),
		);

		$crumbs = array_merge( $user_crumbs, $crumbs );
	}

	return $crumbs;
}
add_filter( 'bp_docs_directory_breadcrumb', 'bp_docs_personal_directory_breadcrumb', 1 );

/**
 * If there's a folder selected, set Current Action to 'personal'.
 *
 * This helps set the breadcrumbs and ensure the proper 'current' state for
 * nav items.
 */
function bpdpt_current_action() {
	if ( bp_docs_is_started_by() && ! empty( $_GET['folder'] ) ) {
		buddypress()->current_action = BP_DOCS_PERSONAL_SLUG;
	}
}
add_filter( 'bp_setup_nav', 'bpdpt_current_action', 9999 );

/**
 * Don't show the Global option on the folder type selector dropdown.
 */
function bpdpt_folder_type_selector( $s ) {
	$s = preg_replace( '|<option.*?value=\"global.*?/option>|', '', $s );
	return $s;
}
add_filter( 'bp_docs_folder_type_selector', 'bpdpt_folder_type_selector' );