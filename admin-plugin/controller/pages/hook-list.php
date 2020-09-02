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
 * @package         Framework\Controller\Pages\HookList
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Pages;

class HookList
{
    //** ******* <!--/ HOOK LIST PAGE /--> ******** **//

    protected $vars;

    protected $apis;

    private $more_detail;

    public static function get_instance($args=NULL)
    {
        return new static($args);
    }

    protected function __construct($args=NULL)
    {
        $this->vars = $args['vars'];
        $this->apis = $args['apis'];
    }

    //** ******* <!--/ EXTERNAL API /--> ******* **//

    public function get_help_tab_content()
    {
        $html .= <<<EOT
        <p>
            A Controller leverages the WordPress use of hooks to all various 
            subsystems to manipulate the data or logic in other subsystems. By 
            utlizing the functions below. On this page, the menu manager 
            subsystem displays all of the hooks associated with this 
            controller.
            <br>
            For the insertion, execution and tracking of Hooks
            <ul>
                <li>
                    <code>add_to_action_hook(label, callback, priority=10, args=0)</code> : 
                    Performs the WordPress add_action call. But also registers 
                    the hook with the controller. Label is the string value 
                    used to uniquely identify the hook. Callback is of mixed 
                    type used to identify the callback function or class/method. 
                    Priority helps to determine the order the callback is 
                    executed in. And, args is the numer of arguments callback 
                    requires.
                </li>
                <li>
                    <code>add_to_filter_hook(label, callback, priority=10, args=1)</code> : 
                    Performs the WordPress add_filter call. But also registers 
                    the hook with the controller. See add_to_action_hook for 
                    details about passed parameters.
                </li>
                <li>
                    <code>do_action_hook(label, caller, ...args)</code> : 
                    Performs the WordPress do_action call. But also registers 
                    the hook with the controller. Caller is the callback used 
                    in add_to_action_hook. It is relevent to hook registration
                </li>
                <li>
                    <code>do_filter_hook(label, caller, ...args)</code> : 
                    Performs the WordPress apply_filters call. But also registers 
                    the hook (see register_hook). Caller is the callback used 
                    in add_to_action_hook. It is relevent to hook registration
                    registers the hook (see register_hook).
                </li>
                <li>
                    <code>remove_from_action_hook(label, callback)</code> : 
                    Performs the WordPress remove_action call. But also 
                    un-registers the hook (see unregister_hook).
                </li>
                <li>
                    <code>remove_from_filter_hook(label, callback)</code> : 
                    Performs the WordPress remove_filter call. But also 
                    un-registers the hook (see unregister_hook).
                </li>
            </ul>
        </p>
        <p>
            If you follow the "Hooks" and "Logic" class structure...
            Each instance of the Controller has these Hooks into WordPress : 
            <br><br>
            <strong>'initialization'</strong> :
            This is where you would "include" any resources unique 
            to your Subsystem. If your Subsystem is defined as a 
            class, you would generally instantiate your classes 
            here. Especially your priamary interface (api) class.
            <br><br>
            <strong>'setup'</strong> : 
            This is where you would establish relationships with 
            other Subsystems which may be needed. It is also where
            all other pre-activation "setup" takes place.
            <br><br>
            <strong>'activation'</strong> : 
            This is where you typically "hook into" a specific part
            of the WordPress ecosystem related to the purpose of 
            your Subsystem. Typically, this is done through an 
            "add_action" of "add_filter" hook. If your Controller 
            is a plugin, these activities will be triggered when 
            the plugin is activated.
            <br><br>
            <strong>'deactivation'</strong> : 
            Simply used to "unwind" what you did in activation. Typically, this 
            is done with "remove_action" or "remove_filter" hooks.
            <br><br>
            <strong>'uninstall'</strong> : 
            This is a rarely used hook and exists for the unusual 
            case of a Subsystem wanting to take extraordinary action
            when a plugin Controller is deleted.
            <br><br>
            <strong>'visualization'</strong> : 
            This is part of the data visualization system built intothe 
            proivded Controllers and Subsystems. It facilaites a way to display 
            data related to administering the various Controllers and Subsystems.
        </p>
EOT;
        return __($html, DBLOX_TEXT_DOMAIN);
    }

    public function get_help_sidebar_content()
    {
        $xlate['top'] = __('References for', DBLOX_TEXT_DOMAIN);
        $xlate['ref'] = __('Defining and calling hooks', DBLOX_TEXT_DOMAIN);
        $data['ref'] = [
            'WP Hooks' => 
                'https://developer.wordpress.org/plugins/hooks/',
        ];
        $html  = '<p>' . $xlate['top'] . '...</p>';
        $html .= '<p>' . $xlate['ref'] . ':';
        foreach ($data['ref'] as $txt => $href) {
            $html .= '<br><a href=';
            $html .= $href . '><strong>' . $txt . '</strong>';
            $html .= '</a>';
        }
        $html .= '</p>';
        return __($html, DBLOX_TEXT_DOMAIN);
    }

    //** <!--/ CALLBACK METHODS /--> **//

