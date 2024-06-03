<?php
namespace Bookly\Backend\Modules\Notifications\Lib;

use Bookly\Lib;
use Bookly\Lib\Entities\Notification;
use Bookly\Backend\Modules\Notifications\Proxy;

/**
 * Class Codes
 * @package Bookly\Backend\Modules\Notifications\Lib
 */
class Codes
{
    /** @var string */
    protected $type;

    /** @var array */
    protected $codes;

    /**
     * Constructor.
     *
     * @param string $type
     */
    public function __construct( $type = 'email' )
    {
        $this->type  = $type;
        $this->codes = array(
            'appointment' => array(
                'appointment_date'               => __( 'Date of appointment', 'bookly' ),
                'appointment_end_date'           => __( 'End date of appointment', 'bookly' ),
                'appointment_end_time'           => __( 'End time of appointment', 'bookly' ),
                'appointment_notes'              => __( 'Customer notes for appointment', 'bookly' ),
                'appointment_time'               => __( 'Time of appointment', 'bookly' ),
                'booking_number'                 => __( 'Booking number', 'bookly' ),
            ),
            'cart' => array(
                'cart_info'                      => __( 'Cart information', 'bookly' ),
                'cart_info_c'                    => __( 'Cart information with cancel', 'bookly' ),
                'appointment_notes'              => __( 'Customer notes for appointment', 'bookly' ),
            ),
            'category' => array(
                'category_name'                  => __( 'Name of category', 'bookly' ),
            ),
            'company' => array(
                'company_address'                => __( 'Address of company', 'bookly' ),
                'company_name'                   => __( 'Name of company', 'bookly' ),
                'company_phone'                  => __( 'Company phone', 'bookly' ),
                'company_website'                => __( 'Company web-site address', 'bookly' ),
            ),
            'customer' => array(
                'client_address'                 => __( 'Address of client', 'bookly' ),
                'client_email'                   => __( 'Email of client', 'bookly' ),
                'client_first_name'              => __( 'First name of client', 'bookly' ),
                'client_last_name'               => __( 'Last name of client', 'bookly' ),
                'client_name'                    => __( 'Full name of client', 'bookly' ),
                'client_phone'                   => __( 'Phone of client', 'bookly' ),
            ),
            'customer_timezone' => array(
                'client_timezone'                => __( 'Time zone of client', 'bookly' ),
            ),
            'customer_appointment' => array(
                'approve_appointment_url'        => __( 'URL of approve appointment link (to use inside <a> tag)', 'bookly' ),
                'cancel_appointment_confirm_url' => __( 'URL of cancel appointment link with confirmation (to use inside <a> tag)', 'bookly' ),
                'cancel_appointment_url'         => __( 'URL of cancel appointment link (to use inside <a> tag)', 'bookly' ),
                'cancellation_reason'            => __( 'Reason you mentioned while deleting appointment', 'bookly' ),
                'google_calendar_url'            => __( 'URL for adding event to client\'s Google Calendar (to use inside <a> tag)', 'bookly' ),
                'reject_appointment_url'         => __( 'URL of reject appointment link (to use inside <a> tag)', 'bookly' ),
            ),
            'payment' => array(
                'payment_type'                   => __( 'Payment type', 'bookly' ),
                'payment_status'                 => __( 'Payment status', 'bookly' ),
                'total_price'                    => __( 'Total price of booking (sum of all cart items after applying coupon)' ),
            ),
            'service' => array(
                'service_duration'               => __( 'Duration of service', 'bookly' ),
                'service_info'                   => __( 'Info of service', 'bookly' ),
                'service_name'                   => __( 'Name of service', 'bookly' ),
                'service_price'                  => __( 'Price of service', 'bookly' ),
            ),
            'staff' => array(
                'staff_email'                    => __( 'Email of staff', 'bookly' ),
                'staff_info'                     => __( 'Info of staff', 'bookly' ),
                'staff_name'                     => __( 'Name of staff', 'bookly' ),
                'staff_phone'                    => __( 'Phone of staff', 'bookly' ),
            ),
            'staff_agenda' => array(
                'agenda_date'                    => __( 'Agenda date', 'bookly' ),
                'next_day_agenda'                => __( 'Staff agenda for next day', 'bookly' ),
                'tomorrow_date'                  => __( 'Date of next day', 'bookly' ),
            ),
            'user_credentials' => array(
                'new_password'                   => __( 'Customer new password', 'bookly' ),
                'new_username'                   => __( 'Customer new username', 'bookly' ),
                'site_address'                   => __( 'Site address', 'bookly' ),
            ),
            'rating'           => array(),
        );

        if ( $type == 'email' ) {
            // Only email.
            $this->codes['company']['company_logo'] = __( 'Company logo', 'bookly' );
            $this->codes['customer_appointment']['cancel_appointment'] = __( 'Cancel appointment link', 'bookly' );
            $this->codes['staff']['staff_photo'] = __( 'Photo of staff', 'bookly' );
        }

        // Add codes from add-ons.
        $this->codes = Proxy\Shared::prepareNotificationCodes( $this->codes, $type );
    }

