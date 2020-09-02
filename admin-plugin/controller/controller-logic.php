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
 * @package         Framework\Controller\Logic
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller;

class Logic
{
    //** ******* <!--/ INSERT LOGIC INTO CONTROLLER'S HOOKS /--> ******* **//

    protected $vars;

    protected $apis;

    public static function get_instance($args=NULL)
    {
        return new static($args);
    }

    protected function __construct($args=NULL)
    {
        $this->vars = $args['vars'];
        $this->apis = $args['apis'];
        $this->insert_logic_into_controller_hooks();
    }

    private function insert_logic_into_controller_hooks()
    {
        $priority = $this->vars['priority'];
        $functions = $this->apis['ctrl']->get_value_for('functions');
        foreach ($functions as $func) {
            if (method_exists($this, $func)) {
                $label = $this->apis['ctrl']->get_controller_label($func, 'action');
                $this->apis['ctrl']->add_to_action_hook($label, [$this, $func], $priority, 0);
            }
        }
    }

    //** <!--/ CONTROLLER'S HOOK INSERTION POINTS /--> **//

    //** <!--/ uninstall - methods /--> **//

    public function uninstall()
    {
        /** NOTES
         * 
         * The label name should match what was stored in the Controller's ['option_id'] field
         * 
         * Reference: 
         * http://wpsmith.net/2014/plugin-uninstall-delete-terms-taxonomies-wordpress-database/
         * https://developer.wordpress.org/plugins/the-basics/uninstall-methods/
         * 
         */
        delete_transient($this->vars['options_label'] . '_transient');
        delete_option($this->vars['options_label']);
    }

    //** <!--/ visualization - methods /--> **//

    public function visualization()
    {
        $label = $this->apis['ctrl']->get_controller_label(__FUNCTION__, 'filter');
        $this->apis['ctrl']->add_to_filter_hook(
            $label, [$this, 'show_data'], 
            $this->vars['priority'], 2
        );
    }

    public function show_data($html, $visible=FALSE)
    {
        $head = $this->apis['ctrl']->collect_plugin_data();
        $html .= '<h2>'. $head['Name'] .'</h2>';
        $html .= " version '" . $head['Version'];
        if($visible){
            $list = [
                'vars'        => $this->apis['ctrl']->get_value_for('all'),
                'subsystems'  => $this->apis['ctrl']->get_value_for('subsystems'),
                'controllers' => $this->apis['ctrl']->get_value_for('controllers'),
                'functions'   => $this->apis['ctrl']->get_value_for('functions'),
            ];
            $html .= '<br>';
            $html .= "<strong>static instance of '". __CLASS__ ."'</strong><br>";
            foreach ($list as $title => $data) {
                $html .= '<br>';
                $html .= $this->apis['ctrl']->array_to_html($data, $title);
            }
        }
        return $html;
    }

}
