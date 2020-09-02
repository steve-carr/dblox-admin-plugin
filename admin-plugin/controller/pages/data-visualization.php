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
 * @package         Framework\Controller\Pages\DataVisualization
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Pages;

class DataVisualization
{
    //** ******* <!--/ DATA VISUALIZATION PAGE /--> ******* **//

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

    //** <!--/ CALLBACK METHODS /--> **//

    public function get_help_tab_content()
    {
        $html = <<<EOT
        <p>
            To access the Controller's Api class methods : 
            <ul>
                <li><code>get_value_for(label='vars')</code> : 
                    Returns the value associated with an offset of label in the 
                    controller's  "vars" array. Use "all" to get everything.
                </li>
            </ul>
            For multiple controller management
            <ul>
                <li><code>register_controller(namespace)</code> : 
                    Registers the "new" controller with the "primary" controller
                    via a unique namespace. If there is no "primary" controller 
                    designated, then the "new" controller assumes that role.
                </li>
                <li><code>get_list_of_all_controller_classes()</code> : 
                    Returns a list (array) of all registered controllers.
                </li>
            </ul>
            For the registration of Subsystems
            <ul>
                <li><code>register_subsystem(label, obj, class='')</code> : 
                    Registers the entry point to the Subsystem's API with the 
                    Controller. Assumes label is a unique string value. Obj is 
                    the object (instance) of the subsystems API. And class is 
                    an optional field. If subsystem is already registered, the 
                    process is skipped.
                </li>
                <li><code>get_registered_subsystem(label='', param='object')</code> : 
                    Determine if a Subsystem is registered with any Controller. 
                    If param=object, return the Subsystem's API object. 
                    Otherwise, param=class, return the Subsystem's API class.
                </li>
                <li><code>are_subsystems_registered(labels=[], &apis=[])</code> : 
                    Performs mulitple calls to get_registered_subsystem based on the
                    values passed in the labels array. The apis array is updated 
                    with the Subsystem's API object. the method returns TRUE if
                    all Subsystems were found, otherwise it returns FALSE.
                </li>
            </ul>
            For the insertion, execution and tracking of Hooks
            <ul>
                <li><code>add_to_action_hook(label, callback, priority=10, args=0)</code> : 
                    Performs the WordPress add_action call. But also registers 
                    the hook (see register_hook). Label is the string value used
                    to uniquely identify the hook. Callback is of mixed type
                    used to identify the callback function or class/method. 
                    Priority helps to determine the order the callback is 
                    executed in. And, args is the numer of arguments callback 
                    requires.
                </li>
                <li><code>add_to_filter_hook(label, callback, priority=10, args=1)</code> : 
                    Performs the WordPress add_filter call. But also registers 
                    the hook (see register_hook). See add_to_action_hook for 
                    details about passed parameters.
                </li>
                <li><code>do_action_hook(label, caller, ...args)</code> : 
                    Performs the WordPress do_action call. But also registers 
                    the hook (see register_hook). Caller is the callback used 
                    in add_to_action_hook. It is relevent to hook registration
                </li>
                <li><code>do_filter_hook(label, caller, ...args)</code> : 
                    Performs the WordPress apply_filters call. But also registers 
                    the hook (see register_hook). Caller is the callback used 
                    in add_to_action_hook. It is relevent to hook registration
                    registers the hook (see register_hook).
                </li>
                <li><code>remove_from_action_hook(label, callback)</code> : 
                    Performs the WordPress remove_action call. But also 
                    un-registers the hook (see unregister_hook).
                </li>
                <li><code>remove_from_filter_hook(label, callback)</code> : 
                    Performs the WordPress remove_filter call. But also 
                    un-registers the hook (see unregister_hook).
                    </li>
                <li><code>register_hook(label, callback, wpfunc='add_action')</code> : 
                    Registers a "hook" with this Controller. Resulting list is 
                    used to create the "Hook List" admin menu page. Label is 
                    the string value used to uniquely identify the hook. 
                    Callback is of mixed type used to identify the callback 
                    function or class/method. And wpfunc is set to either 
                    "add_action" or "add_filter".
                </li>
                <li><code>unregister_hook(label, action, wpfunc='add_action')</code> : 
                    Removes the "hook" from this Controller's list.
                </li>
            </ul>
            If you follow the "Hooks" and "Logic" class structure...
            <ul>
                <li><code>Every Controller has these Hooks into WordPress</code> : 
                    <>'initialization'</strong> :
                        This is where you would "include" any resources unique 
                        to your Subsystem. If your Subsystem is defined as a 
                        class, you would generally instantiate your classes 
                        here. Especially your priamary interface (api) class.
                    <strong>'setup'</strong> : 
                        This is where you would establish relationships with 
                        other Subsystems which may be needed. It is also where
                        all other pre-activation "setup" takes place.
                    <strong>'activation'</strong> : 
                        This is where you typically "hook into" a specific part
                        of the WordPress ecosystem related to the purpose of 
                        your Subsystem. Typically, this is done through an 
                        "add_action" of "add_filter" hook. If your Controller 
                        is a plugin, these activities will be triggered when 
                        the plugin is activated.
                    <strong>'deactivation'</strong> : 
                        Simply used to "unwind" what you did in activation. 
                        Typically, this is done with "remove_action" or 
                        "remove_filter" hooks.
                    <strong>'uninstall'</strong> : 
                        This is a rarely used hook and exists for the unusual 
                        case of a Subsystem wanting to take extraordinary action
                        when a plugin Controller is deleted.
                    <strong>'visualization'</strong> : 
                        This is part of the data visualization system built into
                        the proivded Controllers and Subsystems. It facilaites 
                        is way to display data related to administering the 
                        various Controllers and Subsystems.
                </li>
                <li><code>insert_into_controller_hooks(object=NULL, priority=10, args=1)</code> : 
                    Typically called by the "Hooks" class of a Subsystem 
                    wishing to "hook into" the Controller's hooks. The 
                    Controller creates a series of "do hooks" (do_action, 
                    do_fitlers) and links them into apprpriate the 
                    WordPress hooks. If the Subsystem is defined as a class, 
                    then object is the instance to the Subsystem's api. The 
                    logic steps through each hook type and, if the Subsystem 
                    has a corresponding function/method, a call to 
                    add_to_action_hook is executed.
                </li>
                <li><code>get_controller_label(function, type='action')</code> : 
                    Ensures consistant naming of hooks being created by 
                    insert_into_controller_hooks.
                </li>
                <li><code>insert_logic_into_subsystem_hooks(subsystem, object=NULL, priority=10, args=1)</code> : 
                    This method provides a consistant way to insert the 
                    Subsystem's logic into the "do hooks" it created with the 
                    insert_into_controller_hooks function. Ths subsystem 
                    parameter is the "label" (unique string) to identify the 
                    target Subsystem.
                </li>
                <li><code>get_subsystem_function_label(subsystem, function, type='action')</code> : 
                    Ensures consistant naming of hooks being created by 
                    insert_logic_into_subsystem_hooks.
                </li>
            </ul>
            To access "temporal constant" values you DON'T need to store in WP_OPTIONS
            <ul>
                <li>Each Controller retains an area of memory to hold values 
                    which are needed while a session is active, but don't need 
                    to survive a multiple sessions. The interaction with this
                    memory looks and acts like the functions used to access 
                    the wp_options table. The difference is the values are 
                    temporal (only last during a session) and therefore require
                    no overhead assiciated with maintaining table-based data.
                </li>
                <li><code>
                        get_option_table_valuess(subsystem_label, params, default=NULL)
                    </code> : 
                    Retrieves data based on label/params in the Controller's 
                    <code>stored_memory</code>. Returns default if none is found.
                </li>
                <li><code>set_stored_values(subsystem_label, params, data)</code> :
                    Sets (creates / updates) a label/params pairs in the 
                    Controller's option entry.
                </li>
                <li><code>unset_option_table_valuess</code>(subsystem_label, params) : 
                    Removes a label/params pairs in the Controller's option entry.
                </li>
            </ul>
            To access the values you've stored in WordPress' WP_OPTIONS table
            <ul>
                <li>Each Controller retains a table entry an in wp_options table. 
                    And each Subsystem can store data in the controller's entry.
                    This makes for clean and easy uninstall clean-up. The 
                    following get_ set_ and unset_ methods all mask this 
                    structure. So, you don't have to know which controller your
                    target Subsystem belongs to. Just provide it's label and 
                    the method figures it out for you. There is caching built in
                    as well to minimize actual "hits" on table reads. All data 
                    is store as a "to_json" array. This is also masked for you. 
                    You can assume everything is set and gotten as arrays.
                </li>
                <li><code>get_option_table_values(subsystem_label, handle='', default=NULL)</code> : 
                    If target Subsystem has nothing saved, the default value is 
                    returned. If handle is empty (''), then the entire array of 
                    stored values is returned. If handle is not empty, it is 
                    treated as an index into the Subsystem's array and only that
                    portion is returned.
                </li>
                <li><code>set_option_table_values(subsystem_label, handle, value)</code> : 
                    If handle is empty (''), then the entire array of the Subsystem
                    is replaced with value. If handle is not empty, it is 
                    treated as an index into the Subsystem's array and only that
                    portion is updated with value.
                </li>
                <li><code>unset_option_table_values(subsystem_label, handle='')</code> : 
                    If handle is empty (''), then the entire array of the Subsystem
                    is reremove (unset). If handle is not empty, it is 
                    treated as an index into the Subsystem's array and only that
                    portion is removed.
                </li>
            </ul>
        </p>
EOT;
        return __($html, DBLOX_TEXT_DOMAIN);
    }

