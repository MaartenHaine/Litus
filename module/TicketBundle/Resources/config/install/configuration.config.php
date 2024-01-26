<?php

return array(
    array(
        'key'         => 'ticket.transactions_refresh_date',
        'value'       => '{{ currentDate }}-12:0:0',
        'description' => 'The transactions since noon',
    ),
    array(
        'key'         => 'ticket.remove_reservation_treshold',
        'value'       => 'P2D',
        'description' => 'The date interval after which a person cannot remove a ticket reservation',
    ),
    array(
        'key'         => 'ticket.pdf_generator_path',
        'value'       => 'data/ticket/pdf_generator',
        'description' => 'The path to the PDF generator files',
    ),
    array(
        'key'         => 'ticket.upper_text',
        'value'       => 'I agree that this data will be used, following GDPR guidelines.',
        'description' => serialize(
            array(
                'en' => 'The text on the book tickets page',
                'nl' => 'The extra tekst op de tickets page',
            )
        ),
    ),
    array(
        'key'         => 'ticket.confirmation_email_from',
        'value'       => 'tickets@vtk.be',
        'description' => 'Email address used for sending confirmation emails, also receives sent confirmation emails',
    ),
    array(
        'key'         => 'ticket.confirmation_email_body',
        'value'       => serialize(
            array(
                'subject' => 'VTK Tickets {{ event }}',
                'content' => 'Beste {{ fullname }},


U hebt tickets besteld voor {{ event }}.
Het gekozen ticket is:

{{ option }}

Indien u nog niet betaald hebt kan dit via volgende link: {{ paylink }}


Met Vriendelijke Groeten,

VTK



--- English ---

Dear {{ fullname }},


You ordered tickets for {{ event }}.
The chosen ticket is:

{{ option }}

Payment can be done through the following link should you not have paid yet: {{ paylink }}


Kind regards,

VTK',
            ),
        ),
        'description' => 'Email sent for confirmation of ticket reservation',
    ),
    array(
        'key'         => 'ticket.pay_link_domain',
        'value'       => 'vtk.be',
        'description' => 'The domain for the paylink used in generated emails',
    ),
    array(
        'key'         => 'ticket.subscription_mail_data',
        'value'       => serialize(
            array(
                'en' => array(
                    'subject' => 'Subscription for {{event}}',
                    'content' => 'Dear,
    
    You have subscribed for the event {{event}} on {{eventDate}}.
    
    This event uses QR codes for entry and other functionalities.
    Your personal QR code can be found here:
    <img src="{{qrSource}}" alt="QR code of this page generated by an api of google">
    
    This code links <a href="{{qrLink}}">here</a>.
    If it does not work, please contact us: <a href="mailto:{{actiMail}}">{{actiMail}}</a>.
    
    We are looking forward to seeing you there.
    
    VTK',
                ),
                'nl' => array(
                    'subject' => 'Inschrijving voor {{event}}',
                    'content' => 'Beste,
    
    U heeft zich ingeschreven voor het evenement {{event}} op {{eventDate}}.

    Dit evenement gebruikt QR-codes voor o.a. de inkom en andere functionaliteiten.
    Uw persoonlijke QR-code kan u hier vinden:
    <img src="{{qrSource}}" alt="QR code of this page generated by an api of google">
    
    Deze code linkt met <a href="{{qrLink}}">deze pagina</a>.
    Indien dit niet werkt, gelieve met ons contact op te nemen: <a href="mailto:{{actiMail}}">{{actiMail}}</a>.
    
    Wij kijken er naar uit om u daar te zien.
    
    VTK',
                ),
            )
        ),
        'description' => 'De mail data for the subscription mails.',
    ),
    array(
        'key'         => 'ticket.subscription_mail',
        'value'       => 'activiteiten@vtk.be',
        'description' => 'The mail address used in subscription communication.',
    ),
    array(
        'key'         => 'ticket.subscription_mail_name',
        'value'       => 'VTK',
        'description' => 'The signature name for subscription mails',
    ),
);
