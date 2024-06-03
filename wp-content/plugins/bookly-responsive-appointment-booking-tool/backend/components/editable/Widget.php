<?php
namespace Bookly\Backend\Components\Editable;

use Bookly\Lib;

/**
 * Class Widget
 * @package Bookly\Backend\Components\Editable
 */
class Widget extends Lib\Base\Component
{
    /**
     * Render the widget
     *
     * @param string $doc_slug
     */
    public static function render( $doc_slug )
    {
        self::enqueueStyles( array(
            'module' => array( 'css/editable.css', ),
        ) );

        self::enqueueScripts( array(
            'module' => array(
                'js/ace/ace.js' => array(),
                'js/ace/ext-language_tools.js' => array(),
                'js/editable.js' => array( 'jquery' ),
            ),
        ) );

        wp_localize_script( 'bookly-editable.js', 'BooklyL10nEditable', array(
            'title' => __( 'Edit', 'bookly' ),
        ) );

        self::renderTemplate( 'ace-modal', compact( 'doc_slug' ) );
    }
}