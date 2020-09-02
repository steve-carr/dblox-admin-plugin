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
 * @package         Framework\Controller\Traits\Tools\Paths
 * @subpackage      
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Tools;

trait Paths
{
    //** ******* <!--/ PATH MASSAGE /--> ******** **//

    final public function get_absolute_path($path, $separator='/') 
    {
        /** NOTE
         * Because realpath() does not work on files that do not
         * exist, I wrote a function that does. It replaces (consecutive) 
         * occurences of / and \\ with whatever is in $separator, and processes 
         * /. and /.. fine. Paths returned by get_absolute_path() contain no
         * (back)slash at position 0 (beginning of the string) or position -1 
         * (ending)
         */
        $path = str_replace(array('/', '\\'), $separator, $path);
        $parts = array_filter(explode($separator, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode($separator, $absolutes);
    }

    final public function normalize_path($path, $separator='/')
    {
        /** NOTE
         * Needed a method to normalize a virtual path that could handle .. 
         * references that go beyond the initial folder reference.
         */
        $parts = array();// Array to build a new path from the good parts
        $path = str_replace('\\', $separator, $path);// Replace backslashes with forwardslashes
        $path = preg_replace('/\/+/', $separator, $path);// Combine multiple slashes into a single slash
        $segments = explode($separator, $path);// Collect path segments
        $test = '';// Initialize testing variable
        foreach($segments as $segment){
            if($segment != '.'){
                $test = array_pop($parts);
                if(is_null($test)) {
                    $parts[] = $segment;
                }else if($segment == '..'){
                    if($test == '..')
                        $parts[] = $test;

                    if($test == '..' || $test == '')
                        $parts[] = $segment;
                }else{
                    $parts[] = $test;
                    $parts[] = $segment;
                }
            }
        }
        return implode($separator, $parts);
    }

    final public function get_class_name($classname)
    {
        if ($pos = strrpos($classname, '\\')) {
            return substr($classname, $pos + 1);
        }
        return $pos;
    }
 
}
