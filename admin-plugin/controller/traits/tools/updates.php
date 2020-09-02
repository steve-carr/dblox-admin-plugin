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
 * @package         Framework\Controller\Traits\Tools\Updates
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Tools;

trait Updates
{
    //** ******* <!--/ UPDATES /--> ******** **//

    final protected function set_plugin_auto_update($force=TRUE, $all=FALSE)
    {
        /** Auto Update Args
         *  $force = do you want include "auto_update" feature
         *  $all   = do you want it for all and future plugins and themes
         */
        $action = (TRUE === $force) ? 'true' : 'false';
        if (TRUE === $all) {
            $this->blanket_auto_update($action);
            return;
        }
        // Check if auto_updates are enabled for plugins.
        $this->autoUpdateEnabled['plugin'] = wp_is_auto_update_enabled_for_type('plugin');
        // Enable plugins auto_update UI elements.
        add_filter( 'plugins_auto_update_enabled', '__return_'.$action );
        // Modify the UI to reflect a "no auto update"
        if (FALSE === $force) {
            add_filter(
                'plugin_auto_update_setting_html', 
                'replace_auto_update_setting_html', 
                10, 3
            );
        }
    }

    final protected function set_theme_auto_update($force=TRUE, $all=FALSE)
    {
        /** Auto Update Args
         *  $force = do you want include "auto_update" feature
         *  $all   = do you want it for all and future plugins and themes
         */
        $action = (TRUE === $force) ? 'true' : 'false';
        if (TRUE === $all) {
            $this->blanket_auto_update($action);
            return;
        }
        // Check if auto_updates are enabled for plugins and themes.
        $this->autoUpdateEnabled['theme'] = wp_is_auto_update_enabled_for_type('theme');
        // Enable plugins auto_update UI elements.
        add_filter('themes_auto_update_enabled', '__return_'.$action );
        // Modify the UI to reflect a "no auto update"
        if (FALSE === $force) {
            add_filter(
                'theme_auto_update_setting_template',
                'replace_auto_update_setting_template'
            );
        }
    }

    final protected function blanket_plugin_auto_update($action)
    {
        // Set all plugin auto_updates.
        $state = ($action) ? 'true' : 'false';
        add_filter('auto_update_plugin', '__return_'.$state);
    }

    final protected function blanket_theme_auto_update($action)
    {
        // Set all theme auto_updates.
        $state = ($action) ? 'true' : 'false';
        add_filter('auto_update_theme', '__return_'.$state);
    }

    final public function replace_auto_update_setting_html($html, $plugin_file, $plugin_data) 
    {
        if ( 'my-plugin/my-plugin.php' === $plugin_file ) {
            $html = __(
                'auto_updates are not available for this plugin.',
                DBLOX_TEXT_DOMAIN
            );
        }
        return $html;
    }

    final public function replace_auto_update_setting_template($template) 
    {
        $text = __(
            'auto_updates are not available for this theme.', 
            DBLOX_TEXT_DOMAIN
        );
        $theme_list = [
            'admin-plugin', 
            'publisher-plugiin', 
            'custom-plugin', 
            'child-theme', 
            'parent-theme'
        ];
        return "<# if ( $theme_list.includes( data.id ) ) { #>
            <p>$text</p>
            <# } else { #>
            $template
            <# } #>";
    }
 
}
