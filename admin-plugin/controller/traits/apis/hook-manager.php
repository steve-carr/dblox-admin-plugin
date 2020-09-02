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
 * @package         Framework\Controller\Traits\Apis\HookManager
 * @version         1.0.0
 * @since           1.0.0
 */
/** NOTE
 *  Since PHP 5.6, a variable argument list can be specified with the "..." operator
 * 
 * Reference:
 * https://stackoverflow.com/questions/1422652/how-to-pass-variable-number-of-arguments-to-a-php-function
 */

namespace Dblox\Controller\Traits\Apis;

trait HookManager
{
    //** ******* <!--/ CONTROLLER API - HOOK MANAGER /--> ******** **//

    //** <!--/ if you follow the "Hooks" and "Logic" class structures... /--> **//

    public function insert_into_controller_hooks($object=NULL, $priority=NULL, $args=NULL)
    {
        /** NOTE
         *  Use this method to hook a subsystem's initialization, setup, 
         *  activation, deactivation and visualization methods into the 
         *  controller. Bypasses each method that doesn't exist.
         */
        $cleaned_args = (NULL === $args) ? 0 : $args;
        $pri = (NULL === $priority) ? $this->vars['priority'] : $priority;
        foreach ($this->functions as $function) {
            $label = $this->get_controller_label($function, 'action');
            if (is_object($object) && method_exists($object, $function)) {
                $this->add_to_action_hook(
                    $label, [$object, $function], 
                    $pri, $cleaned_args
                );
            } elseif (function_exists($function)) {
                $this->add_to_action_hook(
                    $label, $function, 
                    $pri, $cleaned_args
                );
            }
        }
    }
    
    public function insert_logic_into_subsystem_hooks($subsystem, $object=NULL, $priority=10, $args=1)
    {
        /** NOTE
         *  Use this method to hook a class' initialization, setup, 
         *  activation, deactivation and visualization methods into their 
         *  associated subsystem. Bypasses each method that doesn't exist.
         * 
         *  Typically, this will be used to load the actual subsystem's logic
         *  into the subsystem's do hooks. Thus allowing customization of the 
         *  subsystem by removing the original apply hook with a changed 
         *  version.
         */
        foreach ($this->functions as $function) {
            $label = $this->get_subsystem_function_label($subsystem, $function);
            if (is_object($object) && method_exists($object, $function)) {
                $this->add_to_action_hook(
                    $label, [$object, $function], 
                    $priority, $args
                );
            }else{
                if (function_exists($function)) {
                    $this->add_to_action_hook(
                        $label, $function, 
                        $priority, $args
                    );
                }
            }
        }
    }
    
    //** <!--/ hook registration, execution & tracking /--> **//

    public function add_to_action_hook($label, $action, $priority=0, $args=0)
    {
        $pri = ($priority <= 0) ? $this->vars['priority'] : $priority;
        $this->register_hook($label, $action, 'add_action');
        add_action($label, $action, $pri, $args);
    }

    public function add_to_filter_hook($label, $action, $priority=0, $args=1)
    {
        $pri = ($priority <= 0) ? $this->vars['priority'] : $priority;
        $this->register_hook($label, $action, 'add_filter');
        add_filter($label, $action, $pri, $args);
    }

    public function do_action_hook($label, $caller, $args=NULL)
    {
        $this->register_hook($label, $caller, 'do_action');
        if (NULL === $args) {
            return do_action($label, '');
        }else{
            return do_action($label, $args);
        }
    }

    public function do_filter_hook($label, $caller, ...$args)
    {
        $this->register_hook($label, $caller, 'apply_filters');
        return apply_filters($label, ...$args);
    }

    public function remove_from_action_hook($label, $action, $priority=0)
    {
        $pri = ($priority === 0) ? $this->vars['priority'] : $priority;
        $this->unregister_hook($label, $action, 'add_action');
        remove_action($label, $action, $pri);
    }

    public function remove_from_filter_hook($label, $action, $priority=0)
    {
        $pri = ($priority === 0) ? $this->vars['priority'] : $priority;
        $this->unregister_hook($label, $action, 'add_filter');
        remove_filter($label, $action, $pri);
    }

    public function register_hook($label, $action, $wpfunc='add_action')
    {
        if (is_array($action)) {
            $class = (is_object($action[0])) ? get_class($action[0]) : $action[0];
            $caller = [
                'class' => $class, 
                'func'  => $action[1],
            ];
        }else{
            $caller = [
                'class' => '', 
                'func'  => $action,
            ];
        }
        /** REFERENCE
         *  https://stackoverflow.com/questions/16308252/php-add-elements-to-multidimensional-array-with-array-push
         */
        $this->hooks[$wpfunc][$label][] = $caller;
    }

    public function unregister_hook($label, $action, $wpfunc='add_action')
    {
        if (!is_array($this->hooks[$wpfunc][$label])) { return FALSE; }
        foreach ($this->hooks[$wpfunc][$label] as $hook) {
            if(($action[0] === $hook['class']) && ($action[1] === $hook['func'])) {
                unset($hook);
            }
        }
    }

    public function execute_do_hook($api, $func, $type='filter', $data=NULL)
    {
        $option = ('filter' === $type) ? 'filter' : 'action';
        $label = $this->get_subsystem_function_label(
            $this->vars['label'], $func, $option
        );
        if ('filter' === $option) {
            $results = $this->do_filter_hook(
                $label, [$api, $func], $data
            );
        }else{
            if (NULL === $data) {
                $results = $this->do_action_hook(
                    $label, [$api, $func], ''
                );
            }else{
                $results = $this->do_action_hook(
                    $label, [$api, $func], $data
                );
            }
        }
        return $results;
    }

}
