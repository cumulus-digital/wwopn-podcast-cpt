<?php
/**
 * Genre features admin screen
 */
namespace WWOPN_Podcast;

require_once BASEPATH . '/traits/features.trait.php';

class GenreFeature {

	use FeaturesTrait;

	static function init() {

		self::$screen = 'wpn_features_genres';

		self::$option_key = '_' . PREFIX . '_features_genres';

		\add_action( 'admin_menu', [__CLASS__, 'addAdminMenu'] );

		\add_action('admin_init', [__CLASS__, 'saveFeature']);

		self::parentInit();

	}

	static function addAdminMenu() {
		\add_submenu_page(
			'edit.php?post_type=' . PREFIX,
			esc_html__('Podcasts Genre Features'),
			esc_html__('Genre Feature'),
			'edit_published_posts',
			self::$screen,
			[__CLASS__, 'showAdminPage']
		);
	}

	static function showAdminPage() {
		global $title;

		$key = PREFIX . '_features';

		$genre_id = null;
		$feature_ids = [];

		if (testGetValue('genre_id', true)) {
			$genre_id = cast($_GET['genre_id'], 'int');
			if ($genre_id > 0) {
				$genre = \get_term($genre_id);
				if (\is_wp_error($genre)) {
					\wp_die('Requested a genre which does not exist.');
				}
				$feature_ids = \get_term_meta($genre_id, self::$option_key, true);
				if ( ! $feature_ids) {
					$feature_ids = [];
				}
			} else {
				$genre_id = null;
			}
		}

		$features = self::getFeatures($feature_ids, 5);

		require_once BASEPATH . '/assets/features/template.admin.features.genres.php';
		require_once BASEPATH . '/assets/post_chooser/template.findpost.php';
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

		if ($_POST[$key] !== 'genre') {
			return;
		}

		$index_key = $key . '-id';
		if ( ! testPostValue($index_key)) {
			return;
		}

		if (testPostValue('genre_id')) {
			$genre_id = cast($_POST['genre_id'], 'int');

			$genre = \get_term($genre_id);
			if (\is_wp_error($genre)) {
				\wp_die('Requested a genre which does not exist.');
			}

			\update_term_meta($genre_id, self::$option_key, $_POST[$index_key]);

			\add_action('admin_notices', function(){
				?>
				<div class="notice notice-success is-dismissible"">
					<p>
						Configuration has been saved.
					</p>
				</div>
				<?php
			});

			return;
		}

		\wp_die('Something went wrong!');
	}

}

GenreFeature::init();