    public function render_page()
    {
        if (!is_admin()) { return; }
        $this->apis['admin_menu'] = $this->apis['ctrl']->get_registered_subsystem('admin_menu');
        if (!is_object($this->apis['admin_menu'])) { return; }
        $more = 'Show All Hooks';
        $less = 'Just Do Hooks';
        $this->check_inputs();
        $page = $this->define_head($more, $less);
        $page .= '<div id="infoBody">';
        $page .= $this->fill_page();
        $page .= '<br>The End</div>';
        echo $page;
    }

    private function check_inputs()
    {
        //* $_GET['detail']
        $value = filter_input(INPUT_GET, 'detail', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if(NULL !== $value) { $this->more_detail = ($value) ? TRUE : FALSE; }
    }

    private function define_head($more, $less)
    {
        $title = 'Hook List';
        $id = $this->get_page_id($title);
        $args = [
            'icon' => $this->vars['icon'],
            'url' => '',
            'title' => $title,
            'label' => ($args['detail']) ? $less : $more,
            'page' => $id,
            'detail' => $this->more_detail,
        ];
        //* Build CSS for page styling
        $style = $this->define_style($id);
        //* Build javascript to control infoBtn
        $script = $this->define_script($more, $less);
        //* Build page form
        $form = $this->define_form($args);
        //* Build page heading
        $head = '<div id="infoHead">'
            . $style . $script . $form
            . '<p id="test"></p>'
            . '</div>';
        return $head;
    }

    private function get_page_id($name)
    {
        $page_id = $this->apis['admin_menu']->get_submenu_id($this->apis['ctrl'], $name);
        return $page_id;
    }

    private function define_style($id)
    {
        return $this->apis['ctrl']->full_admin_page_style(
           $id
        );
    }

    private function define_script($more, $less)
    {
        return $this->apis['ctrl']->admin_page_button_script(
            $more, $less
        );
    }

    private function define_form($args)
    {
        return $this->apis['ctrl']->admin_page_button_form(
           $args
        );
    }

    private function fill_page($html='')
    {
        $hook_list = $this->apis['ctrl']->get_value_for('hooks');
        $indent = '&nbsp&nbsp&nbsp';
        $wpfunc_flag = '';
        foreach ($hook_list as $wpfunc => $wpfuncs) {
            $label_flag = '';
            foreach ($wpfuncs as $label => $callers) {
                foreach ($callers as $caller) {
                    if ( ($this->more_detail === FALSE) 
                        && (($wpfunc === 'add_action') || ($wpfunc === 'add_filter') )
                    ) {
                        // Skip the display of 'add_action' & 'add_filter' hooks
                    }else{
                        // Always display 'do_action' & 'apply_filters' hooks
                        if ($wpfunc !== $wpfunc_flag) {
                            $wpfunc_flag = $wpfunc;
                            $xlate = __('Hooks Calling WordPress' ,DBLOX_TEXT_DOMAIN);
                            $html .= '<h1> ' . $xlate . ' '.$wpfunc.'()</h1>';
                        }
                        if ($label !== $label_flag) {
                            $label_flag = $label;
                            $html .= '<h2>'.$label.'</h2>';
                            if (($wpfunc === 'add_action') || ($wpfunc === 'add_filter')) {
                                $xlate = __('Logic added to hook by' ,DBLOX_TEXT_DOMAIN);
                                $html .= $indent . $xlate . ':<br>';
                            }else{
                                $xlate = __('Hook executed by' ,DBLOX_TEXT_DOMAIN);
                                $html .= $indent . $xlate . ':<br>';
                            }
                        }
                        $html .= $indent.$caller['class'].'->'.$caller['func'].'<br>';
                    }
                }
            }
        }
        return $html;
    }
 
    private function sort_hook_list($hook_list)
    {
        // $this->hooks[$wpfunc][$label][$max] = $caller;
        foreach ($hook_list as $wpfunc => $hooks) {
            foreach ($hooks as $label => $hook) {
                $target_list['key'] = $wpfunc."_".$label;
                $target_list['detail'][$wpfunc][$label] = $hook;
            }
        }
        /*
         *  my_callback($element){
         *      return $element['wp']['weight'];
         *  }
         * 
         *  $map = array_map(my_callback($element), $array);
         * 
         *  array_multisort($map,  SORT_ASC, $array);
         * 
         * https://www.codepunker.com/blog/3-solutions-for-multidimensional-array-sorting-by-child-keys-or-values-in-PHP
         * 
         * $this->hooks[$wpfunc][$label][$max] = $caller;
         * 
         * 
        array_multisort(array_map(function($element) {
            return $element['pfunc']['label'];
        }, $hook_list), SORT_ASC, $hook_list);
         */
        // http://php.net/manual/en/function.array-multisort.php
        /*
        array_multisort(
            $target_list['key'], SORT_ASC, SORT_STRING
            //$target_list['detail'], SORT_ASC, SORT_STRING
        );
         * 
         */
        //$target_list = $this->apis['ctrl']->array_sort_by_column($target_list);
        foreach ($target_list as $key => $org_list) {
            $sorted_list = $target_list['detail'];
        }
        return $sorted_list;
    }
 
    private function sort_array_by_columns(&$arr, ...$columns)
    {
        $sort_col = array();
        foreach ($arr as $key=> $row) {
            foreach ($columns as $col) {
                $sort_col[$col][$key] = $row[$columns[$col]];
            }
        }
        foreach ($columns as $col) {
            array_multisort($sort_col[$col], SORT_ASC, $arr);
        }
    }

}
