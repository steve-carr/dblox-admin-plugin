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
 * 
 * @author          Two Carr Productions, LLC
 * @link            https://twocarrs.com/productions
 * @copyright       Copyright (c) 2020 Two Carr Productions, LLC
 * @license         GPL-2+
 * 
 * @package         Framework\Subsystems\AdminDbVcs\Api
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Subsystems\AdminDbVcs;

class Api
{
    //** ******* <!--/ ADMIN DB VCS SUBSYSTEM'S API /--> ******* **//

    protected $vars;

    protected $apis;

    public static function get_instance($args=NULL)
    {
        return new static($args);
    }

    protected function __construct($args=NULL)
    {
        $this->vars['subsystem'] = $args['vars'];
        $this->apis = $args['apis'];
        $this->vars['root_name'] = 'database-update-';
    }

    //** <!--/ SUBSYTEM'S EXTERNAL API - FEATURES /--> **//
    
    public function get_current_db_version($label)
    {
        $default = [
            'version' => (int) 0, // force variable to be typed as an integer
        ];
        $current = $this->apis['ctrl']->get_option_table_values(
            $label, $this->vars['subsystem']['label'], $default
        );
        return $current['version'];
    }

    public function execute_database_updates($dir, $label, $root_name, $version)
    {
        /** NOTES
         * Registration of Taxonomies MUST NOT take place before 'init' hook.
         * https://developer.wordpress.org/reference/functions/register_taxonomy/
         * 
         *  If you are changing Taxonomy record content:
         *  Updates to the database MUST happen AFTER Taxonomies have been 
         *  registered. Therefore, it is incumbent upon the caller to properly
         *  hook this method into WordPress (likely using 'wp_loaded').
         * 
         *  Determine if user is a network (super) admin. Will also check if 
         *  user is admin if network mode is disabled.
         */
        $continue = $this->validation(
            $dir, $label, $root_name, $version, __FUNCTION__
        );
        if (!$continue) { return FALSE; }
        //* get "current" values store in options table
        $current_version = $this->get_current_db_version($label);
        if ($version > $current_version) {
            $success = $this->update_db( [
                'subsys' => $label,
                'dir' => $dir,
                'root_name' => $root_name,
                'version' => $version,
                'current_version' => $current_version,
            ] );
            return $success;
        }else{
            /** NOTE 
             * @return bool/null Description
             *  TRUE  - if update required and successful
             *  FALSE - if update required and unsuccessful
             *  NULL  - if update not required
             */
            return NULL; // no update needed
        }
    }

    //** <!--/ SUBSYTEM'S INTERNAL METHODS /--> **//

    protected function validation($dir, $subsystem_label, $root_name, $version, $func)
    {
        if (!is_super_admin()) {
            $xlate = __('Do not worry! No action was taken. You must have Adminstrator level access to update the database!', DBLOX_TEXT_DOMAIN);
            $this->message(FALSE, $func.' : '.$xlate);
            return FALSE;
        }
        $target = $this->apis['ctrl']->clean_file_string(
            $dir .DIRECTORY_SEPARATOR.$this->apis['ctrl']->slugit($subsystem_label.'-'.$root_name.'.php')
        );
        $xlate = [
          'version' => __('must have a positive integer value', DBLOX_TEXT_DOMAIN),
          'file'    => __('does not exist', DBLOX_TEXT_DOMAIN),
        ];
        //* validate inputs
        if (!$this->apis['ctrl']->is_pos_int($version)) { 
            $this->message(FALSE, $func.': "'.$version.'" '.$xlate['version'].'<br>');
            return FALSE;
            
        }
        if (!$this->apis['ctrl']->does_file_exist($target)) { 
            $this->message(FALSE, $func.': "'.$target.'" '.$xlate['file'].'<br>');
            return FALSE;
        }
        return TRUE;
    }

    protected function update_db($update)
    {
        $details = '';
        flush_rewrite_rules($hard=TRUE); // specifically related to changing CPTs
        while($update['current_version'] < $update['version']){
            $success = $this->update_once(
                $update['dir'], 
                $update['subsys'], 
                $update['root_name'], 
                ++$update['current_version'],
                $details
            );
            if (TRUE === $success) { 
                $value['version'] = $update['current_version'];
                $value['installed'] = date('o.m.d_H:i:s_e');
                $this->apis['ctrl']->set_option_table_values($update['subsys'], $this->vars['subsystem']['label'], $value);
            }elseif (FALSE === $success) { 
                break;
            }
        }
        $this->message($success, $details);
        return $success;
    }	

    protected function update_once($dir, $subsystem_label, $root_name, $version, &$details)
    {
        $args['missing'] = __('was expected, but not found', DBLOX_TEXT_DOMAIN);
        $args['failed'] = __('failed', DBLOX_TEXT_DOMAIN);
        /** NOTE
         * This logic assumes a single file will contain the initiator 
         * functions for each subsequent database update.
         */
        $args['filename'] = $this->apis['ctrl']->slugit($subsystem_label.'-'.$root_name);
        $args['funcname'] = $args['filename'] . '_' . $version;
        $args['file'] = $this->apis['ctrl']->clean_file_string($dir.DIRECTORY_SEPARATOR.$args['filename'].'.php');
        if ($this->apis['ctrl']->does_file_exist($args['file'])) {
            $success = $this->process_file($args, $details);
        }else{
            $success = FALSE;
            $details .= __FUNCTION__.': '.$args['file'].' '.$args['missing'].'<br>';
        }
        return $success;
    }

    protected function process_file($args, &$details)
    {
        include($args['file']);
        $func = $this->apis['ctrl']->underscore_delimit($args['funcname']);
        if (function_exists($func)) {
            set_time_limit(0); // no PHP timeout for running updates
            $error_msg = call_user_func($func);
            if ('' === $error_msg) {
                $success = TRUE;
            }else{
                $success = FALSE;
                $details .= __FUNCTION__.': '.$func.'() '.$args['failed'].'<br>';
                $details .= $error_msg . '<br>';
            }
        }else{
            $success = FALSE;
            $details .= __FUNCTION__.': '.$func.'() '.$args['missing'].'<br>';
        }
        return $success;
     }

    protected function message($success, $details='')
    {
        $msg = $this->vars['subsystem']['label'] . ': ';
        switch ($success) {
            case TRUE:
                $type = 'success';
                $xlate = __('All database updates were successfully executed.', DBLOX_TEXT_DOMAIN);
                $msg .= $xlate . '<br>';
                break;
            case FALSE:
                $type = 'warning';
                $xlate = __('There was a problem updating the database.', DBLOX_TEXT_DOMAIN);
                $msg .= $xlate . '<br>' . $details;
                break;
            default:
                $type = NULL;
                break;
        }
        if (NULL !== $type) {
            $this->apis['ctrl']->set_notification($type, $msg);
            $this->apis['ctrl']->notify_user();
        }
    }

}
