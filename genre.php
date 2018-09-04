<?php
/**
 * Genre taxonomy and editor functions for Podcast CPT
 */
namespace WWOPN_Podcast;

class Genre {

	static $prefix;
	static $slug = 'genres';

	static function init() {

		self::$prefix = PREFIX . '_genre';

		\add_action('init', [__CLASS__, 'register']);

		// Podcast list filters
		\add_action('restrict_manage_posts', [__CLASS__, 'list_AddFilterDropdown']);
		\add_filter('parse_query', [__CLASS__, 'list_alterFilterQuery']);

		// Make Podcast list genre column sortable
		\add_action(
			'manage_edit-' . PREFIX . '_sortable_columns',
			[__CLASS__, 'list_sortableColumn']
		);
		\add_action('pre_get_posts', [__CLASS__, 'list_orderBy']);

		\add_action('init', [__CLASS__, 'rewriteRule']);

		// Make public taxonomy page list all posts in tax
		\add_filter('pre_get_posts', [__CLASS__, 'public_getAllPosts']);

	}

	static function register() {
		\register_taxonomy(
			self::$prefix,
			PREFIX,
			array(
				'label' => esc_html__( 'Genres' ),
				'labels' => array(
					'name'               => esc_html__( 'Genres' ),
					'items_list'         => esc_html__( 'Podcast Genres' ),
					'singular_name'      => esc_html__( 'Genre' ),
					'menu_name'          => esc_html__( 'Genres' ),
					'name_admin_bar'     => esc_html__( 'Genres' ),
					'all_items'          => esc_html__( 'All Genres' ),
					'parent_item'        => esc_html__( 'Parent Genre' ),
					'add_new'            => esc_html__( 'Add New' ),
					'add_new_item'       => esc_html__( 'Add New Genre' ),
					'edit'               => esc_html__( 'Edit' ),
					'edit_item'          => esc_html__( 'Edit Genre' ),
					'new_item'           => esc_html__( 'New Genre' ),
					'view'               => esc_html__( 'View Genre' ),
					'view_item'          => esc_html__( 'View Genre' ),
					'search_items'       => esc_html__( 'Search Genres' ),
					'not_found'          => esc_html__( 'No Genres found' ),
					'not_found_in_trash' => esc_html__( 'No Genres found in Trash' ),
					'no_terms'           => esc_html__( 'No Genres' ),
				),
				'meta_box_cb' => [__CLASS__, 'editor_feature_addInstructions'],
				'hierarchical' => true,
				'rewrite' => array('slug' => self::$slug, 'with_front' => false),
				'show_in_rest' => true,
				'show_admin_column' => true,
				'query_var' => true,
			)
		);
	}

	/**
	 * Add a rewrite rule so /genres goes to /pods
	 * @return void
	 */
	static function rewriteRule() {
		\add_rewrite_rule(
			'^' . self::$slug . '/?$',
			'index.php?name=pods',
			'top'
		);
	}

	static function editor_feature_addInstructions($post, $box) {
		\post_categories_meta_box($post, $box);
		if (class_exists(GenreFeature::class) && \current_user_can('edit_published_pages')) {
			?>
			<hr>
			<p>
				<span class="misc-pub-post-status wp-media-buttons-icon manage_genre_features">
					<a href="<?=admin_url('edit.php?post_type=' . PREFIX . '&page=' . GenreFeature::$screen)?>">
						<?=esc_html__('Manage Genre Features')?>
					</a>
				</span>
			</p>
			<?php
		}
	}

	static function list_AddFilterDropdown() {
		global $typenow;
		$post_type = PREFIX;
		$taxonomy  = self::$prefix;
		if ($typenow == $post_type) {
			$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
			$info_taxonomy = \get_taxonomy($taxonomy);
			\wp_dropdown_categories(array(
				'show_option_all' => __("Show All {$info_taxonomy->label}"),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'hide_empty'      => true,
			));
		};
	}

	static function list_alterFilterQuery($query) {
		global $pagenow;
		$post_type = PREFIX; // change to your post type
		$taxonomy  = self::$prefix; // change to your taxonomy
		$q_vars    = &$query->query_vars;
		if (
			$pagenow == 'edit.php' &&
			isset($q_vars['post_type']) &&
			$q_vars['post_type'] == $post_type &&
			isset($q_vars[$taxonomy]) &&
			is_numeric($q_vars[$taxonomy]) &&
			$q_vars[$taxonomy] != 0
		) {
			$term = \get_term_by('id', $q_vars[$taxonomy], $taxonomy);
			$q_vars[$taxonomy] = $term->slug;
		}
	}

	static function list_sortableColumn($columns) {
		$columns['taxonomy-' . self::$prefix] = self::$prefix;
		return $columns;
	}

	static function list_orderBy($query) {
		if( ! is_admin() )
			return;

		$orderby = $query->get('orderby');

		if( 'slice' == $orderby ) {
			$query->set('meta_key',self::$prefix);
			$query->set('orderby','meta_value_num');
		}
	}

	static function public_getAllPosts($query) {
		if (is_admin() || ! is_tax(self::$slug)) {
			return;
		}

		$query->query_vars['posts_per_page'] = -1;
		return;
	}

}

Genre::init();