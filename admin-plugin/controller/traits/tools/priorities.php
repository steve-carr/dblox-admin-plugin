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
 * @package         Framework\Controller\Traits\Tools\Priorities
 * @subpackage      
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Tools;

trait Priorities
{
    //** ******* <!--/ PRIORITY MASSAGE /--> ******** **//

    /** NOTE
     *  The default priority for "standard" hooks is "10". If you want to 
     *  influence the order in which subsystems hooks are instantiated try 
     *  using the following "methods" for setting priority.
     *     default = 10 => $default
     *     to get  =  5 => $this->run_earlier($default)
     *     to get  = 15 => $this->run_later($default)
     */

    final public function run_earlier($current=10)
    {
        //* decrease value to run earlier
        $half = (integer)(($current / 2) + 0.5);
        return ($half < 1) ? 1 : $half;
    }

    final public function run_later($current=10)
    {
        //* increase value to run later
        $half = (integer)(($current / 2) + 0.5);
        return ($current + $half);
    }
 
}
