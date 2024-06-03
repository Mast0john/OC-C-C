<?php

class FacetWP_Facet_Time_Since_Addon extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'Time Since', 'fwp' );
    }


    /**
     * Parse the multi-line options string
     */
    function parse_choices( $choices ) {
        $choices = explode( "\n", $choices );
        foreach ( $choices as $key => $choice ) {
            $temp = array_map( 'trim', explode( '|', $choice ) );
            $choices[ $key ] = array(
                'label' => $temp[0],
                'format' => $temp[1],
                'seconds' => strtotime( $temp[1] ),
                'counter' => 0,
            );
        }

        return $choices;
    }


    /**
     * Is the format in the future?
     */
    function is_future( $format ) {
        if ( '+' == substr( $format, 0, 1 ) ) {
            return true;
        }
        elseif ( 'next' == substr( $format, 0, 4 ) ) {
            return true;
        }

        return false;
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $output = array();
        $facet = $params['facet'];
        $where_clause = $params['where_clause'];

        $sql = "
        SELECT f.facet_display_value
        FROM {$wpdb->prefix}facetwp_index f
        WHERE f.facet_name = '{$facet['name']}' $where_clause";
        $results = $wpdb->get_col( $sql );

        // Parse facet choices
        $choices = $this->parse_choices( $facet['choices'] );

        // Loop through the results
        foreach ( $results as $val ) {
            $post_time = (int) strtotime( $val );
            foreach ( $choices as $key => $choice ) {
                $choice_time = $choice['seconds'];

                // next week, etc.
                if ( $this->is_future( $choice['format'] ) ) {
                    if ( $post_time <= $choice_time && $post_time > time() ) {
                        $choices[ $key ]['counter']++;
                    }
                }
                // last week, etc.
                else {
                    if ( $post_time >= $choice_time && $post_time < time() ) {
                        $choices[ $key ]['counter']++;
                    }
                }
            }
        }

        // Return an associative array
        foreach ( $choices as $choice ) {
            if ( 0 < $choice['counter'] ) {
                $output[] = array(
                    'facet_display_value' => $choice['label'],
                    'counter' => $choice['counter'],
                );
            }
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

        $is_empty = empty( $selected_values ) ? ' checked' : '';
        $output .= '<div class="facetwp-radio' . $is_empty  . '" data-value="">' . __( 'Any', 'fwp' ) . '</div>';

        foreach ( $values as $row ) {
            $display_value = esc_html( $row['facet_display_value'] );
            $safe_value = FWP()->helper->safe_value( $display_value );
            $selected = in_array( $safe_value, $selected_values ) ? ' checked' : '';
            $display_value .= " <span class='counts'>(" . $row['counter'] . ")</span>";
            $output .= '<div class="facetwp-radio' . $selected . '" data-value="' . esc_attr( $safe_value ) . '">' . $display_value . '</div>';
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

        $choices = $this->parse_choices( $facet['choices'] );

        foreach ( $choices as $key => $choice ) {
            $safe_value = FWP()->helper->safe_value( $choice['label'] );
            if ( $safe_value === $selected_values ) {
                $selected_values = date( 'Y-m-d H:i:s', (int) $choice['seconds'] );

                if ( $this->is_future( $choice['format'] ) ) {
                    $where_clause = "facet_value <= '$selected_values' AND facet_value > NOW()";
                }
                else {
                    $where_clause = "facet_value >= '$selected_values' AND facet_value < NOW()";
                }

                $sql = "
                SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
                WHERE facet_name = '{$facet['name']}' AND $where_clause";
                return $wpdb->get_col( $sql );
            }
        }

        return array();
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    $(function() {
        FWP.hooks.addAction('facetwp/load/time_since', function($this, obj) {
            $this.find('.facet-source').val(obj.source);
            $this.find('.facet-choices').val(obj.choices);
        });
    
        FWP.hooks.addFilter('facetwp/save/time_since', function(obj, $this) {
            obj['source'] = $this.find('.facet-source').val();
            obj['choices'] = $this.find('.facet-choices').val();
            return obj;
        });
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
        FWP()->display->assets['time-since-front.js'] = plugins_url( '', __FILE__ ) . '/assets/js/front.js';
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <tr>
            <td>
                <?php _e('Choices', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Enter the available choices (one per line)', 'fwp' ); ?></div>
                </div>
            </td>
            <td><textarea class="facet-choices"></textarea></td>
        </tr>
<?php
    }
}
