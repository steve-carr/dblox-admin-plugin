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
 * @package         Framework\Controller\Traits\Apis\StoredValues
 * @version         1.0.0
 * @since           1.0.0
 */
/** REFERENCE
 *  https://stackoverflow.com/questions/16308252/php-add-elements-to-multidimensional-array-with-array-push
 */

namespace Dblox\Controller\Traits\Apis;

trait StoredValues
{
    //** ******* <!--/ CONTROLLER API - STORE VALUES /--> ******** **//

    protected $stored_values = array();

    protected $in = array();

    public function get_stored_values($subsystem=NULL, $params=NULL, $default=NULL)
    {
        /** NOTE - returns the following (multisite aware)
         *  ($subsystem === NULL) returns ALL of this controller's stored data
         *  success returns requested data (subsection of stored data)
         *  Otherwise, returns $default
         */
        if (NULL === $subsystem) { 
            return (is_multisite()) ? 
                $this->stored_values[get_current_blog_id()] : 
                $this->stored_values;
        }
        //* get the subsystem's controller api
        $ctrl_api = $this->get_controller_api_for($subsystem, NULL);
        //* subsystem is not active (registered) at this time.
        if (!is_object($ctrl_api)) { return $default; }
        $this->in = $this->set_args($subsystem, $params, NULL);
        //* if nothing is stored with that subsystem
        if ($this->is_stored_value_empty()) { return $default; }
        //* start by get everything stored for that subsystem
        $stored = $this->get_everything_stored_for_the_subsystem();
        //* are you asking for everything stored for that subsystem
        if ( (NULL === $this->in['par']) || ( 1 > strlen($this->in['par'][0])) ) { return $stored; }
        //* you are asking for a portion of that stored data
        foreach ($this->in['par'] as $p) {
            $stored = $stored[$p];
        }
        return $stored;
    }

    public function set_if_active($subsystem, $params, $data)
    {
        $sub = $this->get_stored_values(
            $subsystem, 
            $params, 
            $default=NULL
        );
        if ($default === $sub) {
            return $default;
        } else {
            $results = $this->set_stored_values(
                $subsystem, 
                $params, 
                $data
            );
            return $results;
        }
    }

    public function unset_stored_values($subsystem, $params=NULL)
    {
        $data = 'unset';
        $updated_data = $this->set_stored_values($subsystem, $params, $data);
        return ('unset' === $updated_data) ? TRUE : FALSE;
    }

    public function set_stored_values($subsystem, $params, $data)
    {
        $this->in = $this->set_args($subsystem, $params, $data);
        if ($this->in['bid'] < 0) {
            //* this is a single site
            $this->set_stored_single($params, $data);
        } else {
            //* this is a multi-site
            $this->set_stored_multi($params, $data);
        }
        unset($this->in);
        return $data;
    }

    protected function set_args($subsystem, $params, $data)
    {
	$api = $this->get_controller_api_for($subsystem, NULL);
        //* you MAY be asking to store details BEFORE subsystem is registered
        $ctrl_api = (NULL === $api) ? $this : $api;
        $parray = (is_array($params)) ? $params : array($params);
        foreach ($parray as $string) {
            $labels[] = $this->underscore_delimit($string);
        }
        $args['api'] = $ctrl_api;
        $args['bid'] = (is_multisite()) ? get_current_blog_id() : -1;
        $args['sub'] = $this->underscore_delimit($subsystem);
        $args['par'] = $labels;
        $args['dat'] = $data;
        return $args;
    }

    protected function set_stored_single($params, $data)
    {
        $param1 = $this->in['sub'];
        if ((NULL === $params) || ('' === $params)) {
            //* you want to set (overwrite) everything stored for that subsystem
            $this->in['api']->stored_values[$param1] = $data;
        } else {
            //* you want to set (overwrite) something into the subsystem storage
            $max = count($this->in['par']);
            if (!array_key_exists($param1, $this->in['api']->stored_values)) {
                $this->in['api']->stored_values[$param1]= NULL;
            }
            $this->ensure_base_array_exists(
                $this->in['api']->stored_values[$param1], $max, 0
            );
            $this->update_target_data(
                $this->in['api']->stored_values[$param1], $max, 0
            );
        }
    }

    protected function set_stored_multi($params, $data)
    {
        $param1 = $this->in['bid'];
        $param2 = $this->in['sub'];
        if ((NULL === $params) || ('' === $params)) {
            //* you want to set (overwrite) everything stored for that subsystem
            $this->in['api']->stored_values[$param1][$param2]= $data;
        } else {
            //* you want to set (overwrite) something into the subsystem storage
            $max = count($this->in['par']);
            if (!array_key_exists($param1, $this->in['api']->stored_values)) {
                $this->in['api']->stored_values[$param1][$param2]= NULL;
            } elseif (!array_key_exists($param2, $this->in['api']->stored_values[$param1])) {
                $this->in['api']->stored_values[$param1][$param2]= NULL;
            }
            $this->ensure_base_array_exists(
                $this->in['api']->stored_values[$param1][$param2],
                $max, 0
            );
            $this->update_target_data(
                $this->in['api']->stored_values[$param1][$param2],
                $max, 0
            );
        }
    }

    protected function is_stored_value_empty()
    {
        if ($this->in['bid'] < 0) {
            //* single site
            $target = $this->in['api']->stored_values[$this->in['sub']];
            return (!is_array($target)) ? TRUE : FALSE;
        } else {
            //* multi-site
            $target1 = $this->in['api']->stored_values[$this->in['bid']];
            if (!is_array($target1)) { return TRUE; }
            $target = $this->in['api']->stored_values[$this->in['bid']][$this->in['sub']];
            return (!is_array($target)) ? TRUE : FALSE;
        }
    }

    protected function get_everything_stored_for_the_subsystem()
    {
        if ($this->in['bid'] < 0) {
            //* single site
            $target = $this->in['api']->stored_values[$this->in['sub']];
        } else {
            //* multi-site
            $target = $this->in['api']->stored_values[$this->in['bid']][$this->in['sub']];
        }
        return $target;
    }

    protected function ensure_base_array_exists(&$target, $max, $nth=0)
    {
        if ($nth < $max) {
            $key = $this->in['par'][$nth];
            if ( (!is_array($target)) || (!key_exists($key, $target))) {
                $target[$key] = NULL;
            }
            if (++$nth < $max) {
                $this->ensure_base_array_exists($target[$key], $max, $nth);
            }
        }
    }

    protected function update_target_data(&$target, $max, $nth=0)
    {
        //* update data values
        if ($nth === $max) {
            if ('unset' === $this->in['dat']) {
                //* remove data
                unset ($target);
            } else {
                //* add or change data values
                $target = $this->insert_data($this->in['dat']);
            }
        //* just working our way "thru" the array
        } elseif ($nth < $max) {
            $this->update_target_data(
                $target[$this->in['par'][$nth]], 
                $max, 
                ++$nth
            );
        }
    }

    protected function insert_data($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $datum) {
                $data[$key] = $datum;
            }
        }else{
            $data = $input;
        }
        return $data;
    }

}
