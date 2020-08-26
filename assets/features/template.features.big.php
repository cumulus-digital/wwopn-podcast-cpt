<?php
	namespace WWOPN_Podcast;
?>
<section class="wpn-features wpn-f-bfg">
	<?php for ($i = 0, $j = 0; $i < 3; $i++): ?>
	<ul class="wpn-f-container">
		<li class="wpn-f-big">
			<a class="wpn-feature" href="<?=$features[$j]->permalink?>" title="<?=\esc_attr($features[$j]->post_title) ?>">
				<?php if (\is_admin()): ?>
					<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$features[$j]->id?>">
					<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
				<?php endif ?>
				<img
					<?php if (defined('HAS_LAZY') && ! \is_admin()): ?>
						src="" data-src="<?=$features[$j]->icon ?>"
					<?php else: ?>
						src="<?=$features[$j]->icon?>"
					<?php endif ?>
					<?php if (\is_admin()): ?>
						data-empty="<?=$features[$j]->empty?>"
					<?php endif ?>
					alt="<?=\esc_attr($features[$j]->post_title) ?>"
				>
				<?php $j++ ?>
			</a>
		</li>
		<li class="wpn-f-cluster">
			<ul>
				<li class="wpn-f-small">
					<a class="wpn-feature" href="<?=$features[$j]->permalink?>" title="<?=\esc_attr($features[$j]->post_title) ?>">
						<?php if (\is_admin()): ?>
							<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$features[$j]->id?>">
							<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
						<?php endif ?>
						<img
							<?php if (defined('HAS_LAZY') && ! \is_admin()): ?>
								src="" data-src="<?=$features[$j]->icon ?>"
							<?php else: ?>
								src="<?=$features[$j]->icon?>"
							<?php endif ?>
							<?php if (\is_admin()): ?>
								data-empty="<?=$features[$j]->empty?>"
							<?php endif ?>
							alt="<?=\esc_attr($features[$j]->post_title) ?>"
						>
						<?php $j++ ?>
					</a>
				</li>
				<li class="wpn-f-small">
					<a class="wpn-feature" href="<?=$features[$j]->permalink?>" title="<?=\esc_attr($features[$j]->post_title) ?>">
						<?php if (\is_admin()): ?>
							<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$features[$j]->id?>">
							<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
						<?php endif ?>
						<img
							<?php if (defined('HAS_LAZY') && ! \is_admin()): ?>
								src="" data-src="<?=$features[$j]->icon ?>"
							<?php else: ?>
								src="<?=$features[$j]->icon?>"
							<?php endif ?>
							<?php if (\is_admin()): ?>
								data-empty="<?=$features[$j]->empty?>"
							<?php endif ?>
							alt="<?=\esc_attr($features[$j]->post_title) ?>"
						>
						<?php $j++ ?>
					</a>
				</li>
			</ul>
		</li>
	</ul>
	<?php endfor; ?>
</section>