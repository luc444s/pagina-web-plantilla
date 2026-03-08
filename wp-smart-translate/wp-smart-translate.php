<?php
/**
 * Plugin Name: WP Smart Translate
 * Plugin URI: https://example.com/wp-smart-translate
 * Description: Traduce texto visible renderizado usando un servidor externo compatible con LibreTranslate.
 * Version: 1.0.0
 * Author: WP Smart Translate Team
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: wp-smart-translate
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('WP_SMART_TRANSLATE_VERSION', '1.0.0');
define('WP_SMART_TRANSLATE_FILE', __FILE__);
define('WP_SMART_TRANSLATE_PATH', plugin_dir_path(__FILE__));
define('WP_SMART_TRANSLATE_URL', plugin_dir_url(__FILE__));

require_once WP_SMART_TRANSLATE_PATH . 'includes/Core/Bootstrap.php';

\WpSmartTranslate\Core\Bootstrap::init();
