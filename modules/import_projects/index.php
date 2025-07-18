<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Index file for Import Projects module
 * 
 * This file prevents directory listing and ensures the module
 * is properly accessed through Perfex CRM's module system.
 * 
 * @package    Import Projects
 * @version    1.0.1
 * @author     Miguel Angel Sánchez
 */

// No direct access allowed
header('HTTP/1.0 403 Forbidden');
die('Direct access to this location is not allowed.');