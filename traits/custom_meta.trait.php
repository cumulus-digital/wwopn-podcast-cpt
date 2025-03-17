<?php

namespace WWOPN_Podcast;

trait CustomMetaboxes {
	public static $metakeys = array();

	public static function parentInit() {
		\add_action( 'add_meta_boxes_' . PREFIX, array( __CLASS__, 'addMetaBoxes' ), 10, 1 );
		\add_action( 'edit_form_after_title', array( __CLASS__, 'addPostTitleBoxes' ), 10, 1 );
		\add_action( 'save_post', array( __CLASS__, 'saveMetaKeys' ), 10, 1 );
		\add_action( 'wp_ajax_autosave_' . PREFIX . '_meta', array( __CLASS__, 'handleAutosave' ) );

		\add_filter( 'wp_insert_post_data', array( __CLASS__, 'generateExcerpt' ), 20, 2 );

		\add_action( 'post_submitbox_start', array( __CLASS__, 'addPostNonce' ), 20, 1 );
	}

	public static function enqueueSortableScript() {
		\wp_enqueue_script( 'sortable', 'https://cdn.jsdelivr.net/npm/@shopify/draggable@1.0.0-beta.7/lib/sortable.js' );
	}

	/**
	 * Add post_nonce to our field.
	 *
	 * @param WP_Post $post
	 */
	public static function addPostNonce( $post ) {
		if ( ! isOurPost( $post ) ) {
			return;
		}
		\wp_nonce_field( 'post_nonce', 'post_nonce' );
	}

	/**
	 * Sanitize a given value by type rules.
	 *
	 * @param string $type     Type rule
	 * @param string $value    Value to sanitize
	 * @param bool   $saveTags If HTML should be preserved
	 * @param int    $post_id  Post ID
	 *
	 * @return mixed Sanitized value
	 */
	public static function sanitizeValue( $type = 'string', $value = null, $saveTags = false, $post_id = 0 ) {
		switch ( $type ) {
			case 'color':
				return (string) \sanitize_textarea_field( self::stripWhitespace( $value, $post_id ) );

				break;
			case 'url':
				return (string) \esc_url_raw( $value );

				break;
			case 'textarea':
				return $saveTags ? (string) self::stripWhitespace( $value, $post_id ) : (string) \sanitize_textarea_field( $value );

				break;
			case 'int':
			case 'integer':
			case 'featured_image':
				return (int) \trim( $value );

				break;
			default:
				return $saveTags ? (string) self::stripWhitespace( $value, $post_id ) : (string) \sanitize_text_field( $value );
		}
	}

	/**
	 * Strip whitespace at the end of Podcast post content.
	 *
	 * @param string $data
	 * @param object $post
	 *
	 * @return string
	 */
	public static function stripWhitespace( $data, $post ) {
		if ( \is_int( $post ) ) {
			$post = \get_post( $post );
		}

		if ( \is_object( $post ) ) {
			$post = (array) $post;
		}

		if ( ! isOurPost( $post ) ) {
			return $data;
		}

		$text = $data;
		if ( \is_array( $data ) && \array_key_exists( 'post_content', $data ) ) {
			$text = $data['post_content'];
		}

		$clean  = \iconv( 'UTF-8', 'ASCII//TRANSLIT//IGNORE', $text );
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
		$clean = \strtr( $clean, $quotes );
		$clean = \str_replace( '&nbsp;', '', $clean );

		$return = \trim( $clean );

		if ( \is_array( $data ) && \array_key_exists( 'post_content', $data ) ) {
			$data['post_content'] = $return;
			$return               = $data;
		}

		return $return;
	}

