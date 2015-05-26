<?php

/**
 * Echo the component's slug
 */
function bp_wimp_listing_slug() {
	echo bp_get_wimp_listing_slug();
}

/**
 * Return the component's slug
 *
 * Having a template function for this purpose is not absolutely necessary, but it helps
 * avoid too-frequent direct calls to the $bp global.
 *
 * @uses apply_filters() Filter 'bp_get_wimp_listing_slug' to change the output
 *
 * @return str $slug The slug from $bp->wimp_listing->slug, if it exists.
 */
function bp_get_wimp_listing_slug() {
	global $bp;
	$slug = isset( $bp->wimp_listing->slug ) ? $bp->wimp_listing->slug : '';

	return apply_filters( 'bp_get_wimp_listing_slug', $slug );
}

/**
 * Output the URL to the Account Dashboard for the BP Component
 *
 * @param string $slug A sub page within if needed
 *
 * @return string
 */
function wmd_get_listing_manager_url( $slug = '' ) {
	global $bp;

	// To ensure our URL is correctly formatted we'll need to apply a forward slash before it
	if ( ! empty( $slug ) ) {
		$slug = trailingslashit( '/' . $slug );
	}

	return bp_loggedin_user_domain() . $bp->wimp_listing->slug . $slug;
}

/**
 * Display our listing management interface
 *
 * @param $data
 */
