<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-grid tvo-set8-template tve_black">
	<?php foreach ( $testimonials as $testimonial ) : ?>
		<?php if ( ! empty( $testimonial ) ) : ?>
			<div class="tvo-item-col tvo-item-s12 tvo-item-m6 tvo-item-l4 ">
				<div class="tvo-testimonial-display-item tvo-apply-background">
					<div class="tvo-testimonial-image-cover tvo-testimonial-real-border" style="background-image: url(<?php echo $testimonial['picture_url'] ?>)">
						<img src="<?php echo $testimonial['picture_url'] ?>"
							 class="tvo-testimonial-image tvo-dummy-image" alt="profile-pic">
					</div>
					<div class="tvo-testimonial-quote<?php echo ( empty( $testimonial['title'] ) ) ? 'tvo-testimonial-quote-small' : '' ?>">
						<?php if ( ! empty( $testimonial['title'] ) ) : ?>
							<?php if ( ! empty( $config['show_title'] ) ) : ?>
								<h4>
									<?php echo $testimonial['title'] ?>
								</h4>
							<?php endif; ?>
						<?php else : ?>
							<span class="tvo-testimonial-name">
								<?php echo $testimonial['name'] ?>
								<?php if ( ! empty( $testimonial['role'] ) ) : ?>
									-
								<?php endif; ?>
							</span>
							<?php if ( ! empty( $config['show_role'] ) ) : ?>
								<span class="tvo-testimonial-role">
								<?php $role_wrap_before = empty( $config['show_site'] ) || empty( $testimonial['website_url'] ) ? '' : '<a href="' . $testimonial['website_url'] . '">';
								$role_wrap_after        = empty( $config['show_site'] ) || empty( $testimonial['website_url'] ) ? '' : '</a>';
								echo $role_wrap_before . $testimonial['role'] . $role_wrap_after; ?>
							</span>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $testimonial['title'] ) ) : ?>
						<div class="tvo-testimonial-info tvo-info-border">
							<span class="tvo-testimonial-name">
								<?php echo $testimonial['name'] ?>
								<?php if ( ! empty( $testimonial['role'] ) ) : ?>
									-
								<?php endif; ?>
							</span>
							<?php if ( ! empty( $config['show_role'] ) ) : ?>
								<span class="tvo-testimonial-role">
								<?php $role_wrap_before = empty( $config['show_site'] ) || empty( $testimonial['website_url'] ) ? '' : '<a href="' . $testimonial['website_url'] . '">';
								$role_wrap_after        = empty( $config['show_site'] ) || empty( $testimonial['website_url'] ) ? '' : '</a>';
								echo $role_wrap_before . $testimonial['role'] . $role_wrap_after; ?>
							</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<div class="tvo-testimonial-content tvo-relative">
						<?php echo $testimonial['content'] ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endforeach ?>
</div>