    public function get_help_sidebar_content()
    {
        $xlate['top'] = __('References for', DBLOX_TEXT_DOMAIN);
        $xlate['ref'] = __('Defining data requirements', DBLOX_TEXT_DOMAIN);
        $xlate['code'] = __('Coding standards', DBLOX_TEXT_DOMAIN);
        $xlate['css'] = __('CSS frameworks', DBLOX_TEXT_DOMAIN);
        $xlate['theme'] = __('Theme frameworks', DBLOX_TEXT_DOMAIN);
        $data['ref'] = [
            'WP References' => 
                'https://developer.wordpress.org/reference/',
            'WP Codex' => 
                'https://codex.wordpress.org/Main_Page',
            'ACF Documentation' => 
                'https://www.advancedcustomfields.com/resources/',
        ];
        $data['code'] = [
            'W3 schools' => 
                'https://www.w3schools.com/php/default.asp',
            'PHP Reference' => 
                '//php.net/manual/en/langref.php',
            'CMD Batch Reference' => 
                'https://docs.microsoft.com/en-us/windows-server/administration/windows-commands/windows-server-backup-command-reference',
        ];
        $data['css'] = [
            'Bootstrap' => 
                'https://getbootstrap.com',
            'Foundation' => 
                'https://foundation.zurb.com',
            'Tachyons' => 
                'https://tachyons.io/',
        ];
        $data['theme'] = [
            'Genesis' => 
                'https://www.studiopress.com/features/',
            'Themecore' => 
                '//www.twocarrs.com/scarr/theme-core/',
        ];
        $list = ['ref', 'css', 'theme', 'code',];
        $html = '<p>' . $xlate['top'] . '...</p>';
        foreach ($list as $key) {
            $html .= '<p>' . $xlate[$key] . ':';
            foreach ($data[$key] as $txt => $href) {
                $html .= '<br><a href=';
                $html .= $href . '><strong>' . $txt . '</strong>';
                $html .= '</a>';
            }
            $html .= '</p>';
        }
        return __($html, DBLOX_TEXT_DOMAIN);
    }

