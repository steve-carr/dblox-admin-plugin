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
 * @package         Framework\Controller\Pages\StoredValues
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Pages;

class StoredValues
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
            To access "temporal constant" - values you want to maintain during 
            a session but DON'T need to store in WP_OPTIONS:
            <br>
            Each Controller retains an area of memory to hold values 
            which are needed while a session is active, but don't need 
            to survive a multiple sessions. The interaction with this
            memory looks and acts like the functions used to access 
            the wp_options table. The difference is the values are 
            temporal (only last during a session) and therefore require
            no overhead assiciated with maintaining table-based data.
            <ul>
                <li>
                    <code>get_stored_values(subsystem_label, params, default=NULL)</code> : 
                    Retrieves data based on label/params in the Controller's 
                    <i>stored_memory</i>. Returns <i>default</i> if none is found.
                </li>
                <li>
                    <code>set_stored_values(subsystem_label, params, data)</code> :
                    Sets (creates / updates) a label/params pairs in the 
                    Controller's <i>stored_memory</i>.
                </li>
                <li>
                    <code>unset_option_table_valuess(subsystem_label, params)</code> : 
                    Removes a label/params pairs in the Controller's 
                    <i>stored_memory</i>.
                </li>
            </ul>
        </p>
EOT;
        return __($html, DBLOX_TEXT_DOMAIN);
    }

    public function get_help_sidebar_content()
    {
        $xlate['top'] = __('References for', DBLOX_TEXT_DOMAIN);
        $xlate['ref'] = __('accessing memory', DBLOX_TEXT_DOMAIN);
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
        $more = 'Show as Array';
        $less = 'Show as Table';
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
        $title = 'Stored Values';
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
        $controller = $this->apis['ctrl']->get_value_for('all');
        $title  = '<h2>' . $controller['title'] . " Controller's Stored Values</h2>";
        if ($this->more_detail === FALSE) {
            $html .= $this->show_tables($title);
        }else{
            $html .= $this->show_arrays($title);
        }
        return $html;
    }
    
    private function show_tables($title)
    {
        $html  = '<h2>' . $title . " Controller's Stored Values</h2>";
        $stored = $this->apis['ctrl']->get_stored_values();
        $options = get_option($this->vars['options_label']);
        //* Controller's Data Stored in Memory
        if (!is_array($stored)) {
            $html .= '<br>';
            $html .= __('There is nothing stored for this Controller.', DBLOX_TEXT_DOMAIN);
        } else {
            foreach ($stored as $subsystem => $array) {
                $sub_title = $this->apis['ctrl']->cap_n_space($subsystem) . ' Subsystem:';
                $head = '<tr><th>Source Controller</th><th>Parameters</th></tr>';
                $html .= $this->apis['ctrl']->array_to_table($array, $sub_title, $head);
            }
        }
        //* Controller's Data Stored in Database Table
        $db_title = "Controller's Data Stored in Database Table";
        $head = '<tr><th>Subsystem</th><th>Handle</th><th>Details</th></tr>';
        $html .= $this->apis['ctrl']->array_to_table($options, $db_title, $head);
        return $html;
    }

    private function show_arrays($title)
    {
        $html = '<h2>' . $title . ' ' 
                . __("Controller's Stored Values", DBLOX_TEXT_DOMAIN) 
                . '</h2>';
        $stored = $this->apis['ctrl']->get_stored_values();
        $options = get_option($this->vars['options_label']);
        //* Controller's Data Stored in Memory
        if (!is_array($stored)) {
            $html .= '<br>';
            $html .= __('There is nothing stored for this Controller.', DBLOX_TEXT_DOMAIN);
        } else {
            foreach ($stored as $subsystem => $array) {
                $sub_title = $this->apis['ctrl']->cap_n_space($subsystem) . ' Subsystem';
                $html .= $this->apis['ctrl']->array_to_html($array, $sub_title, $style=TRUE);
                $html .= '<br><br>';
            }
        }
        $db_title = "Controller's Data Stored in Database Table";
        $html .= $this->apis['ctrl']->array_to_html($options, $db_title, $style=TRUE);
        return $html;
    }

}
