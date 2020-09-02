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
 * @package         Framework\Subsystems\AdminMenu\Logic
 * @version         1.0.0
 * @since           1.0.0
 */
/** REFERENCE
 *  -- Menu Page --
 *  https://developer.wordpress.org/reference/functions/add_menu_page/
 *  -- Submenu Page --
 *  https://developer.wordpress.org/reference/functions/add_submenu_page/
 *  https://kylebenk.com/change-title-first-submenu-page/
 *  http://tekina.info/add-menus-submenus-wordpress-admin-panel/
 *  -- Help Tab --
 *  https://codex.wordpress.org/Class_Reference/WP_Screen/add_help_tab
 *  https://developer.wordpress.org/reference/classes/WP_Screen/add_help_tab/
 *  -- Help Sidebar --
 *  https://developer.wordpress.org/reference/classes/wp_screen/set_help_sidebar/
 *  -- Screen Options --
 *  https://pippinsplugins.com/add-screen-options-tab-to-your-wordpress-plugin/
 *  https://chrismarslender.com/2012/01/26/wordpress-screen-options-tutorial/
 *  -- Misc --
 *  https://codex.wordpress.org/Function_Reference/get_current_screen
 * 
 * PAGE               $SCREEN_ID           FILE
 * -----------------  -------------------  -----------------------
 * Media Library      upload               upload.php
 * Comments           edit-comments        edit-comments.php
 * Tags               edit-post_tag        edit-tags.php
 * Plugins            plugins              plugins.php
 * Links              link-manager         link-manager.php
 * Users              users                users.php
 * Posts              edit-post            edit.php
 * Pages              edit-page            edit.php
 * Edit Site: Themes  site-themes-network  network/site-themes.php
 * Themes             themes-network       network/themes
 * Users              users-network        network/users
 * Edit Site: Users   site-users-network   network/site-users
 * Sites              sites-network        network/sites
 */
   
