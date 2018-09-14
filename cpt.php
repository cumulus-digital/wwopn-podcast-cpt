<?php
/**
 * Podcast Custom Post Type
 */
namespace WWOPN_Podcast;

class CPT {

	use CustomMetaBoxes;

	static $slug = 'pods';
	static $metakeys = [];
	static $meta_save_callbacks = [];

	static function init() {

		\add_action('init', [__CLASS__, 'register']);

		\add_action('rest_api_init', [__CLASS__, 'rest_register_featuredimage']);

		\add_filter('gutenberg_can_edit_post_type', [__CLASS__, 'editor_disableGutenberg'], 10, 2);

		\add_action('admin_enqueue_scripts', [__CLASS__, 'editor_loadScriptsAndStyles']);

		self::registerMeta([
			'key' => '_' . PREFIX . '_meta_subTitle',
			'title' => 'Sub-Title',
			'type' => 'text',
			'context' => 'post-title'
		]);

		self::registerMeta([
			'key' => '_' . PREFIX . '_meta_playerembed',
			'title' => 'Player Embed Code',
			'howto' => '<p>Place the HTML embed code for the podcast player here, not in the post content.</p>',
			'type' => 'textarea',
			'saveTags' => true,
			'context' => 'normal',
			'priority' => 'high'
		]);

		self::registerMeta([
			'key' => '_' . PREFIX . '_meta_headerimage',
			'title' => 'Header Image',
			'type' => 'featured_image',
			'context' => 'side',
			'priority' => 'low'
		]);

		self::registerMeta([
			'key' => '_' . PREFIX . '_meta_social',
			'title' => 'Social Links',
			'type' => 'multi',
			'subtypes' => [
				'website' => [
					'key' => 'website',
					'type' => 'url',
					'title' => 'Show Website',
				],
				'facebook' => [
					'key' => 'facebook',
					'type' => 'url',
					'title' => 'Facebook URL',
				],
				'twitter' => [
					'key' => 'twitter',
					'type' => 'string',
					'title' => 'Twitter URL',
				]
			],
			'context' => 'normal',
			'priority' => 'high',
		]);

		self::registerMeta([
			'key' => '_' . PREFIX . '_meta_storelinks',
			'title' => 'Store Links',
			'type' => 'multi',
			'subtypes' => [
				'apple' => [
					'key' => 'apple',
					'type' => 'url',
					'title' => 'Apple Podcasts',
				],
				'stitcher' => [
					'key' => 'stitcher',
					'type' => 'url',
					'title' => 'Stitcher',
				],
				'tunein' => [
					'key' => 'tunein',
					'type' => 'url',
					'title' => 'TuneIn',
				],
				'spotify' => [
					'key' => 'spotify',
					'type' => 'url',
					'title' => 'Spotify',
				],
				'google' => [
					'key' => 'google',
					'type' => 'url',
					'title' => 'Google Podcasts',
				],
				'daily-wire' => [
					'key' => 'daily-wire',
					'type' => 'url',
					'title' => 'The Daily Wire',
				],
			],
			'sortable' => true,
			'howto' => '<label class="howto">Add URLs for this podcast in other locations. Empty stores will not be displayed.</label>',
			'context' => 'normal',
			'priority' => 'high',
		]);

		self::registerMeta([
			'key' => '_' . PREFIX . '_meta_podcastrss',
			'title' => 'Podcast RSS Feeds',
			'type' => 'multi',
			'subtypes' => [
				'itunes' => [
					'key' => 'itunes',
					'type' => 'url',
					'title' => 'iTunes RSS',
				],
				'google' => [
					'key' => 'google',
					'type' => 'url',
					'title' => 'Google Play RSS',
				],
			],
			'context' => 'normal',
			'priority' => 'default',
		]);

		self::parentInit();

	}

