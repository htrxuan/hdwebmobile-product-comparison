<?php

namespace htrxuan\hdcmp;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders the "Compare" toggle link on shop-loop/single-product pages, handles the resulting
 * request, and renders the [hdcmp_compare] comparison table -- always through
 * HDCMP_Repository::get_products(), so only currently-valid products can ever appear.
 */
final class HDCMP_Frontend
{

    private static $instance = null;
    private static $repository;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        self::$repository = new HDCMP_Repository();

        add_action('template_redirect', array($this, 'maybe_toggle'), 5);
        add_action('woocommerce_after_shop_loop_item', array($this, 'render_toggle_link'));
        add_action('woocommerce_after_add_to_cart_button', array($this, 'render_toggle_link'));
        add_shortcode('hdcmp_compare', array($this, 'render_compare_table'));
    }

    public function maybe_toggle()
    {
        if (is_admin() || empty($_GET['hdcmp_toggle'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- toggling a product in/out of the visitor's OWN comparison list (cookie or own user meta) is not a state change to anyone else's data; every id is independently re-validated by HDCMP_Repository regardless of this link.
            return;
        }

        $product_id = absint(wp_unslash($_GET['hdcmp_toggle'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- see above.
        self::$repository->toggle($product_id);

        $redirect = wp_get_referer();
        wp_safe_redirect($redirect ? $redirect : home_url('/'));
        exit;
    }

    public function render_toggle_link()
    {
        global $product;
        if (!$product instanceof \WC_Product) {
            return;
        }
        $products = self::$repository->get_products();
        $in_list  = in_array($product->get_id(), $products, true);
        $url      = add_query_arg('hdcmp_toggle', $product->get_id());
        $label    = $in_list
            ? __('Remove from comparison', 'hdwebmobile-product-comparison')
            : __('Add to comparison', 'hdwebmobile-product-comparison');
        echo '<a class="hdcmp-toggle" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
    }

    public function render_compare_table()
    {
        $products = self::$repository->get_products();
        if (empty($products)) {
            return '<p class="hdcmp-empty">' . esc_html__('You have no products to compare yet.', 'hdwebmobile-product-comparison') . '</p>';
        }

        ob_start();
        echo '<table class="hdcmp-table">';
        echo '<tr class="hdcmp-row-image">';
        foreach ($products as $product_id) {
            $product = wc_get_product($product_id);
            echo '<td>' . wp_kses_post($product->get_image()) . '<br />';
            echo '<a href="' . esc_url(get_permalink($product_id)) . '">' . esc_html($product->get_name()) . '</a><br />';
            echo '<a class="hdcmp-remove" href="' . esc_url(add_query_arg('hdcmp_toggle', $product_id)) . '">' . esc_html__('Remove', 'hdwebmobile-product-comparison') . '</a>';
            echo '</td>';
        }
        echo '</tr>';
        echo '<tr class="hdcmp-row-price">';
        foreach ($products as $product_id) {
            $product = wc_get_product($product_id);
            echo '<td>' . wp_kses_post($product->get_price_html()) . '</td>';
        }
        echo '</tr>';
        echo '</table>';
        return ob_get_clean();
    }
}
