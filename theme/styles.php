<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file is responsible for serving the one huge CSS of each theme.
 *
 * @package   moodlecore
 * @copyright 2009 Petr Skoda (skodak)  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);

// we need just the values from config.php and minlib.php
define('ABORT_AFTER_CONFIG', true);
require('../config.php'); // this stops immediately at the beginning of lib/setup.php
require_once($CFG->dirroot.'/lib/csslib.php');

if ($slashargument = min_get_slash_argument()) {
    $slashargument = ltrim($slashargument, '/');
    if (substr_count($slashargument, '/') < 2) {
        image_not_found();
    }
    // image must be last because it may contain "/"
    list($themename, $rev, $type) = explode('/', $slashargument, 3);
    $themename = min_clean_param($themename, 'SAFEDIR');
    $rev       = min_clean_param($rev, 'INT');
    $type      = min_clean_param($type, 'SAFEDIR');

} else {
    $themename = min_optional_param('theme', 'standard', 'SAFEDIR');
    $rev       = min_optional_param('rev', 0, 'INT');
    $type      = min_optional_param('type', 'all', 'SAFEDIR');
}

if (!in_array($type, array('all', 'ie', 'editor', 'plugins', 'parents', 'theme'))) {
    header('HTTP/1.0 404 not found');
    die('Theme was not found, sorry.');
}

if (file_exists("$CFG->dirroot/theme/$themename/config.php")) {
    // exists
} else if (!empty($CFG->themedir) and file_exists("$CFG->themedir/$themename/config.php")) {
    // exists
} else {
    header('HTTP/1.0 404 not found');
    die('Theme was not found, sorry.');
}

if ($type === 'ie') {
    css_send_ie_css($themename, $rev, $etag, !empty($slashargument));
}

$candidatesheet = "$CFG->cachedir/theme/$themename/css/$type.css";
$etag = sha1("$themename/$rev/$type");

if (file_exists($candidatesheet)) {
    if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) || !empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        // we do not actually need to verify the etag value because our files
        // never change in cache because we increment the rev parameter
        css_send_unmodified(filemtime($candidatesheet), $etag);
    }
    css_send_cached_css($candidatesheet, $etag);
}

//=================================================================================
// ok, now we need to start normal moodle script, we need to load all libs and $DB
define('ABORT_AFTER_CONFIG_CANCEL', true);

define('NO_MOODLE_COOKIES', true); // Session not used here
define('NO_UPGRADE_CHECK', true);  // Ignore upgrade check

require("$CFG->dirroot/lib/setup.php");

$theme = theme_config::load($themename);

$rev = theme_get_revision();
$etag = sha1("$themename/$rev/$type");

if ($type === 'editor') {
    $cssfiles = $theme->editor_css_files();
    css_store_css($theme, $candidatesheet, $cssfiles);
} else {
    $css = $theme->css_files();
    $allfiles = array();
    foreach ($css as $key=>$value) {
        $cssfiles = array();
        foreach($value as $val) {
            if (is_array($val)) {
                foreach ($val as $k=>$v) {
                    $cssfiles[] = $v;
                }
            } else {
                $cssfiles[] = $val;
            }
        }
        $cssfile = "$CFG->cachedir/theme/$themename/css/$key.css";
        css_store_css($theme, $cssfile, $cssfiles);
        $allfiles = array_merge($allfiles, $cssfiles);
    }
    $cssfile = "$CFG->cachedir/theme/$themename/css/all.css";
    css_store_css($theme, $cssfile, $allfiles);
}

// verify nothing failed in cache file creation
clearstatcache();
if (!file_exists($candidatesheet)) {
    css_send_css_not_found();
}

css_send_cached_css($candidatesheet, $etag);
