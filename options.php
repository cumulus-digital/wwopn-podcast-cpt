<?php
namespace WWOPN_Podcast;

class Options {

	static $settingsName;
	static $defaults;

	static function init() {

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		self::$settingsName = PREFIX;
		self::$defaults = [
			PREFIX . '_checkbox_posts' => 1,
			PREFIX . '_checkbox_comments' => 1
		];

		\add_action( 'admin_menu', [__CLASS__, 'addAdminMenu'] );
		\add_action( 'admin_init', [__CLASS__, 'register'] );
		\add_action( 'admin_bar_menu', [__CLASS__, 'removeNodesFromAdminBar'], 999);

		// add options to the plugins page
		\add_action(
			'plugin_action_links_' . BASE_FILENAME,
			[__CLASS__, 'addActionLink']
		);

		\add_action('admin_menu', [__CLASS__, 'execute']);

	}

	static function loadOptions() {
		return \get_option(
			self::$settingsName,
			self::$defaults
		);
	}

	static function addAdminMenu() {
		\add_options_page(
			__('WWOPN Podcast Custom Post Type'),
			__('Podcast CPT'),
			'manage_options',
			PREFIX,
			[__CLASS__, 'outputPage']
		);
	}

	static function addActionLink($links) {
		if ( ! \current_user_can('manage_options')) {
			return;
		}

		$settings = [
			'<a href="' .
				\esc_url(
					\admin_url(
						'/options-general.php?page=' .
						PREFIX
					)
				) .
			'">' .
			__( 'Settings' ) . '</a>'
		];
		return array_merge($links, $settings);

	}

	static function register() {
		\register_setting(
			PREFIX,
			self::$settingsName,
			[
				'default' => self::$defaults
			]
		);

		\add_settings_section(
			PREFIX . '_options_hidestuff',
			__( 'Hide Default Sections' ),
			function() {
				echo __( 'Select the default Wordpress content types to hide from the admin.' );
			},
			PREFIX
		);

		\add_settings_field(
			PREFIX . '_checkbox_posts',
			__( 'Posts' ),
			function() {
				self::renderCheckbox(PREFIX . '_checkbox_posts');
			},
			PREFIX,
			PREFIX . '_options_hidestuff',
			[
				'label_for' => PREFIX . '_checkbox_posts'
			]
		);

		/*
		\add_settings_field(
			PREFIX . '_checkbox_comments',
			__( 'Comments' ),
			function() {
				self::renderCheckbox(PREFIX . '_checkbox_comments');
			},
			PREFIX,
			PREFIX . '_options_hidestuff',
			[
				'label_for' => PREFIX . '_checkbox_comments'
			]
		);
		*/

	}

	static function renderCheckbox($name) {
		$options = self::loadOptions();
		?>
		<input type="hidden" name="<?=self::$settingsName?>[<?=$name?>]" value="0">
		<input type='checkbox' id="<?=$name?>" name="<?=self::$settingsName?>[<?=$name?>]" <?php
			\checked(
				array_key_exists($name, $options) ? $options[$name] : 0,
				1
			);
		?> value="1">
		<?php
	}

	static function outputPage() {
		?>
		<form action='options.php' method='post'>

			<h1>WWOPN Podcast Custom Post Type</h1>
			<h2></h2>

			<?php
			\settings_fields( self::$settingsName );
			\do_settings_sections( self::$settingsName );
			\submit_button();
			?>

		</form>
		<?php
	}

	static function removeNodesFromAdminBar($wp_admin_bar) {
		$options = self::loadOptions();

	    if (
	    	array_key_exists(PREFIX . '_checkbox_posts', $options) &&
	    	$options[PREFIX . '_checkbox_posts'] == 1
	    ) {
		    $wp_admin_bar->remove_node( 'new-post' );
		    $wp_admin_bar->remove_node( 'new-link' );
	    	$wp_admin_bar->remove_node( 'new-media' );

	    	$new = $wp_admin_bar->get_node('new-content');
	    	$new->href = \admin_url('post-new.php?post_type=' . PREFIX);
	    	$wp_admin_bar->add_node($new);

	    	$page = $wp_admin_bar->get_node('new-page');
	    	$wp_admin_bar->remove_node('new-page');
	    	$wp_admin_bar->add_node($page);

	    	$user = $wp_admin_bar->get_node('new-user');
	    	$wp_admin_bar->remove_node('new-user');
	    	$wp_admin_bar->add_node($user);
	    }

		/*
	    if (
	    	array_key_exists(PREFIX . '_checkbox_comments', $options) &&
	    	$options[PREFIX . '_checkbox_comments'] == 1
	    ) {
	    	$wp_admin_bar->remove_node( 'comments' );
	    }
		*/
	}

	static function execute() {
		\add_action( 'admin_init', function(){
			$options = self::loadOptions();

			// Remove Posts from admin
			if (
				array_key_exists(PREFIX . '_checkbox_posts', $options) &&
				$options[PREFIX . '_checkbox_posts'] == 1
			) {
				\remove_menu_page( 'edit.php' );
				\remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
				\remove_meta_box('dashboard_activity', 'dashboard', 'normal');
			}

			// Disable comments
			/*
			if (
				array_key_exists(PREFIX . '_checkbox_comments', $options) &&
				$options[PREFIX . '_checkbox_comments'] == 1
			) {
				\remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
				\remove_menu_page('edit-comments.php');
				$post_types = \get_post_types();
				foreach ($post_types as $post_type) {
					if(\post_type_supports($post_type, 'comments')) {
						\remove_post_type_support($post_type, 'comments');
						\remove_post_type_support($post_type, 'trackbacks');
					}
				}
			}
			*/
		});
	}

}

Options::init();