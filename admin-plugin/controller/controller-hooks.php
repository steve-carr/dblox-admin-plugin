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
/** Developments Details
 * @author          Two Carr Productions, LLC
 * @link            https://twocarrs.com/productions
 * @copyright       Copyright (c) 2020 Two Carr Productions, LLC
 * @license         GPL-2+
 * 
 * @package         Framework\Controller\Hooks
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller;

class Hooks
{
    //** ******* <!--/ INSERT CONTROLLER'S LOGIC INTO WP HOOKS /--> ******* **//

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
        $this->insert_controller_hooks_into_wordpress();
    }

    public function insert_controller_hooks_into_wordpress()
    {
        /** Reference:
         * http://rachievee.com/the-wordpress-hooks-firing-sequence/
         */
        $count = 0;
        if ($this->vars['is_plugin']) {
            $list = $this->get_list_of_wp_hooks_for_plugins($count);
        }else{
            $list = $this->get_list_of_wp_hooks_for_themes($count);
        }
        $functions = $this->register_wp_hooks_from($list);
        return $functions;
    }

    protected function get_list_of_wp_hooks_for_plugins($count)
    {
        $normal = $this->vars['priority'];
        $list = [
            'initialization' => [
                'label' => 'setup_theme',               // Fires before the theme is loaded.
                'action' => [$this, 'initialization'],
                'priority' => $normal,
                'args' => $count,
            ],
            'setup' => [
                'label' => 'after_setup_theme',         // Fires after the theme is loaded.
                'action' => [$this, 'setup'],
                'priority' => $normal,
                'args' => $count,
            ],
            'activation' => [
                'label' => 'init',                      // Fires after WordPress has finished loading but before any headers are sent.
                'action' => [$this, 'activation'],
                'priority' => $normal,
                'args' => $count,
            ],
            'deactivation' => [
                'label' => __FILE__,
                'action' => [$this, 'deactivation'],
                'priority' => $normal,
                'args' => $count,
            ],
            'uninstall' => [
                'label' => __FILE__,
                'action' => [$this, 'uninstall'],
                'priority' => $normal,
                'args' => $count,
            ],
            'visualization' => [
                'label' => 'wp_loaded',                 // Fires once WP, all plugins, and the theme are fully loaded and instantiated.
                'action' => [$this, 'visualization'],
                'priority' => $normal,
                'args' => $count,
            ],
        ];
        return $list;
    }

    protected function get_list_of_wp_hooks_for_themes($count)
    {
        $normal = $this->vars['priority'];
        $earlier = $this->run_earlier($normal);
        $list = [
            'initialization' => [
                'label' => 'after_setup_theme',         // Fires after the theme is loaded.
                'action' => [$this, 'initialization'],
                'priority' => $earlier,
                'args' => $count,
            ],
            'setup' => [
                'label' => 'after_setup_theme',         // Fires after the theme is loaded.
                'action' => [$this, 'setup'],
                'priority' => $normal,
                'args' => $count,
            ],
            'activation' => [
                'label' => 'init',                      // Fires after WordPress has finished loading but before any headers are sent.
                'action' => [$this, 'activation'],
                'priority' => $normal,
                'args' => $count,
            ],
            'uninstall' => [
                'label' => __FILE__,
                'action' => [$this, 'uninstall'],
                'priority' => $normal,
                'args' => $count,
            ],
            'visualization' => [
                'label' => 'wp_loaded',                 // Fires once WP, all plugins, and the theme are fully loaded and instantiated.
                'action' => [$this, 'visualization'],
                'priority' => $normal,
                'args' => $count,
            ],
        ];
        return $list;
    }
    
    protected function register_wp_hooks_from($list)
    {
        foreach ($list as $type => $activity) {
            switch($type){
                case 'initialization':
                case 'setup':
                case 'activation':
                case 'visualization':
                    $this->apis['ctrl']->add_to_action_hook(
                        $activity['label'], 
                        $activity['action'], 
                        $activity['priority'], 
                        $activity['args']
                    );
                    break;
                case 'deactivation':
                    if ($this->vars['is_plugin']) {
                        //* Don't use add_to_action_hook() in this case, just do the same thing
                        $this->apis['ctrl']->register_hook(
                            'register_deactivation_hook', 
                            $activity['action'], 
                            'add_action'
                        );
                        register_deactivation_hook(
                            $activity['label'], 
                            $activity['action']
                        );
                    }
                    break;
                case 'uninstall':
                    if ($this->vars['is_plugin']) {
                        //* Don't use add_to_action_hook() in this case, just do the same thing
                        $this->apis['ctrl']->register_hook(
                            'register_uninstall_hook', 
                            $activity['action'], 
                            'add_action'
                        );
                        register_uninstall_hook(
                            $activity['label'], 
                            $activity['action']
                        );
                    }
                    break;
            }
            $functions[]=$type;
        }
        return $functions;
    }

    //** <!--/ SUBSYSTEM "DO" HOOK INSERTION POINTS /--> **//

    public function initialization()
    {
        $this->do_action_hook(__FUNCTION__);
    }

    public function setup()
    {
        $this->do_action_hook(__FUNCTION__);
    }

    public function activation()
    {
        /**
         * Detect plugin. For use on Front End only.
         * https://codex.wordpress.org/Function_Reference/is_plugin_active
         */
        // DEBUG NOOP
        //include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        //$plugin_name = substr(strrchr(dirname(__DIR__), "\\"), 1);
        //$active = is_plugin_active($plugin_name.'/'.$plugin_name.'.php');
        //* only super-users can modify the state of this controller
        //if (!$active && !current_user_can('manage_network_plugins')) { 
        //    $this->apis['ctrl']->send_message('activation');
        //    return; 
        //}

        $this->do_action_hook(__FUNCTION__);
    }

    public function deactivation()
    {
        if (!is_admin()) { return; }
        if (!$this->vars['is_plugin']) { return; }
        // the user MUST be a member of this site AND currently logged in.
        if (!is_user_member_of_blog() && !is_user_logged_in()) { return; }
        //* only super-users can modify the state of this controller
        if (!current_user_can('manage_network_plugins')) {
            $this->apis['ctrl']->send_message('deactivation');
            return; 
        }

        $this->do_action_hook(__FUNCTION__);
    }

    public function uninstall()
    {
        if (!is_admin()) { return; }
        if (!$this->vars['is_plugin']) { return; }
        /** Reference: 
         * http://wpsmith.net/2014/plugin-uninstall-delete-terms-taxonomies-wordpress-database/
         * https://wordpress.stackexchange.com/questions/25910/uninstall-activate-deactivate-a-plugin-typical-features-how-to/25979#25979
         */

	// the user MUST be a member of this site AND currently logged in.
        if (!is_user_member_of_blog() && !is_user_logged_in()) { return; }
        //* only super-users can modify the state of this controller
        if (!current_user_can('manage_network_plugins')) {
            $this->apis['ctrl']->send_message('unistall');
            return; 
        }
        
        /** WIP - Need to figure out how/where to create & verify nonces...
        wp_verify_nonce( $this->vars['label'] );
         * 
         */

        //* Important: Check if this file is the one registered with the uninstall hook.
        if ( __FILE__ != WP_UNINSTALL_PLUGIN ) {
            $this->apis['ctrl']->send_message('unistall');
            return; 
        }

        //** <!--/ INSERT ANY REQUIRED CLEAN-UP ACTIVITIES HERE... /--> **//

        /** IMPORTANT 
         * Because this logic is destructive in nature. You should be very 
         * careful and test throughly before inserting any logic here.
         */
        $this->do_action_hook(__FUNCTION__);
    }

    public function visualization()
    {
        if (!is_admin()) { return; }
        $this->do_action_hook(__FUNCTION__);
    }

    private function do_action_hook($func, $args='')
    {
        $label = $this->apis['ctrl']->get_controller_label($func);
        return $this->apis['ctrl']->do_action_hook($label, [$this, $func], $args);
    }

    final private function clear_options()
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
        $label = $this->apis['ctrl']->get_value_for('option_id'); // DBLOX_COMPANY_INITIALS.'_customplugin_options';
        delete_option($label);
    }

}
