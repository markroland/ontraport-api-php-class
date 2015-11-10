<?php

// Include Ontraport class
include('Ontraport.php');

// Include credentials. Defines ONTRAPORT_APPID and ONTRAPORT_KEY
include('ontraport-credentials.php');

// Create new Ontraport object
$Ontraport = new \markroland\Ontraport\Ontraport(ONTRAPORT_APPID, ONTRAPORT_KEY);

// Add contact
$response = $Ontraport->addContact(
	array(
		'Contact Information' => array(
			'First Name' => 'John',
			'Last Name' => 'Smith',
			'Email' => 'johnsmith@example.com'
		),
		'Sequences and Tags' => array(
			'Contact Tags' => 'test',
			'Sequences' => '*/*3*/*8*/*'
		)
	)
);

// Display unformatted (XML) response
// echo $response;

// Debugging info
// var_dump($Ontraport->debug);

// Parse response
$op_contact = new SimpleXMLElement($response);

// Display response attributes
echo $op_contact->status."\n";
echo $op_contact->contact['id']."\n";

?>