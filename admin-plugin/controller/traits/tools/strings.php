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

trait Strings
{
    //** ******* <!--/ STRING MASSAGE /--> ******** **//


    //** ******* <!--/ STRING MASSAGE /--> ******** **//
    
    final public function slugit($string)
    {
        return $this->dash_delimit($string);
    }
    
    final public function labelit($string)
    {
        return $this->underscore_delimit($string);
    }

    final public function dash_delimit($string)
    {
        if (is_string($string)) {
            $insert = '-';
            $str1 = $this->insert_into_string($string, $insert);
            $str2 = strtolower($str1);
            $str = $this->clean_string ($str2);
        }else{
            $str = $string;
        }
        return $str;
    }

    final public function underscore_delimit($string)
    {
        if (is_string($string)) {
            $insert = '_';
            $str1 = $this->insert_into_string($string, $insert);
            $str2 = strtolower($str1);
            $str = $this->clean_string ($str2);
        }else{
            $str = $string;
        }
        return $str;
    }

    final public function cap_n_space($string)
    {
        if (is_string($string)) {
            $insert = ' ';
            $str1 = $this->insert_into_string($string, $insert);
            $str2 = ucwords( strtolower($str1) );
            $str = $this->clean_string ($str2);
        }else{
            $str = $string;
        }
        return $str;
    }

    final public function camel_case($string)
    {
        if (is_string($string)) {
            $insert = ' ';
            $str1 = $this->insert_into_string($string, $insert);
            $str2 = ucwords( strtolower($str1) );
            $str3 = $this->insert_into_string($str2, '');
            $str = $this->clean_string ($str3);
        }else{
            $str = $string;
        }
        return $str;
    }

    final public function clean_string($string)
    {
        if (is_string($string)) {
            $str1 = trim($string);
            $str2 = stripslashes($str1);
            $str3 = htmlspecialchars($str2);
            $str = filter_var($str3, FILTER_SANITIZE_STRING);
        }else{
            $str = $string;
        }
        return $str;
    }

    final public function clean_file_string($string)
    {
        $fs = chr(47); // forward slash
        $bs = chr(92); // backslash
        return str_replace(array($fs, $bs), DIRECTORY_SEPARATOR, $string);
    }

    final private function insert_into_string($string, $replacement)
    {
        $subject = str_replace("/", $replacement, $string);
        $pattern = '/[\\_ -]+/';
        return preg_replace($pattern, $replacement, $subject);
    }

}
