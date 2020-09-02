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
 * @package         Framework\Subsystems\AdminDbVcs\Logic
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Subsystems\AdminDbVcs;

class Logic
{
   //** ******* <!--/ SUBSYSTEM LOGIC LOADER /--> ******* **//

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

     protected function hook_ups()
     {
        if (!is_admin()) { return; }
        //* insert subsystem's logic into the controller's hooks
        $this->apis['ctrl']->insert_into_controller_hooks(
            $this, $this->vars['subsystem']['priority'], 0
        );
        //* Logic::store_help_tab_data => AdminMenu\Logic::insert_help_tab_data
        $label = $this->apis['ctrl']->get_subsystem_function_label(
            'admin_menu', 'insert_help_tab_data', 'action'
        );
        $this->apis['ctrl']->add_to_action_hook(
            $label, [$this, 'store_help_tab_data'],
            $this->vars['subsystem']['priority'], 1
        );
     }

     //** ******* <!--/ SUBSYTEM'S LOGIC /--> ******* **//

    //** <!--/ visualization - supporting functions /--> **//

    public function initialization()
    {
        /** NOTE
         *  This hook is an ideal place to "hook" in with any menu, submenu, 
         *  help tab or helpsidebar creation logic. For example, this is where 
         *  the controllers insert their menu logic.
         */
        $this->apis['ctrl']->do_action_hook(
            $this->vars['labels'][__FUNCTION__], 
            [$this, __FUNCTION__], 
            $this->apis['subsystem']
        );
    }

    public function setup()
    {
        /** NOTE
         *  An alternative point to make your changes. The timing between the 
         *  execution of initialization and setup methods MAY make this a
         *  better place to hook into the subsystem.
         */
        $this->apis['ctrl']->do_action_hook(
            $this->vars['labels'][__FUNCTION__], 
            [$this, __FUNCTION__], 
            $this->apis['subsystem']
        );
    }

    public function activation()
    {
        $this->apis['ctrl']->do_action_hook(
            $this->vars['labels'][__FUNCTION__], 
            [$this, __FUNCTION__], 
            $this->apis['subsystem']
        );
    }

    public function deactivation()
    {
        $this->apis['ctrl']->do_action_hook(
            $this->vars['labels'][__FUNCTION__], 
            [$this, __FUNCTION__], 
            $this->apis['subsystem']
        );
    }

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
            $html .= $this->show_version_details();
        }
        return $html;
    }
    
    protected function show_version_details($subsystem_label='')
    {
        $current = $this->apis['ctrl']->get_option_table_values($this->vars['label'], $subsystem_label, NULL);
        if (NULL === $current) {
            $html .= __('No database versions are being tracked.', DBLOX_TEXT_DOMAIN);
        }else{
            $xlate = __('database updates', DBLOX_TEXT_DOMAIN);
            $html .= $this->apis['ctrl']->array_to_html($current, $xlate);
        }
        return $html;
    }

    //** ******* <!--/ CALLBACK METHODS /--> ******* **//

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
        $menu_api->set_stored_help_tab_values($data, $indices, $this->apis['ctrl']);
        return $id;
    }

    private function get_help_tab_content()
    {
        $html = <<<EOT
        <p>
            The Admin Database Version Control Manager subsystem provides a 
            consistent interface for interacting with any custom database 
            updates your code may require.
            <br>
            To access the Subsystem's API:
            <ul>
                <li><code>
                    execute_database_updates(dir, subsystem_label, root_name, version)
                    </code> : 
                    Attempts to execute updates. If Update needed returns 
                    success (TRUE or FALSE), otherwise NULL.
                </li>
                <li><code>
                    get_current_db_version(subsystem_label)
                    </code> : 
                    Returns stored database version, if exists. Otherwise, 
                    returns zero (0).
                </li>
            </ul>
        </p>
EOT;
        return __($html, DBLOX_TEXT_DOMAIN);
    }

}
