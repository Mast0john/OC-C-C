<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Settings;
?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'calendar' ) ) ?>">
    <div class="card-body">
        <?php Settings\Selects::renderSingle( 'bookly_cal_show_only_business_days', __( 'Show only business days in the calendar', 'bookly' ), __( 'If this setting is enabled then only business days will be visible in the calendar according to the company\'s business hours settings', 'bookly' ) ) ?>
        <?php Settings\Selects::renderSingle( 'bookly_cal_show_only_business_hours', __( 'Show only business hours in the calendar', 'bookly' ), __( 'If this setting is enabled then the visible hours in the calendar will be limited to the company\'s business hours', 'bookly' ) ) ?>
        <?php Settings\Selects::renderSingle( 'bookly_cal_show_only_staff_with_appointments', __( 'Show only staff members with appointments in Day view', 'bookly' ), __( 'If this setting is enabled then only staff members who have associated appointments will be displayed in the Day view', 'bookly' ) ) ?>
        <div class="form-group">
            <?php if ( Bookly\Lib\Config::groupBookingActive() ) : ?>
                <label for="bookly_appointment_participants"><?php esc_html_e( 'Calendar', 'bookly' ) ?></label>
                <select class="form-control custom-select mb-3" id="bookly_appointment_participants">
                    <option value="bookly_cal_one_participant"><?php esc_html_e( 'Appointment with one participant', 'bookly' ) ?></option>
                    <option value="bookly_cal_many_participants"><?php esc_html_e( 'Appointment with many participants', 'bookly' ) ?></option>
                </select>
            <?php else : ?>
                <label for="bookly_appointment_participants"><?php esc_html_e( 'Calendar', 'bookly' ) ?></label>
                <input id="bookly_appointment_participants" type="hidden" name="bookly_appointment_participants" value="bookly_cal_one_participant" />
            <?php endif ?>
            <div id="bookly_cal_one_participant">
                <?php Settings\Inputs::renderTextArea( 'bookly_cal_one_participant', '', __( 'Set order of the fields in calendar', 'bookly' ) ) ?>
                <?php $self::renderTemplate( '_calendar_codes', array( 'participants' => 'one' ) ) ?>
            </div>
            <div id="bookly_cal_many_participants">
                <?php Settings\Inputs::renderTextArea( 'bookly_cal_many_participants', '', __( 'Set order of the fields in calendar', 'bookly' ) ) ?>
                <?php $self::renderTemplate( '_calendar_codes', array( 'participants' => 'many' ) ) ?>
            </div>
        </div>
    </div>

    <div class="card-footer bg-transparent d-flex justify-content-end">
        <?php Inputs::renderCsrf() ?>
        <?php Buttons::renderSubmit() ?>
        <?php Buttons::renderReset( null, 'ml-2' ) ?>
    </div>
</form>