    public function render_page()
    {
        if (!is_admin()) { return; }
        $this->apis['admin_menu'] = $this->apis['ctrl']->get_registered_subsystem('admin_menu');
        if (!is_object($this->apis['admin_menu'])) { return; }
        $more = 'More Detail';
        $less = 'Less Detail';
        $this->check_inputs();
        $page = $this->define_head($more, $less);
        $page .= '<div id="infoBody">';
        $page .= $this->fill_page();
        $page .= '<br>The End</div>';
        echo $page;
    }

    //** ******* <!--/ INTERNAL API /--> ******* **//

    private function check_inputs()
    {
        //* $_GET['detail']
        $value = filter_input(
            INPUT_GET, 
            'detail', 
            FILTER_VALIDATE_BOOLEAN, 
            FILTER_NULL_ON_FAILURE
        );
        if(NULL !== $value) { $this->more_detail = ($value) ? TRUE : FALSE; }
    }

    private function define_head($more, $less)
    {
        $head = $this->apis['ctrl']->collect_plugin_data();
        $id = $this->get_page_id();
        $args = [
            'icon' => $this->vars['icon'],
            'url' => $head['AuthorURI'],
            'title' => $head['Name'],
            'label' => ($this->more_detail) ? $less : $more,
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

    private function get_page_id()
    {
        $page_id = $this->apis['admin_menu']->get_menu_id($this->apis['ctrl'],$this->vars['subsystem']['label']);
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
        $label = $this->apis['ctrl']->get_controller_label('visualization', 'filter');
        $visible = ($this->more_detail) ? TRUE : FALSE;
        $html .= $this->apis['ctrl']->do_filter_hook(
            $label, [$this, 'show_data'], 
            $html, $visible
        );
        return $html;
    }

}
