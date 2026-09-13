<?php

namespace htrxuan\hdcmp;

if (!defined('ABSPATH')) {
    exit;
}

class HDCMP_Admin
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        require_once HDCMP_PLUGIN_DIR . 'includes/class-hdcmp-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['product-comparison'] = array(
            'label'  => __('Product Comparison', 'hdwebmobile-product-comparison'),
            'order'  => 159,
            'render' => array($this, 'render_page'),
        );
        return $tabs;
    }

    public function register_settings()
    {
        register_setting('hdcmp_settings_group', 'hdcmp_settings', array('htrxuan\\hdcmp\\HDCMP_Repository', 'save_settings'));
    }

    public function render_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to do this.', 'hdwebmobile-product-comparison'));
        }

        $max_items = HDCMP_Repository::get_max_items();
        ?>
        <p>
            <?php
            printf(
                /* translators: %s: the [hdcmp_compare] shortcode */
                esc_html__('Add %s to any page to show the comparison table. Every product shown is re-checked to still be real, published, and publicly visible each time the table is rendered.', 'hdwebmobile-product-comparison'),
                '<code>[hdcmp_compare]</code>'
            );
            ?>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields('hdcmp_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="hdcmp_max_items"><?php esc_html_e('Maximum products to compare', 'hdwebmobile-product-comparison'); ?></label></th>
                    <td><input type="number" id="hdcmp_max_items" name="hdcmp_settings[max_items]" min="2" max="10" step="1" value="<?php echo esc_attr($max_items); ?>" style="width:100px;" /></td>
                </tr>
            </table>
            <?php submit_button(__('Save Settings', 'hdwebmobile-product-comparison')); ?>
        </form>
        <?php
    }
}
