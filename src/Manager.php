<?php
/**
 * Configuration management class, loads available configuration files.
 *
 * This performs standard configuration for SlabPHP sites
 *
 * @author Eric
 * @package Slab
 * @subpackage Configuration
 */
namespace Slab\Configuration;

class Manager implements \Slab\Components\ConfigurationManagerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $log;

    /**
     * Configuration values
     *
     * @var Parameter
     */
    private $_config = null;

    /**
     * @var array
     */
    private $fileDirectories = [];

    /**
     * @var array
     */
    private $fileNames = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_config = new Parameter(null, 'Configuration');

        $this->fileDirectories = [getcwd() . '/configs'];
        $this->fileNames = ['default.php', $_SERVER['SERVER_NAME'] . '.php'];
    }

    /**
     * @param \Psr\Log\LoggerInterface $log
     * @return $this
     */
    public function setLogger(\Psr\Log\LoggerInterface $log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @param $directories
     * @return $this
     */
    public function setFileDirectories($directories)
    {
        $this->fileDirectories = $directories;

        return $this;
    }

    /**
     * @param $filenames
     * @return $this
     */
    public function setFileNames($filenames)
    {
        $this->fileNames = $filenames;

        return $this;
    }

    /**
     * Load configurations
     */
    public function loadConfiguration()
    {
        $fileList = $this->buildFileList($this->fileDirectories, $this->fileNames);

        $this->parseConfigurationFiles($fileList);

        $this->mergeConfigurationOption($this->_config, 'configurationPaths', $this->fileDirectories);
    }

    /**
     * Parse an extra configuration file on top of the others
     *
     * @param mixed $configurationFile
     */
    public function pushConfigurationFile($configurationFile)
    {
        if (!is_array($configurationFile)) {
            $configurationFile = [$configurationFile];
        }

        $this->parseConfigurationFiles($configurationFile);
    }

    /**
     * Build file list
     *
     * @param array $directories
     * @param array $fileList
     * @return array
     */
    private function buildFileList($directories = [], $fileList = [])
    {
        $output = [];
        foreach ($directories as $directory) {
            foreach ($fileList as $file) {
                $fileName = $directory . DIRECTORY_SEPARATOR . $file;

                if (is_file($fileName) && is_readable($fileName)) {
                    $output[] = $fileName;
                }
            }
        }

        return $output;
    }


    /**
     * Go through each configuration directory and get the data
     *
     * @param string[] $fileList
     */
    private function parseConfigurationFiles($fileList)
    {
        foreach ($fileList as $file) {
            if (!is_file($file) || !is_readable($file)) {
                if ($this->configuration->getLog())
                {
                    $this->configuration->getLog()->error("Failed to load configuration file " . $file);
                }
                continue;
            }

            global $config;
            $config = array();

            include_once($file);

            foreach ($config as $name => $value) {
                $this->mergeConfigurationOption($this->_config, $name, $value);
            }

            unset($config);
            if (!empty($GLOBALS['config'])) {
                unset($GLOBALS['config']);
            }
        }

        //Do a little global cleanup
        unset($config);
        if (!empty($GLOBALS['config'])) {
            unset($GLOBALS['config']);
        }
    }

    /**
     * Magic get function for getting configuration values
     *
     * @param string $value
     * @return mixed
     */
    public function __get($key)
    {
        if (!empty($this->_config->$key)) {
            return $this->_config->$key;
        } else {
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
        $this->_config->$key = $value;
    }

    /**
     * Isset for configuration options
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->_config->$key);
    }

    /**
     * Return entire configuration table
     * @return Parameter
     */
    public function getTable()
    {
        return $this->_config;
    }

    /**
     * Merge a configuration option in
     *
     * @param mixed $currentNode
     * @param string $name
     * @param mixed $value
     */
    private function mergeConfigurationOption(&$currentNode, $name, $value)
    {
        if (empty($currentNode) || empty($name)) {
            return;
        }

        //We're going to be building our own objects here
        if (is_object($value)) {
            $value = (array)$value;
        }

        if (is_array($value)) {
            if ($this->isAssociative($value)) {
                //An associative array can contain non-leaves, recursively merge them in

                $childNode = null;

                if (!empty($currentNode->$name)) {
                    $childNode = $currentNode->$name;
                } else {
                    $childNode = new Parameter($currentNode, $name);
                    $currentNode->$name = $childNode;
                }

                foreach ($value as $subName => $subValue) {
                    $this->mergeConfigurationOption($childNode, $subName, $subValue);
                }
            } else {
                //Simply an array of values, just add them to the node and assume they're leaves

                if (empty($currentNode->$name)) {
                    $currentNode->$name = array();
                }

                if (!is_array($currentNode->$name)) {
                    $currentNode->$name = array();
                }

                foreach ($value as $subValue) {
                    $currentNode->{$name}[] = $subValue;
                }
            }
        } else {
            //Yay, a scalar value
            $currentNode->$name = $value;
        }
    }

    /**
     * Returns true if an array is an associative array or not
     *
     * @param $array
     * @return bool
     */
    private function isAssociative($array)
    {
        if (!is_array($array)) return false;

        $a = array_keys($array);
        return ($a !== array_keys($a));
    }
}