    /**
     * Render codes for given notification type.
     *
     * @param string $notification_type
     * @param bool   $with_repeated  add codes 'series' from add-on recurring appointments
     */
    public function render( $notification_type, $with_repeated = false )
    {
        $codes = $this->_build( $notification_type );
        if ( $with_repeated ) {
            if ( isset( $this->codes['series'] ) ) {
                $codes = array_merge( $codes, $this->codes['series'] );
            }
        }
        ksort( $codes );

        $tbody = '';
        foreach ( $codes as $code => $description ) {
            $tbody .= sprintf(
                '<tr><td class="p-0"><input value="{%s}" class="border-0" readonly="readonly" onclick="this.select()" /> &ndash; %s</td></tr>',
                $code,
                esc_html( $description )
            );
        }

        printf(
            '<table class="bookly-js-codes bookly-js-codes-%s"><tbody>%s</tbody></table>',
            $notification_type,
            $tbody
        );
    }

    /**
     * Build array of codes for given notification type.
     *
     * @param $notification_type
     * @return array
     */
    private function _build( $notification_type )
    {
        $codes = array();

        switch ( $notification_type ) {
            case Notification::TYPE_APPOINTMENT_REMINDER:
            case Notification::TYPE_NEW_BOOKING:
            case Notification::TYPE_NEW_BOOKING_RECURRING:
            case Notification::TYPE_LAST_CUSTOMER_APPOINTMENT:
            case Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED:
            case Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED_RECURRING:
                $codes = array_merge(
                    $this->codes['appointment'],
                    $this->codes['category'],
                    $this->codes['company'],
                    $this->codes['customer'],
                    $this->codes['customer_appointment'],
                    $this->codes['customer_timezone'],
                    $this->codes['payment'],
                    $this->codes['service'],
                    $this->codes['staff']
                );
                if ( Lib\Config::invoicesActive() &&
                    in_array( $notification_type, array(
                        Notification::TYPE_NEW_BOOKING,
                        Notification::TYPE_NEW_BOOKING_RECURRING,
                        Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED,
                        Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED_RECURRING,
                    ) )
                ) {
                    $codes = array_merge( $codes, $this->codes['invoice'] );
                }
                if ( Lib\Config::ratingsActive() && ( $notification_type == Notification::TYPE_APPOINTMENT_REMINDER ) ) {
                    $codes = array_merge( $codes, $this->codes['rating'] );
                }
                break;
            case Notification::TYPE_STAFF_DAY_AGENDA:
                $codes = array_merge(
                    $this->codes['company'],
                    $this->codes['staff'],
                    $this->codes['staff_agenda']
                );
                break;
            case Notification::TYPE_CUSTOMER_BIRTHDAY:
                $codes = array_merge(
                    $this->codes['company'],
                    $this->codes['customer']
                );
                break;
            case Notification::TYPE_CUSTOMER_NEW_WP_USER:
                $codes = array_merge(
                    $this->codes['company'],
                    $this->codes['customer'],
                    $this->codes['user_credentials']
                );
                break;
            case Notification::TYPE_NEW_BOOKING_COMBINED:
                $codes = array_merge(
                    $this->codes['cart'],
                    $this->codes['company'],
                    $this->codes['customer'],
                    $this->codes['customer_timezone'],
                    $this->codes['payment']
                );
                break;
            default:
                $codes = Proxy\Shared::buildNotificationCodesList( $codes, $notification_type, $this->codes );
        }

        return $codes;
    }

    /**
     * @param array $groups
     * @param bool  $echo
     * @return array
     */
    public function getGroups( array $groups )
    {
        $codes = array();
        foreach ( $groups as $group ) {
            if ( array_key_exists( $group, $this->codes ) ) {
                $codes = array_merge( $codes, $this->codes[ $group ] );
            }
        }

        ksort( $codes );

        return $codes;
    }
}