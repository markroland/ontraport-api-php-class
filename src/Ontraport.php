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
 * @version 0.2.0
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

        $apiMethod = explode('_', strtolower($api_method), 2);
        if (isset($apiMethod[1])) {
            $api_method = $apiMethod[1];
        }

        // Set the request type and construct the POST request
        $postdata = "appid=".$this->ontraport_appid."&key=".$this->ontraport_key."&return_id=1";
        $postdata .= '&reqType='.$api_method;
        $postdata .= '&data='.$data;

        // Set request
        switch ($apiMethod[0]) {
            case 'forms':
                $request_url = 'https://api.ontraport.com/fdata.php';
                break;

            case 'products':
                $request_url = 'https://api.ontraport.com/pdata.php';
                break;

            case 'contacts':
            default:
                $request_url = 'https://api.ontraport.com/cdata.php';
        }

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
     * Send a HTTP request to the API
     *
     * @param $response in raw XML
     * @return object An XML-formatted response
     **/
    public function parseResponse($response)
    {
        try {
            $parsedResponse = new \SimpleXMLElement($response);
        } catch (Exception $ex) {
            return false;
        }

        return $parsedResponse;
    }

    /**
     * Add a Contact
     * @var array $sections A multidimensional associative array of contact information.
     * Key-value pairs will be used as XML key-values
     * @return Returns a sendRequest response
     */
    public function addContact(array $sections = array())
    {

        // Build XML
        $data  = '<contact>'."\n";
        foreach ($sections as $group_tag_name => $section) {
            $data .= "\t".'<Group_Tag name="'.$group_tag_name.'">'."\n";
            foreach ($section as $key => $val) {
                $data .= "\t\t".'<field name="'.$key.'">'.htmlspecialchars($val).'</field>'."\n";
            }
            $data .= "\t".'</Group_Tag>'."\n";
        }
        $data .= '</contact>'."\n";

        // Encoded data
        $data = urlencode(urlencode($data));

        // Send Request
        return $this->sendRequest('contacts_add', 'POST', $data);

    }

    /**
     * Fetch a single Contact
     * @var string $contactId a string with the contact_id being fetched
     * @return Returns a sendRequest response
     */
    public function getContact($contactId)
    {
        if ($contactId) {
            // Build XML
            $data = '<contact_id>' . $contactId . '</contact_id>'."\n";

            // Encoded data
            $data = urlencode(urlencode($data));

            // Send Request
            return $this->sendRequest('contacts_fetch', 'POST', $data);
        }

        return false;

    }

    /**
     * Fetch multiple Contact
     * @var array $contact_id an array of contact_ids to fetch.
     * @return Returns a sendRequest response
     */
    public function getContacts(array $contactIds = array())
    {
        if (!empty($contactIds)) {
            // Build XML
            $data = '';
            foreach ($contactIds as $contactId) {
                $data .= '<contact_id>' . $contactId . '</contact_id>'."\n";
            }

            // Encoded data
            $data = urlencode(urlencode($data));

            // Send Request
            return $this->sendRequest('contacts_fetch', 'POST', $data);
        }

        return false;

    }

    /**
     * Update a Contact
     * @var string $id the id of the contact to update
     * @var array $sections A multidimensional associative array of contact information.
     * Key-value pairs will be used as XML key-values
     * @return Returns a sendRequest response
     */
    public function updateContact($id, array $sections = array())
    {

        // Build XML
        $data  = '<contact id="' . $id . '">'."\n";
        foreach ($sections as $group_tag_name => $section) {
            $data .= "\t".'<Group_Tag name="'.$group_tag_name.'">'."\n";
            foreach ($section as $key => $val) {
                $data .= "\t\t".'<field name="'.$key.'">'.htmlspecialchars($val).'</field>'."\n";
            }
            $data .= "\t".'</Group_Tag>'."\n";
        }
        $data .= '</contact>'."\n";

        // Encoded data
        $data = urlencode(urlencode($data));

        // Send Request
        return $this->sendRequest('contacts_update', 'POST', $data);

    }

    /**
     * Search Contact
     * @var array $sections A multidimensional associative array of search equations.
     * Key-value pairs will be used as XML key-values
     * @return Returns a sendRequest response
     */
    public function searchContacts(array $equations, $page = null)
    {

        // Build XML
        $data  = '<search>'."\n";
        foreach ($equations as $equation) {
            if (isset($equation['field']) && isset($equation['op']) && isset($equation['value'])) {
                $data .= "\t".'<equation>'."\n";
                $data .= "\t\t".'<field>' . $equation['field'] . '</field>'."\n";
                $data .= "\t\t".'<op>' . $equation['op'] . '</op>'."\n";
                $data .= "\t\t".'<value>' . $equation['value'] . '</value>'."\n";
                $data .= "\t".'</equation>'."\n";
            }
        }
        $data .= '</search>'."\n";

        // Encoded data
        $data = urlencode(urlencode($data));

        // Send Request
        return $this->sendRequest('contacts_search', 'POST', $data);
    }



    /**
     * Add a Product Sale
     * @var int $contactId contact id (required)
     * @var int $productId product id (required)
     * @var array $sections A multidimensional associative array of optional fields.
     * Key-value pairs will be used as XML key-values
     * @return Returns a sendRequest response
     */
    public function saleProduct($contactId, $productId, array $optionalFields = array())
    {

        // Build XML
        $data  = '<purchases contact_id="' . $contactId . '" product_id="' . $productId . '">'."\n";
        foreach ($optionalFields as $fieldName => $fieldValue) {
            $data .= "\t".'<field name="' . $fieldName . '">' . $fieldValue . '</field>'."\n";
        }

        // Encoded data
        $data = urlencode(urlencode($data));

        // Send Request
        return $this->sendRequest('products_sale', 'POST', $data);

    }

    /**
     * Log Transactions
     * @var int $contactId An id of the contact
     * @var array $products A multidimensional associative array of products that were purchased.
     * @var int $date A date timestamp
     * Key-value pairs will be used as XML key-values
     * @return Returns a sendRequest response
     */
    public function logTransaction($contactId, array $products, $date = null)
    {

        if (is_null($date)) {
            $date = time();
        }

        if (!is_array($products)) {
            return false;
        }

        // Build XML
        $data = array();
        $data['contact_id'] = $contactId;
        $data['date'] = $date;
        $data['products'] = $products;

        // Encoded data
        $data = json_encode($data);

        // Send Request
        return $this->sendRequest('products_log_transaction', 'POST', $data);
    }

    /**
     * Search Products
     * @var array $sections A multidimensional associative array of search equations.
     * Key-value pairs will be used as XML key-values
     * @return Returns a sendRequest response
     */
    public function searchProducts(array $equations, $page = null)
    {

        // Build XML
        $data  = '<search>'."\n";
        foreach ($equations as $equation) {
            if (isset($equation['field']) && isset($equation['op']) && isset($equation['value'])) {
                $data .= "\t".'<equation>'."\n";
                $data .= "\t\t".'<field>' . $equation['field'] . '</field>'."\n";
                $data .= "\t\t".'<op>' . $equation['op'] . '</op>'."\n";
                $data .= "\t\t".'<value>' . htmlspecialchars($equation['value']) . '</value>'."\n";
                $data .= "\t".'</equation>'."\n";
            }
        }
        $data .= '</search>'."\n";

        // Encoded data
        $data = urlencode(urlencode($data));

        // Send Request
        return $this->sendRequest('products_search', 'POST', $data);
    }

    /**
     * Search Purchases
     * @var array $sections A multidimensional associative array of search equations.
     * Key-value pairs will be used as XML key-values
     * @return Returns a sendRequest response
     */
    public function searchPurchases(array $equations, $page = null)
    {

        // Build XML
        $data  = '<search>'."\n";
        foreach ($equations as $equation) {
            if (isset($equation['field']) && isset($equation['op']) && isset($equation['value'])) {
                $data .= "\t".'<equation>'."\n";
                $data .= "\t\t".'<field>' . $equation['field'] . '</field>'."\n";
                $data .= "\t\t".'<op>' . $equation['op'] . '</op>'."\n";
                $data .= "\t\t".'<value>' . htmlspecialchars($equation['value']) . '</value>'."\n";
                $data .= "\t".'</equation>'."\n";
            }
        }
        $data .= '</search>'."\n";

        // Encoded data
        $data = urlencode(urlencode($data));

        // Send Request
        return $this->sendRequest('products_search_purchase', 'POST', $data);
    }
}
