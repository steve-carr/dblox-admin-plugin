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
 * @package         Framework\Subsystems\AdminMenu\Api
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Subsystems\AdminMenu;

class Api
{
    //** ******* <!--/ ADMIN MENU SUBSYSTEM'S API /--> ******* **//

    protected $vars;

    protected $apis;

    private $ids;

    public static function get_instance($args=NULL)
    {
        return new static($args);
    }

    protected function __construct($args=NULL)
    {
        $this->vars['subsystem'] = $args['vars'];
        $this->apis = $args['apis'];
        //* enable for testing purposes only
        // add_filter('current_screen', [$this, 'return_current_screen'] );
    }

    //** ******* <!--/ SUBSYTEM'S EXTERNAL API /--> ******* **//

    public function get_value_for($label='all')
    {
        if('all' === $label){
            return $this->vars;
        }
        if(in_array($label, $this->vars)) {
            return $this->vars[$label];
        }
        if(array_key_exists($label, $this->vars) ){
            return $this->vars[$label];
        }
        if ('ids' === $label) { return $this->ids; }
        return NULL;
    }

    public function get_menu_id($ctrl, $title)
    {
        $cid = $this->generate_cid($ctrl);
        if (NULL !== $this->ids['menus'][$cid]) {
            $id = $this->ids['menus'][$cid];
        }else{
            //* BEWARE you are looking for menu_id before it is stored
            $args['title'] = $title;
            $args['cid'] = $cid;
            $args['type'] = 'menu';
            $id = $this->generate_menu_id($args);
        }
        return $id;
    }

    public function get_submenu_id($ctrl, $name)
    {
        $cid = $this->generate_cid($ctrl);
        $label = $this->apis['ctrl']->underscore_delimit($name);
        if (NULL !== $this->ids[$cid][$label]) {
            $id = $this->ids[$cid][$label];
        }else{
            //* BEWARE you are looking for submenu_id before it is stored
            $args['title'] = $label;
            $args['cid'] = $cid;
            $args['type'] = 'submenus';
            $id = $this->generate_menu_id($args);
        }
        return $id;
    }

    public function add_admin_submenu_page($slug, $pkg)
    {
        /** NOTE
         *  $pkg = [
         *    'ctrl' ,
         *    'title' ,
         *    'capability' ,
         *    'priority' => ,
         *    'callback' => [object, func],
         *    'help_tab' => [object, func],
         *    'help_sidebar' => [object, func]
         *  ];
         */
        $pkg['cid'] = $this->generate_cid($pkg['ctrl']);
        $pkg['pid'] = $slug;
        $pkg['menu_title'] = $pkg['title'];
        $pkg['id'] = $this->set_stored_submenu_values($pkg, $pkg['ctrl']);
        $pkg['tid'] = $this->add_help_tab($pkg);
        $this->add_help_sidebar($pkg);
    }

    public function add_new_submenu_page($pkg)
    {
        /** NOTE
         *  $pkg = [
         *    'ctrl' ,
         *    'title' ,
         *    'capability' ,
         *    'priority' => ,
         *    'callback' => [object, func],
         *    'help_tab' => [object, func],
         *    'help_sidebar' => [object, func]
         *  ];
         */
        $ids = $this->add_submenu_page($pkg);
        $args = array_merge($pkg, $ids);
        $args['menu_title'] = $args['title'];
        $args['tid'] = $this->add_help_tab($args);
        $this->add_help_sidebar($args);
    }

    private function add_submenu_page($args)
    {
        $cid = $this->generate_cid($args['ctrl']);
        $pid = $this->get_menu_id($args['ctrl'], $args['title']);
        $data = [
            'pid'        => $pid,
            'page_title' => $args['title'],
            'menu_title' => $args['title'],
            'capability' => $args['capability'],
            'callback'   => $args['callback'],
        ];
        $id = $this->set_stored_submenu_values($data, $args['ctrl']);
        return [
            'cid' => $cid,
            'pid' => $pid,
            'id'  => $id,
        ];
    }

    private function add_help_tab($args)
    {
        if (!method_exists($args['help_tab'][0], $args['help_tab'][1])) { return; }
        $data = [
            'title' => $args['menu_title'],
            'priority' => $args['priority'],
            'content' => call_user_func($args['help_tab']),
        ];
        $indices = [
            'type' => 'submenus',
            'pid' => $args['pid'],
            'id' => $args['id'],
        ];
        $tid = $this->set_stored_help_tab_values($data, $indices, $args['ctrl']);
        return $tid;
    }

    private function add_help_sidebar($args)
    {
        if (!method_exists($args['help_sidebar'][0], $args['help_sidebar'][1])) { return; }
        $data = [
            'id' => $args['tid'],
            'content' => call_user_func($args['help_sidebar']),
        ];
        $indices = [
            'ctrl' => $args['ctrl'],
            'type' => 'submenus',
            'pid' => $args['pid'],
            'id' => $args['id'],
        ];
        $this->set_stored_help_sidebar_values($data, $indices);
    }

