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
 * @package         Framework\Controller\Traits\Tools\Admin
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Tools;


trait Admin
{
    //** ******* <!--/ ADMINISTRATION /--> ******** **//
     
    public function get_current_screen_id($screen) 
    {
        /** RFERENCE
         *  https://shibashake.com/wordpress-theme/how-to-get-the-wordpress-screen-id-of-a-page
         */
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { return $screen; }
        print_r($screen);
        return $screen;
    }

    public function collect_plugin_data($markup=FALSE, $translate=TRUE)
    {
        $plugin_file = 
            $this->get_value_for('dir') .
            DIRECTORY_SEPARATOR .
            $this->slugit($this->get_value_for('label')) . '.php';
        if( ! function_exists('get_plugin_data') ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        return get_plugin_data($plugin_file, $markup, $translate);
    }

    public function disable_theme_update_notifications($dir)
    {
        $plugin = $this->is_dir_for_plugins($dir);
        if ($plugin) { return; }
        $this->add_to_filter_hook(
            'http_request_args', [$this, 'disable_wporg_request'], 
            $this->run_earlier(), 2
        );
    }

    public function disable_wporg_request($r, $url)
    {
        /*
         * @author          Leland Fiegel, Themetry.com
         * @link            https://themetry.com/unwanted-theme-updates/
         */
        // If it's not a theme update request, bail.
	if (0 !== strpos( $url, 'https://api.wordpress.org/themes/update-check/1.1/')) { return $r; }
        // Decode the JSON response
        $themes = json_decode( $r['body']['themes'] );
        // Remove the active parent and child themes from the check
        $parent = get_option( 'template' );
        $child = get_option( 'stylesheet' );
        unset( $themes->themes->$parent );
        unset( $themes->themes->$child );
        // Encode the updated JSON response
        $r['body']['themes'] = json_encode( $themes );
        return $r;
    }
 
    public function is_in_menu_tree($pid)
    {
        /** REFERENCE
         *  https://css-tricks.com/snippets/wordpress/if-page-is-parent-or-child/
         */
        // $pid = The ID of the page we're looking for pages underneath
	global $post;      // load details about this page
	if(is_page()&&($post->post_parent==$pid||is_page($pid))) {
            return true;   // we're at the page or at a sub page
        } else {
            return false;  // we're elsewhere
        }
    }

}
