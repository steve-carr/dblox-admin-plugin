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
 * @package         Framework\AdminPlugin\Controller\Api
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller;

class AdminPluginApi extends \Dblox\Controller\Api
{
    //** ******* <!--/ ADMIN PLUGIN CONTROLLER API /--> ******** **//

    private static $instance;

    final public static function get_instance($args=NULL)
    {
        if (NULL === self::$instance) {
            self::$instance  = parent::get_instance($args);
        }
        return self::$instance;
    }

    //* <!--/ Singleton protection methods /-->
    final protected function __construct() {}
    final private function __clone() {}
    final private function __wakeup() {}

}
