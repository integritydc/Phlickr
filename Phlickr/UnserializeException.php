<?php

/**
 * Exception thrown when PHP data cannot be unserialized cannot be parsed.
 *
 * @package Phlickr
 * @fork    Integritydc
 * @author  Andrew Morton <drewish@katherinehouse.com> (Package)
 */
class Phlickr_UnserializeException extends Phlickr_Exception {
    /**
     *
     * @var string
     */
    protected $_data;

    /**
     * Constructor
     *
     * @param string $message
     * @param string $data
     */
    public function __construct($message = null, $data = null) {
        parent::__construct($message);
        $this->_data = (string) $data;
    }

    public function __toString() {
        $s = "exception '" . __CLASS__ . "' {$this->message}\n";
        if (isset($this->_data)) {
            $s .= "Data: '{$this->_data}'\n";
        }
        $s .= "Stack trace:\n" . $this->getTraceAsString();
        return $s;
    }

    /**
     * Return the un-parseable data.
     *
     * @return string
     */
    public function getData() {
        return $this->_data;
    }
}