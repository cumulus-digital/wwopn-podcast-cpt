<?php
namespace WWOPN_Podcast;

trait CPTTrait {

	static $metakeys = [];

	static function parentInit() {
		\add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);
		\add_action('edit_form_after_title', [__CLASS__, 'addPostTitleBoxes'], 10, 1);
		\add_action('save_post', [__CLASS__, 'saveMetaKeys'], 10, 1);
		\add_action('wp_ajax_autosave_' . PREFIX . '_meta', [__CLASS__, 'handleAutosave']);

		\add_filter('wp_insert_post_data', [__CLASS__, 'generateExcerpt'], 20, 2);

		\add_action('post_submitbox_start', [__CLASS__, 'addPostNonce'], 20, 1);
	}

	/**
	 * Add post_nonce to our field
	 * @param WP_Post $post
	 * @return void
	 */
	static function addPostNonce($post) {
		if ( ! isOurPost($post)) {
			return;
		}
		wp_nonce_field('post_nonce', 'post_nonce');
	}

	/**
	 * Sanitize a given value by type rules
	 * @param  string  $type     Type rule
	 * @param  string  $value    Value to sanitize
	 * @param  boolean $saveTags If HTML should be preserved
	 * @param  integer $post_id  Post ID
	 * @return mixed             Sanitized value
	 */
	static function sanitizeValue($type = 'string', $value, $saveTags = false, $post_id = 0) {
		switch($type) {
			case 'url':
				return (string) \esc_url_raw($value);
				break;
			case 'textarea':
				return $saveTags ? (string) self::stripWhitespace($value, $post_id) : (string) \sanitize_textarea_field($value);
				break;
			case 'int':
			case 'integer':
			case 'featured_image':
				return (int) trim($value);
				break;
			default:
				return $saveTags ? (string) self::stripWhitespace($value, $post_id) : (string) \sanitize_text_field($value);
		}
	}

	/**
	 * Strip whitespace at the end of Podcast post content
	 * @param  string $data
	 * @param  object $post
	 * @return string
	 */
	static function stripWhitespace($data, $post) {
		if (is_int($post)) {
			$post = get_post($post);
		}

		if (is_object($post)) {
			$post = (array) $post;
		}

		if ( ! isOurPost($post)) {
			return $data;
		}

		$text = $data;
		if (is_array($data) &&  array_key_exists('post_content', $data)) {
			$text = $data['post_content'];
		}

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
		$quotes = array(
		    "\xC2\xAB"     => '"', // « (U+00AB) in UTF-8
		    "\xC2\xBB"     => '"', // » (U+00BB) in UTF-8
		    "\xE2\x80\x98" => "'", // ‘ (U+2018) in UTF-8
		    "\xE2\x80\x99" => "'", // ’ (U+2019) in UTF-8
		    "\xE2\x80\x9A" => "'", // ‚ (U+201A) in UTF-8
		    "\xE2\x80\x9B" => "'", // ‛ (U+201B) in UTF-8
		    "\xE2\x80\x9C" => '"', // “ (U+201C) in UTF-8
		    "\xE2\x80\x9D" => '"', // ” (U+201D) in UTF-8
		    "\xE2\x80\x9E" => '"', // „ (U+201E) in UTF-8
		    "\xE2\x80\x9F" => '"', // ‟ (U+201F) in UTF-8
		    "\xE2\x80\xB9" => "'", // ‹ (U+2039) in UTF-8
		    "\xE2\x80\xBA" => "'", // › (U+203A) in UTF-8
		);
		$clean = strtr($clean, $quotes);
		$clean = str_replace('&nbsp;', '', $clean);

		$return = trim($clean);

		if (is_array($data) && array_key_exists('post_content', $data)) {
			$data['post_content'] = $return;
			$return = $data;
		}
		return $return;
	}

	/**
	 * Determine if request is safe to save metadata in
	 * @return boolean
	 */
	static function safeToSave() {
		// Do not save in WP's own autosave, request doesn't contain metas!
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( ! isPost()) {
			return false;
		}

		if ( ! \current_user_can('edit_pages')) {
			return false;
		}

		return true;
	}

	/**
	 * Save a given meta key value as a type
	 * @param integer $post_id  post ID for metadata
	 * @param string  $key      registered metakey
	 * @param string  $type     coercive type
	 * @param boolean $saveTags If HTML should be preserved
	 * @return void
	 */
	static function saveMeta($post_id, $key, $type = 'string', $saveTags = false) {
		if ( ! self::safeToSave()) {
			return;
		}

		if (testPostValue($key, true)) {
			$value = $_POST[$key];
			if ($type === 'multi') {
				$original = \get_post_meta($post_id, $key, true);
				if (! $original) {
					$original = array();
				}
				foreach(self::$metakeys[$key]->subtypes as $subkey=>$subtype) {
					if (array_key_exists($subkey, $value)) {
						$value[$subkey] = self::sanitizeValue($subtype->type, $value[$subkey], $subtype->saveTags, $post_id);
					}
				}
				$value = array_merge($original, $value);
			} else {
				$value = self::sanitizeValue($type, $value, $saveTags, $post_id);
			}
			$success = \update_post_meta($post_id, $key, $value);
			if ($success) {
				return true;
			}
			return false;
		}

		\delete_post_meta($post_id, $key);
		return -1;
	}

	/**
	 * Verify the nonce in the request, AJAX or POST. Kills the request otherwise.
	 * @return void
	 */
	static function verifyNonces() {
		if (isAjax() && ! \check_ajax_referer('ajax_nonce', 'nonce', false)) {
			\wp_send_json(['success' => false, 'error' => 'Invalid nonce.', $_REQUEST]);
		}
		if (isPost() && array_key_exists('post_nonce', $_POST) && ! \wp_verify_nonce($_POST['post_nonce'], 'post_nonce')) {
			\wp_die('Invalid nonce');
		}
	}

	/**
	 * Save all metakeys in the request
	 * @param  integer $post_id Post ID
	 * @return void
	 */
	static function saveMetaKeys($post_id) {
		if ( ! isOurPost($post_id)) {
			return false;
		}
		self::verifyNonces();
		foreach (self::$metakeys as $metakey) {
			self::saveMeta($post_id, $metakey->key, $metakey->type, $metakey->saveTags);
		}
	}

	/**
	 * Handle custom AJAX autosave event to save metakeys in the request
	 */
	static function handleAutosave() {
		if ( ! isAjax()) {
			wp_send_json(['success' => false, 'msg' => 'Not an AJAX request'], 400);
		}

		if ( ! testPostValue('post_ID')) {
			wp_send_json(['success' => false, 'msg' => 'Request must contain post_ID'], 400);
		}

		self::verifyNonces();

		// Check that we're in our own posts
		if ( ! isOurPost($_POST['post_ID'])) {
			wp_send_json(['success' => false, 'msg' => PREFIX . ' autosave called outsite of ' . PREFIX]);
		}
		
		$affected = [];
		$success = true;
		foreach($_POST as $key=>$value) {
			if (array_key_exists($key, self::$metakeys)) {
				$result = self::saveMeta(
					$_POST['post_ID'],
					self::$metakeys[$key]->key,
					self::$metakeys[$key]->type,
					self::$metakeys[$key]->saveTags
				);
				$affected[] = [
					'key' => $key,
					'value' => $_POST[$key],
					'success' => $result
				];
				if ($success > 0 && $result < 1) {
					$success = $result;
				}
			}
		}

		$response = [
			'success' => $success,
			'msg' => 'Autosaved.',
			'affected' => $affected,
			'new_nonce' => wp_create_nonce('ajax_nonce')
		];

		if ($success) {
			wp_send_json($response);
		} else {
			$response['msg'] = 'Autosave failed!';
			wp_send_json($response, 400);
		}
	}

	/**
	 * Generate excerpts if one is not set. Takes the first paragraph
	 * if it can find one, trims it, then ends on a word.
	 * @param array $data
	 * @param object $post
	 * @return array
	 */
	static function generateExcerpt($data, $post) {
		if (
			! isOurPost($post) ||
			empty($data['post_content']) ||
			$data['post_status'] === 'inherit' ||
			$data['post_status'] === 'trash' ||
			! empty($data['post_excerpt']) // leave existing excerpts alone
		) {
			return $data;
		}

		// Remove bad stuff
		$text = \strip_shortcodes($data['post_content']);
		$text = \apply_filters('the_content', $text);
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = html_entity_decode($text);

		// Get the first paragraph
		if (stristr($text, '</p>')) {
			$text = substr($text, 0, stripos($text, '</p>') + 4);
		} else if (strstr($text, "\n\n")) {
			$text = substr($text, 0, stripos($text, "\n\n"));
		}

		// Trim to maximum excerpt length
		$length = \apply_filters('excerpt_length', 55);
		$text = \wp_trim_words($text, $length, '');

		// End on a sentance ending
		$allowed_end = array('.', '!', '?', '...', '…', '&hellip;');
		$words = explode(' ', $text);
		$found = false;
		$last = '';
		while( ! $found && ! empty($words)) {
			$last = array_pop($words);
			$r = '';
		    for ($i = mb_strlen($last); $i>=0; $i--) {
		        $r .= mb_substr($last, $i, 1);
		    }
			$end = $r;
			$found = in_array(mb_substr($end, 0, 1), $allowed_end);
		}
		if ( ! empty($words)) {
			$text = rtrim(
				implode(
					' ',
					$words
				) . ' ' . $last
			);
		}

		$data['post_excerpt'] = $text;

		return $data;
	}

	/**
	 * Register a new metakey
	 * @param  array $options
	 * @return void
	 */
	static function registerMetaBox($options) {
		$defaults = [
			'key' => null,
			'title' => null,
			'type' => null,
			'subtypes' => array(),
			'saveTags' => false,
			'display_func' => function($post, $key, $type) {
				self::displayMetaBox($post, $key, $type);
			},
			'howto' => null,
			'spellcheck' => false,
			'autocomplete' => 'off',
			'context' => 'advanced',
			'priority' => 'default',
			'required' => false,
			'pattern' => null,
		];
		$opts = (object) array_merge($defaults, $options);

		if ($opts->subtypes && count($opts->subtypes)) {
			$subtypeDefaults = array(
				'key' => null,
				'type' => 'string',
				'title' => false,
				'saveTags' => false,
				'howto' => null,
				'required' => false,
				'pattern' => null,
				'spellcheck' => false,
				'autocomplete' => 'off',
			);
			foreach($opts->subtypes as $key=>$subtype) {
				$opts->subtypes[$key] = (object) array_merge($subtypeDefaults, $subtype);
			}
		} 

		self::$metakeys[$opts->key] = $opts;
	}

	/**
	 * Create metaboxes for registered keys
	 */
	static function addMetaBoxes() {
		foreach(self::$metakeys as $meta) {
			if ($meta->context == 'normal' || $meta->context == 'side' || $meta->context == 'advanced') {
				\add_meta_box(
					$meta->key,
					esc_html__($meta->title),
					function($post) use ($meta) {
						call_user_func_array($meta->display_func, [$post, $meta->key, $meta->type]);
					},
					PREFIX,
					$meta->context,
					$meta->priority
				);
			}
		}
	}

	/**
	 * Create metaboxe under post title input
	 * @param WP_Post $post
	 */
	static function addPostTitleBoxes($post) {
		if ( ! isOurPost($post)) {
			return;
		}
		foreach(self::$metakeys as $meta) {
			if ($meta->context === 'post-title') {
				$value = \get_post_meta($post->ID, $meta->key, true);

				?>
					<div class="wpn_meta_autosave wpn_meta-posttitle">
						<?php self::displayField_TEXT($meta, $value) ?>
					</div>
				<?php
			}
		}
	}

	/**
	 * Display a metabox for a given key
	 * @param  WP_Post $post
	 * @param  string  $key
	 * @return void
	 */
	static function displayMetaBox($post, $key, $type="string") {
		if ( ! array_key_exists($key, self::$metakeys)) {
			throw new \Exception('Attempted to autocreate a metabox for a key which does not exist!');
		}

		$meta = self::$metakeys[$key];
		$value = \get_post_meta($post->ID, $meta->key, true);

		?>
		<div class="wpn_meta_autosave <?=$type?>">
			<?php self::outputType($meta, $value) ?>
			<?php if ($type == 'multi'): ?>
				<?=$meta->howto?>
			<?php endif ?>
		</div>
		<?php
	}

	/**
	 * Output the form field for a given meta
	 * @param  object $meta
	 * @param  mixed $value Current value of field
	 * @return void
	 */
	static function outputType($meta, $value) {
		switch($meta->type) {
			case 'string':
			case 'text':
				return self::displayField_TEXT($meta, $value);
				break;
			case 'url':
				return self::displayField_URL($meta, $value);
				break;
			case 'textarea':
				return self::displayField_TEXTAREA($meta, $value);
				break;
			case 'checkbox':
				return self::displayField_CHECKBOX($meta, $value);
				break;
			case 'featured_image':
				return self::displayField_IMAGEBOX($meta, $value);
			case 'multi':
				foreach($meta->subtypes as $subkey=>$submeta) {
					if (is_array($value) && array_key_exists($subkey, $value)) {
						$subvalue = $value[$subkey];
					} else {
						$subvalue = null;
					}
					$submeta->key = $meta->key . '[' . $submeta->key . ']';
					self::outputType($submeta, $subvalue);
				}
				return;
				break;
		}
		throw new \Exception('Attempted to autocreate a metabox with an unsupported type.');
	}

	static function displayField_TEXT($meta, $value) {
		?>

		<p>
			<label for="meta_text_<?=$meta->key?>"><?=$meta->title?></label>
			<input type="text" name="<?=$meta->key?>" size="30" value="<?=esc_attr($value)?>" id="meta_text_<?=$meta->key?>" spellcheck="<?=$meta->spellcheck ?>" autocomplete="<?=$meta->autocomplete ?>"
				<?php if ($meta->required): ?>
					required="<?=$meta->required ?>"
				<?php endif ?>
				<?php if ($meta->pattern): ?>
					pattern="<?=$meta->pattern ?>"
				<?php endif ?>
			>
			<?php if ($meta->howto): ?>
				<?=$meta->howto ?>
			<?php endif ?>
		</p>

		<?php
	}

	static function displayField_TEXTAREA($meta, $value) {
		?>

		<p>
			<label class="screen-reader-text" for="excerpt"><?=$meta->title?></label>
			<textarea class="wpn-meta-autosave" name="<?=$meta->key?>" style="display:block;width:100%;height:8em;margin:12px 0 0;"
				<?php if ($meta->required): ?>
					required="<?=$meta->required ?>"
				<?php endif ?>
				<?php if ($meta->pattern): ?>
					pattern="<?=$meta->pattern ?>"
				<?php endif ?>
			><?=esc_textarea($value) ?></textarea>
			<?php if ($meta->howto): ?>
				<?=$meta->howto ?>
			<?php endif ?>
		</p>

		<?php
	}

	static function displayField_URL($meta, $value) {
		?>

		<p>
			<label for="meta_text_<?=$meta->key?>"><?=$meta->title?></label>
			<input type="url" name="<?=$meta->key?>" size="30" value="<?=esc_attr($value)?>" id="meta_text_<?=$meta->key?>" spellcheck="false" autocomplete="off"
				<?php if ($meta->required): ?>
					required="<?=$meta->required ?>"
				<?php endif ?>
				<?php if ($meta->pattern): ?>
					pattern="<?=$meta->pattern ?>"
				<?php endif ?>
			>
			<?php if ($meta->howto): ?>
				<?=$meta->howto ?>
			<?php endif ?>
		</p>

		<?php
	}


	static function displayField_IMAGEBOX($meta, $value) {
		$key = $meta->key;
		$has_image = false;
		$image = '<img width="100%" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">';

		if ($value && \get_post($value)) {
			$has_image = true;
			$image = \wp_get_attachment_image($value, [266, 266]);
		}

		include __DIR__ . '/assets/editor/template.meta.headerimage.php';
	}

}