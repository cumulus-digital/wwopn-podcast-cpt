<?php
/**
 * Feature posts admin
 */
namespace WWOPN_Podcast;

trait FeaturesTrait {

	static $screen;
	static $option_key;

	static function parentInit() {

		if (!self::$option_key) {
			die('You must set an $option_key in classes which use the FeaturesTrait.');
		}

		\add_action('admin_enqueue_scripts', [__CLASS__, 'addParentScriptsAndStyles']);

	}

	static function addParentScriptsAndStyles($hook) {

	    $current_screen = \get_current_screen();

		if ($current_screen->id !== PREFIX . '_page_' . self::$screen) {
			return;
		}

		\wp_enqueue_style('thickbox'); // needed for find posts div
		\wp_enqueue_script('thickbox'); // needed for find posts div
		\wp_enqueue_script('media');
		\wp_enqueue_script('wp-ajax-response');

		\wp_enqueue_script( 'swappable', 'https://cdn.jsdelivr.net/npm/@shopify/draggable@1.0.0-beta.7/lib/swappable.js');
		\wp_enqueue_script( PREFIX . '_feature_scripts', \plugin_dir_url(BASE_FILENAME) . 'assets/features/scripts.admin.js', ['wp-api', 'swappable'] );
		\wp_enqueue_style( PREFIX . '_feature_styles', \plugin_dir_url(BASE_FILENAME) . 'assets/features/styles.css' );
	}

	static function getFeatures(array $ids, int $max = 5, $fill_empties = true) {

		$empty = \is_admin() ?
						\plugin_dir_url(BASE_FILENAME) . 'assets/features/bigx.svg' :
						'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABdwAAAXcAQMAAAAC+94nAAAAA1BMVEVHcEyC+tLSAAAAAXRSTlMAQObYZgAAASlJREFUeF7twQENAAAAwqD3T20PBxQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfBpTqAABuxiL4QAAAABJRU5ErkJggg==';

		if ($fill_empties) {
			$features = array_fill(
				0,$max,
				(object) [
					'id' => null,
					'permalink' => '#',
					'post_title' => null,
					'icon' => $empty,
					'empty' => $empty,
				]
			);
		}
		$ids = array_filter($ids);

		if (count($ids)) {
			$raw_features = (new \WP_Query([
				'post_type' => PREFIX,
				'posts_per_page' => $max,
				'orderby' => 'post__in',
				'post__in' => $ids,
				'meta_query' => array(
					array(
						'key'     => '_thumbnail_id',
						'value'   => '',
						'compare' => '!=',
					)
				)
			]));

			if ($raw_features->have_posts()) {
				$raw_posts = $raw_features->posts;
				foreach($ids as $index=>$post_id) {
					$has_post = array_filter(
						$raw_posts,
						function($f) use (&$post_id) {
							return $f->ID == $post_id;
						}
					);
					if (count($has_post)) {
						$has_post = array_pop($has_post);
						//$thumb_id = (int) \get_post_thumbnail_id($has_post->ID);
						$features[$index] = (object) [
							'id' => $has_post->ID,
							'post_title' => $has_post->post_title,
							'permalink' => \get_post_permalink($has_post->ID),
							//'icon' => $thumb_id,
							'empty' => $empty,
						];
					}
				}
			}
		}

		return $features;
	}

}