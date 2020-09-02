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
 * @package         Framework\Controller\Traits\Tools\Labels
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Tools;

trait Labels
{
    //** ******* <!--/ LABEL MANAGEMENT FUNCTIONS /--> ******** **//

    public function get_controller_label($func, $type='action')
    {
        $ctrl =$this->vars['label'];
        $tag = ('action' === $type) ? 'action' : 'filter';
        $label = $this->generate_label([$ctrl, $func, $tag]);
        return $label;
    }

    public function get_action_labels($subsystem)
    {
       $funcs = $this->get_value_for('functions');
       foreach ($funcs as $func) {
            $labels[$func] = $this->get_subsystem_function_label(
                $subsystem, 
                $func,
                'action'
            );
       }
       return $labels;
    }

    public function get_subsystem_function_label($subsystem, $function, $type='action')
    {
        $tag = ('filter' === $type) ? $type : 'action';
        $label = $this->generate_label([$subsystem, $function, $tag]);
        return $label;
    }

    public function get_transient_label($args=NULL)
    {
        $base = [
            $this->vars['label'],
            'transient'
        ];
        if (NULL !== $args) {
            if (is_array($args)) {
                $add = $args;
            }else{
                $add = [$args];
            }
            $indices = array_merge($base, $add);
        }else{
            $indices = $base;
        }
        $label = $this->generate_label($indices);
        return $label;
    }

    final public function multisite_label($label)
    {
        return (is_multisite()) ? $label.'_'.get_current_blog_id() : $label;
    }

}
