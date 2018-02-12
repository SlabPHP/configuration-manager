<?php
/**
 * Configuration Parameter Object
 *
 * All configuration options should be held within a parameter container so
 * that if an unspecified configuration option is attempted, we can log it
 * and return a null default value.
 *
 * @author eric
 * @package Slab
 * @subpackage Configuration
 */
namespace Slab\Configuration;

class Parameter
{
    /**
     * Parent Parameter
     *
     * @var Parameter
     */
    protected $_parent;

    /**
     * Node name
     *
     * @var string
     */
    protected $_name;

    /**
     * Constructor
     *
     * @param Parameter|null $parent
     * @param string $name
     */
    public function __construct($parent = null, $name = '')
    {
        $this->_parent = $parent;

        $this->_name = $name;
    }

    /**
     * Get name of configuration value
     *
     * @param string $value
     * return $string;
     */
    public function getConfigurationParameterName($value = null)
    {
        $output = '';

        if (!empty($this->_parent) && $this->_parent instanceof Parameter) {
            $output .= $this->_parent->getConfigurationParameterName();
        }

        if (!empty($this->_name)) {
            if (!empty($output)) $output .= '->';

            $output .= $this->_name;
        } else {
            if (!empty($output)) $output .= '->';

            $output .= '{unspecified}';
        }

        if (!empty($value)) {
            if (!empty($output)) $output .= '->';

            $output .= $value;
        }

        return $output;
    }

    /**
     * Magic get function for getting configuration values
     *
     * @param string $value
     * @return mixed
     */
    public function __get($key)
    {
        if (!empty($this->$key)) {
            return $this->$key;
        } else {
            $this->getSystem()->log->error("A request for a configuration option '" . $this->getConfigurationParameterName($key) . "' was attempted but it has not been set.");
            return false;
        }
    }

    /**
     * Magic setter for configuration options
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * Isset for configuration options
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }

    /**
     * Get flattened scalars from a configuration parameter, recursively
     *
     * @return array
     */
    public function flattenResult()
    {
        $output = [];

        $vars = (array)$this;

        foreach ($vars as $field => $value) {
            //Trim off the protected items that come in as \0*\0_name
            if (ord($field[0]) == 0) continue;

            if ($value instanceof Parameter) {
                $output[$field] = $value->flattenResult();
            } else {
                $output[$field] = $value;
            }
        }

        return $output;
    }
}