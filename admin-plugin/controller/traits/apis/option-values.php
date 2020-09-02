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
 * @package         Framework\Controller\Traits\Apis\OptionValues
 * @version         1.0.0
 * @since           1.0.0
 */
/** REFERENCE
 *  https://www.virendrachandak.com/techtalk/php-isset-vs-empty-vs-is_null/
 */

namespace Dblox\Controller\Traits\Apis;

trait OptionValues
{
    //** ******* <!--/ CONTROLLER API - OPTION VALUES /--> ******** **//

    public $cached_options = array();

    public function get_option_table_values($subsystem, $handle='', $default=NULL)
    {
        //* get the subsystem's controller api
        $ctrl['api'] = $this->get_controller_api_for($subsystem, NULL);
        //* subsystem is not active (registered) at this time.
        if (!is_object($ctrl['api'])) { return $default; }
        $this->in = $this->set_args($subsystem, $ctrl, NULL);
        $ctrl['id']  = $ctrl['api']->multisite_label(
            $ctrl['api']->get_value_for('options_label')
        );
        //* get all stored values from subsystem's controller
        $options = $this->get_cached_options($ctrl, NULL);
        //* the controller has nothing stored at this time
        if ((NULL === $options) || (empty($options))) { return $default; }
        //* data exists, but subsystem_label does not
        if (!array_key_exists($subsystem, $options)) { return $default; }
        //* data exists, including $subsystem, but valid $handle does not
        if (('' !== $handle) && (!array_key_exists($handle, $options[$subsystem]))) { return $default; }
        //* we have legit stored data
        if ('' !== $handle) {
            return $options[$subsystem][$handle];
        }else{
            return $options[$subsystem];
        }
    }
    
    protected function get_cached_options($ctrl, $default=NULL)
    {
        if (  empty($ctrl['api']->cached_options)||
           (  '' == $ctrl['api']->cached_options)||
           (NULL == $ctrl['api']->cached_options)||
           (  [] == $ctrl['api']->cached_options)
        ) { 
            // if cache is empty, go to wp_options table.
            $options = get_option($ctrl['id'], $default);
            $ctrl['api']->cached_options = $options;
        }else{
            $options = $ctrl['api']->cached_options;
        }
        return $options;
    }

    public function set_option_table_values($subsystem_label, $handle, $value)
    {
        $ctrl['api'] = $this->get_controller_api_for($subsystem_label, NULL);
        if (!is_object($ctrl['api'])) {
            $ctrl['api'] = $this;
        }
        $ctrl['id']  = $ctrl['api']->multisite_label(
            $ctrl['api']->get_value_for('options_label')
        );
        $options = $this->get_cached_options($ctrl);
        //* you want the whole array
        if ('' === $handle) {
            $options[$subsystem_label] = $value;
        //* you want a subset of the array
        }else{ 
            $options[$subsystem_label][$handle] = $value;
        }
        $this->set_cached_options($ctrl, $options); 
    }
    
    public function unset_option_table_values($subsystem_label, $handle='')
    {
        $ctrl['api'] = $this->get_controller_api_for($subsystem_label, NULL);
        if (!is_object($ctrl['api'])) { return FALSE; }
        $ctrl['id']  = $ctrl['api']->multisite_label(
            $ctrl['api']->get_value_for('options_label')
        );
        $options = $this->get_cached_options($ctrl);
        if (NULL === $options) { return NULL; }
        if (!array_key_exists($subsystem_label, $options)) { return FALSE; }
        if (('' !== $handle) && (!array_key_exists($handle, $options[$subsystem_label]))) { return FALSE; }
        //* you want the whole array
        if ('' === $handle) {
            $options[$subsystem_label] = '';
            unset($options[$subsystem_label]);
        //* you want a subset of the array
        }else{ 
            $options[$subsystem_label][$handle] = '';
            unset($options[$subsystem_label][$handle]);
        }
        $this->set_cached_options($ctrl, $options); 
    }

    protected function set_cached_options($ctrl, $options)
    {
        $changed = update_option($ctrl['id'], $options); 
        if ($changed) { $ctrl['api']->cached_options = $options; }
        return $changed;
    }

}