	/**
	 * Register CPT
	 * @return void
	 */
	static function register() {
		\register_post_type( PREFIX, // Register Custom Post Type
			array(
				'labels'       => array(
					'name'                  => esc_html__( 'Podcast Pages' ),
					'singular_name'         => esc_html__( 'Podcast Page' ),
					'menu_name'             => esc_html__( 'Podcasts' ),
					'name_admin_bar'        => esc_html__( 'Podcast' ),
					'all_items'             => esc_html__( 'All Podcasts' ),
					'add_new'               => esc_html__( 'Add New' ),
					'add_new_item'          => esc_html__( 'Add New Podcast Page' ),
					'edit'                  => esc_html__( 'Edit' ),
					'edit_item'             => esc_html__( 'Edit Podcast Page' ),
					'new_item'              => esc_html__( 'New Podcast Page' ),
					'view'                  => esc_html__( 'View Podcast Page' ),
					'view_item'             => esc_html__( 'View Podcast Page' ),
					'search_items'          => esc_html__( 'Search Podcast Pages' ),
					'not_found'             => esc_html__( 'No Podcast Pages found' ),
					'not_found_in_trash'    => esc_html__( 'No Podcast Pages found in Trash' ),
					'featured_image'        => esc_html__( 'Podcast Icon' ),
					'set_featured_image'    => esc_html__( 'Set Podcast Icon' ),
					'remove_featured_image' => esc_html__( 'Remove Podcast Icon' ),
					'use_featured_image'    => esc_html__( 'Use as Podcast Icon' )
				),
				'description'           => 'Landing pages for podcasts.',
				'public'                => true,
				'capability_type'       => 'page',
				'show_in_rest'          => true,
				'rest_base'             => 'podcasts',
				'rest_controller_class' => '\WP_REST_Posts_Controller',
				'rewrite'               => array('slug' => self::$slug),
				'menu_position'         => 4,
				'menu_icon'             => 'dashicons-playlist-audio',
				'hierarchical'          => false,
				'has_archive'           => true,
				'can_export'            => true,
				'supports' => array(
					'title',
					'excerpt',
					'editor',
					'revisions',
					'thumbnail',
				),
				'taxonomies' => array(
					'genre',
					PREFIX . '_tag',
				),
			)
		);
	}

	/**
	 * Add featured image to REST responses for this CPT
	 * @return [type] [description]
	 */
	static function rest_register_featuredimage(){
		if (
			! isset($_GET['featured_media']) ||
			! $_GET['featured_media']
		) {
			return;
		}

		\register_rest_field( PREFIX,
			'featured_media_url',
			array(
				'get_callback'    => [__CLASS__, 'getFeaturedImage'],
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}


	static function getID($pod_id) {
		if ( ! $pod_id) {
			$pod_id = \get_the_ID();
		} else if (is_object($pod_id)) {
			$pod_id = $pod_id->ID;
		} else if (is_array($pod_id)) {
			$pod_id = $pod_id['ID'];
		}
		return $pod_id;
	}

	/**
	 * Retrieve featured image URL for given podcast
	 * @param  WP_Post|array|integer  $pod_id        podcast
	 * @return string|null
	 */
	static function getFeaturedImage($pod_id = NULL) {
		$pod_id = self::getID($pod_id);
		$pod = \get_post($pod_id);
		if($pod->featured_media){
			$img = \wp_get_attachment_image_src($pod->featured_media, 'app-thumb');
			return $img[0];
		}
		return null;
	}

	static function getSocialLinks($pod_id = NULL) {
		$pod_id = self::getID($pod_id);
		$sociallinks = \get_post_meta($pod_id, '_' . PREFIX . '_meta_social', true);
		return array_filter((array) $sociallinks, function($link) {
			if ($link && strlen($link)) {
				return true;
			}
		});
	}

	static function getStoreLinks($pod_id = NULL) {
		$pod_id = self::getID($pod_id);
		$storelinks = \get_post_meta($pod_id, '_' . PREFIX . '_meta_storelinks', true);
		return array_filter((array) $storelinks, function($link) {
			if (strlen($link)) {
				return true;
			}
		});
	}

	static function getPlayerEmbed($pod_id = NULL) {
		$pod_id = self::getID($pod_id);
		return \get_post_meta($pod_id, '_' . PREFIX . '_meta_playerembed', true);
	}

	static function getSubtitle($pod_id = NULL) {
		$pod_id = self::getID($pod_id);
		return \get_post_meta($pod_id, '_' . PREFIX . '_meta_subTitle', true);
	}

	/**
	 * Register scripts and styles for the post editor
	 * @param  string $hook
	 * @return void
	 */
	static function editor_loadScriptsAndStyles($hook) {
		if ($hook !== 'post-new.php' && $hook !== 'post.php') {
			return;
		}
		$screen = \get_current_screen();
		if ($screen->id !== PREFIX) {
			return;
		}

		\wp_enqueue_script( PREFIX . '_editor_scripts', \plugin_dir_url(BASE_FILENAME) . 'assets/editor/scripts.js', ['wp-util'] );

		// Set up AJAX for editor script
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$params = array(
			'url' => admin_url('admin-ajax.php', $protocol),
			'nonce' => wp_create_nonce('ajax_nonce'),
		);
		\wp_localize_script( PREFIX . '_editor_scripts', 'wpn_ajax_object', $params);

		\wp_enqueue_style( PREFIX . '_editor_styles', \plugin_dir_url(BASE_FILENAME) . 'assets/editor/styles.css' );
	}

	/**
	 * Disable Gutenberg for this CPT
	 * @param  boolean $is_enabled
	 * @param  string $post_type
	 * @return boolean
	 */
	static function editor_disableGutenberg($is_enabled, $post_type = null) {
		if ($post_type === PREFIX) {
			return false;
		}

		return $is_enabled;
	}

}

CPT::init();