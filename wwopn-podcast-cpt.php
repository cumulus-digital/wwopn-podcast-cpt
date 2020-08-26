<?php
/**
* Plugin Name: WWOPN Podcast Custom Post Type
* Plugin URI: github.com/cumulus-digital/wwopn-podcast-cpt
* GitHub Plugin URI: cumulus-digital/wwopn-podcast-cpt
* Description: A plugin to create a custom post type for Podcasts
* Version:  1.64
* Author: Daniel Vena
* Author URI: westwoodone.com
* License: GPL2
*/
namespace WWOPN_Podcast;

const PLUGIN_NAME = 'wwopn-podcast-cpt';
const PREFIX = 'wpn_podcast';
const TXTDOMAIN = PREFIX;
const BASEPATH = __DIR__;
const BASE_FILENAME = PLUGIN_NAME . DIRECTORY_SEPARATOR . PLUGIN_NAME . '.php';

require_once BASEPATH . '/helpers.php';

require_once BASEPATH . '/options.php';

require_once BASEPATH . '/traits/custom_meta.trait.php';
require_once BASEPATH . '/cpt.php';
require_once BASEPATH . '/genre.php';
require_once BASEPATH . '/tag.php';

require_once BASEPATH . '/features.big.php';
require_once BASEPATH . '/features.genre.php';

require_once BASEPATH . '/shortcodes.php';

require_once BASEPATH . '/admin_inits.php';

/**
 * Flush permalinks on activation
 */
function plugin_activation() {
	if ( ! \get_option('permalink_structure')) {
		die(
			'<p style="font-family:sans-serif">' .
			sprintf(__('Podcast Posts requires a <a href="%s" target="_top">permalink structure</a> be set to something other than "Plain".'), \admin_url('options-permalink.php'))
		);
	}

	// Flush permalinks after activation
	\add_action( 'admin_init', 'flush_rewrite_rules', 20 );

}
\register_activation_hook( __FILE__, __NAMESPACE__ . '\plugin_activation');

/**
 * Ensure a permalink structure exists,
 * otherwise display an error on all admin pages
 */
function plugin_checkPermalinks() {
	if (\get_option('permalink_structure')) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p>
		<?=sprintf(__('Podcast Posts requires a <a href="%s">permalink structure</a> be set to something other than "Plain".'), \admin_url('options-permalink.php'))?>
		</p>
	</div>
	<?php
}
\add_action( 'admin_notices', __NAMESPACE__ . '\plugin_checkPermalinks' );

function enqueue_styles() {
	\wp_register_style(
		PREFIX . '_style_features',
		\plugin_dir_url(__FILE__) . 'assets/features/styles.css'
	);
	\wp_enqueue_style(PREFIX . '_style_features');
}
\add_action('wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_styles');