    //** <!--/ set (in stored_values) - for integration /--> **//

    public function generate_cid($ctrl)
    {
        return $this->apis['ctrl']->generate_cid($ctrl);
    }

    private function generate_menu_id($args)
    {
        /** NOTE
         *  $args = [
         *      'title'    =>  {menu's) unique name
         *      'cid'      =>  controller's label
         *      'type'     => 'menu', 'subsubmenu' or 'help_tab'
         *  ]
         */
        switch ($args['type']) {
            case 'submenus':
                $cat = strtolower($args['title']) .'_sub_' . $args['cid']; 
                break;
            case 'menu':
                $cat = 'toplevel_menu_' . $args['cid']; 
                break;
            case 'help_tab':
            default:
                $cat = strtolower($args['title']) .'_help_' . $args['cid']; 
                break;
        }
        $id = $this->apis['ctrl']->underscore_delimit($cat);
        return esc_html($id); //* must be HTML-safe (no empty spaces)
    }

    public function set_stored_menu_values($data, $ctrl)
    {
        $cid = $this->generate_cid($ctrl);
        $args['title'] = $data['menu_title'];
        $args['cid'] = $cid;
        $args['type'] = 'menu';
        $id = $this->generate_menu_id($args);

        $details['id'] = $id;
        $details['page_title'] = $this->apis['ctrl']->cap_n_space($data['page_title']);
        $details['menu_title'] = $this->apis['ctrl']->cap_n_space($data['menu_title']);
        $details['capability'] = $data['capability'];
        $details['callback'] = $data['callback'];
        $details['icon'] = $data['icon'];
        $details['position'] = $data['position'];

        $this->apis['ctrl']->set_stored_values(
            $this->vars['subsystem']['label'], 
            [$cid, 'menus', $id],
            $details
        );
        $this->ids['menus'][$cid] = $id;
        return $id;
    }

    public function set_stored_submenu_values($data, $ctrl)
    {
        /** NOTE
         *  Capability parameter of add_submenu_page() function can only take a 
         *  single capability, so if you are using the built in roles you can 
         *  select a capability from any of these (use any of these freely):
         *      moderate_comments
         *      manage_categories
         *      manage_links
         *      unfiltered_html
         *      edit_others_posts
         *      edit_pages
         *      edit_others_pages
         *      edit_published_pages
         *      publish_pages
         *      delete_pages
         *      delete_others_pages
         *      delete_published_pages
         *      delete_others_posts
         *      delete_private_posts
         *      edit_private_posts
         *      read_private_posts
         *      delete_private_pages
         *      edit_private_pages
         *      read_private_pages
         * 
         *  REFERENCES
         *  https://wordpress.stackexchange.com/questions/13493/menu-capability-in-wordpress
         */
        $cid = $this->generate_cid($ctrl);
        $args['title'] = $data['menu_title'];
        $args['cid'] = $cid;
        $args['type'] = 'submenus';

        $details['id'] = $this->generate_menu_id($args);
        $details['pid'] = $data['pid'];
        $details['page_title'] = $this->apis['ctrl']->cap_n_space($data['page_title']);
        $details['menu_title'] = $this->apis['ctrl']->cap_n_space($data['menu_title']);
        $details['capability'] = $data['capability'];
        $details['callback'] = $data['callback'];

        $this->apis['ctrl']->set_stored_values(
            $this->vars['subsystem']['label'], 
            [$cid, 'submenus', $details['pid'], $details['id']], 
            $details
        );
        $label = $this->apis['ctrl']->underscore_delimit($details['menu_title']);
        $this->ids[$cid][$label] = $details['id'];
        return $details['id'];
    }

    public function set_stored_help_tab_values($data, $indices, $ctrl)
    {
        /** REFERENCES
         *  https://developer.wordpress.org/reference/classes/wp_screen/add_help_tab/
         *  https://codex.wordpress.org/Class_Reference/WP_Screen/add_help_tab
         */
        $cid = $this->generate_cid($ctrl);
        $args['title'] = $data['title'];
        $args['cid'] = $cid;
        $args['type'] = 'help_tab';
        
        $details['id'] = $this->generate_menu_id($args);
        $details['title'] = $this->apis['ctrl']->cap_n_space($data['title']);
        $details['priority'] = $data['priority'];
        $details['content'] = $data['content'];

        $this->apis['ctrl']->set_stored_values(
            $this->vars['subsystem']['label'], 
            [$cid, 'help_tabs', $indices['type'], $indices['pid'], $indices['id'], $details['id']], 
            $details
        );
        $label = $this->apis['ctrl']->underscore_delimit($details['title']);
        $this->vars['ids']['submenus'][$label] = $details['id'];
        return $details['id'];
    }

