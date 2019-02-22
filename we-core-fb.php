<?php
/*
Plugin Name:  WE CORE Facebook API
Plugin URI:   https://www.webenfold.com
description:  This plugin is a part of WebEnfold's WE-Core Engine and intended to use with only WE-Core Engine. This contains Facebook OAuth and Graph API for PHP Application.
Version:      1.1
Author:       WebEnfold
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

require 'updater/plugin-update-checker.php';
$update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/webenfold/we-core-fb/',
	__FILE__,
	'we-core-fb'
);
