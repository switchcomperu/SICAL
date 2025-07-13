<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'import_projects_history`');
$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'import_projects_rows`');
