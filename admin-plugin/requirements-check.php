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
/** Repurposed code
 * 
 * Reference: https://markjaquith.wordpress.com/2018/02/19/handling-old-wordpress-and-php-versions-in-your-plugin/
 * 
 * I recommend that your main plugin file just be a simple bootstrapper, where 
 * you define your autoloader, do a few checks, and then call a method that 
 * initializes your plugin code. I also recommend that this main plugin file be 
 * PHP 5.2 compatible. This should be easy to do 
 * (just be careful not to use __DIR__).
 * 
 * In this file, you should check the minimum PHP and WordPress versions that 
 * you are going to support. And if the minimums are not reached, have 
 * the plugin:
 *    * Not initialize (you don’t want syntax errors).
 *    * Display an admin notice saying which minimum version was not met.
 *    * Deactivate itself (optional).
 * 
 * Do not die() or wp_die(). That’s “rude”, and a bad user experience. Your goal 
 * here is for them to update WordPress or ask their host to move them off an 
 * ancient version of PHP, so be kind.
 * 
 * @author          Mark Jaquith
 * @link            https://coveredweb.com/
 * @license         GPL-2+
 * 
 * @package         Requirements_Check
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\AdminPlugin;

class Requirements_Check
{
    private $title = '';
    private $php =  '5.6.20'; //* as of wp 5.2;
    private $wp = '5.2';
    private $file;

    /** Return a new instance of this class
     * 
     * @return object $instance.
     */
    public static function get_instance($args)
    {
        return new Requirements_Check($args);
    }

    protected function __construct($args) 
    {
        foreach ( array( 'title', 'php', 'wp', 'file' ) as $setting ) {
            if ( isset( $args[$setting] ) ) {
                $this->$setting = $args[$setting];
            }
        }
    }

    public function if_requirements_pass() 
    {
        $passes = $this->php_passes() && $this->wp_passes();
        if ( ! $passes ) {
            add_action( 'admin_notices', array( $this, 'indicates_deactivation_is_required' ) );
        }
        return $passes;
    }

    public function indicates_deactivation_is_required() 
    {
        if ( isset( $this->file ) ) {
            deactivate_plugins( plugin_basename( $this->file ) );
        }
    }

    private function php_passes() 
    {
        if ( $this->__php_at_least( $this->php ) ) {
            return true;
        } else {
            add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
            return false;
        }
    }

    private static function __php_at_least( $min_version ) 
    {
        return version_compare( phpversion(), $min_version, '>=' );
    }

    public function php_version_notice() 
    {
        echo '<div class="error">';
        echo "<p>The &#8220;" . esc_html( $this->title ) . "&#8221; plugin cannot run on PHP versions older than " . $this->php . '. Please contact your host and ask them to upgrade.</p>';
        echo '</div>';
    }

    private function wp_passes() 
    {
        if ( $this->__wp_at_least( $this->wp ) ) {
            return true;
        } else {
            add_action( 'admin_notices', array( $this, 'wp_version_notice' ) );
            return false;
        }
    }

    private static function __wp_at_least( $min_version ) 
    {
        return version_compare( get_bloginfo( 'version' ), $min_version, '>=' );
    }

    public function wp_version_notice() 
    {
        echo '<div class="error">';
        echo "<p>The &#8220;" . esc_html( $this->title ) . "&#8221; plugin cannot run on WordPress versions older than " . $this->wp . '. Please update WordPress.</p>';
        echo '</div>';
    }


}
