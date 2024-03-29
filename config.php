<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// Moodle configuration file                                             //
//                                                                       //
// This file should be renamed "config.php" in the top-level directory   //
//                                                                       //
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 1999 onwards  Martin Dougiamas  http://moodle.com       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////
unset($CFG);  // Ignore this line
global $CFG;  // This is necessary here for PHPUnit execution
$CFG = new stdClass();

//===========================================================================
// @check
// Keept this when you use MariaDB, mysqli if you use MySQL
//===========================================================================
$CFG->dbtype = 'mariadb';  // 'pgsql', 'mariadb', 'mysqli', 'mssql', 'sqlsrv' or 'oci'

$CFG->dblibrary = 'native';  // 'native' only at the moment
$CFG->dbhost = 'localhost';  // eg 'localhost' or 'db.isp.com' or IP

//===========================================================================
// @check
// The name of the Database you created
//===========================================================================
$CFG->dbname = 'moodle';

//===========================================================================
// @check
// You may need to change the username and password to authenticate to the DB
//===========================================================================
$CFG->dbuser = 'root';
$CFG->dbpass = '';

$CFG->prefix = 'mdl_';       // prefix to use for all table names

$CFG->dboptions = array(
    'dbpersist' => false,
    'dbsocket'  => false,
    'dbport'    => '',
    'dbhandlesoptions' => false,
    'dbcollation' => 'utf8mb4_unicode_ci',
);

//===========================================================================
// @check
// You may need to update the port here
// Here moodle is saved in C:\xampp\htdocs\moodle
// C:\xampp\htdocs is the root directoy where files are served from the Webserver
//===========================================================================
$CFG->wwwroot = 'http://localhost:5000/moodle';

//=========================================================================
// @check
// The location to the sitedata Folder you created
//=========================================================================
$CFG->dataroot = 'C:\xampp\sitedata\moodle';

$CFG->directorypermissions = 02777;

$CFG->cachejs = false;

$CFG->admin = 'admin';

//=========================================================================
// ALL DONE!  To continue installation, visit your main page with a browser
//=========================================================================

require_once(__DIR__ . '/lib/setup.php'); // Do not edit

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
