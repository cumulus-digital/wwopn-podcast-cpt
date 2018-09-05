<?php
/**
 * Podcast Custom Post Type
 */
namespace WWOPN_Podcast;

class CPT {

	use CPTTrait;

	static $slug = 'pods';
	static $metakeys = [];
	static $meta_save_callbacks = [];

	static function init() {

		\add_action('init', [__CLASS__, 'register']);

		\add_action('rest_api_init', [__CLASS__, 'rest_register_featuredimage']);

		\add_filter('gutenberg_can_edit_post_type', [__CLASS__, 'editor_disableGutenberg'], 10, 2);

		\add_action('admin_enqueue_scripts', [__CLASS__, 'editor_loadScriptsAndStyles']);
	
		self::registerMetaBox([
			'key' => '_' . PREFIX . '_meta_subTitle',
			'title' => 'Sub-Title',
			'type' => 'text',
			'context' => 'post-title'
		]);

		self::registerMetaBox([
			'key' => '_' . PREFIX . '_meta_playerembed',
			'title' => 'Player Embed Code',
			'howto' => '<p>Place the HTML embed code for the podcast player here, not in the post content.</p>',
			'type' => 'textarea',
			'saveTags' => true,
			'context' => 'normal',
			'priority' => 'high'
		]);

		self::registerMetaBox([
			'key' => '_' . PREFIX . '_meta_headerimage',
			'title' => 'Header Image',
			'type' => 'featured_image',
			'context' => 'side',
			'priority' => 'low'
		]);

		self::registerMetaBox([
			'key' => '_' . PREFIX . '_meta_social',
			'title' => 'Social Links',
			'type' => 'multi',
			'subtypes' => [
				'facebook' => [
					'key' => 'facebook',
					'type' => 'url',
					'title' => 'Facebook URL',
				],
				'twitter' => [
					'key' => 'twitter',
					'type' => 'string',
					'title' => 'Twitter Handle',
					'howto' => '<label class="howto">Name only, do not include "@"</label>',
					'required' => false,
					'pattern' => '^$|^[^@].*$'
				]
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

	static function getKey($tag) {
		return self::$metakeys[$tag];
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

	/**
	 * Retrieve featured image URL for given podcast
	 * @param  array $pod        podcast
	 * @return string|null
	 */
	static function getFeaturedImage($pod) {
		if($pod['featured_media']){
			$img = \wp_get_attachment_image_src($pod['featured_media'], 'app-thumb');
			return $img[0];
		}
		return null;
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

		\wp_enqueue_script( PREFIX . '_editor_scripts', \plugin_dir_url(__FILE__) . 'assets/editor/scripts.js' );
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$params = array(
			'url' => admin_url('admin-ajax.php', $protocol),
			'nonce' => wp_create_nonce('ajax_nonce'),
		);
		\wp_localize_script( PREFIX . '_editor_scripts', 'wpn_ajax_object', $params);
		\wp_enqueue_style( PREFIX . '_editor_styles', \plugin_dir_url(__FILE__) . 'assets/editor/styles.css' );
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

	static function editor_meta_subTitle($post) {
		if ( ! isOurPost($post)) {
			return;
		}
		$key = self::$metakeys['subTitle'];
		$subtitle = \get_post_meta($post->ID, $key, true);
		?>
		<div class="meta_subtitle">
			<?=\wp_nonce_field($key, $key . '-nonce');?>
			<label for="meta_subtitle">Sub-Title:</label>
			<input type="text" name="<?=$key?>" size="30" value="<?=esc_attr($subtitle)?>" id="meta_subtitle" spellcheck="true" autocomplete="off">
		</div>
		<?php
	}

	static function editor_meta_subTitle_save($post_id) {
		if ( ! self::editor_meta_safeToSave()) {
			return;
		}

		$key = self::$metakeys['subTitle'];

		if (testPostValue($key, true)) {
			$value = (string) \sanitize_text_field($_POST[$key]);
			\update_post_meta($post_id, $key, $value);
			return;
		}

		\delete_post_meta($post_id, $key);
	}

	/**
	 * Add meta box for player embed code
	 * @return void
	 */
	static function editor_meta_playerEmbed() {
		\add_meta_box(
			self::$metakeys['playerEmbed'],
			esc_html__('Player Embed Code'),
			[__CLASS__, 'editor_meta_playerEmbed_show'],
			PREFIX,
			'normal',
			'high'
		);
	}

	/**
	 * Display the meta box for player embed
	 * @param  object $post
	 * @return void
	 */
	static function editor_meta_playerEmbed_show($post) {
		$key = self::$metakeys['playerEmbed'];
		$embed = \get_post_meta($post->ID, $key, true);

		?>
		<div class="wpn_meta_autosave">
			<?=\wp_nonce_field($key, $key . '-nonce');?>
			<label class="screen-reader-text" for="excerpt">Player Embed Code</label>
			<textarea class="wpn-meta-autosave" name="<?=$key?>" style="display:block;width:100%;height:8em;margin:12px 0 0;"><?=esc_textarea($embed) ?></textarea>
			<p>Place the HTML embed code for the podcast player here, not in the post content.</p>
		</div>
		<?php
	}

	/**
	 * Save data entered in player embed box
	 * @param  integer $post_id
	 * @return void
	 */
	static function editor_meta_playerEmbed_save($post_id) {
		if ( ! self::editor_meta_safeToSave()) {
			return;
		}

		$key = self::$metakeys['playerEmbed'];

		if (testPostValue($key, true)) {
			$value = (string) $_POST[$key];
			\update_post_meta($post_id, $key, $value);
			return;
		}

		\delete_post_meta($post_id, $key);
	}
	
	/**
	 * Add meta box for header image
	 * @return void
	 */
	static function editor_meta_headerImage() {
		\add_meta_box(
			self::$metakeys['headerImage'],
			esc_html__('Header Image'),
			[__CLASS__, 'editor_meta_headerImage_show'],
			PREFIX,
			'side',
			'low'
		);
	}

	/**
	 * Display the meta box for header image
	 * @param  object $post
	 * @return void
	 */
	static function editor_meta_headerImage_show($post) {
		$key = self::$metakeys['headerImage'];
		$image_id = \get_post_meta( $post->ID, $key, true );
		$has_image = false;
		$image = '<img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">';

		if ($image_id && \get_post($image_id)) {
			$has_image = true;
			$image = \wp_get_attachment_image($image_id, 'thumbnail');
		}

		$meta_display_name = 'Header Image';

		include __DIR__ . '/assets/editor/template.meta.headerimage.php';
	}

	/**
	 * Save header image ID to post metadata
	 * @param  integer $post_id
	 * @return void
	 */
	static function editor_meta_headerImage_save($post_id) {
		if ( ! self::editor_meta_safeToSave()) {
			return;
		}

		$key = self::$metakeys['headerImage'];
		if (testPostValue($key, true)) {
			$value = (int) $_POST[$key];
			\update_post_meta($post_id, $key, $value);
			return;
		}

		\delete_post_meta($post_id, $key);
	}


}

CPT::init();