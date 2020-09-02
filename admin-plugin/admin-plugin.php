<?php

/** WordPress required details
Plugin Name:     Admin Plugin Controller
Plugin URI:      https://twocarrs.com/dblox
Description:     Bringing block-oriented solutions to plugins and themes.
Author:          Two Carr Productions, LLC
Author URI:      https://twocarrs.com/productions
Version:         1.0.0
Text Domain:     dblox_text_domain
Domain Path:     /languages
Network:         true            // <== Multisite enabled
Requires at least: 5.2
Requires PHP:    5.6.20
* 
*  Specify "Network: true" to require that a plugin is activated
*  across all sites in an installation. This will prevent a plugin from being
*  activated on a single site when Multisite is enabled.
*/
/**
 * Copyright (C) 2020 Two Carr Productions, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/** Developments Details
 * @author          Two Carr Productions, LLC
 * @link            https://twocarrs.com/productions
 * @copyright       Copyright (c) 2020 Two Carr Productions, LLC
 * @license         GPL-2+
 * 
 * @package         Framework
 * @version         1.0.0
 * @since           1.0.0
 * 
 * @namespace 
 * Change the 1st value in namespace if you want to personalize this framework  
 * to match your company's name. Change the 2nd value of this name space for 
 * each Controller you invoke within a WordPress environment.
 * 
 * Note, that if you change these values you will also have to replace the 
 * corresponding string in all files within the framework. For example:
 * 
 *     replace all occurrences of "Tccp/AdminPlugin" with "Acme/CoolTool"
 * 
 * Assumed details regarding field names containing:
 * label = lowercase with "_" and no ' '
 * slug  = lowercase with "-" and no ' '
 * id    = typically, lowercase with no ' '. (may contain '-' or '_')
 *         usually derived from external source such as WordPress
 * 
 */

namespace Dblox\AdminPlugin;
use \Dblox\AdminPlugin as Framework;
use \Dblox\Controller as Master;

if (!defined('ABSPATH')) { exit; }

if (!defined('DBLOX_COMPANY_INITIALS')) {
    //* MUST be lowercase (no ' ', '-' or '_')
    define('DBLOX_COMPANY_INITIALS', 'dblox');  //* all lowercase please
}

if (!defined('DBLOX_TEXT_DOMAIN')) {
    //* Make WP required detail (above) the same value
    define('DBLOX_TEXT_DOMAIN', 'dblox_text_domain');
}

$function_name = __NAMESPACE__."\\".'pre_admin_plugin_init';
if( !function_exists($function_name) ){
    
    function pre_admin_plugin_init()
    {
        $title = 'Admin Plugin';
        $good = validate_requirements($title);
        if (!$good) { return; }
        
        $label = str_replace(' ', '_', strtolower($title));
        //** <!--/ SET YOUR CONTROLLER SPECIFICS HERE /--> **//
        $args['vars'] = [
            'label'                 => $label,                // MUST be unique to each Controller
            'title'                 => $title,                // from WP required details (above)
            'options_label'         => DBLOX_COMPANY_INITIALS . '_' . $label .'_options',    // use to store data in wp-options
            /** NOTE
             *  Only Administrators of single site installations have the 
             *  'update_core' capability. For Multisite, only Super Admin has it.
             *  https://codex.wordpress.org/Roles_and_Capabilities
             */
            'capability'            => 'update_core',         // use as "default" when needed
            /** NOTE
             * 'icon'               => 'dashicons-hammer',    // Admin Plugin Framework
             * 'icon'               => 'dashicons-lightbulb', // Custom Plugin Framework
             * 'icon'               => 'dashicons-vault',     // Parent Theme Framework
             * 'icon'               => 'dashicons-art',       // Custom (Child) Theme Framework
             */
            'icon'                  => 'dashicons-hammer',    // Custom Plugin Framework
            'position'              => 66,                    // after Plugins
            'priority'              => 10,                    // default priority for "hook" calls
            'dir'                   => __DIR__,
            'is_plugin'             => is_dir_for_plugins(__DIR__),
            'auto_update'           => [
                'plugins'           => TRUE,
                'themes'            => TRUE,
            ],
        ];
        include_supporting_files();
        include __DIR__ . '/admin-plugin-api.php';
        $args['apis']['ctrl'] = Master\AdminPluginApi::get_instance($args);
        $args['apis']['ctrl']->bootstrap($args);
    }

}

$validate_requirements = __NAMESPACE__ . "\\" . 'validate_requirements';
if( !function_exists($valid_requirements) ){

    function validate_requirements($title)
    {
        //* Use "dirname(__FILE__)" & "array()" in case php is pre-5.3
        $dir = dirname(__FILE__);
        $min_reqs = array(
            'title' => $title,
            'php'   => '5.6.25',  //* 5.6.25 = earliest version I have to test with.
            'wp'    => '3.8.0',   //* 3.8 Help needs >=3.3 Customizer needs >=3.4
            'file'  => __FILE__,
        );
        include_once $dir . '/requirements-check.php';
        //* check minimum resource requirements *//
        $check = Framework\Requirements_Check::get_instance($min_reqs);
        return $check->if_requirements_pass();
    }

}

$is_dir_for_plugins = __NAMESPACE__ . "\\" . 'is_dir_for_plugins';
if( !function_exists($is_dir_for_plugins) ){

    function is_dir_for_plugins($dir)
    {
        $normalized = strtolower(str_replace("\\", '/', $dir));
        if (strpos($normalized, '/plugins/') !== FALSE) {
            return TRUE;
        }else{
            if (strpos($normalized, '/themes/') !== FALSE) {
                return FALSE;
            }else{
                return NULL;  //* possible bad input
            }
        }
    }

}

$include_supporting_files = __NAMESPACE__ . "\\" . 'include_supporting_files';
if( !function_exists($include_supporting_files) ){

    function include_supporting_files()
    {
        if (defined('DBLOX_PRIMARY_CONTROLLER')) { return; }
        // primary controller has NOT been declared
        $traits = __DIR__ . '/controller/traits';
        include $traits . '/apis/hook-manager.php';
        include $traits . '/apis/registration.php';
        include $traits . '/apis/stored-values.php';
        include $traits . '/apis/option-values.php';
        include $traits . '/tools/admin.php';
        include $traits . '/tools/arrays.php';
        include $traits . '/tools/generators.php';
        include $traits . '/tools/labels.php';
        include $traits . '/tools/notifications.php';
        include $traits . '/tools/paths.php';
        include $traits . '/tools/priorities.php';
        include $traits . '/tools/strings.php';
        include $traits . '/tools/styles.php';
        include $traits . '/tools/tests.php';
        include $traits . '/tools/updates.php';
        include $traits . '/tools/urls.php';
        include __DIR__ . '/controller/pages.php';
        include __DIR__ . '/controller/pages/data-visualization.php';
        include __DIR__ . '/controller/pages/hook-list.php';
        include __DIR__ . '/controller/pages/stored-values.php';
        include __DIR__ . '/controller/controller-hooks.php';
        include __DIR__ . '/controller/controller-logic.php';
        include __DIR__ . '/controller/controller-api.php';
    }

}

$label = strtolower(preg_replace("/\+/", '_', $function_name));
add_action($label, $function_name);
do_action($label); 
