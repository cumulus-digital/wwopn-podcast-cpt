<?php
	namespace WWOPN_Podcast;
?>
<section class="wpn-features wpn-f-bfg">
	<?php for ($i = 0, $j = 0; $i < 3; $i++): ?>
	<ul class="wpn-f-container">
		<li class="wpn-f-big">
			<a class="wpn-feature" href="<?=$features[$j]->permalink?>">
				<?php if (\is_admin()): ?>
					<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$features[$j]->id?>">
					<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
				<?php endif ?>
				<img src="<?=$features[$j]->icon?>" data-empty="<?=\plugin_dir_url(__FILE__)?>bigx.svg">
				<?php $j++ ?>
			</a>
		</li>
		<li class="wpn-f-cluster">
			<ul>
				<li class="wpn-f-small">
					<a class="wpn-feature" href="<?=$features[$j]->permalink?>">
						<?php if (\is_admin()): ?>
							<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$features[$j]->id?>">
							<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
						<?php endif ?>
						<img src="<?=$features[$j]->icon?>" data-empty="<?=\plugin_dir_url(__FILE__)?>bigx.svg">
						<?php $j++ ?>
					</a>
				</li>
				<li class="wpn-f-small">
					<a class="wpn-feature" href="<?=$features[$j]->permalink?>">
						<?php if (\is_admin()): ?>
							<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$features[$j]->id?>">
							<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
						<?php endif ?>
						<img src="<?=$features[$j]->icon?>" data-empty="<?=\plugin_dir_url(__FILE__)?>bigx.svg">
						<?php $j++ ?>
					</a>
				</li>
			</ul>
		</li>
	</ul>
	<?php endfor; ?>
</section>