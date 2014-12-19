<?php
/**
 * The template for displaying Member Directory archive.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WIMP Member Directory
 */

get_header(); ?>

	<section class="main-body" class="site-content" role="main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<h1 class="page-title">Member Directory: Find A WIMP</h1>
			</header><!-- .page-header -->

			<?php while ( have_posts() ) : the_post(); ?>
				<?php $listing = wmd_get_listing(); ?>
				<section
					id="member-<?php echo esc_attr( $listing->member_id ); ?>"
					class="wimp-member-listing member-<?php echo esc_attr( $listing->member_id ); ?>">

					<header class="listing-header">

						<div class="listing-name">
							<?php if ( ! empty( $listing->logo_id ) ) : ?>
								<?php $logo = wp_get_attachment_image_src( $listing->logo_id, 'wmd_logo' ); ?>
								<img src="<?php echo esc_url( $logo[0] ); ?>"
								     width="<?php echo esc_attr( $logo[1] ); ?>"
								     height="<?php echo esc_attr( $logo[2] ); ?>"
								     alt="<?php echo esc_attr( $listing->title ); ?>" />
							<?php else : ?>
								<h2 class="listing-text-logo">
									<?php echo esc_html( $listing->title ); ?>
								</h2>
							<?php endif; ?>
						</div>

						<div class="member-meta">
							<ul>
								<li class="wmd-cost"><?php wmd_format_prices( $listing->low_price, $listing->high_price ); ?></li>
								<li><?php wmd_format_location( $listing->locations ); ?></li>
							</ul>
						</div>

					</header>

					<article class="listing-wrapper">
						<?php wmd_display_portfolio( $listing->portfolio ); ?>
					</article>

					<aside class="directory-archive-meta">
						<div class="column">
							<table>
								<tbody>
									<?php wmd_format_terms( $listing, 'industries' ); ?>
									<?php wmd_format_terms( $listing, 'types' ); ?>
								</tbody>
							</table>
						</div>
						<div class="column">
							<table>
								<tbody>
									<?php wmd_format_terms( $listing, 'technologies' ); ?>
								</tbody>
							</table>
						</div>
					</aside>

					<section class="listing-cta">
						<a href="<?php the_permalink(); ?>">View Details</a>
						<a href="<?php echo esc_url( $listing->url ); ?>">Visit Website</a>
					</section>
				</section>

			<?php endwhile; ?>

		<?php endif; ?>

	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>