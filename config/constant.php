<?php

return [
    'default' => [
        'logo'      => 'default/hipl-logo-white.png',
        'favicon'   => 'default/favicon.png',
        'no_image'  => 'default/no-image.jpg',
        'user_icon' => 'default/user-icon.svg',
        'page_loader' => 'default/page-loader.gif',
    ],

    'profile_max_size' => 2048,
    'profile_max_size_in_mb' => '2MB',

    'roles' =>[
        'system_admin'      => 1,
        'trust_admin'       => 2,
        'hospital_admin'    => 3,
        'speciality_lead'   => 4,
        'staff_coordinator' => 5,
        'anesthetic_lead'   => 6,
        'booker'            => 7,
        'chair'             => 8,
    ],
  
    'date_format' => [
        'date' => 'd-m-Y',
        'time' => 'H:i',
        'date_time' => 'd-m-Y H:i:s'
    ],

    'search_date_format' => [ //$whereFormat = '%m/%d/%Y %h:%i %p';
        'date' => '%d-%m-%Y',
        'time' => '%H:%i',
        'date_time' => '%d-%m-%Y %H:%i:%s'
    ],

    'time_slots' =>[
        'AM',
        'PM',
        'EVE',
    ],

    'availability_status' =>[
        0 => 'Pending',
        1 => 'Confirm', 
        2 => 'Decline'
    ],

    'notification_section' => [
        'announcements' => 'Announcements',
    ],

    'notification_type' => [
        'session_available'     => 'Session Available',
        'session_confirmed'     => 'Session Confirmed',
        'session_cancelled'     => 'Session Cancelled',
        'session_utilised'      => 'Session Utilised',
        'session_declined'      => 'Session Declined',
        'session_not_approved'  => 'Session Unapproved',
        'user_profile_updated_email'    => 'Email Updated',
        'user_profile_updated_hospital' => 'Hospital Updated',

        'quarter_available' => 'Quarter Available',

    ],

    'unavailable_speciality_id' => 10,

    'session_status' => [
        'at_risk' => 1,
        'closed'  => 2,
    ]
    

];