namespace Dblox\Subsystems\AdminMenu;

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
        //* insert this subsystem's logic into the controller's hooks
        $this->apis['ctrl']->insert_into_controller_hooks(
            $this, 
            $this->vars['subsystem']['priority'],
            0
        );
        //* hook into this subsystem's initialzation method
        $this->apis['ctrl']->add_to_action_hook(
            $this->vars['labels']['initialization'], 
            [$this, 'store_help_tab_data'],
            $this->vars['subsystem']['priority'],
            1
        );
        //* hook directly into WordPress' admin_menu hook
        $this->apis['ctrl']->add_to_action_hook(
            'admin_menu', 
            [$this, 'menu_setup'],
            $this->vars['subsystem']['priority'],
            0
        );
        //* triggered after the necessary elements to identify a screen are set up. 
        $this->apis['ctrl']->add_to_action_hook(
            'current_screen', 
            [$this, 'render_help_tabs'], 
            $this->vars['subsystem']['priority'],
            0
        );
    }

    //** ******* <!--/ SUBSYTEM'S HOOKS => Controller\Logic:<foo> /--> ******* **//

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

    //** ******* <!--/ CALLBACK METHODS - Logic::hookups /--> ******* **//

    //** <!--/ callback - admin_menu::initialization /--> **//

    public function store_help_tab_data($menu_api)
    {
        //* add "admin menu" help tab to controller's data visulaization submenu
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
        $this->apis['subsystem']->set_stored_help_tab_values($data, $indices, $this->apis['ctrl']);
        return $id;
    }

    private function get_help_tab_content()
    {
        $html = <<<EOT
        <p>
            The Admin Menu Manager subsystem provides a consistent interface 
            for interacting with admin menus, submenus, help tabs and help sidebars.
            While the following holds true for each of the areas described above
            "submenu" wil be used to explain how to interact with this subsystem.
            <br>
            First call is to <code>set_stored_submenu_values</code>. This stores 
            the data related to your submenu into shared memory. You want to do that
            during the <code>setup</code> phase (after initialization and before 
            activation). Unless you are doing something special, that is all you 
            need to do. This subsystem takes care of the rest.
            <br>
            Among the parameters stored in memory for one or more of these 
            functions is <code>callback</code> which is a function you will need
            to write. Consult the WoredPress references for details.
            <br>
            To access the Subsystem's API:
            <ul>
                <li><code>
                    set_stored_menu_values(data, ctrl_api)
                    </code> : 
                    Stores new menu data and returns it's unique identifier (pid).<br>
                    data = [id, page_title, title, capability, callback, icon, position]
                </li>
                <li><code>
                    set_stored_submenu_values(data, ctrl_api)
                    </code> : 
                    Stores new submenu data and returns it's unique identifier (id).<br>
                    data = [pid, page_title, title, capability, id, callback]
                </li>
                <li><code>
                    set_stored_help_tab_values(data, indices)
                    </code> : 
                    Stores new help tab data and returns it's unique identifier (tid).<br>
                    data = [tid, title, content]<br>
                    indices = [ctrl_api, pid, (optional)id]
                </li>
                <li><code>
                    set_stored_help_sidebar_values(data, indices)
                    </code> : 
                    Stores new help sidebar data and returns it's unique identifier.<br>
                    data = [tid, content]<br>
                    indices = [ctrl_api, pid, (optional)id]
                </li>
            </ul>
            Available Subsystem API for customizatiion:
            <ul>
                <li><code>
                    delete_menu(ctrl_api, pid)
                    </code> : 
                    Removes designated menu from WordPress as well as shared memory.
                    It will also remove assocaited help tab or sidbar.
                </li>
                <li><code>
                    delete_submenu(ctrl_api, pid, id)
                    </code> : 
                    Removes designated submenu from WordPress as well as shared memory.
                    It will also remove assocaited help tab or sidbar.
                </li>
            </ul>
        </p>
EOT;
        return __($html, DBLOX_TEXT_DOMAIN);
    }

 
    //** <!--/ callback - Logic::hookups => WP's admin_menu hook /--> **//

    public function menu_setup()
    {
        //* if you want to add submenu page(s) into memory BEFORE rendering
        $this->insert_submenu_page();
        $controllers = $this->apis['ctrl']->get_list_of_all_controller_classes();
        foreach ($controllers as $ctrl_class) {
            $ctrl_api = $ctrl_class::get_instance();
            $cid = $this->apis['subsystem']->generate_cid($ctrl_api);
            $this->add_menus($cid);
            $this->add_submenus($cid);
        }
    }

    private function insert_submenu_page()
    {
        /** NOTE
         *  Use this hook if you want to hook into WP's admin_menu hook and
         *  your logic MUST execute BEFORE $this->menu_setup executes.
         */
        $label = $this->apis['ctrl']->get_subsystem_function_label(
            'admin_menu', __FUNCTION__, 'action'
        );
        $this->apis['ctrl']->do_action_hook(
            $label, [$this, __FUNCTION__], 
            $this->apis['subsystem'], 0
        );
    }

    private function add_menus($cid)
    {
        /** REFERENCE
         *  https://developer.wordpress.org/reference/functions/add_menu_page/
         */
        $menus = $this->get_data($cid, ['menus']);
        if (!is_array($menus)) { return; }
        foreach ($menus as $pid => $menu) {
            $hook_suffix[$pid] = add_menu_page(
                $menu['page_title'],
                $menu['menu_title'],
                $menu['capability'],
                $menu['id'],
                $menu['callback'],
                $menu['icon'],
                $menu['position']
            );
        }
        if (!is_array($hook_suffix)) { return NULL; }
        $this->apis['ctrl']->set_stored_values(
            $this->vars['subsystem']['label'], 
            [$cid, 'hook_suffix','menus'], 
            $hook_suffix
        );
        return $hook_suffix;
    }

    private function add_submenus($cid)
    {
        $data = $this->get_data($cid, ['submenus']);
        if (!is_array($data)) { return; }
        foreach ($data as $pid => $submenus) {
            foreach ($submenus as $id => $submenu) {
                /** NOTE (id trickery)
                 *  Forcing the submenu's "id" equat to the menu's "pid" 
                 *  removes the duplicate "MENU NAME" submenu item. 
                 *  This makes selecting the menu item and the 1st submenu item 
                 *  result in the same page being rendered.
                 * 
                 *  http://tekina.info/add-menus-submenus-wordpress-admin-panel/
                 */
                if('Data Visualization' === $submenu['menu_title']){
                    $submenu['id'] = $submenu['pid'];
                }
                $hook_suffix[$pid][$id] = add_submenu_page(
                    $submenu['pid'],
                    $submenu['page_title'],
                    $submenu['menu_title'],
                    $submenu['capability'],
                    $submenu['id'],
                    $submenu['callback']
                );
            }
        }
        if (!is_array($hook_suffix)) { return NULL; }
        $this->apis['ctrl']->set_stored_values(
            $this->vars['subsystem']['label'], 
            [$cid, 'hook_suffix','submenus'], 
            $hook_suffix
        );
        return $hook_suffix;
    }

    //** <!--/ callback - Logic::hookups => WP's current_screen hook /--> **//
    
    public function render_help_tabs()
    {
        //* if you want to add new help tab(s) BEFORE rendering
        $this->insert_help_tab_data();
        $controllers = $this->apis['ctrl']->get_list_of_all_controller_classes();
        foreach ($controllers as $ctrl_class) {
            $ctrl_api = $ctrl_class::get_instance();
            $cid = $this->apis['ctrl']->generate_cid($ctrl_api);
            $data = $this->get_data($cid, ['help_tabs']);
            if (!is_array($data)) { continue; }
            foreach ($data as $type => $types) {
                foreach ($types as $pid => $pages) {
                    foreach ($pages as $page_id => $tabs) {
                        $args = [
                            'cid' => $cid,
                            'type' => $type, // 'menu' or 'submenus'
                            'pid' => $pid,
                            'id' => $page_id,
                        ];
                        $wp_screen_api = $this->get_screen_api($args);
                        $this->add_help_sidebar($args, $wp_screen_api);
                        foreach ($tabs as $tab) {
                            $this->add_help_tab($tab, $wp_screen_api);
                        }
                    }
                }
            }
        }
    }

    private function insert_help_tab_data()
    {
        /** NOTE
         *  Use this hook if you want to hook into WP's current_screen hook and
         *  your logic MUST execute BEFORE $this->render_help_tabs executes.
         */
        $label = $this->apis['ctrl']->get_subsystem_function_label(
            'admin_menu', __FUNCTION__, 'action'
        );
        $this->apis['ctrl']->do_action_hook(
            $label, 
            [$this, __FUNCTION__], 
            $this->apis['subsystem']
        );
    }

    private function get_data($cid, $args)
    {
        foreach ($args as $sub) {
            $data[$sub] = $this->apis['ctrl']->get_stored_values(
                $this->vars['subsystem']['label'], 
                [$cid, $sub],
                NULL
            );
        }
        if (count($args) < 2) {
            return $data[$sub];
        }else{
            return $data;
        }
    }

    private function get_screen_api($args)
    {
        $params = [
            $args['cid'], 
            'hook_suffix', 
            $args['type'], 
            $args['pid'], 
            $args['id']
        ];
        //$screen  = 'load-'; //"load-{$GLOBALS['pagenow']}"
        $screen .= $this->apis['ctrl']->get_stored_values(
            $this->vars['subsystem']['label'], 
            $params,
            NULL
        );
        $wp_screen_api = \WP_Screen::get($screen);
        return $wp_screen_api;
    }

    private function add_help_tab($help_tab, $wp_screen_api)
    {
        if (!is_object($wp_screen_api)) { return; }
        if('Data Visualization' === $help_tab['title']){
            $help_tab['title'] = 'Controller';
        }
        $args = [
            'id'       => $help_tab['id'],
            'title'    => $help_tab['title'],
            'content'  => $help_tab['content'],
            'callback' => NULL,
            'priority' => $help_tab['priority'],
        ];
        $wp_screen_api->add_help_tab($args);
    }

    private function add_help_sidebar($args, $wp_screen_api)
    {
        $bars = $this->get_data($args['cid'], ['help_sidebars']);
        //$content = $bars[$args['type']][$args['pid']][$args['id']]['content'];
        $type    = $bars[$args['type']];
        $pid     = $type[$args['pid']];
        $id      = $pid[$args['id']];
        $content = $id['content'];
        if ((!is_object($wp_screen_api)) ||
            (NULL === $content) || 
            ('' === $content)) { return; }
        $wp_screen_api->set_help_sidebar($content);
    }

}
