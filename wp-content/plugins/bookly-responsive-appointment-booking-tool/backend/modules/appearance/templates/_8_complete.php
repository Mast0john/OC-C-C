<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Appearance\Codes;
use Bookly\Backend\Components\Appearance\Editable;
?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>
    <div class="bookly-box bookly-js-done-success">
        <?php Editable::renderText( 'bookly_l10n_info_complete_step', Codes::getJson( 8, true ) ) ?>
    </div>
    <div class="bookly-box bookly-js-done-limit-error collapse">
        <?php Editable::renderText( 'bookly_l10n_info_complete_step_limit_error', Codes::getJson( 8 ) ) ?>
    </div>
    <div class="bookly-box bookly-js-done-processing collapse">
        <?php Editable::renderText( 'bookly_l10n_info_complete_step_processing', Codes::getJson( 8, true ) ) ?>
    </div>
</div>