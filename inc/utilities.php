<?php
function make_get_user_meta($user_id) {
    $user = get_user_by('ID', $user_id);
    if(!$user) {
        return new WP_Error('user_error', 'User not found.');
    }
    $return['name'] =       (get_field('display_name', 'user_' . $user_id ) ? get_field('display_name', 'user_' . $user_id ) : $user->display_name);
    $return['badges'] =     get_field('certifications', 'user_' . $user_id );
    $return['title'] =      get_field('title', 'user_' . $user_id);
    $return['bio'] =        get_field('bio', 'user_' . $user_id);
    $return['gallery'] =    get_field('image_gallery', 'user_' . $user_id);
    $return['photo'] =      get_field('photo', 'user_' . $user_id);
    $return['link'] =       get_author_posts_url($user_id);
    return $return;
}


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


function make_get_user_memberships($user_id) {
    if(function_exists('wc_memberships_get_user_active_memberships')) :
        $active_memberships = wc_memberships_get_user_active_memberships($user_id);
        $memberships = array();
        if($active_memberships) :
            foreach($active_memberships as $membership) :
                $memberships[] = $membership;
            endforeach;
        endif;
        return $memberships;  
    endif;

}

function make_get_user_billing_address($user_id) {
    $address = [];
    $address['fname'] = get_user_meta( $user_id, 'first_name', true );
    $address['lname'] = get_user_meta( $user_id, 'last_name', true );
    $address['address_1'] = get_user_meta( $user_id, 'billing_address_1', true ); 
    $address['address_2'] = get_user_meta( $user_id, 'billing_address_2', true );
    $address['city'] = get_user_meta( $user_id, 'billing_city', true );
    $address['postcode'] = get_user_meta( $user_id, 'billing_postcode', true );
    return $address;
}


function make_get_resources_for_bookable_product($product_id) {
    // Load the product and ensure it’s a bookable product
    $product = wc_get_product($product_id);

    if (!$product || !$product->is_type('booking')) {
        return new WP_Error('invalid_product', 'Product is not a bookable product');
    }

    // Get the resources
    $resources = $product->get_resources();
    // Format the resources array for easier use
    $resources_list = [];
    foreach ($resources as $resource) {
        $resources_list[] = [
            'id'   => $resource->get_id(),
            'name' => $resource->get_name(),
        ];
    }

    return $resources_list;
}