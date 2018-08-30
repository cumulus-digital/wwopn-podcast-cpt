<?php
/**
 * Tag taxonomy for Podcast CPT
 */
namespace WWOPN_Podcast;

class Tag {

	static $prefix;
	static $slug = 'tags';

	static function init() {

		self::$prefix = PREFIX . '_tag';

		\add_action('init', [__CLASS__, 'register']);

	}

	static function register() {
		\register_taxonomy(
			self::$prefix,
			PREFIX,
			array(
				'hierarchical' => false,
				'label' => esc_html__( 'Tags' ),
				'labels' => array(
					'name'               => esc_html__( 'Tags' ),
					'items_list'         => esc_html__( 'Podcast Tags' ),
					'singular_name'      => esc_html__( 'Tag' ),
					'menu_name'          => esc_html__( 'Tags' ),
					'name_admin_bar'     => esc_html__( 'Tags' ),
					'all_items'          => esc_html__( 'All Tags' ),
					'parent_item'        => esc_html__( 'Parent Tag' ),
					'add_new'            => esc_html__( 'Add New' ),
					'add_new_item'       => esc_html__( 'Add New Tag' ),
					'edit'               => esc_html__( 'Edit' ),
					'edit_item'          => esc_html__( 'Edit Tag' ),
					'new_item'           => esc_html__( 'New Tag' ),
					'view'               => esc_html__( 'View Tag' ),
					'view_item'          => esc_html__( 'View Tag' ),
					'search_items'       => esc_html__( 'Search Tags' ),
					'not_found'          => esc_html__( 'No Tags found' ),
					'not_found_in_trash' => esc_html__( 'No Tags found in Trash' ),
					'no_terms'           => esc_html__( 'No Tags' ),
				),
				'meta_box_cb' => [__CLASS__, 'editor_addInstructions'],
				'rewrite' => array('slug' => self::$slug, 'with_front' => false),
				'show_in_rest' => true,
				'query_var' => true,
			)
		);
	}


	static function editor_addInstructions($post, $box) {
		\post_tags_meta_box($post, $box);
		if (\current_user_can('edit_published_pages')) {
			?>
			<p class="howto">
				Podcast tags should be descriptive of the podcast to aid
				content discovery. e.g.: "conservative", "wrestling"
			</p>
			<p class="howto">
				Do not duplicate Genres as Tags.
			</p>
			<?php
		}
	}

}

Tag::init();