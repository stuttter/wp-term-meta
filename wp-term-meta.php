<?php
/**
 * Plugin Name: WP Term Meta
 * Plugin URI:  https://wordpress.org/plugins/wp-term-meta/
 * Description: Metadata, for taxonomy terms.
 * Author:      John James Jacoby
 * Version:     0.1.0
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPL2
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Term_Order' ) ) :
/**
 * Main WP Term Order class
 *
 * @link https://make.wordpress.org/core/2013/07/28/potential-roadmap-for-taxonomy-meta-and-post-relationships/ Taxonomy Roadmap
 * @link http://core.trac.wordpress.org/ticket/10142 Term meta discussion
 *
 * @since 0.1.0
 */
final class WP_Term_Meta {

	/**
	 * @var string Plugin version
	 */
	public $version = '0.1.0';

	/**
	 * @var string Database version
	 */
	public $db_version = '201508110001';

	/**
	 * @var string Database version key
	 */
	public $db_version_key = 'wpdb_termmeta_version';

	/**
	 * @var string File for plugin
	 */
	public $file = '';

	/**
	 * @var string URL to plugin
	 */
	public $url = '';

	/**
	 * @var string Path to plugin
	 */
	public $path = '';

	/**
	 * @var string Basename for plugin
	 */
	public $basename = '';

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		// Activation hook
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Setup plugin
		$this->file     = __FILE__;
		$this->url      = plugin_dir_url( $this->file );
		$this->path     = plugin_dir_path( $this->file );
		$this->basename = plugin_basename( $this->file );

		// Force `termmeta` on to $wpdb global
		add_action( 'init',           array( $this, 'modify_wpdb' ) );
		add_action( 'switch_to_blog', array( $this, 'modify_wpdb' ) );

		// New site creation
		add_action( 'wpmu_new_blog',  array( $this, 'new_blog' ) );

		// Add `termmeta` to array of tables to delete
		add_filter( 'wpmu_drop_tables', array( $this, 'drop_tables' ) );

		// Check if DB needs upgrading
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}
	}

	/**
	 * Administration area hooks
	 *
	 * @since 0.1.0
	 */
	public function admin_init() {

		// Check for DB update
		$this->maybe_upgrade_database();
	}

	/**
	 * Quick touchup to `$wpdb`
	 *
	 * @since 0.1.0
	 */
	public static function modify_wpdb() {
		global $wpdb;
		$wpdb->termmeta = "{$wpdb->prefix}termmeta";
	}

	/**
	 * When a new site is created, also install the `termmeta` table if this
	 * plugin is network activated on this network.
	 *
	 * @since 0.1.0
	 *
	 * @param int $site_id
	 */
	public function new_blog( $site_id = 0 ) {
		if ( is_plugin_active_for_network( $this->basename ) ) {
			$this->install( $site_id );
		}
	}

	/**
	 * Also drop the `termmeta` table
	 *
	 * @since 0.1.0
	 *
	 * @global object $wpdb
	 *
	 * @param array $tables
	 */
	public function drop_tables( $tables = array() ) {
		global $wpdb;

		// Add the `termmeta` table to the $tables array
		$tables[] = "{$wpdb->prefix}termmeta";

		return $tables;
	}

	/**
	 * Install this plugin on a specific site
	 *
	 * @since 0.1.0
	 *
	 * @param int $site_id
	 */
	public function install( $site_id = 0 ) {

		// Not switched
		$switched = false;

		// Should we switch?
		if ( false !== $site_id ) {
			$switched = true;
			switch_to_blog( $site_id );
		}

		// Run the upgrade routine directly
		$this->upgrade_database();

		// Should we switch back?
		if ( true === $switched ) {
			restore_current_blog();
		}
	}

	/**
	 * Activation hook
	 *
	 * Handles both single & multi site installations
	 *
	 * @since 0.1.0
	 *
	 * @global  object  $wpdb
	 * @param   bool    $network_wide
	 */
	public function activate( $network_wide = false ) {
		global $wpdb;

		// if activated on a particular blog, just set it up there.
		if ( false === $network_wide ) {
			$this->install();
			return;
		}

		// Bail if a large network; you already know what to do
		if ( wp_is_large_network( 'sites' ) ) {
			return;
		}

		// Install on all sites in the network
		$sql   = "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}'";
		$sites = $wpdb->get_col( $sql );
		foreach ( $sites as $site_id ) {
			$this->install( $site_id );
		}
	}

	/**
	 * Should a database update occur
	 *
	 * Runs on `admin_init`
	 *
	 * @since 0.1.0
	 */
	private function maybe_upgrade_database() {

		// Check DB for version
		$db_version = get_option( $this->db_version_key );

		// Needs
		if ( $db_version < $this->db_version ) {
			$this->upgrade_database( $db_version );
		}
	}

	/**
	 * Create the `termmeta` database table
	 *
	 * @since 0.1.0
	 *
	 * @param  int $old_version
	 *
	 * @global object $wpdb
	 */
	private function upgrade_database( $old_version = 0 ) {

		$old_version = (int) $old_version;

		// The main column alter
		if ( $old_version < 201508110005 ) {
			$this->create_termmeta_table();
		}

		// Update the DB version
		update_option( $this->db_version_key, $this->db_version );
	}

	/**
	 * Create the `termmeta` table
	 *
	 * @since 0.1.0
	 *
	 * @global object $wpdb
	 */
	private function create_termmeta_table() {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		/*
		 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
		 * As of 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
		 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
		 */
		$max_index_length = 191;

		// Check for `dbDelta`
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		dbDelta( array(
			"CREATE TABLE {$wpdb->prefix}termmeta (
				meta_id bigint(20) unsigned NOT NULL auto_increment,
				term_id bigint(20) unsigned NOT NULL default '0',
				meta_key varchar(255) default NULL,
				meta_value longtext,
				PRIMARY KEY (meta_id),
				KEY term_id (term_id),
				KEY meta_key (meta_key($max_index_length))
			) {$charset_collate};"
		) );

		// Make doubly sure the `$wpdb` global is modified
		$this->modify_wpdb();
	}
}
endif;

