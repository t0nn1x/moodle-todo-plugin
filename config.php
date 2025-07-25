<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// Moodle configuration file                                             //
//                                                                       //
// This file should be renamed "config.php" in the top-level directory   //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

unset($CFG);
global $CFG;
$CFG = new stdClass();

//===========================================================================
// Database Configuration for Docker
//===========================================================================
$CFG->dbtype = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost = 'db';  // Docker service name

//===========================================================================
// Database credentials (matching docker-compose.yml)
//===========================================================================
$CFG->dbname = 'moodle';
$CFG->dbuser = 'moodleuser';
$CFG->dbpass = 'moodlepass';

$CFG->prefix = 'mdl_';

$CFG->dboptions = array(
    'dbpersist' => false,
    'dbsocket'  => false,
    'dbport'    => '',
    'dbhandlesoptions' => false,
    'dbcollation' => 'utf8mb4_unicode_ci',
);

//===========================================================================
// Web Configuration for Docker
//===========================================================================
$CFG->wwwroot = 'http://localhost:8080';

//=========================================================================
// Data Directory (Docker volume)
//=========================================================================
$CFG->dataroot = '/var/www/moodledata';

$CFG->directorypermissions = 02777;

// Development settings (remove in production)
$CFG->cachejs = false;
$CFG->cachetemplates = false;
$CFG->debug = 32767;  // Show all debug messages
$CFG->debugdisplay = 1;  // Display debug messages

$CFG->admin = 'admin';

//=========================================================================
// ALL DONE!  To continue installation, visit your main page with a browser
//=========================================================================

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
