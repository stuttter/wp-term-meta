# WP Term Meta

Metadata, for taxonomy terms.

WP Term Meta allows developers to store key/value pairs of data along with a category, tag, or any custom taxonomy.

# Installation

* Download and install using the built in WordPress plugin installer.
* Activate in the "Plugins" network admin panel using the "Activate" link.
* When activating for an entire network, each site will have it's own taxonomy term metadata.

# Usage

### add_meta_data()

```
/**
 * Add metadata field to a term.
 *
 * @since 0.1.0
 * @param  int     $term_id     Post ID
 * @param  string  $meta_key    Metadata name
 * @param  mixed   $meta_value  Metadata value
 * @param  bool    $unique      Optional, default is false. Whether the same key
 *                              can be duplicated
 *
 * @return bool False for failure. True for success.
 */
```

### delete_meta_data()

```
/**
 * Remove metadata matching criteria from a term.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key if needed.
 *
 * @since 0.1.0
 *
 * @param  int     $term_id    Term ID
 * @param  string  $meta_key   Metadata name
 * @param  mixed   $meta_value Optional. Metadata value
 *
 * @return bool False for failure. True for success.
 */
```

### delete_term_meta_by_key()

```
/**
 * Delete everything from term meta matching meta key.
 *
 * @since 0.1.0
 *
 * @param string $term_meta_key Key to search for when deleting.
 *
 * @return bool Whether the term meta key was deleted from the database.
 */
```

### get_term_meta()

```
/**
 * Retrieve term meta field for a term.
 *
 * @since 0.1.0
 *
 * @param  int     $term_id  Term ID
 * @param  string  $key      The meta key to retrieve
 * @param  bool    $single   Whether to return a single value
 *
 * @return mixed Will be an array if $single is false. Will be value of meta
 *               data field if $single is true
 */
```

### update_term_meta()

```
/**
 * Update term meta field based on term ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and term ID.
 *
 * If the meta field for the term does not exist, it will be added.
 *
 * @since 0.1.0
 *
 * @param  int    $term_id     Term ID
 * @param  string $meta_key    Metadata key
 * @param  mixed  $meta_value  Metadata value
 * @param  mixed  $prev_value  Optional. Previous value to check before removing
 *
 * @return bool False on failure, true if success.
 */
```

### get_terms()

Use the `meta_query` argument according te the `WP_Meta_Query` specification:
http://codex.wordpress.org/Class_Reference/WP_Meta_Query

```
$terms = get_terms( 'category', array(
	'depth'      => 1,
	'number'     => 100,
	'parent'     => 0,
	'orderby'    => 'order', // Try the "wp-term-order" plugin!
	'order'      => 'ASC',
	'hide_empty' => false,
	'meta_query' => array( array(
		'key' => 'term_thumbnail'
	) )
) );
```

# FAQ

### Does this create new database tables?

Yes. It creates a new `wp_termmeta` database table for each site it's activated on.

### Does this modify existing database tables?

No. All of WordPress's core database tables remain untouched.

### Does this support querying by metadata?

Yes. It uses the `WP_Meta_Query` class to create the necessary MySQL. You can interface with it by passing a `meta_query` argument into `get_terms()`, by filtering `get_terms_args` or however else you choose.

### Where can I get support?

The WordPress support forums: https://wordpress.org/tags/wp-term-meta/

### Can I contribute?

Yes, please! The number of users needing taxonomy term metadata is growing fast. Having an easy-to-use API and powerful set of functions is critical to managing complex WordPress installations. If this is your thing, please help us out!
