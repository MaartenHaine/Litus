<?php

return array(
    'apibundle' => array(
        'api_admin_key' => array(
            'add', 'delete', 'edit', 'manage',
        ),
        'api_auth' => array(
            'getCorporate', 'getPerson',
        ),
        'api_br' => array(
            'add-company','edit-company-name', 'add-cv-book',  'add-page-visible', 'is-page-visible', 'get-cv-years', 'get-company-id', 'send-activation', 'add-user', 'get-user-id',
        ),
        'api_calendar' => array(
            'activeEvents', 'poster',
        ),
        'api_config' => array(
            'entries',
        ),
        'api_cudi' => array(
            'articles', 'book', 'bookings', 'cancelBooking', 'currentSession', 'openingHours', 'signIn', 'signInStatus', 'is-same',
        ),
        'api_door' => array(
            'getRules', 'log',
        ),
        'api_mail' => array(
            'getAliases', 'getLists', 'getListsArchive',
        ),
        'api_member' => array(
            'all',
        ),
        'api_news' => array(
            'all',
        ),
        'api_oauth' => array(
            'authorize', 'shibboleth', 'token',
        ),
        'api_shift' => array(
            'active', 'responsible', 'volunteer', 'signOut',
        ),
    ),
);
