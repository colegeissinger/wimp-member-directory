<?php

/**
 * Contains any helper/template functions that will allow us to handle things in the front-end
 * or possibly the admin area of the WMD plugin
 */

/**
 * Processes a post object or post ID into a member directory object.
 *
 * @param object|int $post   The post object or post ID
 * @param string     $output Accepts OBJECT or ARRAY
 *
 * @return array|object|bool
 */
function wmd_get_listing( $post = null, $output = OBJECT ) {
	// Check if we passed a post ID
	if ( is_int( $post ) ) {
		$post = get_post( $post );
	} elseif ( ! isset( $post ) || ! is_object( $post ) || empty( $post ) ) {
		global $post;

		// It's possible the global $post object isn't rendered...
		if ( empty( $post ) || ! isset( $post->ID ) ) {
			return false;
		}
	} else {
		// If all else fails, return empty.
		return false;
	}

	// Fetch the company logo
	$logo         = get_post_meta( $post->ID, '_wmd_company_logo', true );
	$logo_id      = get_post_meta( $post->ID, '_wmd_company_logo_id', true );

	$data = array(
		'ID'                => (int) $post->ID,
		'member_id'         => (int) $post->post_author,
		'title'             => $post->post_title,
		'slug'              => $post->post_name,
		'logo_id'           => ( ! empty( $logo ) ? (int) $logo_id : 0 ),
		'portfolio'         => get_post_meta( $post->ID, '_wmd_portfolio_items', true ),
		'url'               => get_post_meta( $post->ID, '_wmd_url', true ),
		'low_price'         => get_the_terms( $post->ID, 'wmd-price-low' ),
		'high_price'        => get_the_terms( $post->ID, 'wmd-price-high' ),
		'locations'         => get_the_terms( $post->ID, 'wmd-location' ),
		'industries'        => get_the_terms( $post->ID, 'wmd-industry' ),
		'technologies'      => get_the_terms( $post->ID, 'wmd-technology' ),
		'types'             => get_the_terms( $post->ID, 'wmd-type' ),
		'member_level'      => get_the_terms( $post->ID, 'wmd-level' ),
		'post_date'         => $post->post_date,
		'post_date_gmt'     => $post->post_date_gmt,
		'post_modified'     => $post->post_modified,
		'post_modified_gmt' => $post->post_modified_gmt,
		'guid'              => $post->guid,
	);

	// If the output requested is anything but OBJECT, we'll return an array
	if ( OBJECT !== $output ) {
		return $data;
	}

	return (object) $data;
}

/**
 * Pairs up the low and high price values into a single string
 *
 * @param int $low  The low price
 * @param int $high The high price
 */
function wmd_format_prices( $low, $high ) {
	if ( ! $low || ! $high || ! is_array( $low ) || ! is_array( $high ) ) {
		echo 'Prices not listed';

		return false;
	}

	$output  = '$' . number_format( intval( array_shift( $low )->name ) );
	$output .= '-$' . number_format( intval( array_shift( $high )->name ) );

	echo esc_html( $output );
}

/**
 * Formats an array of location terms into a read-able format
 *
 * @param array $locations The array of location term objects
 */
function wmd_format_location( $locations ) {
	if ( ! $locations || ! is_array( $locations ) ) {
		echo 'Unknown Location';

		return false;
	}

	$city  = '';
	$state = '';

	foreach ( $locations as $location ) {
		if ( 0 === $location->parent ) {
			$state = $location->name;
		} elseif ( 0 < $location->parent ) {
			$city = $location->name;
		}
	}

	if ( empty( $state ) || empty( $city ) ) {
		return '';
	}

	echo esc_html( $city . ', ' . wmd_format_state( $state, 'abbr' ) );
}

/**
 * Format State
 *
 * Note: Does not format addresses, only states. $input should be as exact as possible, problems
 * will probably arise in long strings, example 'I live in Kentukcy' will produce Indiana.
 *
 * @example echo myClass::format_state( 'Florida', 'abbr'); // FL
 * @example echo myClass::format_state( 'we\'re from georgia' ) // Georgia
 *
 * @param  string $input  Input to be formatted
 * @param  string $format Accepts 'abbr' to output abbreviated state, default full state name.
 * @return string         Formatted state on success,
 */
