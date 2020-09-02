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
 * @package         Framework\Subsystems\AdminMenubar
 * @version         1.0.0
 * @since           1.0.0
 */
/** NOTES
 *  @description
 *  The purpose of this subsystem is to provide and integrate solution for
 *  adding admin menubars. To leverage this subsystem you need to inject the 
 *  descriptive details of the component you want to create. The subsystem 
 *  takes care of interacting with WordPress to ensure your component shows as 
 *  expected.
 * 
 *  The Logic class fulfills the purpose of the subsystem. In addition it:
 *     - incorporates action hooks to facilitate external changes
 *     - injects a help tab into the controller's "data visualization" submenu
 *       providing descriptive details about the subsystem.
 * 
 *  The Api class provides methods for external interaction with the subsystem. 
 *  the relevant details (title, content, etc.) to describe your addition. 
 * 
 *  REFERNECES
 */

namespace Dblox\Subsystems\AdminMenubar;
use Dblox\Subsystems\AdminMenubar as Subsystem;

if (!defined('ABSPATH')) { exit; } // Exit if accessed directly

$function_name = __NAMESPACE__."\\".'pre_admin_menubar_init';
if( !function_exists($function_name) ){

    function pre_admin_menubar_init()
    {
        include __DIR__ . '/admin-menubar-api.php';
        include __DIR__ . '/admin-menubar-logic.php';
        $ctrl_class = get_controller_class(2, __DIR__);
        $args['apis']['ctrl'] = $ctrl_class::get_instance();
        $args['vars'] = [
            'title'       => __('Admin Menubar Manager', DBLOX_TEXT_DOMAIN),
            'version'     => '1.0.0',
            'label'       => 'admin_menubar',
            'capability'  => 'activate_plugins', // need access to admin page -> plugins
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

if(is_admin()) {
    $label = str_replace("\\", '_', strtolower($function_name));
    add_action($label, $function_name);
    do_action($label);
}