function wmd_get_listing_form( $data, $updated = array() ) {
	$defaults = array(
		'ID'           => 0,
		'title'        => '',
		'slug'         => '',
		'content'      => '',
		'member_id'    => 0,
		'logo_id'      => 0,
		'portfolio'    => array(),
		'url'          => '',
		'low_price'    => 0,
		'high_price'   => 0,
		'state'        => '',
		'city'         => '',
		'industries'   => array(),
		'technologies' => array(),
		'types'        => array(),
	);
	$data = wp_parse_args( $data, $defaults );
	$taxonomies = new WMD_Taxonomies();

	// Check if the post is published or a draft
	$post_status = get_post_status( $data['ID'] );
	?>
	<form action="<?php echo esc_url( home_url() . $_SERVER['REQUEST_URI'] ); ?>" method="post" id="wmd-listings" class="standard-form base" xmlns="http://www.w3.org/1999/html">
		<?php
		// Display or status message after a save
		if ( ! empty( $updated ) ) {
			$status = ( $updated['saved'] ) ? 'success' : 'error';
			$message = $updated['message'];

			echo '<div class="wmd-notification wmd-' . esc_attr( $status ) . '">' . esc_html( $message ) . '</div>';
		}
		?>
		<?php wp_nonce_field( 'create-edit-listing', 'wmd-listing-nonce', true ); ?>
		<input name="wmd[post-id]" value="<?php echo esc_attr( $data['ID'] ); ?>" id="id" type="hidden" />
		<div>
			<label for="title">Title</label>
			<input type="text" name="wmd[title]" id="title" value="<?php echo esc_attr( $data['title'] ); ?>" />
		</div>
		<div>
			<label for="content">Bio</label>
			<?php
			wp_editor( $data['content'], 'content', array(
				'wpautop'          => false,
				'media_buttons'    => false,
				'textarea_name'    => 'wmd[content]',
				'teeny'            => true,
				'drag_drop_upload' => true,
			) );
			?>
		</div>
		<div>
			<label for="logo">Logo</label>
			<div class="media-upload media-grid-items">
				<?php
				$logo = ( 0 !== $data['logo_id'] || '' !== $data['logo_id'] ) ? wp_get_attachment_image_src( $data['logo_id'], 'full' ) : array(); ?>
				<div class="media-grid-item upload-btn">
					<?php if ( ! empty( $logo ) ) : ?>
						<div class="wmd-media-btn upload-logo">
							<img src="<?php echo wp_get_attachment_image_src( $data['logo_id'] )[0]; ?>" width="150" height="150">
						</div>
					<?php else : ?>
						<div class="wmd-media-btn upload-logo">
							<img src="<?php echo esc_url( WMD_URL . 'images/add-new-image-x2.jpg' ); ?>" width="150" height="150" alt="Add Logo">
						</div>
					<?php endif; ?>
					<input type="hidden" name="wmd[logo]" id="logo" value="<?php echo ( isset( $logo[0] ) ) ? esc_attr( $logo[0] ) : ''; ?>" />
					<input type="hidden" name="wmd[logo-id]" id="logo-id" value="<?php echo absint( $data['logo_id'] ); ?>" />
				</div>
			</div>
		</div>
		<div>
			<label>Portfolio</label>
			<div class="description">
				<p>Pro Tip: Any captions you add to images will display on your listing!</p>
			</div>
			<div class="media-upload media-grid-items portfolio-items">
				<?php
				if ( ! empty( $data['portfolio'] ) ) :
					foreach ( $data['portfolio'] as $id => $image ) : ?>
						<div class="media-grid-item">
							<div class="wmd-media-btn change-portfolio">
								<span class="wmd-control dashicons dashicons-trash"></span>
								<img src="<?php echo wp_get_attachment_image_src( $id )[0]; ?>" width="150" height="150">
							</div>
							<input type="hidden" name="wmd[portfolio][<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $image ); ?>" data-id="<?php echo esc_attr( $id ); ?>" data-type="portfolio" />
						</div>
					<?php endforeach;
				endif; ?>
				<div class="media-grid-item upload-btn">
					<div class="wmd-media-btn upload-portfolio">
						<img src="<?php echo esc_url( WMD_URL . 'images/add-new-image-x2.jpg' ); ?>" width="150" height="150" alt="Add New Portfolio Image">
					</div>
				</div>
			</div>
			<?php unset( $id, $image ); ?>
		</div>
		<div>
			<label for="url">Website URL</label>
			<input type="url" name="wmd[url]" id="url" value="<?php echo esc_url( $data['url'] ); ?>" placeholder="http://www.domain.com" />
		</div>
		<div>
			<label>Price Range</label>
			<div class="two-column-form">
				<?php
				$price_low = ( isset( $data['low_price'][0]->slug ) ) ? number_format( $data['low_price'][0]->slug ) : '';
				$price_high = ( isset( $data['high_price'][0]->slug ) ) ? number_format( $data['high_price'][0]->slug ) : '';
				?>
				$<input type="text" name="wmd[low_price]" id="price-low" class="price-low price" value="<?php echo esc_attr( $price_low ); ?>" placeholder="0" /> to
				$<input type="text" name="wmd[high_price]" id="price-high" class="price-high price" value="<?php echo esc_attr( $price_high ); ?>" placeholder="0" />
			</div>
			<?php unset( $price_low, $price_high ); ?>
		</div>
		<div>
			<label>Location</label>
			<div class="two-column-form">
				<?php
				$states = get_terms( $taxonomies::STATE, array(
					'hide_empty' => false,
				) );
				$cities = get_terms( $taxonomies::CITY, array(
					'hide_empty' => false,
				) );
				?>
				<select name="wmd[state][0]" id="state" class="state-selection">
					<option value="0">Select A State</option>
					<?php foreach ( $states as $state ) :
						$current = ( isset( $data['state'][0] ) ) ? $data['state'][0]->name : ''; ?>
						<option value="<?php echo esc_attr( $state->term_id ); ?>"
							<?php selected( $current, $state->name ); ?>>
							<?php echo esc_html( $state->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>,
				<select name="wmd[city][0]" id="city" class="city-selection">
					<option value="0">Select A City</option>
					<?php foreach ( $cities as $city ) :
						$current = ( isset( $data['city'][0] ) ) ? $data['city'][0]->name : ''; ?>
						<option value="<?php echo esc_attr( $city->term_id ); ?>"
							<?php selected( $current, $city->name ); ?>>
							<?php echo esc_html( $city->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<input type="text" name="wmd[city][new]" placeholder="Add New City" data-type="<?php echo esc_attr( $taxonomies::CITY ); ?>" class="add-new" />
				<button class="button add-new-tax" id="add-city">Add New</button>
				<div class="loading">
					<img src="<?php echo esc_url( WMD_URL . 'images/loading.gif' ); ?>" alt="" /> Saving...
				</div>
			</div>
			<?php unset( $cities, $city, $state, $states, $current ); ?>
		</div>
		<div>
			<label for="industry">Industries You Serve</label>
			<?php
			$industries = wmd_get_terms( $taxonomies::INDUSTRY );
			?>
			<select multiple="multiple" class="industry-selection" name="wmd[industry][]" id="industry">
				<?php foreach ( $industries as $industry ) : ?>
					<option value="<?php echo esc_attr( $industry->term_id ); ?>"
						<?php wmd_selected( $industry->term_id, $data['industries'] ); ?>>
						<?php echo esc_html( $industry->name ); ?>
					</option>
				<?php endforeach; unset( $industries ); ?>
			</select>
			<input type="text" name="wmd[industry][new]" placeholder="Add New Industry" data-type="<?php echo esc_attr( $taxonomies::INDUSTRY ); ?>" class="add-new" />
			<button class="button add-new-tax">Add New</button>
			<div class="loading"><img src="<?php echo esc_url( WMD_URL . 'images/loading.gif' ); ?>" alt="" /> Saving...</div>
		</div>
		<div>
			<label for="technologies">Technologies You Use</label>
			<?php
			$technologies = wmd_get_terms( $taxonomies::TECHNOLOGY );
			?>
			<select multiple="multiple" class="tech-selection" name="wmd[tech][]" id="techonologies">
				<?php foreach ( $technologies as $tech ) : ?>
					<option value="<?php echo esc_attr( $tech->term_id ); ?>"
						<?php wmd_selected( $tech->term_id, $data['technologies'] ); ?>>
						<?php echo esc_html( $tech->name ); ?>
					</option>
				<?php endforeach; unset( $technologies ); ?>
			</select>
			<input type="text" name="wmd[tech][new]" placeholder="Add New Technology" data-type="<?php echo esc_attr( $taxonomies::TECHNOLOGY ); ?>" class="add-new" />
			<button class="button add-new-tax">Add New</button>
			<div class="loading"><img src="<?php echo esc_url( WMD_URL . 'images/loading.gif' ); ?>" alt="" /> Saving...</div>
		</div>
		<div>
			<label for="services">Services You Provide</label>
			<?php
			$types = wmd_get_terms( $taxonomies::TYPE );
			?>
			<select multiple="multiple" class="services-selection" name="wmd[types][]" id="services">
				<?php foreach ( $types as $type ) : ?>
					<option value="<?php echo esc_attr( $type->term_id ); ?>"
						<?php wmd_selected( $type->term_id, $data['types'] ); ?>>
						<?php echo esc_html( $type->name ); ?>
					</option>
				<?php endforeach; unset( $type ); ?>
			</select>
			<input type="text" name="wmd[types][new]" placeholder="Add New Service" data-type="<?php echo esc_attr( $taxonomies::TYPE ); ?>" class="add-new" />
			<button class="button add-new-tax">Add New</button>
			<div class="loading"><img src="<?php echo esc_url( WMD_URL . 'images/loading.gif' ); ?>" alt="" /> Saving...</div>
		</div>
		<div style="margin-top:20px">
			<input type="checkbox" name="wmd[publish]" id="publish" value="publish"<?php checked( 'publish', $post_status ); ?> /> <label for="publish" style="display:inline-block;cursor:pointer"><strong>Publish Listing</strong></label>
		</div>
		<div id="submit-container">
			<input type="submit" value="Save Listing" name="saving-wmd-listing" id="submit-listing" />
		</div>
	</form>
	<?php
}