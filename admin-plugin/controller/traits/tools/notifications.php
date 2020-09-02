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
 * @package         Framework\Controller\Traits\Tools\Notification
 * @version         1.0.0
 * @since           1.0.0
 */
/** NOTES
 * 
 * Admin Notification Subpackage
 * 
 * General purpose method for communicating to users via WordPress' admin
 * menu messaging mechanism.
 * 
 * WordPress offers a simple textbox feedabck mechanism for user 
 * communiacattion. There are three types of messages: 'error', 'warning' and 
 * 'success'. The difference is essentially the decoration (color) of the 
 * textbox.
 * 
 * To leverage this subsystem, you simply need to add the following three lines
 * of code to your Objective:
 * 
 *        1. $notify = TCP_Admin_Notification::get_instance();
 *        2. $notify->set_notification($type, $html);
 *        3. $notify->notify_user();
 * 
 * Where:
 *        $type = 'error', 'warning' or 'success'
 *        $html = the html-formatted message you sish to communicate.
 * 
 * Step #2 and #3 are seperated to allow you to build a nested (complex) 
 * message prior to sending it.
 * 
 * Reference: https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
 * 
 */

namespace Dblox\Controller\Traits\Tools;

trait Notifications
{
    //** ******* <!--/ NOTIFICATION /--> ******** **//

    /** WP Admin menu message type
     *
     * @var string
     */
    private $notification_type;

    /** Holds html (text) string
     *
     * This is the text that appears in the WP admin menu message box.
     * 
     * @var string (html)
     */
    private $notification_msg;

    /** Who originated the message
     *
     * Allows dev to see source of error. This is
     * an optional field on all mthod calls.
     * 
     * @var string
     */
    private $plaintiff;

    /** Indicates whether $plaintiff shows in output message
     *
     * @var boolean - TRUE = show plaintiff details, otherwise FALSE
     */
    private $verbose;

    //** ******* <!--/ EXTERNAL API /--> ******* **//

    /** Shorthand for sending easy warning meesages to users
     * 
     * @param string $msg
     */
    final public function warn_user($msg)
    {
        $this->set_notification('notice-warning', $msg);
        $this->notify_user();
    }
    
    /** Add to text string containing message to user on Admin screen
     * 
     * Passed in parameters used to set up notification. The passed in html is
     * added to any existin message. This allows for low-level functions to 
     * provide specific detail & high-level function to give context.
     * 
     * @param string $type - "warning", "error" or other = "success" Defines 
     *                       visual attributes of the pop-up window.
     * @param string $html - html formatted string containing your message
     * @param string $plaintiff [optional] "owner" of the notice, defaults to ''
     */
    final public function set_notification($type, $html, $plaintiff='')
    {
        $this->notification_msg .= $html;
        $type_l = strtolower($type);
        switch ($type_l) {
            case 'notice-warning':
            case 'warning':
                $this->notification_type = 'notice-warning';
                break;
            case 'notice-error':
            case 'error':
                $this->notification_type = 'notice-error';
                break;
            default:
                $this->notification_type = 'notice-success';
                break;
        }
        $this->plaintiff = $plaintiff;
    }
    
    /** Indicator to include more detail in message
     * 
     * Automatically reset (FALSE) each time message is displayed.
     * 
     * @param void
     * @return void
     */
    final public function set_verbose()
    {
        $this->verbose = TRUE;
    }
    
    /** Request the notice be sent in WordPress
     * 
     * @param void
     * @return void
     */
    final public function notify_user()
    {
        add_action( 'admin_notices', [$this, 'notify_wp'] );
    }

    /** Called by WordPress hook
     * 
     * Packages and outputs text message in html format. Assumes the output is
     * wrapped within the WordPress Admin notification system.
     * 
     * @param void
     * @return void
     */
    final public function notify_wp()
    {
        if ('' == $this->notification_msg) {
            /**
             * I put this specific message in to mess with the translators ;^)
             * You may want to change it to something meaninful.
             */
            $this->notification_msg = __('Nothing is what I want!');
        }
        if($this->verbose){
            $this->notification_msg .= ' - ' . $this->plaintiff;
        }
        $type = $this->notification_type;
        $msg = $this->notification_msg;
        $html  = '<div class="notice ' . $type . ' is-dismissible">';
        $html .=     '<div class="wrap"><strong>' . $this->vars['title'] . ' Controller' . ':</strong><br>' . $msg . '</div>';
        $html .= '<br></div>';
        echo $html;
        $this->set_notification_defaults();
    }

    final private function set_notification_defaults()
    {
        $this->notification_type = 'notice-warning';
        $this->notification_msg = '';
        $this->plaintiff = '';
        $this->verbose = FALSE;
    }

}
