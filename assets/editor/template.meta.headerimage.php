<?php
	namespace WWOPN_Podcast;
?>
<div
	class="meta_extraimage <?=$has_image ? 'has_image' : ''?>"
	data-uploader-title="<?=esc_html__('Choose an image')?>"
	data-uploader-button-text="<?=esc_html__('Set ' . $meta->title)?>"
>
	<?=$image?>

	<p class="hide-if-no-js click_to_edit">
		Click the image to edit or update
	</p>
	<p class="hide-if-no-js meta_extraimage-functions">
		<a href="#" class="meta_extraimage-remove">
			<?=esc_html__('Remove ' . $meta->title)?>
		</a>
		<a href="#" class="meta_extraimage-add">
			<?=esc_html__('Set ' . $meta->title)?>
		</a>
	</p>
	<?php if ($meta->howto): ?>
		<label class="howto"><?=$meta->howto ?></label>
	<?php endif ?>
	<input type="hidden" id="<?=$meta->key?>" class="image_id" name="<?=$meta->key?>" value="<?=($value) ? \esc_attr($value) : '' ?>">

</div>