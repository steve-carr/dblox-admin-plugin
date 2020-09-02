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
 * @package         Framework\Subsystems\Dependency\Api
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Subsystems\Dependency;

class Api
{
    //** ******* <!--/ ADMIN DEPENDENCY SUBSYSTEM'S API /--> ******* **//

    protected $vars;

    protected $apis;

    public static function get_instance($args=NULL)
    {
        return new static($args);
    }

    protected function __construct($args=NULL)
    {
        $this->vars = $args['vars'];
        $this->apis = $args['apis'];
        $this->vars['root_name'] = 'database-update-';
    }

    //** <!--/ SUBSYTEM'S EXTERNAL API - FEATURES /--> **//

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

    public function add_dependency($type, $name, $owner=NULL)
    {
        if(!$this->valid_type($type)){ return NULL; }
        $label = (NULL === $owner) ? ($owner.'-'.$type.'-'.$name) : ($type.'-'.$name);
        $id = $this->apis['ctrl']->dash_delimit($label.'-'.__CLASS__);

        $data['id'] = $id;
        $data['owner'] = $owner;
        $data['type'] = $type;
        $data['name'] = $name;
 
        $this->apis['ctrl']->set_stored_values(
            $this->vars['label'], 
            [$id],
            $data
        );
       return $id;
    }

    public function delete_dependency($id) 
    {
 
        $this->apis['ctrl']->unset_stored_values(
            $this->vars['label'], 
            [$id],
            NULL
        );
    }

    public function list_of_missing()
    {
       $html = $this->list_as_html(FALSE);
       return $html;
    }

    public function list_of_all()
    {
       $html = $this->list_as_html(TRUE);
       return $html;
    }

    private function list_as_html($show_all)
    {
 
        $dependencies = $this->apis['ctrl']->get_stored_values(
            $this->vars['label'], 
            NULL
        );
        if (NULL === $dependencies) { return NULL; }
        $html = $type = $name = '';
        foreach ($dependencies as $dependency) {
            $name = $dependency['owner'];
            $type = $dependency['type'];
            $name = $dependency['name'];
            $active = $this->active_type_name($type, $name);
            if($show_all || !$active) {
                $html .= '"' . $owner . '" requires ' . $type . ' =>  "' . $name . '" <br>';
            }
        }
        return $html;
    }

    private function active_type_name($type, $name)
    {
        switch ($type) {
            case 'function':
                $valid = function_exists($name);
                break;
            case 'class':
                $valid = class_exists($name);
                break;
            case 'theme':
                $theme = wp_get_theme();
                $valid = ($name === $theme->get('Name') || $name === $theme->parent()->get('Name'));
                break;
            case 'constant':
                $valid = defined($name);
                break;
            case 'file': // should be absolute location
                $valid = $this->apis['ctrl']->does_file_exist($name);
                break;
            default:
                $valid = FALSE;
                break;
        }
        return $valid;
    }
    
    private function valid_type($type)
    {
        switch ($type) {
            case 'function':
            case 'class':
            case 'theme':
            case 'constant':
            case 'file':
                $valid = TRUE;
                break;
            default:
                $valid = FALSE;
                break;
        }
        return $valid;
    }

}
