<?php
/**
 * Configuration object for Configuration Library
 *
 * @package Slab
 * @subpackage Configuration
 * @author Eric
 */
namespace Slab\Configuration;

class Configuration
{
    /**
     * @var array
     */
    private $cascadingSearchDirectories = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $log;

    /**
     * @var array
     */
    private $fileList = [];

    /**
     * Configuration constructor.
     */
    public function __construct()
    {
        $this->cascadingSearchDirectories = [getcwd() . '/configs'];
        $this->fileList = ['default.php', $_SERVER['SERVER_NAME'] . '.php'];
    }

    /**
     * @param $directories
     * @return $this
     */
    public function setCascadingSearchDirectories($directories)
    {
        $this->cascadingSearchDirectories = $directories;

        return $this;
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
     * @return array
     */
    public function getCascadingSearchDirectories()
    {
        return $this->cascadingSearchDirectories;
    }

    /**
     * @return array
     */
    public function getFileList()
    {
        return $this->fileList;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLog()
    {
        return $this->log;
    }
}