    public function set_stored_help_sidebar_values($data, $indices)
    {
        /** REFERENCES
         *  https://codex.wordpress.org/Class_Reference/WP_Screen/set_help_sidebar
         */
        $details['id'] = $data['id'];
        $details['content'] = $data['content'];

        $cid = $this->generate_cid($indices['ctrl']);
        $this->apis['ctrl']->set_stored_values(
            $this->vars['subsystem']['label'], 
            [$cid, 'help_sidebars', $indices['type'], $indices['pid'], $indices['id']], 
            $details
        );
    }

    //** <!--/ delete (via WordPress) -  for customiztion /--> **//
    
    public function remove_menu($ctrl, $id)
    {
        //* WordPress call
        remove_menu_page($id);
        //* clean out stored memory
        $cid = $this->generate_cid($ctrl);
        $this->remove_stored_values([$cid]);
    }

    public function remove_submenu($ctrl, $pid, $id)
    {
        //* WordPress call
        remove_submenu_page($pid, $id);
        //* clean out stored memory
        $cid = $this->generate_cid($ctrl);
        $types = ['submenus', 'help_tabs', 'help_sidebars', 'hook_suffix'];
        foreach ($types as $type) {
            if ('submenus' === $type) {
               $this->remove_stored_values([$cid, $type, $pid, $id]);
            }else{
               $this->remove_stored_values([$cid, $type, $pid]);
            }
        }
    }
    
    public function remove_help_tab($ctrl, $pid)
    {
        $cid = $this->generate_cid($ctrl);
        $types = ['help_tabs', 'help_sidebars'];
        foreach ($types as $type) {
            $this->remove_stored_values([$cid, $type, $pid]);
        }
    }
    
    public function remove_help_sidebar($ctrl, $pid)
    {
        $cid = $this->generate_cid($ctrl);
        $this->remove_stored_values([$cid, 'help_sidebars', $pid]);
    }

    private function remove_stored_values($indices)
    {
        $data = $this->apis['ctrl']->get_stored_values(
            $this->vars['subsystem']['label'], 
            $indices, 
            NULL
        );
        if (NULL === $data) { return; }
        $this->apis['ctrl']->unset_stored_values(
            $this->vars['subsystem']['label'], 
            $indices, 
            NULL
        );
    }

    //** <!--/ get (from stored_values) - for customiztion /--> **//

    public function get_menu_stored_values($ctrl, $pid=NULL)
    {
        $cid = $this->generate_cid($ctrl);
        $indices = [$cid, 'menus'];
        if (NULL !== $pid) { $indices[]= $pid; }
        //* get data for controller's menu & submenus
        $data = $this->apis['ctrl']->get_stored_values(
            $this->vars['subsystem']['label'], 
            $indices, 
            NULL
        );
        return $data;
    }

    public function get_submenu_stored_values($ctrl, $pid=NULL, $id=NULL)
    {
        $cid = $this->generate_cid($ctrl);
        $indices = [$cid, 'submenus'];
        if (NULL !== $pid) { $indices[]= $pid; }
        if (NULL !== $id) { $indices[]= $id; }
        //* get data for controller's menu & submenus
        $data = $this->apis['ctrl']->get_stored_values(
            $this->vars['subsystem']['label'], 
            $indices, 
            NULL
        );
        return $data;
    }

    public function get_help_tab_stored_values($ctrl, $type=NULL, $pid=NULL, $id=NULL)
    {
        $cid = $this->generate_cid($ctrl);
        $indices = [$cid, 'help_tabs'];
        if (NULL !== $type) { $indices[]= $type; }
        if (NULL !== $pid) { $indices[]= $pid; }
        if (NULL !== $id) { $indices[]= $id; }
        $data = $this->apis['ctrl']->get_stored_values(
            $this->vars['subsystem']['label'], 
            $indices, 
            NULL
        );
        return $data;
    }

    public function get_tab_sidebar_stored_values($ctrl, $type=NULL, $pid=NULL)
    {
        $cid = $this->generate_cid($ctrl);
        $indices = [$cid, 'help_sidebars'];
        if (NULL !== $type) { $indices[]= $type; }
        if (NULL !== $pid) { $indices[]= $pid; }
        $data = $this->apis['ctrl']->get_stored_values(
            $this->vars['subsystem']['label'], 
            $indices, 
            NULL
        );
        return $data;
    }

    public function get_hook_suffix_stored_values($ctrl, $type=NULL, $pid=NULL, $id=NULL)
    {
        /** NOTE
         *  This method exists to "help" in the case where you want to 
         *  include a link to an admin menu or submenu somewhere (menubar).
         */
        $cid = $this->generate_cid($ctrl);
        $indices = [$cid, 'hook_suffix'];
        if (NULL !== $type) { $indices[]= $type; }
        if (NULL !== $pid) { $indices[]= $pid; }
        if (NULL !== $id) { $indices[]= $id; }
        $data = $this->apis['ctrl']->get_stored_values(
            $this->vars['subsystem']['label'], 
            $indices, 
            NULL
        );
        return $data;
    }
   
}