function wmd_format_state( $input, $format = '' ) {
	if ( ! $input || empty( $input ) ) {
		return '';
	}

	$states = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District Of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
	);

	foreach ( $states as $abbr => $name ) {
		if ( preg_match( "/\b($name)\b/", ucwords( strtolower( $input ) ), $match ) ) {
			if ( 'abbr' == $format ) {
				return $abbr;
			} else {
				return $name;
			}
		} elseif ( preg_match( "/\b($abbr)\b/", strtoupper( $input ), $match ) ) {
			if ( 'abbr' == $format ) {
				return $abbr;
			} else {
				return $name;
			}
		}
	}

	return '';
}

/**
 * Displays the portfolio slider
 *
 * @param array $items An array of portfolio items
 */
function wmd_display_portfolio( $items ) {
	if ( empty( $items ) ) {
		return false;
	}
	?>
	<div class="flexslider">
		<ul class="slides">
			<?php foreach ( $items as $key => $value ) : ?>
				<li><img src="<?php echo esc_url( $value ); ?>" /></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Extracts the main member directory terms from the listing object
 *
 * @param object $listing The member listing object
 *
 * @return array $data An array of term objects for a specific listing object
 */
function wmd_list_terms( $listing ) {
	if ( ! is_object( $listing ) ) {
		return false;
	}

	if ( ! isset( $listing->industries, $listing->types, $listing->technologies ) ) {
		return false;
	}

	$allowed_fields = array(
		'industries',
		'types',
		'technologies',
	);
	$data = array();

	foreach ( $listing as $term => $values ) {
		if ( ! in_array( $term, $allowed_fields ) ) {
			continue;
		}

		$data[ $term ] = $values;
	}

	return $data;
}

/**
 * Applies the standard formatting of a listings meta data
 * Requires a listing object (@see wmd_get_listing()), if no type is passed, we'll output all.
 *
 * Pass taxonomy names through $type to return only the terms for that taxonomy.
 *
 * @param object $listing The listing object
 * @param string $type    The name of a listing taxonomy
 * @param bool   $return  Allows us to return or echo the results
 *
 * @return string|bool
 */
function wmd_format_terms( $listing, $type = null, $return = false ) {
	if ( empty( $listing ) || ! is_object( $listing ) ) {
		return '';
	}

	$terms        = wmd_list_terms( $listing );
	$html_default = array(
		'before-meta' => '<tr>',
		'after-meta'  => '</tr>',
		'before-list' => '<td>',
		'after-list'  => '</td>',
	);
	$html         = apply_filters( 'wmd_meta_html', $html_default );
	$output       = '';

	$output .= sprintf( '%s', wp_kses_post( $html['before-meta'] ) );

	foreach ( $terms as $name => $term ) {
		// Only apply the term type if it is in our array.
		// If we pass an empty array, we'll just output everything.
		if ( $name !== $type || ! $term ) {
			continue;
		}

		$output .= sprintf( '%s', wp_kses_post( $html['before-list'] ) );
		$output .= '<strong>' . esc_html( wmd_convert_tax_name( $name ) ) . ':</strong>';
		$output .= sprintf( '%s', wp_kses_post( $html['after-list'] ) );

		$output .= sprintf( '%s', wp_kses_post( $html['before-list'] ) );
		$end = end( $term );
		// Create a comma separated list of the terms attached to our taxonomy
		foreach ( $term as $term_id => $t ) {

			$output .= '<a href="' . esc_url( get_term_link( $t ) ) . '">'
			           . esc_html( $t->name ) . '</a>';

			// Only append a comma if we are not on the last item in the array
			if ( $end->term_id !== $t->term_id ) {
				$output .= ', ';
			}
		}
		$output .= sprintf( '%s', wp_kses_post( $html['after-list'] ) );
	}

	$output .= sprintf( '%s', wp_kses_post( $html['after-meta'] ) );

	// We may need to return our string instead of echoing it.
	if ( $return ) {
		return $output;
	}

	echo $output;
}

/**
 * Converts the default Taxonomy names into something more front-end readable.
 *
 * @param string $tax The name of the taxonomy we want to modify
 *
 * @return string $output
 */
function wmd_convert_tax_name( $tax ) {
	switch ( $tax ) {
		case 'industries':
			$output = 'Industry';
			break;
		case 'types':
			$output = 'Type';
			break;
		case 'technologies':
			$output = 'Technology';
			break;
		default:
			$output = '';
	}

	return $output;
}