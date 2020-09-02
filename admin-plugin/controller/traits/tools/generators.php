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
 * @package         Framework\Controller\Traits\Tools\Generators
 * @subpackage      
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Tools;

trait Generators
{
    //** ******* <!--/ STRING GENERATORS /--> ******** **//
    
    final public function generate_label($tags)
    {
        $text = $this->generate($tags, $ch='_');
        return $this->underscore_delimit($text);
    }

    final public function generate_slug($tags)
    {
        $text = $this->generate($tags, $ch='-');
        return $this->dash_delimit($text);
    }

    final public function generate_id($tags)
    {
        return $this->generate_label($tags);
    }

    final public function generate_title($tags)
    {
        $text = $this->generate($tags, $ch=' ');
        return $this->cap_n_space($text);
    }

    final public function generate_camel_case($tags)
    {
        $text = $this->generate($tags, $ch=' ');
        return $this->camel_case($text);
    }

    final public function generate_cid($ctrl)
    {
        /** NOTE
         * Looking for a unique way to identify the controller which manages
         * the subsystem in question. Legal values are:
         *  1) the object to the controller's api
         *  2) the class name to the controller
         *  3) a function returning a unigue id
         *  4) you passed in the controller's label, do nothing
         */
        $arg = 'label';
        if (is_object($ctrl)) { 
            $label = $ctrl->get_value_for($arg);
        }elseif (class_exists($ctrl)) {
            $api = $ctrl::get_instance();
            $label = $api->get_value_for($arg);
        }elseif (function_exists($ctrl)) {
            $label =  $ctrl($arg);
        } else {
            $label = $ctrl;
        }
        return $this->generate_id($label);
    }

    final private function generate($tags, $replacement)
    {
        $target = DBLOX_COMPANY_INITIALS;
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $target .= $replacement . $tag;
            }
        }else{
            $target .= $replacement . $tags;
        }
        $subject = filter_var($target, FILTER_SANITIZE_STRING);
        return $this->insert_into_string($subject, $replacement);
    }
 
}
