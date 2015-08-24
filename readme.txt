=== WP Term Meta ===
Contributors: johnjamesjacoby
Tags: taxonomy, term, meta, metadata
Requires at least: 4.2
Tested up to: 4.3
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Metadata, for taxonomy terms.

WP Term Meta allows developers to store key/value pairs of data along with a category, tag, or any custom taxonomy.

Also checkout:

* [WP Term Order](https://wordpress.org/plugins/wp-term-order/ "Metadata, for taxonomy terms.")
* [WP Term Colors](https://wordpress.org/plugins/wp-term-colors/ "Pretty colors for categories, tags, and other taxonomy terms.")
* [WP Term Icons](https://wordpress.org/plugins/wp-term-icons/ "Pretty icons for categories, tags, and other taxonomy terms.")
* [WP User Groups](https://wordpress.org/plugins/wp-user-groups/ "Group users together with taxonomies & terms.")
* [WP Event Calendar](https://wordpress.org/plugins/wp-event-calendar/ "Flexible events, with a calendar view.")

== Screenshots ==

1. Term Metadata Database Schema

== Installation ==

Download and install using the built in WordPress plugin installer.

Activate in the "Plugins" area of your admin by clicking the "Activate" link.

No further setup or configuration is necessary.

== Frequently Asked Questions ==

= Does this create new database tables? =

Yes. It creates a new `wp_termmeta` database table for each site it's activated on.

= Does this modify existing database tables? =

No. All of WordPress's core database tables remain untouched.

= How do I query for terms via metadata? =

With WordPress's `get_terms()` function, the same as usual, but with an additional `meta_query` argument according the `WP_Meta_Query` specification:
http://codex.wordpress.org/Class_Reference/WP_Meta_Query

```
$terms = get_terms( 'category', array(
        'depth'      => 1,
        'number'     => 100,
        'parent'     => 0,
        'orderby'    => 'order', // Try the "wp-term-order" plugin!
        'order'      => 'ASC',
        'hide_empty' => false,

        // Looky looky!
        'meta_query' => array( array(
                'key' => 'term_thumbnail'
        ) )
) );
```

= Where can I get support? =

The WordPress support forums: https://wordpress.org/tags/wp-term-meta/

= Where can I find documentation? =

http://github.com/johnjamesjacoby/wp-term-meta/

== Changelog ==

= 0.1.0 =
* Initial release
