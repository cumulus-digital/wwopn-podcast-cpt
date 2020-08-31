<?php
/**
 * Custom shortcodes for outputting features
 */
namespace WWOPN_Podcast;

function shortcode_feature_big() {
	$key = PREFIX . '_features';

	$feature_ids = \get_option(
		BigFeature::$option_key,
		array_fill(0,9,0)
	);

	$features = BigFeature::getFeatures($feature_ids, 9);

	ob_start();
	require BASEPATH . '/assets/features/template.features.big.php';
	$output = ob_get_clean();

	return $output;
}
\add_shortcode('podcasts-big', __NAMESPACE__ . '\shortcode_feature_big');

function shortcode_feature_genre($attr) {
	$attr = \shortcode_atts([
		'id' => 0,
		'slug' => null
	], $attr, 'podcasts-genre');

	$field = 'term_id';
	$query = $attr['id'];
	if ( ! $attr['id'] && $attr['slug']) {
		$field = 'slug';
		$query = $attr['slug'];
	}

	if ( ! $query) {
		return 'No Genre ID or slug specified in shortcode.';
	}

	$key = PREFIX . '_features';

	$genre = \get_term_by($field, $query, PREFIX . '_genre');
	if (\is_wp_error($genre)) {
		return 'Requested a genre which does not exist.';
	}

	$feature_ids = \get_term_meta($genre->term_id, GenreFeature::$option_key, true);

	if (!$feature_ids) {
		return;
	}

	$features = array_pad(
		GenreFeature::getFeatures($feature_ids, 5, false),
		5,
		(object) array(
			'id' => null,
			'permalink' => null,
			'icon' => null,
			'post_title' => null
		)
	);

	ob_start();
	require BASEPATH . '/assets/features/template.features.genre.php';
	$output = ob_get_clean();

	return $output;
}
\add_shortcode('podcasts-genre', __NAMESPACE__ . '\shortcode_feature_genre');

function shortcode_embedCode($attr) {
	$atts = \shortcode_atts( array(
		'post' => 0,
	), $attr, 'podcast-embed');

	$id = $attr['post'];
	if ($id == '0') return 'Please provide a post ID.';

	$key = CPT::$metakeys['playerEmbed'];
	$embed = \get_post_meta($id, $key, true);

	return $embed;
}
\add_shortcode('podcast-embed', __NAMESPACE__ . '\shortcode_embedCode');