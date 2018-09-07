<?php
/**
 * Misc extras to run at admin_init
 */
namespace WWOPN_Podcast;

function admin_extra_inits() {

	/**
	 * Lower Yoast metabox in editor
	 */
	\add_filter('wpseo_metabox_prio', function() {
		return 'low';
	}, 10, 1);

}

\add_action('admin_init', 'WWOPN_Podcast\admin_extra_inits', 10);