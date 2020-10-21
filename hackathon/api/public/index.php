<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
define('REQUEST_MICROTIME', microtime(true));

/*
 * Defines the variables of publication related
 */
$v = version();
define('VERSION', $v['short']);
defined('ENVIRONMENT')
    || define('ENVIRONMENT',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'development'));
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('post_max_size','128M');
ini_set('upload_max_filesize','128M');
ini_set('max_input_vars','5000');

set_time_limit(0);
date_default_timezone_set("America/Sao_Paulo");

function version() {
    $MAJOR = 2;
    $MINOR = 0;
    $PATCH = 0;
    exec('git describe --always',$version_mini_hash);
    exec('git rev-list HEAD | wc -l',$version_number);
    exec('git log -1',$line);
    $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
    $commitDate->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
    $version['short'] = "v$MAJOR.$MINOR.$PATCH - ".trim($version_number[0]).".".$version_mini_hash[0]." (" . $commitDate->format('d-m-Y H:i:s') . ")";
    $version['full'] = "v$MAJOR.$MINOR.$PATCH - ".trim($version_number[0]).".$version_mini_hash[0] [".str_replace('commit ','',$line[0])."]" . " (" . $commitDate->format('Y-m-d H:i:s') . ")";
    return $version;
}
    
// Setup autoloading
require 'vendor/autoload.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
