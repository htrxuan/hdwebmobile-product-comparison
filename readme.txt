=== HDWebmobile Product Comparison ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, product comparison, compare products, shopping
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Compare products side by side -- only real, published, publicly visible products are ever shown, re-checked fresh every time.

== Description ==

HDWebmobile Product Comparison lets shoppers add products to a comparison list from the shop or a product page, then view them side by side with the `[hdcmp_compare]` shortcode.

= Security by design =
A common weakness in product-comparison tools is accepting a product id from a request or a stored list and rendering whatever post exists at that id, without re-checking it is still a real, published, publicly visible WooCommerce product (CWE-862 / CWE-200, broken access control leading to information disclosure). That lets a comparison request surface a draft, a trashed post, or a product deliberately hidden from the catalog -- and in the worst cases, a completely unrelated post type.

This plugin re-validates every stored product id, every single time the list is read, rather than trusting whatever is already sitting in the cookie or account:

* A product only ever appears if it is currently a real, published, WooCommerce product that isn't hidden from the catalog -- checked fresh on every read, never assumed from what was true when it was added.
* If a product is removed, unpublished, or hidden after being added, it silently disappears from the comparison the next time the list is loaded, and the stored list is corrected to match -- it never lingers as a stale, invisible entry.

= Key Features =
* A simple "Add to comparison" / "Remove" link on shop and product pages
* A `[hdcmp_compare]` shortcode for a side-by-side comparison table
* Works for guests (a cookie) and logged-in customers (their own account) alike
* A configurable maximum number of products to compare at once

= Limitations (please read before installing) =
* Compares name, image, and price only in this version -- no custom attribute rows
* No AJAX "compare" widget/popup -- the toggle link and shortcode table are plain page content

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-product-comparison` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. Add `[hdcmp_compare]` to any page, then go to **WooCommerce > HDWebmobile > Product Comparison** to set the maximum number of products.

== How to Use ==

= 1. Add products to compare =
Click "Add to comparison" on any shop or product page.

= 2. View the comparison =
Visit the page where you've placed the `[hdcmp_compare]` shortcode.

== Screenshots ==

1. The "Add to comparison" link on a product.
2. The comparison table from the [hdcmp_compare] shortcode.

== Changelog ==

= 1.0.0 =
* Initial release: guest- and account-based product comparison, with every listed product re-validated on every read.
