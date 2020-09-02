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
 * @package         Framework\Controller\Traits\Tools\Tests
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Tools;

trait Tests
{
    //** ******* <!--/ TESTS /--> ******** **//

    final public function is_dir_for_plugins($dir)
    {
        $normalized = strtolower(str_replace("\\", '/', $dir));
        if (strpos($normalized, '/plugins/') !== FALSE) {
            return TRUE;
        }else{
            if (strpos($normalized, '/themes/') !== FALSE) {
                return FALSE;
            }else{
                return NULL;  //* possible bad input
            }
        }
    }

    final public function is_dir_empty($dir)
    {
        if (!is_readable($dir)){
            return NULL; 
        }
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return FALSE;
            }
        }
        return TRUE;
    }

    final public function is_pos_int($var)
    {
        $success = (is_int($var) === TRUE) ? TRUE : NULL;
        if ($success && (($var < 1) || ($var > PHP_INT_MAX))) {$success = FALSE;}
        return $success;
    }

    final public function does_file_exist($file)
    {
        /** NOTE
         *  The result of file_exists() is cached. Use clearstatcache() to 
         *  clear the cache.
         */
        $exist = file_exists($file);
        clearstatcache();
        return $exist;
    }
 
}
