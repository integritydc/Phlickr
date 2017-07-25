<?php

/**
 * @version $Id: Response.php 500 2006-01-03 23:29:08Z drewish $
 * @author  Andrew Morton <drewish@katherinehouse.com>
 * @license http://opensource.org/licenses/lgpl-license.php
 *          GNU Lesser General Public License, Version 2.1
 * @package Phlickr
 */

/**
 * Phlickr_Response handles the XML returned by a Phlickr_Request.
 *
 * Sample usage:
 * <code>
 * <?php
 * include_once 'Phlickr/Api.php';
 *
 * // store a sample response into a variable
 * $xmlResponse = <<<XML
 * <?xml version="1.0" encoding="utf-8" ?>
 *  <rsp stat="ok">
 *      <user id="39059360@N00">
 *          <username>just testing</username>
 *      </user>
 *  </rsp>
 * XML;
 *
 * // instantiate the object
 * $response = new Phlickr_Response($xmlResponse);
 *
 * // was the request successful?
 * print $response->isOk();
 *
 * // view the response (using its __toString() function)
 * print $response;
 * ?>
 * </code>
 *
 * This class is responsible for:
 * - Converting the XML string returned by a Phlickr_Request object into a
 *   SimpleXML object.
 * - Determining the success or failure of the request.
 *
 * @package Phlickr
 * @author  Andrew Morton <drewish@katherinehouse.com>
 * @since   0.1.0
 */
class Phlickr_Response {
    /**
     * The Request was sent and the server responded with a valid Response.
     * This constant is defined by Flickr's API.
     *
     * @var string
     */
    const STAT_OK = 'ok';
    /**
     * The Request was sent but the server found a problem with the Request.
     * This constant is defined by Flickr's API.
     *
     * @var string
     */
    const STAT_FAIL = 'fail';

    /**
     * Payload of the Response.
     *
     * @var object SimpleXMLElement or
     *      string JSON or
     *      Array unserialized PHP data
     */
    var $data = null;

    /**
     * Format of the Reponse.
     *
     * @var string
     */
    private $_format = null;

    /**
     * Status of the Reponse.
     *
     * @var string
     * @see STAT_OK, STAT_FAIL
     */
    var $stat = null;
    /**
     * Error code.
     * This variable is only assigned when !$this->isOk()
     *
     * @var integer
     */
    var $err_code = null;
    /**
     * Error message.
     * This variable is only assigned when !$this->isOk()
     *
     * @var string
     * @access public
     */
    var $err_msg = null;

    /**
     * Constructor takes output from the http request
     *
     * @param string $restResult XML string from a Flickr_Request object.
     * @param boolean $throwOnFailed Should an exception be thrown when the
     *      response indicates failure?
     * @throws Phlickr_XmlParseException, Phlickr_Exception
     */
    function __construct($format, $restResult, $throwOnFailed = false) {
        $this->_format = $format;
        switch($this->_format) {
            case 'rest':
                $data = simplexml_load_string($restResult);
                if (false === $data) {
                    throw new Phlickr_XmlParseException('Could not parse XML.', $restResult);
                }
                $this->stat = (string) $data['stat'];
                if(property_exists($data, 'err')) 
                {
                    $this->err_code = (integer) $data->err['code'];
                    $this->err_msg = (string) $data->err['msg'];
                }
                break;
            case 'json':
                preg_match('/^jsonFlickrApi\((.*)\)$/', $restResult, $matches);
                $data = $matches[1];
                $json = json_decode($data, true);
                $this->stat = (string) $json['stat'];
                if(array_key_exists('code', $json)) 
                {
                    $this->err_code = (integer) $json['code'];
                    $this->err_msg = (string) $json['message'];
                }
                break;
            case 'php_serial':
                $data = unserialize($restResult);
                if (false === $data) {
                    throw new Phlickr_UnserializeException('Could not parse the supplied PHP.', $restResult);
                }
                $this->stat = (string) $data['stat'];
                if(array_key_exists('code', $data)) 
                {
                    $this->err_code = (integer) $data['code'];
                    $this->err_msg = (string) $data['message'];
                }
                break;

        }

        if ($this->isOk()) {
            $this->data = $data;
        } else {
            if ($throwOnFailed) {
                throw new Phlickr_MethodFailureException($this->err_msg, $this->err_code);
            }
        }
    }

    public function __toString() {
        switch($this->_format) {
            case 'rest':
                return $this->data->asXML();
                break;
            case 'json':
                return $this->data;
                break;
            case 'php_serial':
                return serialize($this->data);
                break;
        }
    }

    /**
     * Check if the Response is successful
     *
     * @return boolean
     */
    public function isOk() {
        return ($this->stat == self::STAT_OK);
    }

    /**
     * Get the XML Object.
     *
     * @return  object SimpleXML
     * @see     SimpleXML::asXML()
     * @since   0.2.3
     * @todo    Deprecated. Should use getData() from now on.
     */
    public function getXml() {
        return $this->getData();
    }

    /**
     * Get the data.
     *
     * @return  object SimpleXML or
     *          string JSON or
     *          Array unseralized PHP
     *          
     * @since   0.2.3
     */
    public function getData() {
        switch($this->_format) {
            case 'rest':
                return $this->data;
                break;
            case 'json':
                return $this->data;
                break;
            case 'php_serial':
                return $this->data;
                break;
        }
    }
}
