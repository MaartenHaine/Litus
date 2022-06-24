<?php

return array(
    'apibundle' => array(
        'api_admin_key' => array(
            'add', 'delete', 'edit', 'manage',
        ),
        'api_auth' => array(
            'getCorporate', 'getPerson',
        ),
        'api_calendar' => array(
            'activeEvents', 'poster',
        ),
        'api_config' => array(
            'entries',
        ),
        'api_cudi' => array(
            'articles', 'book', 'bookings', 'cancelBooking', 'currentSession', 'openingHours', 'signIn', 'signInStatus', 'isSame',
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
