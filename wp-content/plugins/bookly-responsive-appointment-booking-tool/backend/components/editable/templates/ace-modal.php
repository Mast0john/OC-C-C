<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
/**
 * @var string $doc_slug
 */
?>
<div class="bookly-modal bookly-fade" id="bookly-editable-modal" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="bookly-ace-editor"></div>
                <small class="form-text text-muted"><?php printf( __( 'Start typing "{" to see the available codes. For more information, see the <a href="%s" target="_blank">documentation</a> page', 'bookly' ), 'https://api.booking-wp-plugin.com/go/' . $doc_slug ) ?></small>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderSubmit( 'bookly-ace-save', null, __( 'Apply', 'bookly' ) ) ?>
                <?php Buttons::renderCancel( __( 'Close', 'bookly' ) ) ?>
            </div>
        </div>
    </div>
</div>