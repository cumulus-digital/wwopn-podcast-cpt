<?php
namespace WWOPN_Podcast;
?>
<section class="wpn-features wpn-f-genre wpn-f-genre-all">
	<ul class="wpn-f-container" style="<?=\esc_attr(\wp_kses($attr['container-css'], ''))?>">
		<?php foreach($pods as $pod): ?>
			<li style="<?=\esc_attr(\wp_kses($attr['icon-css'], ''))?>">
				<?php if ($pod->ID): ?>
				<a class="wpn-feature" href="<?=\get_post_permalink($pod->ID)?>" title="<?=\esc_attr($pod->post_title) ?>">
					<?=\get_the_post_thumbnail($pod, $attr['size-name'], ['alt' => \esc_attr($pod->post_title)])?>
				</a>
				<?php else: ?>
					No Podcasts found.
				<?php endif ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>