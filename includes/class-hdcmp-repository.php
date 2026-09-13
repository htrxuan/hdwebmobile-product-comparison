<?php

namespace htrxuan\hdcmp;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The only class that touches comparison-list storage. Logged-in visitors are stored in a
 * single user meta key; guests are stored entirely in a first-party cookie -- mirrors this
 * suite's already-established Wishlist storage pattern.
 *
 * Several competing product-comparison plugins accepted a product id from an AJAX request or a
 * stored list and returned/rendered whatever post existed at that id, without re-checking that
 * it was still a real, published, publicly visible WooCommerce product (CWE-862/CWE-200 --
 * broken access control leading to information disclosure). That let a comparison request
 * surface a draft, a trashed, or a deliberately hidden-from-catalog product's name, price, and
 * image to a visitor who should never have been able to see them at all -- and in the worst
 * cases, a crafted id for an unrelated, non-product post type.
 *
 * This plugin re-validates every stored id, every single time the list is read, through
 * is_valid_product() -- never trusting that a value already sitting in the cookie or user meta
 * is still safe to show. An id that no longer checks out (deleted, unpublished, or hidden from
 * the catalog since it was added) is silently dropped from both what's displayed AND what's
 * re-saved, so the comparison list self-heals rather than accumulating stale, unlisted ids.
 */
class HDCMP_Repository
{
    const META_KEY    = '_hdcmp_products';
    const COOKIE_KEY  = 'hdcmp_compare';
    const COOKIE_DAYS = 30;

    public static function get_max_items()
    {
        $settings = get_option('hdcmp_settings', array());
        $max      = is_array($settings) && isset($settings['max_items']) ? absint($settings['max_items']) : 4;
        return $max > 0 ? $max : 4;
    }

    public static function save_settings($input)
    {
        $max = isset($input['max_items']) ? absint($input['max_items']) : 4;
        if ($max < 2) {
            $max = 2;
        }
        if ($max > 10) {
            $max = 10;
        }
        $settings = array('max_items' => $max);
        update_option('hdcmp_settings', $settings);
        return $settings;
    }

    /**
     * The one and only gate a product id must pass before this plugin will ever show it. Takes
     * only an id -- there is no way to bypass this check by supplying anything else.
     */
    public static function is_valid_product($product_id)
    {
        $product_id = absint($product_id);
        if (!$product_id || 'product' !== get_post_type($product_id)) {
            return false;
        }
        $product = wc_get_product($product_id);
        return $product instanceof \WC_Product && 'publish' === get_post_status($product_id) && $product->is_visible();
    }

    /**
     * Always re-validates and self-heals: the returned list is exactly what's safe to display
     * right now, and (if anything was pruned) that pruned list is written back to storage too.
     */
    public function get_products()
    {
        $raw     = $this->get_raw_ids();
        $valid   = array_values(array_filter($raw, array(__CLASS__, 'is_valid_product')));

        if (count($valid) !== count($raw)) {
            $this->save_ids($valid);
        }

        return $valid;
    }

    public function toggle($product_id)
    {
        $product_id = absint($product_id);
        if (!self::is_valid_product($product_id)) {
            return array('added' => false, 'count' => count($this->get_products()));
        }

        $products = $this->get_products();
        $index    = array_search($product_id, $products, true);

        if (false !== $index) {
            unset($products[$index]);
            $products = array_values($products);
            $added    = false;
        } elseif (count($products) >= self::get_max_items()) {
            return array('added' => false, 'count' => count($products), 'limit_reached' => true);
        } else {
            $products[] = $product_id;
            $added      = true;
        }

        $this->save_ids($products);
        return array('added' => $added, 'count' => count($products));
    }

    public function clear()
    {
        $this->save_ids(array());
    }

    private function get_raw_ids()
    {
        if (is_user_logged_in()) {
            $products = get_user_meta(get_current_user_id(), self::META_KEY, true);
            return is_array($products) ? array_map('absint', $products) : array();
        }
        return $this->get_guest_ids();
    }

    private function save_ids($products)
    {
        $products = array_values(array_unique(array_map('absint', $products)));

        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), self::META_KEY, $products);
            return;
        }
        $this->set_guest_cookie($products);
    }

    private function get_guest_ids()
    {
        if (empty($_COOKIE[self::COOKIE_KEY])) {
            return array();
        }
        $raw = sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE_KEY]));
        return array_filter(array_map('absint', explode(',', $raw)));
    }

    private function set_guest_cookie($products)
    {
        $value  = implode(',', $products);
        $expire = time() + self::COOKIE_DAYS * DAY_IN_SECONDS;
        setcookie(self::COOKIE_KEY, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true);
        $_COOKIE[self::COOKIE_KEY] = $value;
    }
}
