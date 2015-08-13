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
 * Add meta data field to a term.
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

# FAQ

### Does this create new database tables?

Yes. It creates a new `wp_terms` database table for each site it's activated on.

### Does this modify existing database tables?

No. All of WordPress's core database tables remain untouched.

### Where can I get support? =

The WordPress support forums: https://wordpress.org/tags/wp-term-meta/

### Where can I find documentation? =

### Can I contribute?

Yes, please! The number of users needing taxonomy term metadata is growing fast. Having an easy-to-use API and powerful set of functions is critical to managing complex WordPress installations. If this is your thing, please help us out!
