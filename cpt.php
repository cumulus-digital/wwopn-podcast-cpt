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

	// Image size limit
	static $image_size_limit = '1mB';

	static function init() {

		\add_action('init', [__CLASS__, 'register']);

		\add_action('rest_api_init', [__CLASS__, 'rest_register_featuredimage']);

		\add_filter('gutenberg_can_edit_post_type', [__CLASS__, 'editor_disableGutenberg'], 10, 2);
		\add_filter('use_block_editor_for_post_type', [__CLASS__, 'editor_disableGutenberg'], 10, 2);

		\add_filter('wp_handle_upload_prefilter', [__CLASS__, 'editor_limitFeaturedImageSize']);

		\add_action('admin_enqueue_scripts', [__CLASS__, 'editor_loadScriptsAndStyles']);

		\add_filter('admin_post_thumbnail_html', [__CLASS__, 'editor_featuredImageHowTo']);

		\add_action( 'pre_get_posts', [__CLASS__, 'public_sortOrder'], 1, 1 );

		\add_filter('jetpack_sitemap_post_types', function($post_types) {
			$post_types[] = PREFIX;
			return $post_types;
		});

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
			'howto' => '<strong>Header image must be less than ' . self::$image_size_limit . '.</strong>',
			'type' => 'featured_image',
			'context' => 'side',
			'priority' => 'low',
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
					'order' => 1,
				],
				'facebook' => [
					'key' => 'facebook',
					'type' => 'url',
					'title' => 'Facebook URL',
					'order' => 2,
				],
				'twitter' => [
					'key' => 'twitter',
					'type' => 'url',
					'title' => 'Twitter URL',
					'order' => 3,
				],
				'instagram' => [
					'key' => 'instagram',
					'type' => 'url',
					'title' => 'Instagram URL',
					'order' => 4,
				],
			],
			'context' => 'normal',
			'priority' => 'high',
		]);

		self::registerMeta([
			'key' => '_' . PREFIX . '_meta_storelinks',
			'title' => 'Store Links',
			'type' => 'multi',
			'subtypes' => [
				'amazon' => [
					'key' => 'amazon',
					'type' => 'url',
					'title' => 'Amazon Music',
				],
				'apple' => [
					'key' => 'apple',
					'type' => 'url',
					'title' => 'Apple Podcasts',
				],
				'daily-wire' => [
					'key' => 'daily-wire',
					'type' => 'url',
					'title' => 'The Daily Wire',
				],
				'google' => [
					'key' => 'google',
					'type' => 'url',
					'title' => 'Google Podcasts',
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
				]
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
					'name'                  => esc_html__( 'Podcasts' ),
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
					'featured_image'        => esc_html__( 'Cover Art' ),
					'set_featured_image'    => esc_html__( 'Set Podcast Cover Art' ),
					'remove_featured_image' => esc_html__( 'Remove Podcast Cover Art' ),
					'use_featured_image'    => esc_html__( 'Use as Podcast Cover Art' )
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
	static function getFeaturedImage($pod) {
		if ( ! array_key_exists('featured_media', $pod) && array_key_exists('ID', $pod)) {
			$pod_id = self::getID($pod);
			$pod = \get_post($pod_id, ARRAY_A);
			$thumb_id = \get_post_thumbnail_id($pod['ID']);
			$pod['featured_media'] = $thumb_id;
		}
		if(array_key_exists('featured_media', $pod)){
			$img = \wp_get_attachment_image_src($pod['featured_media'], 'app-thumb');
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
	 * Display a helpful tip in Podcast Cover Art metabox.
	 * @param  string $html
	 * @return string
	 */
	static function editor_featuredImageHowTo($html) {
		if (\get_post_type() === PREFIX) {
			return $html . '<label class="howto"><strong>Cover art must be less than ' . self::$image_size_limit . '.</strong><br><br><strong>Tip:</strong> Create the cover art image at 1200x1200 pixels and save it as a JPG <em>at very low quality</em> to reduce the file size as much as possible. The lower quality artifacts will get smoothed out when the image is displayed at smaller dimensions.</label>';
		}
		return $html;
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

	/**
	 * Convert a string representation of file size to bytes
	 * @param  string $from
	 * @return int
	 */
	static function convertToBytes($from) {
		$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
		$number = substr($from, 0, -2);
		$suffix = strtoupper(substr($from,-2));

	    //B or no suffix
		if(is_numeric(substr($suffix, 0, 1))) {
			return preg_replace('/[^\d]/', '', $from);
		}

		$exponent = array_flip($units)[$suffix] ?? null;
		if($exponent === null) {
			return null;
		}

		return $number * (1024 ** $exponent);
	}

	/**
	 * Limit file size for Featured Images
	 * @param array $file
	 * @return array
	 */
	static function editor_limitFeaturedImageSize($file) {
		// Only handle our own images
		if (
			strpos($file['type'], 'image') !== false &&
			isset($_REQUEST['post_id'])
		) {
			$post_id = self::getID(filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT));
			if (\get_post_type($post_id) === PREFIX) {
				$size = filter_var($file['size'], FILTER_SANITIZE_NUMBER_INT);
				if ($size > self::convertToBytes(self::$image_size_limit)) {
					$file['error'] = 
						'Podcast images must be less than ' . self::$image_size_limit . '.';
				}
			}
		}
		return $file;
	}

	/**
	 * Force archives of this CPT to display all and sort by title
	 *
	 * @param \WP_Query $query
	 * @return void
	 */
	static function public_sortOrder($query) {
		if (\is_admin() || ! $query->is_main_query()) {
			return;
		}
		if ( ! $query->is_post_type_archive(PREFIX)) {
			return;
		}
		if (\is_singular()) {
			return;
		}
		//$query->set('posts_per_page', -1);
		$query->set('orderby', array('title' => 'ASC'));
	}

}

CPT::init();