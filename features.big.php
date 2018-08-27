<?php
/**
 * Big feature admin screen
 */
namespace WWOPN_Podcast;

require_once __DIR__ . '/features.trait.php';

class BigFeature {

	use FeaturesTrait;

	static $screen = 'wpn_features_big';

	static function init() {

		self::$option_key = '_' . PREFIX . '_features_big';

		\add_action( 'admin_menu', [__CLASS__, 'addAdminMenu'] );

		\add_action('admin_init', [__CLASS__, 'saveFeature']);

		self::parentInit();

	}

	static function addAdminMenu() {
		\add_submenu_page(
			'edit.php?post_type=' . PREFIX,
			esc_html__('Podcasts Big Feature'),
			esc_html__('Big Feature'),
			'edit_published_posts',
			self::$screen,
			[__CLASS__, 'showAdminPage']
		);
	}

	static function showAdminPage() {
		global $title;

		$key = PREFIX . '_features';
		
		$feature_ids = \get_option(
			self::$option_key,
			array_fill(0,9,0)
		);

		$features = self::getFeatures($feature_ids, 9);

		require_once __DIR__ . '/assets/features/template.admin.features.big.php';
		require_once __DIR__ . '/assets/post_chooser/template.findpost.php';
	}

	static function saveFeature() {
		if ( ! isPost()) {
			return;
		}

		if ( ! \current_user_can('edit_published_pages')) {
			return;
		}

		$key = PREFIX . '_features';
		if ( ! testPostValue($key)) {
			return;
		}

		if ($_POST[$key] !== 'big') {
			return;
		}

		$index_key = $key . '-id';
		if ( ! testPostValue($index_key)) {
			return;
		}
		
		\update_option(self::$option_key, $_POST[$index_key]);

		\add_action('admin_notices', function(){
			?>
			<div class="notice notice-success is-dismissible"">
				<p>
					Configuration has been saved.
				</p>
			</div>
			<?php
		});

	}

}

BigFeature::init();