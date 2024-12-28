<?php
namespace Theincubator\PhpRestApiLite\Helpers;
/**
 * Class to get properties from settings.ini file.
 *
 * @author charanputrevu
 */
class Settings {
    private $settings;
    
    public function __construct() {
        $this->settings = parse_ini_file("settings.ini");
    }
    
    /**
     * Get a property from settings.ini file
     * @param string $property
     * @return string
     */
    public function getProperty(string $property) {
        return $this->settings[$property];
    }
}
