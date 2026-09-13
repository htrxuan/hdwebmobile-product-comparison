# HDWebmobile Product Comparison

Compare products side by side -- only real, published, publicly visible products are ever shown, re-checked fresh every time.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-product-comparison/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

Lets shoppers add products to a comparison list from the shop or a product page, then view them side by side with the `[hdcmp_compare]` shortcode.

## Why this plugin exists

Several competing product-comparison plugins accepted a product id from a request or a stored list and rendered whatever post existed at that id, without re-checking it was still a real, published, publicly visible WooCommerce product (CWE-862 / CWE-200, broken access control leading to information disclosure). That let a comparison request surface a draft, a trashed post, or a product deliberately hidden from the catalog -- and in the worst cases, a completely unrelated post type.

This plugin re-validates every stored product id, every single time the list is read, rather than trusting whatever is already sitting in the cookie or account:

* A product only ever appears if it is currently a real, published, WooCommerce product that isn't hidden from the catalog -- checked fresh on every read, never assumed from what was true when it was added.
* If a product is removed, unpublished, or hidden after being added, it silently disappears from the comparison the next time the list is loaded, and the stored list is corrected to match -- it never lingers as a stale, invisible entry.

## Features

* A simple "Add to comparison" / "Remove" link on shop and product pages
* A `[hdcmp_compare]` shortcode for a side-by-side comparison table
* Works for guests (a cookie) and logged-in customers (their own account) alike
* A configurable maximum number of products to compare at once

## Limitations

* Compares name, image, and price only in this version -- no custom attribute rows
* No AJAX "compare" widget/popup -- the toggle link and shortcode table are plain page content

## Installation

1. Upload to `/wp-content/plugins/hdwebmobile-product-comparison`, or install through the WordPress plugins screen.
2. Activate. WooCommerce must already be installed and active.
3. Add `[hdcmp_compare]` to any page, then go to **WooCommerce > HDWebmobile > Product Comparison** to set the maximum.

## Development

```
includes/
  class-hdcmp-activator.php   activation check
  class-hdcmp-core.php        bootstraps admin + frontend
  class-hdcmp-repository.php  storage + the ONLY validity gate a product must pass -- see its docblock for the security design
  class-hdcmp-admin.php       hub tab and settings form
  class-hdcmp-frontend.php    toggle link, request handler, [hdcmp_compare] shortcode
  class-hdcmp-hub.php         shared HDWebmobile admin hub (see the suite's other plugins)
```

## License

GPLv2 or later — https://www.gnu.org/licenses/gpl-2.0.html
