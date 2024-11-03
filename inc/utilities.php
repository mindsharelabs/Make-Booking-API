<?php



function make_get_user_bookings($userID) {
    $return = [];
    if (!class_exists('WC_Booking')) {
        $return = new WP_Error('booking_error', 'WooCommerce Bookings not available.');
    }

    // Query to get bookings
    $args = [
        'post_type'      => 'wc_booking',
        'post_status'    => ['confirmed', 'paid', 'pending'], // Filter by statuses you consider upcoming
        'posts_per_page' => -1,
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => '_booking_customer_id',
                'value'   => $userID,
                'compare' => '='
            ],
            [
                'key'     => '_booking_end',
                'value'   => current_time('timestamp'),
                'compare' => '>',
                'type'    => 'NUMERIC'
            ]
        ],
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $booking = new WC_Booking(get_the_ID());

            $return[] = [
                'id'            => $booking->get_id(),
                'product_name'  => get_the_title($booking->get_product_id()),
                'start_date'    => date('Y-m-d H:i:s', $booking->get_start()),
                'end_date'      => date('Y-m-d H:i:s', $booking->get_end()),
                'status'        => $booking->get_status(),
            ];
        }
    }

    wp_reset_postdata();

    return $return;

}

add_action('plugin_loaded', function() {
    mapi_write_log('Plugin loaded');
    mapi_write_log(make_get_user_bookings(cet_curent_user_id()));
});