<?php
/**
 * General tools for WWOPN Podcast CPT
 */
namespace WWOPN_Podcast;

function __($string) {
	return \__($string, TXTDOMAIN);
}

function _e($string) {
	echo __($string);
}

function esc_html__($string) {
	return \esc_html__($string, TXTDOMAIN);
}

function esc_html_e($string) {
	echo esc_html__($string);
}

function _n($singular, $plural, $count) {
	return \_n($singular, $plural, $count, TXTDOMAIN);
}

/**
 * Determine if request is POST
 * @return boolean
 */
function isPost() {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		return true;
	}
	return false;
}

/**
 * Determine if request is AJAX
 * @return boolean
 */
function isAjax() {
	if (defined('DOING_AJAX') && DOING_AJAX) {
		return true;
	}
	return false;
}

/**
 * Determine if a given post or post ID is under our scope
 * @param  mixed  $post_id
 * @return boolean
 */
function isOurPost($post) {
	if (is_object($post) || is_array($post)) {
		$post = (object) $post;
		if (property_exists($post, 'ID')) {
			$id = $post->ID;
		} else if (property_exists($post, 'id')) {
			$id = $post->id;
		}
	} else {
		$id = $post;
	}
	$postObj = \get_post($id);
	if ($postObj && $postObj->post_type === PREFIX) {
		return true;
	}
	return false;
}

/**
 * Cast a given value
 * @param  mixed $val
 * @param  string $cast
 * @return mixed
 */
function cast($val, $cast) {
	if (is_null($val)) {
		return null;
	}
	switch ($cast) {
		case 'string':
		case 'text':
			$val = trim($val);
			break;
		case 'int':
		case 'integer':
			$val = (int) $val;
			break;
		case 'bool':
		case 'boolean':
			$val = (bool) $val;
		case 'array':
			$val = (array) $val;
			break;
		case 'object':
			$val = (object) $val;
			break;
		default:
			$val = null;
	}
	return $val;
}

/**
 * Test for existance of a POST value.
 * Optionally test if it is empty.
 * Optionally test if it is equal to a value.
 * @param  string  $key        POST key
 * @param  boolean $test_empty If true, test that key value is not an empty value
 * @param  mixed   $is_value   If set, test that key value is equal to given value
 * @return boolean
 */
function testPostValue($key, $test_empty = false, $is_value = -8675309) {
	if (isset($_POST[$key])) {
		return true;
	}
	if ($test_empty && ! empty($_POST[$key])) {
		return true;
	}
	if ($is_value !== -8675309 && $_POST[$key] === $is_value) {
		return true;
	}
	return false;
}

/**
 * Test for existance of a GET value.
 * Optionally test if it is empty.
 * Optionally test if it is equal to a value.
 * @param  string  $key        GET key
 * @param  boolean $test_empty If true, test that key value is not an empty value
 * @param  mixed   $is_value   If set, test that key value is equal to given value
 * @return boolean
 */
function testGetValue($key, $test_empty = false, $is_value = -8675309) {
	if (isset($_GET[$key])) {
		return true;
	}
	if ($test_empty && ! empty($_GET[$key])) {
		return true;
	}
	if ($is_value !== -8675309 && $_GET[$key] === $is_value) {
		return true;
	}
	return false;
}
