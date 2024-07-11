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
        'speciality_lead'   => 3,
        'staff_coordinator' => 4,
        'anesthetic_lead'   => 5,
        'booker'            => 6,
        'chair'             => 7,
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

    
    

];
