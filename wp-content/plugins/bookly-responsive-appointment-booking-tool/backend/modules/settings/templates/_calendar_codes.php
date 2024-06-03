<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Modules\Settings\Proxy;

$codes = array(
    'appointment_date' => __( 'Date of appointment', 'bookly' ),
    'appointment_time' => __( 'Time of appointment', 'bookly' ),
    'booking_number'   => __( 'Booking number', 'bookly' ),
    'category_name'    => __( 'Name of category', 'bookly' ),
    'company_address'  => __( 'Address of company', 'bookly' ),
    'company_name'     => __( 'Name of company', 'bookly' ),
    'company_phone'    => __( 'Company phone', 'bookly' ),
    'company_website'  => __( 'Company web-site address', 'bookly' ),
    'internal_note'    => __( 'Internal note', 'bookly' ),
    'service_capacity' => __( 'Capacity of service', 'bookly' ),
    'service_duration' => __( 'Duration of service', 'bookly' ),
    'service_info'     => __( 'Info of service', 'bookly' ),
    'service_name'     => __( 'Name of service', 'bookly' ),
    'service_price'    => __( 'Price of service', 'bookly' ),
    'staff_email'      => __( 'Email of staff', 'bookly' ),
    'staff_info'       => __( 'Info of staff', 'bookly' ),
    'staff_name'       => __( 'Name of staff', 'bookly' ),
    'staff_phone'      => __( 'Phone of staff', 'bookly' ),
);
if ( $participants == 'one' ) {
    $codes['client_email']      = __( 'Email of client', 'bookly' );
    $codes['client_name']       = __( 'Full name of client', 'bookly' );
    $codes['client_first_name'] = __( 'First name of client', 'bookly' );
    $codes['client_last_name']  = __( 'Last name of client', 'bookly' );
    $codes['client_phone']      = __( 'Phone of client', 'bookly' );
    $codes['payment_status']    = __( 'Status of payment', 'bookly' );
    $codes['payment_type']      = __( 'Payment type', 'bookly' );
    $codes['status']            = __( 'Status of appointment', 'bookly' );
    $codes['total_price']       = __( 'Total price of booking (sum of all cart items after applying coupon)', 'bookly' );
}

$codes = Proxy\Shared::prepareCalendarAppointmentCodes( $codes, $participants );

echo Bookly\Lib\Utils\Codes::tableHtml( $codes );