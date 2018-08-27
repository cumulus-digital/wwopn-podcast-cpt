<?php
	namespace WWOPN_Podcast;
?>
<div
	class="wpn_meta_autosave meta_extraimage <?=$has_image ? 'has_image' : ''?>"
	data-uploader-title="<?=esc_html__('Choose an image')?>"
	data-uploader-button-text="<?=esc_html__('Set ' . $meta_display_name)?>"
>
	<?=\wp_nonce_field($key, $key . '-nonce')?>

	<?=$image?>

	<p class="hide-if-no-js howto">
		Click the image to edit or update
	</p>
	<p class="hide-if-no-js meta_extraimage-functions">
		<a href="#" class="meta_extraimage-remove">
			<?=esc_html__('Remove ' . $meta_display_name)?>
		</a>
		<a href="#" class="meta_extraimage-add">
			<?=esc_html__('Set ' . $meta_display_name)?>
		</a>
	</p>
	<input type="hidden" id="<?=$key?>" class="image_id" name="<?=$key?>" value="<?=$image_id?\esc_attr($image_id):''?>">

</div>