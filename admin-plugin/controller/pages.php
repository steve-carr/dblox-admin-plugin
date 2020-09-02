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
 * @package         Framework\Controller\Pages
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller;

class Pages
{
    //** ******* <!--/ BUILD ASSOCIATED ADMIN MENU PAGES /--> ******* **//

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
        if (!is_admin()) { return; }
        $this->initialization($args);
        //* hook into the admin_menu subsystem's initialization method
        $label = $this->apis['ctrl']->get_subsystem_function_label(
            'admin_menu', 'initialization', 'action'
        );
        $this->apis['ctrl']->add_to_action_hook(
            $label, [$this, 'build_admin_menu_pages'],
            $this->vars['priority'], 1
        );
    }

    public function initialization($args)
    {
        /** IMPORTANT - list order
         *  It is important the "data-visualization" be first in the list. 
         *  There is logic in admin-menu-api.php which assumes that is the case.
         */
        $list = [
            'data_visualization' => ['Data Visualization' => 'DataVisualization'],
            'hook_list'          => ['Hook List'          => 'HookList'],
            'stored_values'      => ['Stored Values'      => 'StoredValues'],
        ];
        foreach ($list as $label => $arr ) {
            foreach ($arr as $title => $classname ) {
                $class = __NAMESPACE__ . "\\" . 'Pages' . "\\" . $classname;
                $this->apis['pages'][$label] = $class::get_instance($args);
                $this->vars['pages'][$label] = $title;
            }
        }
    }

    public function build_admin_menu_pages($menu_api)
    {
        if (!is_admin()) { return; }
        //* add top menu
        $this->vars['pid'] = $this->build_menu_page($menu_api);
        foreach ($this->vars['pages'] as $label => $title) {
            $args = [
                'menu_api' => $menu_api,
                'page_api' => $this->apis['pages'][$label],
                'pid' => $this->vars['pid'],
                'page_title' => $title,
                'menu_title' => $title,
            ];
            //* add submenu
            $args['id'] = $this->vars['ids'][$label] = $this->build_submenu_page($args);
            //* add submenu's help tab
            $args['tid'] = $this->build_help_tab($args);
            //* add submenu's help sidebar
            $this->build_help_sidebar($args);
        }
    }

    protected function build_menu_page($menu_api)
    {
        $data = [
            'page_title' => $this->vars['title'],
            'menu_title' => $this->vars['title'],
            'capability' => $this->vars['capability'],
            'callback' => [
                $this->apis['pages']['data_visualization'], 
                'render_page'
            ],
            'icon' => $this->vars['icon'],
            'position' => $this->vars['position'],
        ];
        return $menu_api->set_stored_menu_values($data, $this->apis['ctrl']);
    }

    protected function build_submenu_page($args)
    {
        $data = [
            'pid'        => $args['pid'],
            'page_title' => $args['page_title'],
            'menu_title' => $args['menu_title'],
            'capability' => $this->vars['capability'],
            'callback'   => [$args['page_api'], 'render_page'],
        ];
        $id = $args['menu_api']->set_stored_submenu_values(
            $data, $this->apis['ctrl']
        );
        return $id;
    }

    protected function build_help_tab($args, $type='submenus')
    {
        if (!method_exists($args['page_api'], 'get_help_tab_content')) { return NULL; }
        $typed = ('submenus' === $type) ? $type : "menu";
        $data = [
            'title' => $args['menu_title'],
            'priority' => $this->vars['priority'],
            'content' => $args['page_api']->get_help_tab_content(),
        ];
        $indices = [
            'ctrl' => $this->apis['ctrl'],
            'type' => $typed,
            'pid' => $args['pid'],
            'id' => $args['id'],
        ];
        $tid = $args['menu_api']->set_stored_help_tab_values(
            $data, $indices, $this->apis['ctrl']
        );
        return $tid;
    }

    protected function build_help_sidebar($args, $type='submenus')
    {
        if (!method_exists($args['page_api'], 'get_help_sidebar_content')) {
            return;
        }
        $typed = ('submenus' === $type) ? $type : "menu";
        $data = [
            'id' => $args['id'],
            'content' => $args['page_api']->get_help_sidebar_content(),
        ];
        $indices = [
            'ctrl' => $this->apis['ctrl'],
            'type' => $typed,
            'pid' => $args['pid'],
            'id' => $args['id'],
        ];
        $args['menu_api']->set_stored_help_sidebar_values($data, $indices);
    }

}
