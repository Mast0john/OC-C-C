<?php
/*
Plugin Name: FacetWP - Star Rating
Description: Star rating facet
Version: 1.0.4
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-star-rating
*/

defined( 'ABSPATH' ) or exit;


/**
 * FacetWP registration hook
 */
function fwp_star_rating_facet( $facet_types ) {
    $facet_types['star_rating'] = new FacetWP_Facet_Star_Rating();
    return $facet_types;
}
add_filter( 'facetwp_facet_types', 'fwp_star_rating_facet' );


/**
 * Star rating facet class
 */
class FacetWP_Facet_Star_Rating
{

    function __construct() {
        $this->label = __( 'Star Rating', 'fwp' );

        add_filter( 'facetwp_store_unfiltered_post_ids', array( $this, 'store_unfiltered_post_ids' ) );
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];

        // Apply filtering (ignore the facet's current selection)
        if ( isset( FWP()->or_values ) && ( 1 < count( FWP()->or_values ) || ! isset( FWP()->or_values[ $facet['name'] ] ) ) ) {
            $post_ids = array();
            $or_values = FWP()->or_values; // Preserve the original
            unset( $or_values[ $facet['name'] ] );

            $counter = 0;
            foreach ( $or_values as $name => $vals ) {
                $post_ids = ( 0 == $counter ) ? $vals : array_intersect( $post_ids, $vals );
                $counter++;
            }

            // Return only applicable results
            $post_ids = array_intersect( $post_ids, FWP()->unfiltered_post_ids );
        }
        else {
            $post_ids = FWP()->unfiltered_post_ids;
        }

        $output = array();
        $post_ids = empty( $post_ids ) ? array( 0 ) : $post_ids;
        $where_clause = ' AND post_id IN (' . implode( ',', $post_ids ) . ')';

        foreach ( array( 4, 3, 2, 1 ) as $rating ) {
            $sql = "
            SELECT COUNT(*)
            FROM {$wpdb->prefix}facetwp_index f
            WHERE f.facet_name = '{$facet['name']}' AND FLOOR(f.facet_value) >= '$rating' $where_clause";
            $output[ $rating ] = array(
                'counter' => (int) $wpdb->get_var( $sql )
            );
        }

        return $output;
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $facet = $params['facet'];
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];

        if ( ! empty( $selected_values[0] ) ) {
            $output .= '<div class="facetwp-star facetwp-star-any" data-value="">&laquo; Any rating</div>';
        }

        foreach ( $values as $stars => $result ) {
            $class = in_array( $stars, $selected_values ) ? ' selected' : '';
            $output .= '<div class="facetwp-star' . $class . '" data-value="' . $stars . '"><span class="star-icon stars-' . $stars . '"></span> & up <span class="facetwp-counter">(' . $result['counter'] . ')</span></div>';
        }

        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $selected_values = $params['selected_values'];
        $selected_values = is_array( $selected_values ) ? $selected_values[0] : $selected_values;

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' AND facet_value >= '$selected_values'";
        return $wpdb->get_col( $sql );
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/star_rating', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
    });

    wp.hooks.addFilter('facetwp/save/star_rating', function(obj, $this) {
        obj['source'] = $this.find('.facet-source').val();
        return obj;
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
?>
<link href="<?php echo WP_CONTENT_URL; ?>/plugins/facetwp-star-rating/assets/css/front.css" rel="stylesheet">
<script>
(function($) {
    wp.hooks.addAction('facetwp/refresh/star_rating', function($this, facet_name) {
        var selected_values = [];
        $this.find('.facetwp-star.selected').each(function() {
            var val = $(this).attr('data-value');
            if ('' != val) {
                selected_values.push(val);
            }
        });
        FWP.facets[facet_name] = selected_values;
    });

    $(document).on('click', '.facetwp-star', function() {
        var $facet = $(this).closest('.facetwp-facet');
        $facet.find('.facetwp-star').removeClass('selected');
        $(this).addClass('selected');
        FWP.autoload();
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Store unfiltered post IDs if this facet type exists
     */
    function store_unfiltered_post_ids( $boolean ) {
        if ( FWP()->helper->facet_setting_exists( 'type', 'star_rating' ) ) {
            return true;
        }

        return $boolean;
    }
}
