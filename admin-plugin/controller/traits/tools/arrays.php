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
 * @package         Framework\Controller\Traits\Tools\Arrays
 * @subpackage      
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Tools;

trait Arrays
{
    //** ******* <!--/ ARRAYS /--> ******** **//
 
    public function array_sort_by_column($arr, $col=0, $dir=SORT_ASC, $type=SORT_STRING)
    {
        $temp = $arr;
        $sort_col = array();
        foreach ($arr as $key=> $row) {
            $sort_col[$key] = $row[$col];
        }
        array_multisort($sort_col, $temp, $arr, $type);
        return $temp;
    }

    public function array_to_html($array, $name='array')
    {
        $style=FALSE;
        if(!is_array($array)) { return; }
        $args = [
            'data' => $array,
            'name' => $name,
            'indent' => '',
            'delimiter' => '&nbsp&nbsp&nbsp', // chr(9) = "TAB"
            'level' => 0,
        ];
        $html  = (FALSE === $style) ? '' : $this->define_style();
        //$html .= '<strong>' . $args['name'] . "</strong>:<br>";
        $html .= '<div class="array_tool">';
        $html .= $this->array_to($args);
        $html .= '</div>';
        return $html;
    }

    public function array_to_table($array, $name='Table', $head='')
    {
        if(!is_array($array)) { return; }
        $args = [
            'data' => $array,
            'name' => $name,
            'indent' => '',
            'delimiter' => '<td>',
            'level' => 0,
        ];
        $html  = $this->define_style();
        $html .= '<h3>' . $name . ':</h3>';
        $html .= '<div class="array_tool"><table>';
        $html .= $head;
        $html .= $this->array_to($args);
        $html .= '</table></div>';
        return $html;
    }
    
    //** ******* <!--/ INTERNAL APIS /--> ******** **//

    private function define_style()
    {
        return '<style>'
        . '.array_tool{overflow-x:auto;max-width:700px;max-height:650px;border: 1px solid lightslategray;padding:4px}'
        . 'table,th,td{border:1px solid lightslategray;}'
        . 'table{border-collapse:collapse;width:100%;}'
        . 'th,td{text-align:left;vertical-align:top;padding:4px;}'
        . '</style>';
    }

    //* WARNING - This is a recursive function.
    private function array_to($args)
    {
        if ('<td>' !== $args['delimiter']){
            $html = $this->pre_process_item($args);
        }
        //* if element in this array is a non-empty array (recurse) otherwise process
        foreach ($args['data'] as $key => $value) {
            $args['data'] = $value;
            $args['name'] = $key;
            if(is_array($value) && ([] !== $value)){
                $this->increment_indent($args);
                $html .= $this->array_to($args);
                $this->decrement_indent($args);
            }else{
                $html .= $this->process_item($value, $args);
            }
        }
        return $html;
    }

    private function increment_indent(&$args)
    {
        if ('<td>' === $args['delimiter']){
            $col  = (0 === $args['level']) ? '<tr>' : '';
            $col .= '<td>' . $args['name'] . '</td>';
            $args['indent'] .= $col;
        }
        ++$args['level'];
    }

    private function decrement_indent(&$args)
    {
        if ('<td>' === $args['delimiter']){
            $temp = array();
            $array = explode($args['delimiter'], $args['indent']);
            for ($i=0; $i<$args['level']; $i++) {
                $temp[] = $array[$i];
            }
            $args['indent'] = implode($args['delimiter'], $temp);
        }
        --$args['level'];
    }

    private function pre_process_item($args)
    {
        $indent = '';
        for($i=0; $i<=$args['level']; $i++){
            $indent .= $args['delimiter'];
        }
        return $indent . $args['name'] . ":<br>";
    }

    private function process_item($value, &$args)
    {
        if ('<td>' === $args['delimiter']){
            $col .= $args['indent']
                . '<td>'
                . $args['name'] . ' => '. $this->process_value($value)
                . '</td>'
                . '</tr>';
        }else{
            $indent = '';
            $max = $args['level'] + 1;
            for($i=0; $i<=$max; $i++){
                $indent .= $args['delimiter'];
            }
            $string = $this->process_value($value);
            $col .= $indent . $args['name'] . ' => ' . $string . '<br>';
        }
        return $col;
    }

    private function process_value($value)
    {
        if (  empty($value)  || is_null($value) || 
            (!isset($value)) || ('' === $value) ) {
            $string = __('is empty', DBLOX_TEXT_DOMAIN);
        }elseif(is_bool($value)){
            $string = ($value) ? 'TRUE' : 'FALSE';
        }else{
            if(is_object($value)){
                $string = __('object instance', DBLOX_TEXT_DOMAIN);
            }else{
                $string = $value;
            }
        }
        return esc_html($string);
    }
 
}
