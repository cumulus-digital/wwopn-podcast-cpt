<?php
namespace WWOPN_Podcast;
?>
<section class="wpn-features wpn-f-genre">
	<ul class="wpn-f-container">
		<?php foreach($features as $j=>$feature): ?>
			<li>
				<?php if ($feature->id || \is_admin()): ?>
				<a class="wpn-feature" href="<?=$feature->permalink?>" title="<?=\esc_attr($features[$j]->post_title) ?>">
					<?php if (\is_admin()): ?>
						<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$feature->id?>">
						<span class="wpn-f-clear" title="Clear this box"><span class="screen-reader-text">Clear this box.</span></span>
					<?php endif ?>
					<img
						<?php if (defined('HAS_LAZY') && ! \is_admin()): ?>
							src="" data-src="<?=$feature->icon ?>"
						<?php else: ?>
							src="<?=$feature->icon?>"
						<?php endif ?>
						<?php if (\is_admin()): ?>
							data-empty="<?=$feature->empty?>"
						<?php endif ?>
						alt="<?=\esc_attr($features[$j]->post_title) ?>"
					>
				</a>
				<?php endif ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>