	/**
	 * Determine if request is safe to save metadata in.
	 *
	 * @return bool
	 */
	public static function safeToSave() {
		// Do not save in WP's own autosave, request doesn't contain metas!
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( ! isPost() ) {
			return false;
		}

		if ( ! \current_user_can( 'edit_published_pages' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Save a given meta key value as a type.
	 *
	 * @param int    $post_id  post ID for metadata
	 * @param string $key      registered metakey
	 * @param string $type     coercive type
	 * @param bool   $saveTags If HTML should be preserved
	 */
	public static function saveMeta( $post_id, $key, $type = 'string', $saveTags = false ) {
		if ( ! self::safeToSave() ) {
			return;
		}

		if ( testPostValue( $key, true ) ) {
			$value = $_POST[$key];
			if ( $type === 'multi' ) {
				$original = \get_post_meta( $post_id, $key, true );
				if ( ! $original ) {
					$original = array();
				}
				foreach ( $value as $subkey => $subvalue ) {
					// Verify that we're getting a registered subtype that is not disabled
					if (
						\array_key_exists( $subkey, self::$metakeys[$key]->subtypes )
						&& ! self::$metakeys[$key]->subtypes[$subkey]->disabled
					) {
						$subtype        = self::$metakeys[$key]->subtypes[$subkey];
						$value[$subkey] = self::sanitizeValue( $subtype->type, $value[$subkey], $subtype->saveTags, $post_id );
					} else {
						unset( $value[$subkey] );
					}
				}
				$newvalue = \array_merge( $original, $value );
				if ( \property_exists( self::$metakeys[$key], 'sortable' ) && self::$metakeys[$key]->sortable ) {
					$newvalue = self::orderKeySort( $newvalue, \array_keys( $value ) );
				}
				$value = $newvalue;
			} else {
				$value = self::sanitizeValue( $type, $value, $saveTags, $post_id );
			}
			$success = \update_post_meta( $post_id, $key, $value );
			if ( $success ) {
				return true;
			}

			return false;
		}

		\delete_post_meta( $post_id, $key );

		return -1;
	}

	/**
	 * Sort a multidimensional array by the 'order' key on its items.
	 *
	 * @param array $arr
	 *
	 * @return array
	 */
	public static function orderSort( $arr ) {
		\uasort( $arr, function ( $a, $b ) {
			if (
				! \is_array( $a ) || ! \is_array( $b )
																			   || ! \array_key_exists( 'order', $a ) || ! \array_key_exists( 'order', $b )
			) {
				return 0;
			}

			return $a['order'] < $b['order'] ? -1 : 1;
		} );

		return $arr;
	}

	/**
	 * Sort keys of an array against values in an order array.
	 *
	 * @param array $arr
	 * @param array $order_keys
	 *
	 * @return array
	 */
	public static function orderKeySort( $arr, $order_keys ) {
		\uksort( $arr, function ( $key1, $key2 ) use ( $order_keys ) {
			$pos1 = \array_search( $key1, $order_keys );
			$pos2 = \array_search( $key2, $order_keys );
			if ( $pos1 === $pos2 ) {
				return 0;
			}

			return $pos1 < $pos2 ? -1 : 1;
		} );

		return $arr;
	}

	/**
	 * Verify the nonce in the request, AJAX or POST. Kills the request otherwise.
	 */
	public static function verifyNonces() {
		if ( isAjax() && ! \check_ajax_referer( 'ajax_nonce', 'nonce', false ) ) {
			\wp_send_json( array( 'success' => false, 'error' => 'Invalid nonce.', $_REQUEST ) );
		}
		if ( isPost() && \array_key_exists( 'post_nonce', $_POST ) && ! \wp_verify_nonce( $_POST['post_nonce'], 'post_nonce' ) ) {
			\wp_die( 'Invalid nonce' );
		}
	}

	/**
	 * Save all metakeys in the request.
	 *
	 * @param int $post_id Post ID
	 */
	public static function saveMetaKeys( $post_id ) {
		if ( ! isOurPost( $post_id ) ) {
			return false;
		}
		self::verifyNonces();
		foreach ( self::$metakeys as $metakey ) {
			self::saveMeta( $post_id, $metakey->key, $metakey->type, $metakey->saveTags );
		}
	}

	/**
	 * Handle custom AJAX autosave event to save metakeys in the request.
	 */
	public static function handleAutosave() {
		if ( ! isAjax() ) {
			\wp_send_json( array( 'success' => false, 'msg' => 'Not an AJAX request' ), 400 );
		}

		if ( ! testPostValue( 'post_ID' ) ) {
			\wp_send_json( array( 'success' => false, 'msg' => 'Request must contain post_ID' ), 400 );
		}

		self::verifyNonces();

		// Check that we're in our own posts
		if ( ! isOurPost( $_POST['post_ID'] ) ) {
			\wp_send_json( array( 'success' => false, 'msg' => PREFIX . ' autosave called outsite of ' . PREFIX ) );
		}

		$affected = array();
		$success  = true;
		foreach ( $_POST as $key => $value ) {
			if ( \array_key_exists( $key, self::$metakeys ) ) {
				$result = self::saveMeta(
					$_POST['post_ID'],
					self::$metakeys[$key]->key,
					self::$metakeys[$key]->type,
					self::$metakeys[$key]->saveTags
				);
				$affected[] = array(
					'key'     => $key,
					'value'   => $_POST[$key],
					'success' => $result,
				);
				if ( $success > 0 && $result < 1 ) {
					$success = $result;
				}
			}
		}

		$response = array(
			'success'   => $success,
			'msg'       => 'Autosaved.',
			'affected'  => $affected,
			'new_nonce' => \wp_create_nonce( 'ajax_nonce' ),
		);

		if ( $success ) {
			\wp_send_json( $response );
		} else {
			$response['msg'] = 'Autosave failed!';
			\wp_send_json( $response, 400 );
		}
	}

	/**
	 * Generate excerpts if one is not set. Takes the first paragraph
	 * if it can find one, trims it, then ends on a word.
	 *
	 * @param array  $data
	 * @param object $post
	 *
	 * @return array
	 */
	public static function generateExcerpt( $data, $post ) {
		if (
			! isOurPost( $post )
			|| empty( $data['post_content'] )
			|| $data['post_status'] === 'inherit'
			|| $data['post_status'] === 'trash'
			|| ! empty( $data['post_excerpt'] ) // leave existing excerpts alone
		) {
			return $data;
		}

		// Remove bad stuff
		$text = \strip_shortcodes( $data['post_content'] );
		$text = \apply_filters( 'the_content', $text );
		$text = \str_replace( ']]>', ']]&gt;', $text );
		$text = \html_entity_decode( $text );

		// Get the first paragraph
		if ( \mb_stristr( $text, '</p>' ) ) {
			$text = \mb_substr( $text, 0, \mb_stripos( $text, '</p>' ) + 4 );
		} elseif ( \mb_strstr( $text, "\n\n" ) ) {
			$text = \mb_substr( $text, 0, \mb_stripos( $text, "\n\n" ) );
		}

		// Trim to maximum excerpt length
		$length = \apply_filters( 'excerpt_length', 55 );
		$text   = \wp_trim_words( $text, $length, '' );

		// End on a sentance ending
		$allowed_end = array( '.', '!', '?', '...', '…', '&hellip;' );
		$words       = \explode( ' ', $text );
		$found       = false;
		$last        = '';
		while ( ! $found && ! empty( $words ) ) {
			$last = \array_pop( $words );
			$r    = '';
			for ( $i = \mb_strlen( $last ); $i >= 0; --$i ) {
				$r .= \mb_substr( $last, $i, 1 );
			}
			$end   = $r;
			$found = \in_array( \mb_substr( $end, 0, 1 ), $allowed_end );
		}
		if ( ! empty( $words ) ) {
			$text = \rtrim(
				\implode(
					' ',
					$words
				) . ' ' . $last
			);
		}

		$data['post_excerpt'] = $text;

		return $data;
	}

	/**
	 * Register a new metakey.
	 *
	 * @param array $options
	 */
	public static function registerMeta( $options ) {
		$defaults = array(
			'key'         => null,
			'title'       => null,
			'type'        => null,
			'subtypes'    => array(),
			'sortable'    => false,
			'saveTags'    => false,
			'displayFunc' => function ( $post, $key, $type ) {
				self::displayMetaBox( $post, $key, $type );
			},
			'howto'        => null,
			'spellcheck'   => false,
			'autocomplete' => 'off',
			'context'      => 'advanced',
			'priority'     => 'default',
			'required'     => false,
			'pattern'      => null,
		);
		$opts = (object) \array_merge( $defaults, $options );

		if ( $opts->subtypes && \count( $opts->subtypes ) ) {
			$subtypeDefaults = array(
				'key'          => null,
				'type'         => 'string',
				'title'        => false,
				'saveTags'     => false,
				'howto'        => null,
				'required'     => false,
				'pattern'      => null,
				'spellcheck'   => false,
				'autocomplete' => 'off',
				'disabled'     => false,
			);
			foreach ( $opts->subtypes as $key => $subtype ) {
				$opts->subtypes[$key] = (object) \array_merge( $subtypeDefaults, $subtype );
			}
		}

		if ( $opts->sortable ) {
			\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueueSortableScript' ) );
		}

		self::$metakeys[$opts->key] = $opts;
	}

	/**
	 * Create metaboxes for registered keys.
	 *
	 * @param WP_POST $post
	 */
	public static function addMetaBoxes( $post ) {
		foreach ( self::$metakeys as $meta ) {
			if ( $meta->context == 'normal' || $meta->context == 'side' || $meta->context == 'advanced' ) {
				\add_meta_box(
					$meta->key,
					\esc_html__( $meta->title ),
					function ( $post ) use ( $meta ) {
						\call_user_func_array( $meta->displayFunc, array( $post, $meta->key, $meta->type ) );
					},
					PREFIX,
					$meta->context,
					$meta->priority
				);
			}
		}
	}

	/**
	 * Create metaboxe under post title input.
	 *
	 * @param WP_Post $post
	 */
	public static function addPostTitleBoxes( $post ) {
		if ( ! isOurPost( $post ) ) {
			return;
		}
		foreach ( self::$metakeys as $meta ) {
			if ( $meta->context === 'post-title' ) {
				$value = \get_post_meta( $post->ID, $meta->key, true );

				?>
					<div class="wpn_meta_autosave wpn_meta-posttitle">
						<?php self::outputType( $meta, $value ); ?>
					</div>
				<?php
			}
		}
	}

	/**
	 * Display a metabox for a given key.
	 *
	 * @param WP_Post $post
	 * @param string  $key
	 * @param mixed   $type
	 */
	public static function displayMetaBox( $post, $key, $type = 'string' ) {
		if ( ! \array_key_exists( $key, self::$metakeys ) ) {
			throw new \Exception( 'Attempted to autocreate a metabox for a key which does not exist!' );
		}

		$meta  = self::$metakeys[$key];
		$value = \get_post_meta( $post->ID, $meta->key, true );

		?>
		<div class="wpn_meta_autosave <?php echo $type; ?> <?php echo $meta->sortable ? 'sortable' : ''; ?>">
			<?php self::outputType( $meta, $value ); ?>
			<?php if ( $type == 'multi' ): ?>
				<?php echo $meta->howto; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Output the form field for a given meta.
	 *
	 * @param object $meta
	 * @param mixed  $value   Current value of field
	 * @param array  $options (Optional)
	 */
	public static function outputType( $meta, $value, array $options = array() ) {
		if ( \property_exists( $meta, 'disabled' ) && (bool) $meta->disabled ) {
			return;
		}
		$options = \array_merge(
			array(
				'sortable' => false,
			),
			$options
		);
		switch ( $meta->type ) {
			case 'color':
				return self::displayField_COLOR( $meta, $value, $options );

				break;
			case 'string':
			case 'text':
				return self::displayField_TEXT( $meta, $value, $options );

				break;
			case 'url':
				return self::displayField_URL( $meta, $value, $options );

				break;
			case 'textarea':
				return self::displayField_TEXTAREA( $meta, $value, $options );

				break;
			case 'checkbox':
				return self::displayField_CHECKBOX( $meta, $value, $options );

				break;
			case 'featured_image':
				return self::displayField_IMAGEBOX( $meta, $value, $options );
			case 'multi':
				$sortable = false;

				// Generate a temporary array with all possible keys for a baseline
				$default = array();
				foreach ( $meta->subtypes as $subkey => $submeta ) {
					$default[$subkey] = '';
				}
				$temp_value = \array_merge( $default, (array) $value );

				// Order by original value's keys
				$value = self::orderKeySort( $temp_value, \array_keys( (array) $value ) );

				if ( \property_exists( $meta, 'sortable' ) && $meta->sortable === true ) {
					$sortable = true;
				}
				$options['sortable'] = $sortable;

				foreach ( (array) $value as $subkey => $subvalue ) {
					if ( \array_key_exists( $subkey, $meta->subtypes ) ) {
						$submeta      = $meta->subtypes[$subkey];
						$submeta->key = $meta->key . '[' . $submeta->key . ']';
						self::outputType( $submeta, $subvalue, $options );
					}
				}

				return;

				break;
		}

		throw new \Exception( 'Attempted to autocreate a metabox with an unsupported type.' );
	}

	public static function displayField_COLOR( $meta, $value, array $options = array() ) {
		?>
		<p class="<?php echo $options['sortable'] ? 'sortable' : ''; ?>">
			<label for="meta_text_<?php echo $meta->key; ?>"><?php echo \esc_html( $meta->title ); ?></label>
			<input type="color" name="<?php echo $meta->key; ?>" size="30" value="<?php echo \esc_attr( $value ); ?>" id="meta_text_<?php echo $meta->key; ?>" spellcheck="<?php echo $meta->spellcheck; ?>" autocomplete="<?php echo $meta->autocomplete; ?>"
				<?php if ( $meta->required ): ?>
					required="<?php echo \esc_attr( $meta->required ); ?>"
				<?php endif; ?>
				<?php if ( $meta->pattern ): ?>
					pattern="<?php echo \esc_attr( $meta->pattern ); ?>"
				<?php endif; ?>
			>
			<?php if ( $meta->howto ): ?>
				<label class="howto"><?php echo $meta->howto; ?></label>
			<?php endif; ?>
		</p>

		<?php
	}

	public static function displayField_TEXT( $meta, $value, array $options = array() ) {
		?>

		<p class="<?php echo $options['sortable'] ? 'sortable' : ''; ?>">
			<label for="meta_text_<?php echo $meta->key; ?>"><?php echo \esc_html( $meta->title ); ?></label>
			<input type="text" name="<?php echo $meta->key; ?>" size="30" value="<?php echo \esc_attr( $value ); ?>" id="meta_text_<?php echo $meta->key; ?>"
				<?php if ( $meta->required ): ?>
					required="<?php echo \esc_attr( $meta->required ); ?>"
				<?php endif; ?>
				<?php if ( $meta->pattern ): ?>
					pattern="<?php echo \esc_attr( $meta->pattern ); ?>"
				<?php endif; ?>
			>
			<?php if ( $meta->howto ): ?>
				<label class="howto"><?php echo $meta->howto; ?></label>
			<?php endif; ?>
		</p>

		<?php
	}

	public static function displayField_TEXTAREA( $meta, $value, array $options = array() ) {
		?>

		<p class="<?php echo $options['sortable'] ? 'sortable' : ''; ?>">
			<label class="screen-reader-text" for="excerpt"><?php echo \esc_html( $meta->title ); ?></label>
			<textarea class="wpn-meta-autosave" name="<?php echo $meta->key; ?>" style="display:block;width:100%;height:8em;margin:12px 0 0;"
				<?php if ( $meta->required ): ?>
					required="<?php echo \esc_attr( $meta->required ); ?>"
				<?php endif; ?>
				<?php if ( $meta->pattern ): ?>
					pattern="<?php echo \esc_attr( $meta->pattern ); ?>"
				<?php endif; ?>
			><?php echo \esc_textarea( $value ); ?></textarea>
			<?php if ( $meta->howto ): ?>
				<label class="howto"><?php echo $meta->howto; ?></label>
			<?php endif; ?>
		</p>

		<?php
	}

	public static function displayField_URL( $meta, $value, array $options = array() ) {
		?>

		<p class="<?php echo $options['sortable'] ? 'sortable' : ''; ?>">
			<label for="meta_text_<?php echo $meta->key; ?>"><?php echo \esc_html( $meta->title ); ?></label>
			<input type="url" name="<?php echo $meta->key; ?>" size="30" value="<?php echo \esc_attr( $value ); ?>" id="meta_text_<?php echo $meta->key; ?>" spellcheck="false" autocomplete="off"
				<?php if ( $meta->required ): ?>
					required="<?php echo \esc_attr( $meta->required ); ?>"
				<?php endif; ?>
				<?php if ( $meta->pattern ): ?>
					pattern="<?php echo \esc_attr( $meta->pattern ); ?>"
				<?php endif; ?>
			>
			<?php if ( $meta->howto ): ?>
				<label class="howto"><?php echo $meta->howto; ?></label>
			<?php endif; ?>
		</p>

		<?php
	}

	public static function displayField_IMAGEBOX( $meta, $value ) {
		$key       = $meta->key;
		$has_image = false;
		$image     = '<img width="100%" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">';

		if ( $value && \get_post( $value ) ) {
			$has_image = true;
			$image     = \wp_get_attachment_image( $value, array( 266, 266 ) );
		}

		include BASEPATH . '/assets/editor/template.meta.headerimage.php';
	}
}
