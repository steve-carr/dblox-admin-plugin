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
 * @vars         Framework\Subsystems\AdminMenubar\Logic
 * @version         1.0.0
 * @since           1.0.0
 */
/** NOTES
 * Add a "node" to Admin menubar
 * 
 * WordPress refers to menubar items as "nodes".
 * 
 * render_node takes four arguments. Only the first is required. To create
 * a top-level node, call render_node with a single argument, the 
 * title you want to show. You can also pass an icon alternative, if you 
 * want to use something other than the icon you've associated with the 
 * Plugin Controller. With that call to render_node, you've defined your 
 * top-level,or parent, node.
 * 
 * To create subordinate node (appearing under the top-level node), you 
 * continue to make calls to render_node. But, now you start adding more
 * information. The first argument is the name you want assoicated with this
 * sub-node. The second argument is an (optional) icon to link with this 
 * node. Use this with caution. Pass a second argument of, 'none' to 
 * indicate you don't want an icon. The third argument, (href) is telling
 * the node what to do if the user selects that node. And finally, the 
 * fourth node indicates the top-level (parent) node to place this sub-node 
 * under. Simple include the "name" field you used to create the top-level
 * node.
 * 
 * I know this sounds complicated. I tried to simplify the actual interface 
 * as best I could while still providing dev control. This may be a case 
 * where you simply use portions of my logic and "role your own" menubar
 * interface.
 * 
 * Note: While these nodes will show on larger screens, they disappear when
 * the screen size is reduced (mobile size). This appears to be a 
 * WordPress intended side-effect.
 * 
 * Please give me a shout out if you figure out a way around this 
 * limitation.
 * 
 * 
 * NOTE: This is a placeholder showing an UNPROVEN bug in WordPress
 * 
 * ISSUES when including "icons" in submenus
 * 1. The name (1st argument) cannot contain spaces.
 * 2. Unexpected indentation takes place.
 * 
 * Suspect this (#2) is a CSS issue.
 * 
 */

namespace Dblox\Subsystems\AdminMenubar;

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

     private function hook_ups()
     {
        if (!is_admin()) { return; }
        //* insert this subsystem's logic into the controller's hooks
        $this->apis['ctrl']->insert_into_controller_hooks(
            $this, 
            $this->vars['subsystem']['priority'],
            0
        );
        //* hook the "do" logic into WordPress
        $this->apis['ctrl']->add_to_action_hook(
            'wp_before_admin_bar_render', 
            [$this, 'menubar_activation'], 
            $this->var['priority'], 
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

    //** ******* <!--/ SUBSYTEM'S LOGIC /--> ******* **//

    //** <!--/ initialization - methods /--> **//

    public function initialization()
    {
        /** NOTE
         *  This hook is an ideal place to "hook" in with any menubar creation 
         *  logic.
         */
        $this->apis['ctrl']->do_action_hook(
            $this->vars['labels'][__FUNCTION__], 
            [$this, __FUNCTION__], 
            $this->apis['subsystem']
        );
    }

    //** <!--/ setup - supporting functions /--> **//

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
            The Admin Menubar Manager subsystem provides a consistent interface for interacting with admin menubar.
            To access the Subsystem's API:
            <ul>
                <li><code>
                    get_menubar_id_for(ctrl)
                    </code> : 
                    Given the Controller's function, namespace|class or object, 
                    return the assoicated top menubar's ID (pid).
                </li>
                <li><code>
                    id = set_stored_menubar_values(data, ctrl)
                    </code> : 
                    Stores new menubar data and returns it's unique identifier (id).
                    <br>
                    data = [pid, icon, title, href, meta]<br>
                    <br>
                    Since this is the "top" menubar, parent id (pid) = ''. The 
                    functtion returns the menubars id.
                </li>
                <li><code>
                    id = set_stored_sub_menubar_values(data, ctrl)
                    </code> : 
                    Stores new menubar data and returns it's unique identifier (id).
                    <br>
                    data = [pid, icon, title, href, meta] 
                    <br>
                    The functtion returns the menubars id.
                </li>
                <li><code>
                    remove_menubar(id)
                    </code> : 
                    Given an ID, the assoicated menubar item is removed.
                </li>
                <li><code>
                    get_menubars_stored_values(ctrl, id=NULL, pid=NULL)
                    </code> : 
                    Given an ID and PID (if sub-menubar), return the assoicated 
                    menubar detail.
                </li>
            </ul>
        </p>
EOT;
        return __($html, DBLOX_TEXT_DOMAIN);
    }

    //** <!--/ menubars callback - methods /--> **//
 
    public function menubar_activation()
    {
        if (!is_admin_bar_showing()) { return; }
        if (!current_user_can($this->vars['subsystem']['capability'])) { return; }
        $list = $this->apis['ctrl']->get_list_of_all_controller_classes();
        foreach ($list as $ctrl_class) {
            $ctrl_api = $ctrl_class::get_instance();
            $menubars = $this->apis['subsystem']->get_menubars_stored_values($ctrl_api);
            if (is_array($menubars)) {
                $this->show_menubars($menubars);
            }
        }
    }

    private function show_menubars($menubars)
    {
        global $wp_admin_bar;
        
        foreach ($menubars as $menubar) {
            $icon_html = $this->get_icon_html($menubar['icon']);
            $name_html = $this->get_name_html($menubar['title']);
            $meta = $this->build_meta($menubar['title'], $menubar['meta']);
            // Insert into WordPress
            $wp_admin_bar->add_node( 
                [
                    'id' => $menubar['id'],
                    'title' => $icon_html . $name_html,
                    'parent' => $menubar['pid'],
                    'href' => esc_html($menubar['href']),
                    'group' => NULL,
                    'meta' => $meta,
                ]
            );
        }
    }

    private function get_icon_html($icon)
    {
        if (('none' === $icon) || ('' === $icon)) {
            $html = '';
        }else{
            $html = <<<EOT
            <span class="ab-icon dashicons {$icon}"></span>
EOT;
        }
        return $html;
    }
    
    private function get_name_html($name)
    {
        $html = <<<EOT
        <span class="ab-label">{$name}</span>
EOT;
        return $html;
    }

    private function build_meta($title, $custom_meta)
    {
        if (!is_array($custom_meta)) { return; }
        $meta_a = [
            'title' => $title,
            'tabindex' => PHP_INT_MAX,
        ];
        $meta = array_merge( $meta_a, $custom_meta );
        return $meta;
    }

}
