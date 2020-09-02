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
 * 
 * @author          Two Carr Productions, LLC
 * @link            https://twocarrs.com/productions
 * @copyright       Copyright (c) 2020 Two Carr Productions, LLC
 * @license         GPL-2+
 * 
 * @package         Framework\Subsystems\Dependency\Logic
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Subsystems\Dependency;

class Logic
{
    //** ******* <!--/ SUBSYSTEM HOOKS /--> ******* **//

    protected $vars;

    protected $apis;

    public static function get_instance($args=NULL)
    {
        return new static($args);
    }

    protected function __construct($args=NULL)
    {
        $this->vars['subsystem'] = $args['vars'];
        $this->apis = $args['apis'];
        $this->vars['ctrl'] = $this->apis['ctrl']->get_value_for('all');
        $this->vars['labels'] = $this->apis['ctrl']->get_action_labels(
            $this->vars['subsystem']['label']
        );
        $this->hook_ups();
     }

     private function hook_ups()
     {
        //* insert this subsystem's logic into the controller's hooks
        $this->apis['ctrl']->insert_into_controller_hooks(
            $this, 
            $this->vars['subsystem']['priority'],
            0
        );
        //* Logic::store_help_tab_data => AdminMenu\Logic::insert_help_tab_data
        $label = $this->apis['ctrl']->get_subsystem_function_label(
            'admin_menu', 'insert_help_tab_data', 'action'
        );
        $this->apis['ctrl']->add_to_action_hook(
            $label, 
            [$this, 'store_help_tab_data'],
            $this->vars['subsystem']['priority'],
            1
        );
     }

    //** <!--/ initialization - methods /--> **//

    public function initialization()
    {
        $this->apis['ctrl']->do_action_hook(
            $this->vars['labels'][__FUNCTION__], 
            [$this, __FUNCTION__], 
            $this->apis['subsystem']
        );
    }

    //** <!--/ setup - supporting functions /--> **//

    public function setup()
    {
        $this->apis['ctrl']->do_action_hook(
            $this->vars['labels'][__FUNCTION__], 
            [$this, __FUNCTION__], 
            $this->apis['subsystem']
        );
    }

    //** <!--/ activation - methods /--> **//
 
    public function activation()
    {
        $this->apis['ctrl']->do_action_hook(
            $this->vars['labels'][__FUNCTION__], 
            [$this, __FUNCTION__], 
            $this->apis['subsystem']
        );
    }

    //** <!--/ deactivation - methods /--> **//
    
    public function deactivation()
    {
        $this->apis['ctrl']->remove_action_hook(
            $this->vars['labels']['activation'], 
            [$this, __FUNCTION__]
        );
    }

    //** <!--/ visualization - supporting functions /--> **//

    public function visualization()
    {
        $label = $this->apis['ctrl']->get_controller_label(__FUNCTION__, 'filter');
        $this->apis['ctrl']->add_to_filter_hook(
            $label, [$this, 'show_data'], 
            $this->vars['priority'], 2
        );
    }

    public function show_data($html, $visible)
    {
        $html .= '<h2>' . $this->vars['subsystem']['title'] . '</h2>';
        $html .= " version '" . $this->vars['subsystem']['version'] . "'<br>";
        if($visible){
            $html .= '<br>';
            $html .= "<strong>static instance of '" . __CLASS__ . "'</strong><br>";
            $html .= $this->apis['ctrl']->array_to_html($this->vars, 'vars');
            $html .= '<br>';
        }else{
            $list = $this->apis['subsystem']->list_of_all();
            if (NULL === $list) {
                $html .= 'No active dependencies!<br>';
            }else{
                $html .= 'Dependency list:<br>';
                $html .= '<strong>'.$list.'</strong>';
            }
        }
        return $html;
    }

    //** ******* <!--/ CALLBACK METHODS /--> ******* **//

    //** <!--/ admin dependency callback - methods /--> **//

    public function do_activation()
    {
 
        $dependencies = $this->apis['ctrl']->get_stored_values(
            $this->vars['subsystem']['label'], 
            NULL
        );
        if (!is_array($dependencies)) { return $html; }
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins');
        foreach ( $active_plugins as $index => $plugin ) {
            if ( array_key_exists( $plugin, $all_plugins ) ) {
                $active[] = $all_plugins[$plugin]['Name'];
            }
        }
        foreach ($dependencies as $dep) {
            if (!array_key_exists($dep['name'], $active) ) {
                $missing[] = $dep['name'];
            }
        }
        if (is_array($missing)) {
            $xlate = __('Missing dependency plugins', DBLOX_TEXT_DOMAIN);
            $this->apis['ctrl']->send_message($missing, $xlate);
        }
    }

    //** <!--/ help tab callback - methods /--> **//

    public function store_help_tab_data($menu_api)
    {
        //* add "admin menubar" help tab to controller's data visulaization submenu
        $pid = $menu_api->get_menu_id($this->apis['ctrl'], $this->vars['subsystem']['label']);
        $id = $menu_api->get_submenu_id($this->apis['ctrl'], 'data_visualization');
        $data = [
            'title' => $this->vars['subsystem']['label'],
            'priority' => $this->vars['subsystem']['priority'],
            'content' => $this->get_help_tab_content(),
        ];
        $indices = [
            'ctrl' => $this->apis['ctrl'],
            'type' => 'submenus',
            'pid' => $pid,
            'id' => $id,
        ];
        $tid = $menu_api->set_stored_help_tab_values($data, $indices, $this->apis['ctrl']);
        //* no associated help sidebar
    }

    private function get_help_tab_content()
    {
        $html = <<<EOT
        <p>
            The Admin Depenendency Manager subsystem provides a consistent interface for managing any plugin, theme or custom code dependencies you want to controll.
            To access the Subsystem's API:
            <ul>
                <li><strong>add_dependency</strong> : Creates a dependency and returns it's ID.</li>
                <li><strong>delete_dependency</strong> : Removes designated dependency.</li>
                <li><strong>list_of_missing</strong> : Creates an html-formatted list of missing dependencies.</li>
                <li><strong>list_of_all</strong> : Creates an html-formatted list of all dependencies.</li>
            </ul>
        </p>
EOT;
        return __($html, DBLOX_TEXT_DOMAIN);
    }

}
