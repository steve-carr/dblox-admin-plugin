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
 * @package         Framework\Subsystems\AdminMenubar\Api
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Subsystems\AdminMenubar;

class Api
{
    //** ******* <!--/ ADMIN MENUBAR SUBSYSTEM'S API /--> ******* **//

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
        $this->vars['ctrl'] = $this->apis['ctrl']->get_value_for('all');
    }

    //** ******* <!--/ SUBSYTEM'S EXTERNAL API /--> ******* **//


    public function get_value_for($label='all')
    {
        if('all' === $label){
            return $this->vars;
        }
        if(in_array($label, $this->vars)) {
            return $this->vars[$label];
        }
        if(array_key_exists($label, $this->vars) ){
            return $this->vars[$label];
        }
        return NULL;
    }
   
    //** <!--/ set (in stored_values) - for integration /--> **//
    
    private function generate_cid($ctrl)
    {
        return $this->apis['ctrl']->generate_cid($ctrl);
    }

    public function set_stored_menubar_values($in_data, $ctrl)
    {
        /** NOTE
         *  The difference between a top and sub-menubar entry is - top has 
         *  ['pid' => ''] while sub-menubar has ['pid' => "top's id" ].
         * 
         *  Since this logic is used to create all menubar entries, the 
         *  generation of a unique value $data['id'] depends on the value of
         *  $in_data['pid'].
         */
        $vars = $ctrl->get_value_for('all');
        $tags[] = $this->vars['subsystem']['label'];
        $tags[] = (strlen($in_data['pid']) > 2) ? 
            $in_data['title'] : // we're creating a sub-menubar
            $vars['title']; // we're creating a top-menubar
        $id = $this->apis['ctrl']->generate_id($tags);
        $data['id'] = $id;
        $data['pid'] = $in_data['pid'];
        $data['icon'] = $in_data['icon'];
        $data['title'] = $this->apis['ctrl']->cap_n_space($in_data['title']);
        $data['href'] = $in_data['href'];
        $data['meta'] = $in_data['meta'];

        $cid = $this->generate_cid($ctrl);
        $this->apis['ctrl']->set_stored_values(
            $this->vars['subsystem']['label'], 
            [$cid, $id],
            $data
        );
        return $id;
    }

    public function set_stored_sub_menubar_values($in_data, $ctrl)
    {
        /** NOTE
         *  There is an issue with sub-menubars having icons. Use with care.
         */
        $in_data['icon'] = '';
        $in_data['meta'] = [];
        $id = $this->set_stored_menubar_values($in_data, $ctrl);
        return $id;
    }

    //** <!--/ delete (via WordPress) -  for customiztion /--> **//

    public function remove_menubar($ctrl, $id=NULL)
    {
        $cid = $this->generate_cid($ctrl);
        if (NULL === $id) {
            $menubars = $this->get_menubars_stored_values($ctrl, $id);
            foreach ($menubars as $menubar) {
                $this->apis['ctrl']->unset_stored_values(
                    $this->vars['subsystem']['label'], 
                    [$cid, $menubar[$id]],
                    NULL
                );
                $this->remove_node($menubar[$id]);
            }
        }else{
            $this->apis['ctrl']->unset_stored_values(
                $this->vars['subsystem']['label'], 
                [$cid, $id],
                NULL
            );
            $this->remove_node($id);
        }
    }

    //** <!--/ get (from stored_values) - for customiztion /--> **//

    public function get_menubars_stored_values($ctrl, $id=NULL)
    {
        $cid = $this->generate_cid($ctrl);
        $indices = (NULL === $id) ? [$cid] : [$cid, $id];
        $data = $this->apis['ctrl']->get_stored_values(
            $this->vars['subsystem']['label'], 
            $indices,
            NULL
        );
        return $data;
    }

}
