<?php

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
 * 
 * @author          Two Carr Productions, LLC
 * @link            https://twocarrs.com/productions
 * @copyright       Copyright (c) 2020 Two Carr Productions, LLC
 * @license         GPL-2+
 * 
 * @package         Framework\Subsystems\AdminDbVcs
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Subsystems\AdminDbVcs;
use Dblox\Subsystems\AdminDbVcs as Subsystem;

if (!defined('ABSPATH')) { exit; } // Exit if accessed directly

$function_name = __NAMESPACE__."\\".'pre_admin_db_vcs_init';
if( !function_exists($function_name) ){

    function pre_admin_db_vcs_init()
    {
        include __DIR__ . '/admin-db-vcs-api.php';
        include __DIR__ . '/admin-db-vcs-logic.php';
        $ctrl_class = get_controller_class(2, __DIR__);
        $args['apis']['ctrl'] = $ctrl_class::get_instance();
        $args['vars'] = [
            'title'       => __('Admin DB VCS Manager', DBLOX_TEXT_DOMAIN),
            'version'     => '1.0.0',
            'label'       => 'admin_db_vcs',
            'priority'    => 10,
            'dir'         => __DIR__,
            'namespace'   => __NAMESPACE__,
        ];
        $subsystem_class = __NAMESPACE__."\\".'Api';
        $args['apis']['subsystem'] = $subsystem_class::get_instance($args);
        $args['apis']['ctrl']->register_subsystem(
            $args['vars']['label'], 
            $args['apis']['subsystem'], 
            $subsystem_class
        );
        Subsystem\Logic::get_instance($args);
    }

}

$get_controller_class = __NAMESPACE__ . "\\" . 'get_controller_class';
if( !function_exists($get_controller_class) ){

    function get_controller_class($levels, $path)
    {
        $separator = '/';   // "\\" or '/';
        $path = str_replace(array('/', '\\'), $separator, $path);
        if (version_compare(phpversion(), '7.0.0', '>=' )) {
            $dirname = substr(strrchr(dirname($path, $levels), $separator), 1);
        } else {
            $dirname = dirname($path);
            for ($i=1; $i<$levels; $i++){
                $dirname = dirname($dirname);
            }
            $dirname = substr(strrchr($dirname, $separator), 1);
        }
        $subject = ucwords(str_replace('-', ' ', $dirname));
        $framework = str_replace(' ', '', $subject);
        $class = ucwords(DBLOX_COMPANY_INITIALS)."\\".'Controller'."\\".$framework.'Api';
        return $class;
    }

}

$is_wplogin = __NAMESPACE__ . "\\" . 'is_wplogin';
if( !function_exists($is_wplogin) ){

    function is_wplogin()
    {
        $ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);
        return ((in_array($ABSPATH_MY.'wp-login.php', get_included_files()) || in_array($ABSPATH_MY.'wp-register.php', get_included_files()) ) || (isset($_GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') || $_SERVER['PHP_SELF']== '/wp-login.php');
    }

}


if (is_admin()) {
    $label = str_replace("\\", '_', strtolower($function_name));
    add_action($function_name, $function_name);
    do_action($function_name);
}