/**
 * Instantiate the main WordPress Term Meta class.
 *
 * @since 0.1.0
 */
function _wp_term_meta() {
	new WP_Term_Meta();
}
add_action( 'plugins_loaded', '_wp_term_meta' );


/** Functions *****************************************************************/

/**
 * Add meta data field to a term.
 *
 * @param  int     $term_id     Post ID
 * @param  string  $meta_key    Metadata name
 * @param  mixed   $meta_value  Metadata value
 * @param  bool    $unique      Optional, default is false. Whether the same key
 *                              can be duplicated
 *
 * @return bool False for failure. True for success.
 */
function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'term', $term_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove metadata matching criteria from a term.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key if needed.
 *
 * @param  int     $term_id    Term ID
 * @param  string  $meta_key   Metadata name
 * @param  mixed   $meta_value Optional. Metadata value
 *
 * @return bool False for failure. True for success.
 */
function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'term', $term_id, $meta_key, $meta_value );
}

/**
 * Retrieve term meta field for a term.
 *
 * @param  int     $term_id  Term ID
 * @param  string  $key      The meta key to retrieve
 * @param  bool    $single   Whether to return a single value
 *
 * @return mixed Will be an array if $single is false. Will be value of meta
 *               data field if $single is true
 */
function get_term_meta( $term_id, $key, $single = false ) {
	return get_metadata( 'term', $term_id, $key, $single );
}

/**
 * Update term meta field based on term ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and term ID.
 *
 * If the meta field for the term does not exist, it will be added.
 *
 * @param  int    $term_id     Term ID
 * @param  string $meta_key    Metadata key
 * @param  mixed  $meta_value  Metadata value
 * @param  mixed  $prev_value  Optional. Previous value to check before removing
 *
 * @return bool False on failure, true if success.
 */
function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'term', $term_id, $meta_key, $meta_value, $prev_value );
}

/** Term Meta Query ***********************************************************/

/**
 * Filter `term_clauses` and add support for a `meta_query`
 *
 * @param array $pieces     Terms query SQL clauses.
 * @param array $taxonomies An array of taxonomies.
 * @param array $args       An array of terms query arguments.
 *
 * @return Array of query pieces, maybe modifed
 */
function _wp_term_meta_clauses( $pieces = array(), $taxonomies = array(), $args = array() ) {

	// Maybe do a meta query
	if ( ! empty( $args['meta_query'] ) ) {

		// Make doubly sure $wpdb is prepared
		WP_Term_Meta::modify_wpdb();

		// Get the meta query parts
		$meta_query = new WP_Meta_Query( $args['meta_query'] );
		$meta_query->parse_query_vars( $args );

		// Combine pieces & meta-query clauses
		if ( ! empty( $meta_query->queries ) ) {

			/**
			 * It's possible in a future version of WordPress that our `term_id`
			 * usage might need to be swapped to `term_taxonomy_id`.
			 */
			$meta_clauses     = $meta_query->get_sql( 'term', 'tt', 'term_id', $taxonomies );
			$pieces['join']  .= $meta_clauses['join'];
			$pieces['where'] .= $meta_clauses['where'];
		}
	}

	// Return possibly modified pieces array
	return $pieces;
}
add_filter( 'terms_clauses', '_wp_term_meta_clauses', 10, 3 );

/**
 * Filter `get_terms_args` and add an empty `meta_query` argument.
 *
 * This is mostly a dumb hack to ensure that `meta_query` starts as an available
 * argument in the `$args` array, to get developers familiar with it eventually
 * maybe possibly being available all of the time.
 *
 * @since 0.1.0
 *
 * @param  array  $args  An array of get_term() arguments.
 *
 * @return array  Array of arguments with `meta_query` parameter added
 */
function _wp_get_terms_args( $args = array() ) {
	return wp_parse_args( $args, array(
		'meta_query' => ''
	) );
}
add_filter( 'get_terms_args', '_wp_get_terms_args', -999 );
