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
 * @package         Framework\Controller\Api
 * @version         1.0.0
 * @since           1.0.0
 */
/** NOTES
 *  @description
 *  The purpose of this subsystem is to provide and integrate solution for
 *  incorporating a general purpose controller into your website. The 
 *  controller takes care of interacting with WordPress to ensure your plugin 
 *  is properly integrated as expected. 
 * 
 *  The Logic class fulfills the purpose of the controller. In addition it:
 *     - incorporates action hooks to facilitate external changes
 *     - injects a help tab into the controller's "data visualization" submenu
 *       providing descriptive details about the subsystem.
 * 
 *  The Api class provides methods for external interaction with the controller. 
 * 
 *  REFERNECES
 */

namespace Dblox\Controller;
use \Dblox\Controller as Master;

class Api
{
    //** ******* <!--/ CONTROLLER API /--> ******** **//

    use Master\Traits\Apis\HookManager, 
        Master\Traits\Apis\OptionValues, 
        Master\Traits\Apis\Registration,
        Master\Traits\Apis\StoredValues;

    use Master\Traits\Tools\Admin, 
        Master\Traits\Tools\Arrays, 
        Master\Traits\Tools\Generators, 
        Master\Traits\Tools\Labels,
        Master\Traits\Tools\Notifications, 
        Master\Traits\Tools\Paths, 
        Master\Traits\Tools\Priorities, 
        Master\Traits\Tools\Strings, 
        Master\Traits\Tools\Styles, 
        Master\Traits\Tools\Tests, 
        Master\Traits\Tools\Updates, 
        Master\Traits\Tools\Urls;
    
    public $apis;

    protected $vars;

    protected $controllers;

    protected $subsystems;

    protected $hooks;

    protected $functions;

    public static function get_instance($args=NULL)
    {
        return new static($args);
    }

    public function bootstrap($args=NULL)
    {
        $this->vars = $args['vars'];
        $this->apis = $args['apis'];
        $this->functions = [
            'initialization',
            'setup',
            'activation',
            'deactivation',
            'visualization',
            'uninstall',
        ];
        $this->init_controller_supports($args);
        $this->register_controller(__NAMESPACE__, $this->vars['label']);
        $this->auto_update_control($this->vars['auto_update']);
        $success = $this->bootstrap_subsystem_management();
        if(!$success) { $this->send_message('subsystem'); }
    }

    protected function init_controller_supports($args)
    {
        //* the order these execute is important
        $args['apis']['subsystem'] = $this;
        $this->apis['pages'] = $args['apis']['pages'] = Master\Pages::get_instance($args);
        $this->apis['hooks'] = $args['apis']['hooks'] = Master\Hooks::get_instance($args);
        $this->apis['logic'] = $args['apis']['logic'] = Master\Logic::get_instance($args);
    }

    protected function auto_update_control($updates)
    {
        $this->blanket_plugin_auto_update($updates['plugins']);
        $this->blanket_theme_auto_update($updates['themes']);
    }

    protected function bootstrap_subsystem_management()
    {
        $success = FALSE;
        $basedir = $this->vars['dir'] . '/subsystems';
        if (!is_readable($basedir)){ return $success; }
        foreach (glob($basedir . '/*', GLOB_ONLYDIR) as $dir) {
            $dir_label = $this->underscore_delimit(
                basename($dir, '.php')
            );
            foreach (glob($dir . '/*.php') as $file ) {
                $filename = $this->underscore_delimit( basename($file, ".php") );
                if($dir_label === $filename) {
                    include $file;
                    $success = TRUE;
                }
            }
        }
        return $success;
    }

    //** ******* <!--/ EXTERNAL CONTROLLER API /--> ******* **//

    public function get_value_for($label='all')
    {
        switch ($label) {
            case 'all':
                return $this->vars;
            case 'controllers':
                return $this->controllers;
            case 'functions':
                return $this->functions;
            case 'hooks':
                return $this->hooks;
            case 'subsystems':
                return $this->subsystems;
        }
        if (is_array($this->vars[$label])) { return $this->vars[$label]; }
        if (array_key_exists($label, $this->vars)) { return $this->vars[$label]; }
        return NULL;
    }

    public function send_message($title='', $arg=[])
    {
        switch ($title) {
            case 'activation':
                $xlate = __('Sorry, you are not authorized to activate this plugin!', DBLOX_TEXT_DOMAIN);
                break;
            case 'deactivation':
                $xlate = __('Sorry, you are not authorized to deactivate this plugin!', DBLOX_TEXT_DOMAIN);
                break;
            case 'uninstall':
                $xlate = __('Sorry, you are not authorized to uninstall this plugin!', DBLOX_TEXT_DOMAIN);
                break;
            case 'authorized':
                $xlate = __('Sorry, you are not authorized to access this plugin!', DBLOX_TEXT_DOMAIN);
                break;
            case 'access':
                $xlate = __('Sorry, you are not authorized to access this page!', DBLOX_TEXT_DOMAIN);
                break;
            case 'subsystem':
                $xlate = __("You haven't installed any Subsystems! Therefore this plugin does nothing...", DBLOX_TEXT_DOMAIN);
                break;
            default:
                $xlate = $this->array_to_html($arg, $title);
                break;
        }
        $this->warn_user($xlate);
    }

    public function get_theme_details()
    {
        $theme_obj = wp_get_theme();
        $theme['name'] = $theme_obj->Name;
        $theme['version'] = $theme_obj->Version;
        $parent = $theme_obj->parent();
        if(FALSE === $parent){
            $theme['parent'] = FALSE; //no parent - you should always use a child theme!;
            $theme['is_genesis'] = FALSE;
        }else{
            $framework = 'Genesis';
            $theme['parent'] = $parent->get('Name');
            $theme['is_genesis'] = ($framework === $theme['name'] || $framework === $theme['parent']);
        }
        $api = $this->get_registered_subsystem('themecore');
        $theme['is_themecore'] = (is_object($api)) ? TRUE : FALSE;
        if ($theme['is_genesis']) {
            $theme['id'] = 'genesis';
        }else{
            if ($theme['is_themecore']) {
                $theme['id'] = 'themecore';
            }else{
                $theme['id'] = 'custom';
            }
        }
        return $theme;
    }

    protected function get_controller_api_for($subsystem_label, $default=NULL)
    {
        $controllers = $this->get_list_of_all_controller_classes();
        foreach ($controllers as $class) {
            $ctrl_api = $class::get_instance();
            $subs = $ctrl_api->get_value_for('subsystems');
            if(is_array($subs[$subsystem_label])) {
                return $ctrl_api;
            }
        }
        //* error $subsystem_label is not registered with a controller
        return $default;
    }

}
