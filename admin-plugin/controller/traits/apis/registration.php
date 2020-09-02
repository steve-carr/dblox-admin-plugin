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
/** Development Details
 * @author          Two Carr Productions, LLC
 * @link            https://twocarrs.com/productions
 * @copyright       Copyright (c) 2020 Two Carr Productions, LLC
 * @license         GPL-2+
 * 
 * @package         Framework\Controller\Traits\Apis\Registration
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Apis;

trait Registration
{

    //** <!--/ multi-controller management /--> **//
    
    public function register_controller($namespace, $label, $pri_class=NULL)
    {
        $classname = str_replace(' ', '', ucwords(str_replace('_', ' ', $label))). 'Api';
        $class = $namespace . "\\" . $classname;
        if (defined('DBLOX_PRIMARY_CONTROLLER')) {               
            //* primary controller has been declared
            if ( (DBLOX_PRIMARY_CONTROLLER === $class) ||
                 (DBLOX_PRIMARY_CONTROLLER === $pri_class) ) {
                //* this is the primary controller
                if (DBLOX_PRIMARY_CONTROLLER !== $class) {
                    //* NOT primary calling itself, called by another controller
                    //* add to primary controller's managed list of controllers
                    $this->controllers[$label] = $class;
                }
            }else{
                // register with the primary controller
                $pri_class = DBLOX_PRIMARY_CONTROLLER;
                $pri_ctrl_api = $pri_class::get_instance();
                $pri_ctrl_api->register_controller($namespace, $label, $pri_class);
            }
        }else{
            //* primary controller has NOT been declared
            define("DBLOX_PRIMARY_CONTROLLER", $class);
        }
    }

    protected function get_controller_class()
    {
        return __NAMESPACE__ . "\\" . str_replace(' ', '', $this->vars['title']) . 'Api';
    }

    public function get_label_from_controller_class($class)
    {
        //* find location of last "\" - decimal value of "\" = chr(92)
        $start = strrpos($class, chr(92), -0) + 1;
        //* strip the "Api" from the end of the class_name
        $stringCamelCase = substr($class, $start, -3);
        //* create array based of Capitalized characters
        $array = preg_split(
            '#([A-Z][^A-Z]*)#', 
            $stringCamelCase, 
            null, 
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        //* build label from resulting array
        foreach ($array as $value) {
            $label .= lcfirst($value) . '_';
        }
        return rtrim($label, '_');
    }

    public function get_list_of_all_controller_classes()
    {
        $pri_ctrl_class = DBLOX_PRIMARY_CONTROLLER;
        $pri_ctrl_api = $pri_ctrl_class::get_instance();
        $label = $pri_ctrl_api->get_value_for('label');
        $controllers = $pri_ctrl_api->get_value_for('controllers');
        $prime[$label] = $pri_ctrl_class;
        $list = (is_array($controllers)) ? array_merge($prime, $controllers) : $prime;
        return $list;
    }

    //** <!--/ subsystem registration & tracking /--> **//

    public function register_subsystem($label, $obj, $class='')
    {
        if (is_array($this->subsystems) && 
            in_array($label, $this->subsystems)) { return; }
        $this->subsystems[$label] = [
            'class' => $class,
            'object' => $obj,
        ];
    }
  
    public function get_registered_subsystem($label='', $param='object', $level=0)
    {
        if('' == $label) { return $this->subsystems; }
        $value = NULL;
        if(is_array($this->subsystems[$label])) {
            //* It is registered with this controller
            if(is_object($this->subsystems[$label][$param])){
                $value = $this->subsystems[$label]['object'];
            }else{
                $value = $this->subsystems[$label]['class'];
            }
            return $value;
        }else{
            //* It MAY be registered with another controller - $level controls recurrsion
            if ( ($level === 0) && (defined('DBLOX_PRIMARY_CONTROLLER')) ) {
                ++$level;
                $value = $this->was_susbsytem_registered_with_another_controller($label, $param, $level);
            }
        }
        return $value;
    }

    protected function was_susbsytem_registered_with_another_controller($label, $param, $level)
    {
        $value = NULL;
        $initial_class = $this->get_controller_class();
        $primary_class = DBLOX_PRIMARY_CONTROLLER;
        $primary_controller_api = $primary_class::get_instance();
        $controllers = $primary_controller_api->get_list_of_all_controller_classes();
        foreach ($controllers as $class) {
            if ($class !== $initial_class) {
                //* as long as its not target controller (already tested)
                $api = $class::get_instance();
                $value = $api->get_registered_subsystem($label, $param, NULL, $level);
                if (NULL !== $value) { return $value; }
            }
        }
        return $value;
    }

    public function are_subsystems_registered($labels, &$apis, $silence=FALSE)
    {
        $missing = NULL;
        foreach ($labels as $value) {
            $apis[$value] = $this->get_registered_subsystem($value);
            if (!is_object($apis[$value])) { 
                $missing[] .= $value;
            }
        }
        if (is_array($missing)) {
            if (FALSE === $silence) {
                $xlate = __('Missing Subsystem(s)', DBLOX_TEXT_DOMAIN);
                $this->send_message($xlate.':', $missing);
            }
            return FALSE;
        }else{
            return TRUE;
        }
    }

}
