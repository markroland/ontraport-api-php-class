<?php

namespace markroland\Ontraport;

/**
 *
 * A PHP class that acts as wrapper for the Ontraport API
 *
 * Read Ontraport API documentation at https://github.com/Ontraport/ontra_api_examples
 *
 * Copyright 2014 Mark Roland
 * Licensed under the MIT License
 * @author Mark Roland
 * @copyright 2014 Mark Roland
 * @license http://opensource.org/licenses/MIT
 * @link http://github.com/markroland/ontraport-api-php-class
 * @version 0.1.0
 *
 **/
class Ontraport
{

    /**
     * App ID
     * @var string
     */
    private $ontraport_appid = '';

    /**
     * Key
     * @var string
     */
    private $ontraport_key = '';

    /**
     * A variable to hold debugging information
     * @var array
     */
    public $debug = array();

    /**
     * Class constructor
     *
     * @param string $appid The App ID
     * @param string $key The App Key
     * @return null
     **/
    public function __construct($appid, $key)
    {

        // Save account ID to class object variable
        $this->ontraport_appid = $appid;
        $this->ontraport_key = $key;

    }

    /**
     * Send a HTTP request to the API
     *
     * @param string $api_method The API method to be called
     * @param string $http_method The HTTP method to be used (GET, POST, PUT, DELETE, etc.)
     * @param array $data Any data to be sent to the API
     * @return string An XML-formatted response
     **/
    private function sendRequest($api_method, $http_method = 'GET', $data = null)
    {

        // Set the request type and construct the POST request
        $postdata = "appid=".$this->ontraport_appid."&key=".$this->ontraport_key."&return_id=1";
        $postdata .= '&reqType='.$api_method;
        $postdata .= '&data='.$data;

        // Set request
        $request_url = 'https://api.ontraport.com/cdata.php';

        // Debugging output
        $this->debug = array();
        $this->debug['HTTP Method'] = $http_method;
        $this->debug['Request URL'] = $request_url;

        // Create a cURL handle
        $ch = curl_init();

        // Set the request
        curl_setopt($ch, CURLOPT_URL, $request_url);

        // Do not ouput the HTTP header
        curl_setopt($ch, CURLOPT_HEADER, false);

        // Save the response to a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Send data as PUT request
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);

        // This may be necessary, depending on your server's configuration
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Send data
        if (!empty($postdata)) {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: '.strlen($postdata)));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

            // Debugging output
            $this->debug['Posted Data'] = $postdata;

        }

        // Execute cURL request
        $curl_response = curl_exec($ch);

        // Save CURL debugging info
        $this->debug['Curl Info'] = curl_getinfo($ch);

        // Close cURL handle
        curl_close($ch);

        // Return parsed response
        return $curl_response;
    }

    /**
     * Add a Contact
     * @var array $contact An associative array of contact information. Key-value pairs will be
     * used as XML key-values
     * @var array $sequences_and_tags An associative array of sequence and/or tag information.
     * Key-value pairs will be used as XML key-values
     */
    public function addContact($contact, $sequences_and_tags)
    {

        // Build XML
        $data  = '<contact>'."\n";
        $data .= "\t".'<Group_Tag name="Contact Information">'."\n";
        foreach ($contact as $key => $val) {
            $data .= "\t\t".'<field name="'.$key.'">'.$val.'</field>'."\n";
        }
        $data .= "\t".'</Group_Tag>'."\n";
        $data .= "\t".'<Group_Tag name="Sequences and Tags">'."\n";
        foreach ($sequences_and_tags as $key => $val) {
            $data .= "\t\t".'<field name="'.$key.'">'.$val.'</field>'."\n";
        }
        $data .= "\t".'</Group_Tag>'."\n";
        $data .= '</contact>'."\n";

        // Encoded data
        $data = urlencode(urlencode($data));

        // Send Request
        return $this->sendRequest('add', 'POST', $data);

    }
}
