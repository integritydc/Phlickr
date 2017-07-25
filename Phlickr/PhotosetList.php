<?php

/**
 * @version $Id: PhotosetList.php 500 2006-01-03 23:29:08Z drewish $
 * @author  Andrew Morton <drewish@katherinehouse.com>
 * @license http://opensource.org/licenses/lgpl-license.php
 *          GNU Lesser General Public License, Version 2.1
 * @package Phlickr
 */

/**
 * Phlickr_PhotosetList is a read-only list of a users's photosets.
 *
 * This class allows the viewing user's photosets. If you need to add, remove,
 * or reorder photosets, use the Phlickr_AuthedPhotosetList class instead.
 *
 * @todo    Add sample code.
 * @package Phlickr
 * @author  Andrew Morton <drewish@katherinehouse.com>
 * @see     Phlickr_AuthedPhotosetList
 */
class Phlickr_PhotosetList extends Phlickr_Framework_ListBase {
    /**
     * The name of the XML element in the response that defines the object.
     *
     * @var string
     */
    const XML_RESPONSE_LIST_ELEMENT = 'photosets';
    /**
     * The name of the XML element in the response that defines a member of the list.
     *
     * @var string
     */
    const XML_RESPONSE_ELEMENT = 'photoset';
    /**
     * The name of the Flickr API method that provides the info on this object.
     *
     * @var string
     */
    const XML_METHOD_NAME = 'flickr.photosets.getList';

    /**
     * Flickr user id
     *
     * @var string
     */
    private $_userId = null;

    /**
     * Flickr PhotosetList allowed optional parameters
     *
     * @var string
     */
    protected $allowedParams = ['page', 'per_page', 'primary_photo_extras'];

    /**
     * Constructor
     *
     * The PhotosetList requires a User Id. If $userId is null the class will
     * try to use the $api->getUserId().
     *
     * @param   object Phlickr_API $api
     * @param   string $userId User Id. If this isn't provided, the API's User
     *          Id will be used instead.
     * @param   Array $params Optional arguments that can be passed
     *          to the API call.
     * @throws  Phlickr_Exception
     */
    function __construct(Phlickr_Api $api, $userId = null, $params = []) {
        if (isset($userId)) {
            $this->_userId = $userId;
        } else {
            $this->_userId = $api->getUserId();
        }

        if (is_null($this->_userId)) {
            throw new Phlickr_Exception('The photoset needs a User Id.');
        }

        parent::__construct(
            $api->createRequest(
                self::getRequestMethodName(),
                array_merge(
                    self::getRequestMethodParams($this->_userId),
                    $this->filterParams($params)
                )
            ),
            self::XML_RESPONSE_ELEMENT,
            self::XML_RESPONSE_LIST_ELEMENT
        );
    }
    /**
     * Returns the name of this object's getInfo API method.
     *
     * @return  string
     */
    static function getRequestMethodName() {
        return self::XML_METHOD_NAME;
    }
    /**
     * Returns an array of parameters to be used when creating a
     * Phlickr_Request to call this object's getInfo API method.
     *
     * @param   string $userId The user id of the photoset's owner.
     * @return  array
     */
    static function getRequestMethodParams($userId) {
        return array('user_id' => (string) $userId);
    }

    /**
     * Return an array of Phlickr_Photosets.
     *
     * @return array
     */
    public function getPhotosets() {
        if (!isset($this->_cachedXml->{$this->getResponseElement()})) {
            $this->load();
        }
        $ret = array();
        foreach ($this->_cachedXml->{$this->getResponseElement()} as $xml) {
            $set = new Phlickr_Photoset($this->getApi(), $xml);
            $ret[] = $set;
        }
        return $ret;
    }

    /**
     * Return the User Id that owns these photosets.
     *
     * @return string
     */
    function getUserId() {
        return $this->_userId;
    }
}
