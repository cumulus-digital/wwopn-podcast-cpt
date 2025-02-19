<?php

/**
 * Podcast Custom Post Type.
 */

namespace WWOPN_Podcast;

class CPT {
	use CustomMetaBoxes;

	public static $slug = 'pods';

	public static $metakeys = array();

	public static $meta_save_callbacks = array();

	// Image size limit
	public static $image_size_limit = '1mB';

	public static function init() {
		\add_action( 'init', array( __CLASS__, 'register' ) );

		\add_action( 'rest_api_init', array( __CLASS__, 'rest_register_featuredimage' ) );

		\add_filter( 'gutenberg_can_edit_post_type', array( __CLASS__, 'editor_disableGutenberg' ), 10, 2 );
		\add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'editor_disableGutenberg' ), 10, 2 );

		// \add_action( 'load-post.php', array( __CLASS__, 'migrations' ) );
		\add_action( 'wp', array( __CLASS__, 'migrations' ), \PHP_INT_MIN, 1 );

		\add_filter( 'wp_handle_upload_prefilter', array( __CLASS__, 'editor_limitFeaturedImageSize' ) );

		\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'editor_loadScriptsAndStyles' ) );

		\add_filter( 'admin_post_thumbnail_html', array( __CLASS__, 'editor_featuredImageHowTo' ) );

		\add_action( 'pre_get_posts', array( __CLASS__, 'public_sortOrder' ), 1, 1 );

		\add_filter( 'jetpack_sitemap_post_types', function ( $post_types ) {
			$post_types[] = PREFIX;

			return $post_types;
		} );

		self::registerMeta( array(
			'key'     => '_' . PREFIX . '_meta_subTitle',
			'title'   => 'Sub-Title',
			'type'    => 'text',
			'context' => 'post-title',
		) );

		self::registerMeta( array(
			'key'      => '_' . PREFIX . '_meta_playerembed',
			'title'    => 'Player Embed Code',
			'howto'    => '<p>Place the HTML embed code for the podcast player here, not in the post content.</p>',
			'type'     => 'textarea',
			'saveTags' => true,
			'context'  => 'normal',
			'priority' => 'high',
		) );

		self::registerMeta( array(
			'key'      => '_' . PREFIX . '_meta_headercolor',
			'title'    => 'Header Color',
			'type'     => 'text',
			'context'  => 'side',
			'priority' => 'low',
		) );

		self::registerMeta( array(
			'key'      => '_' . PREFIX . '_meta_headerimage',
			'title'    => 'Header Image',
			'howto'    => '<strong>Header image must be less than ' . self::$image_size_limit . '.</strong>',
			'type'     => 'featured_image',
			'context'  => 'side',
			'priority' => 'low',
		) );

		self::registerMeta( array(
			'key'      => '_' . PREFIX . '_meta_header3dcolor',
			'title'    => 'Header 3D Color',
			'type'     => 'text',
			'context'  => 'side',
			'priority' => 'low',
		) );

		self::registerMeta( array(
			'key'      => '_' . PREFIX . '_meta_social',
			'title'    => 'Social Links',
			'type'     => 'multi',
			'subtypes' => array(
				'website' => array(
					'key'   => 'website',
					'type'  => 'url',
					'title' => 'Show Website',
					'order' => 1,
				),
				'facebook' => array(
					'key'   => 'facebook',
					'type'  => 'url',
					'title' => 'Facebook URL',
					'order' => 2,
				),
				'x' => array(
					'key'   => 'x',
					'type'  => 'url',
					'title' => 'X URL',
					'order' => 3,
				),
				'instagram' => array(
					'key'   => 'instagram',
					'type'  => 'url',
					'title' => 'Instagram URL',
					'order' => 4,
				),
			),
			'context'  => 'normal',
			'priority' => 'high',
		) );

		self::registerMeta( array(
			'key'      => '_' . PREFIX . '_meta_storelinks',
			'title'    => 'Store Links',
			'type'     => 'multi',
			'subtypes' => array(
				'amazon' => array(
					'key'   => 'amazon',
					'type'  => 'url',
					'title' => 'Amazon Music',
				),
				'apple' => array(
					'key'   => 'apple',
					'type'  => 'url',
					'title' => 'Apple Podcasts',
				),
				'daily-wire' => array(
					'key'   => 'daily-wire',
					'type'  => 'url',
					'title' => 'The Daily Wire',
				),
				'google' => array(
					'key'   => 'google',
					'type'  => 'url',
					'title' => 'Google Podcasts',
				),
				'stitcher' => array(
					'key'   => 'stitcher',
					'type'  => 'url',
					'title' => 'Stitcher',
				),
				'tunein' => array(
					'key'   => 'tunein',
					'type'  => 'url',
					'title' => 'TuneIn',
				),
				'spotify' => array(
					'key'   => 'spotify',
					'type'  => 'url',
					'title' => 'Spotify',
				),
				'pandora' => array(
					'key'   => 'pandora',
					'type'  => 'url',
					'title' => 'Pandora',
				),
				'siriusxm' => array(
					'key'   => 'siriusxm',
					'type'  => 'url',
					'title' => 'SiriusXM',
				),
				'youtube' => array(
					'key'   => 'youtube',
					'type'  => 'url',
					'title' => 'YouTube',
				),
			),
			'sortable' => true,
			'howto'    => '<label class="howto">Add URLs for this podcast in other locations. Empty stores will not be displayed.</label>',
			'context'  => 'normal',
			'priority' => 'high',
		) );

		self::registerMeta( array(
			'key'      => '_' . PREFIX . '_meta_podcastrss',
			'title'    => 'Podcast RSS Feeds',
			'type'     => 'multi',
			'subtypes' => array(
				'itunes' => array(
					'key'   => 'itunes',
					'type'  => 'url',
					'title' => 'iTunes RSS',
				),
				'google' => array(
					'key'   => 'google',
					'type'  => 'url',
					'title' => 'Google Play RSS',
				),
			),
			'context'  => 'normal',
			'priority' => 'default',
		) );

		self::parentInit();
	}

	/**
	 * Register CPT.
	 */
	public static function register() {
		\register_post_type(
			PREFIX, // Register Custom Post Type
			array(
				'labels' => array(
					'name'                  => \esc_html__( 'Podcasts' ),
					'singular_name'         => \esc_html__( 'Podcast Page' ),
					'menu_name'             => \esc_html__( 'Podcasts' ),
					'name_admin_bar'        => \esc_html__( 'Podcast' ),
					'all_items'             => \esc_html__( 'All Podcasts' ),
					'add_new'               => \esc_html__( 'Add New' ),
					'add_new_item'          => \esc_html__( 'Add New Podcast Page' ),
					'edit'                  => \esc_html__( 'Edit' ),
					'edit_item'             => \esc_html__( 'Edit Podcast Page' ),
					'new_item'              => \esc_html__( 'New Podcast Page' ),
					'view'                  => \esc_html__( 'View Podcast Page' ),
					'view_item'             => \esc_html__( 'View Podcast Page' ),
					'search_items'          => \esc_html__( 'Search Podcast Pages' ),
					'not_found'             => \esc_html__( 'No Podcast Pages found' ),
					'not_found_in_trash'    => \esc_html__( 'No Podcast Pages found in Trash' ),
					'featured_image'        => \esc_html__( 'Cover Art' ),
					'set_featured_image'    => \esc_html__( 'Set Podcast Cover Art' ),
					'remove_featured_image' => \esc_html__( 'Remove Podcast Cover Art' ),
					'use_featured_image'    => \esc_html__( 'Use as Podcast Cover Art' ),
				),
				'description'           => 'Landing pages for podcasts.',
				'public'                => true,
				'capability_type'       => 'page',
				'show_in_rest'          => true,
				'rest_base'             => 'podcasts',
				'rest_controller_class' => '\WP_REST_Posts_Controller',
				'rewrite'               => array( 'slug' => self::$slug ),
				'menu_position'         => 4,
				'menu_icon'             => 'dashicons-playlist-audio',
				'hierarchical'          => false,
				'has_archive'           => true,
				'can_export'            => true,
				'supports'              => array(
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
	 * Add featured image to REST responses for this CPT.
	 *
	 * @return [type] [description]
	 */
	public static function rest_register_featuredimage() {
		if (
			! isset( $_GET['featured_media'] )
			|| ! $_GET['featured_media']
		) {
			return;
		}

		\register_rest_field(
			PREFIX,
			'featured_media_url',
			array(
				'get_callback'    => array( __CLASS__, 'getFeaturedImage' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}

	public static function getID( $pod_id ) {
		if ( ! $pod_id ) {
			$pod_id = \get_the_ID();
		} elseif ( \is_object( $pod_id ) ) {
			$pod_id = $pod_id->ID;
		} elseif ( \is_array( $pod_id ) ) {
			$pod_id = $pod_id['ID'];
		}

		return $pod_id;
	}

	/**
	 * Retrieve featured image URL for given podcast.
	 *
	 * @param WP_Post|array|int $pod_id podcast
	 * @param mixed             $pod
	 *
	 * @return string|null
	 */
	public static function getFeaturedImage( $pod ) {
		if ( ! \array_key_exists( 'featured_media', $pod ) && \array_key_exists( 'ID', $pod ) ) {
			$pod_id                = self::getID( $pod );
			$pod                   = \get_post( $pod_id, ARRAY_A );
			$thumb_id              = \get_post_thumbnail_id( $pod['ID'] );
			$pod['featured_media'] = $thumb_id;
		}
		if ( \array_key_exists( 'featured_media', $pod ) ) {
			$img = \wp_get_attachment_image_src( $pod['featured_media'], 'app-thumb' );

			return $img[0];
		}
	}

	public static function getSocialLinks( $pod_id = null ) {
		$pod_id      = self::getID( $pod_id );
		$sociallinks = \get_post_meta( $pod_id, '_' . PREFIX . '_meta_social', true );

		return \array_filter( (array) $sociallinks, function ( $link ) {
			if ( $link && \mb_strlen( $link ) ) {
				return true;
			}
		} );
	}

	public static function getStoreLinks( $pod_id = null ) {
		$pod_id     = self::getID( $pod_id );
		$storelinks = \get_post_meta( $pod_id, '_' . PREFIX . '_meta_storelinks', true );

		return \array_filter( (array) $storelinks, function ( $link ) {
			if ( \mb_strlen( $link ) ) {
				return true;
			}
		} );
	}

	public static function getPlayerEmbed( $pod_id = null ) {
		$pod_id = self::getID( $pod_id );

		return \get_post_meta( $pod_id, '_' . PREFIX . '_meta_playerembed', true );
	}

	public static function getSubtitle( $pod_id = null ) {
		$pod_id = self::getID( $pod_id );

		return \get_post_meta( $pod_id, '_' . PREFIX . '_meta_subTitle', true );
	}

	/**
	 * Register scripts and styles for the post editor.
	 *
	 * @param string $hook
	 */
	public static function editor_loadScriptsAndStyles( $hook ) {
		if ( $hook !== 'post-new.php' && $hook !== 'post.php' ) {
			return;
		}
		$screen = \get_current_screen();
		if ( $screen->id !== PREFIX ) {
			return;
		}

		\wp_enqueue_style( 'wp-color-picker' );
		\wp_enqueue_script( PREFIX . '_editor_scripts', \plugin_dir_url( BASE_FILENAME ) . 'assets/editor/scripts.js', array( 'wp-util', 'wp-color-picker' ) );

		// Set up AJAX for editor script
		$protocol = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? 'https://' : 'http://';
		$params   = array(
			'url'   => \admin_url( 'admin-ajax.php', $protocol ),
			'nonce' => \wp_create_nonce( 'ajax_nonce' ),
		);
		\wp_localize_script( PREFIX . '_editor_scripts', 'wpn_ajax_object', $params );

		\wp_enqueue_style( PREFIX . '_editor_styles', \plugin_dir_url( BASE_FILENAME ) . 'assets/editor/styles.css' );
	}

	/**
	 * Display a helpful tip in Podcast Cover Art metabox.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public static function editor_featuredImageHowTo( $html ) {
		if ( \get_post_type() === PREFIX ) {
			return $html . '<label class="howto"><strong>Cover art must be less than ' . self::$image_size_limit . '.</strong><br><br><strong>Tip:</strong> Create the cover art image at 1200x1200 pixels and save it as a JPG <em>at very low quality</em> to reduce the file size as much as possible. The lower quality artifacts will get smoothed out when the image is displayed at smaller dimensions.</label>';
		}

		return $html;
	}

	/**
	 * Disable Gutenberg for this CPT.
	 *
	 * @param bool   $is_enabled
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public static function editor_disableGutenberg( $is_enabled, $post_type = null ) {
		if ( $post_type === PREFIX ) {
			return false;
		}

		return $is_enabled;
	}

	/**
	 * Convert a string representation of file size to bytes.
	 *
	 * @param string $from
	 *
	 * @return int
	 */
	public static function convertToBytes( $from ) {
		$units  = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
		$number = \mb_substr( $from, 0, -2 );
		$suffix = \mb_strtoupper( \mb_substr( $from, -2 ) );

		// B or no suffix
		if ( \is_numeric( \mb_substr( $suffix, 0, 1 ) ) ) {
			return \preg_replace( '/[^\d]/', '', $from );
		}

		$exponent = \array_flip( $units )[$suffix] ?? null;
		if ( $exponent === null ) {
			return;
		}

		return $number * ( 1024 ** $exponent );
	}

	/**
	 * Limit file size for Featured Images.
	 *
	 * @param array $file
	 *
	 * @return array
	 */
	public static function editor_limitFeaturedImageSize( $file ) {
		// Only handle our own images
		if (
			\mb_strpos( $file['type'], 'image' ) !== false
			&& isset( $_REQUEST['post_id'] )
		) {
			$post_id = self::getID( \filter_input( \INPUT_POST, 'post_id', \FILTER_SANITIZE_NUMBER_INT ) );
			if ( \get_post_type( $post_id ) === PREFIX ) {
				$size = \filter_var( $file['size'], \FILTER_SANITIZE_NUMBER_INT );
				if ( $size > self::convertToBytes( self::$image_size_limit ) ) {
					$file['error']
						= 'Podcast images must be less than ' . self::$image_size_limit . '.';
				}
			}
		}

		return $file;
	}

	/**
	 * Force archives of this CPT to display all and sort by title.
	 *
	 * @param \WP_Query $query
	 */
	public static function public_sortOrder( $query ) {
		if ( \is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( ! $query->is_post_type_archive( PREFIX ) ) {
			return;
		}
		if ( \is_singular() ) {
			return;
		}
		// $query->set('posts_per_page', -1);
		$query->set( 'orderby', array( 'title' => 'ASC' ) );
	}

	/**
	 * Handle any necessary migrations.
	 *
	 * @param mixed $query
	 * @param mixed $wp
	 */
	public static function migrations( $wp ) {
		if (
			$wp->query_vars['post_type'] !== PREFIX
			|| ( \is_singular() && ! \is_singular( PREFIX ) )
			|| ( \is_admin() && ! \get_post_type() !== PREFIX )
		) {
			return;
		}

		$pod_id = \get_queried_object_id();

		if ( ! $pod_id ) {
			return;
		}

		$sociallinks = \get_post_meta( $pod_id, '_' . PREFIX . '_meta_social', true );
		if ( ! $sociallinks ) {
			return;
		}

		// Migrate Twitter to X
		if ( \array_key_exists( 'twitter', $sociallinks ) ) {
			$sociallinks['x'] = $sociallinks['twitter'];
			if ( \preg_match( '@^https?://twitter\.com@i', $sociallinks['twitter'], $matches ) ) {
				\do_action( 'qm/debug', $matches );
				$sociallinks['x'] = \str_ireplace( $matches[0], 'https://x.com', $sociallinks['x'] );
			}
			unset( $sociallinks['twitter'] );
			// Update post meta
			\update_post_meta( $pod_id, '_' . PREFIX . '_meta_social', $sociallinks );
		}
	}
}

CPT::init();
