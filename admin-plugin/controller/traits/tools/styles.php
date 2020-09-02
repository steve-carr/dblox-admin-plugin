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
 * @package         Framework\Controller\Traits\Tools\Styles
 * @subpackage      
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Tools;

trait Styles
{
    //** ******* <!--/ STYLE /--> ******** **//

    public function full_admin_page_style($id)
    {
        /**IMPORTANT
         * In WP core, #wpfooter is set with position: absolute. This creates a
         * situation where the footer can overwrite details. The solution is to 
         * set position: static.
         */
        
        $this->vars['element_id'] = $element_id = 
            $this->dash_delimit(DBLOX_COMPANY_INITIALS . '-' . $id);
        $style = <<<EOT
        <style>
            #wpfooter{
                position: static;
            }
            #{$element_id}{
                background-color: snow; /* aka #fffafa */
                padding: 15px 15px 15px 15px;
            }
            h2{
                color: darkblue;
                font-size: 24px;
            }
            h3{
                color: lightslategray;
                font-size: 18px;
            }
            h4{
                color: lightslategray;
                font-size: 16px;
            }
            table, th, td {
                border: 1px solid lightslategray;
            }
            table{
                border-collapse: collapse;
                overflow-x:auto;
            }
            th, td {
                text-align: left;
                padding: 4px;
                vertical-align: top;
            }
        </style>
EOT;
        return $style;
    }

    public function minimum_admin_page_style($id)
    {
        /**IMPORTANT
         * In WP core, #wpfooter is set with position: absolute. This creates a
         * situation where the footer can overwrite details. The solution is to 
         * set position: static.
         */
        $this->vars['element_id'] = $element_id = 
            $this->dash_delimit(DBLOX_COMPANY_INITIALS . '-' . $id);
        $style = <<<EOT
        <style>
            #wpfooter{
                position: static;
            }
            #{$element_id}{
                background-color: snow; /* aka #fffafa */
                padding: 15px 15px 15px 15px;
            }
        </style>
EOT;
        return $style;
    }

    public function admin_page_button_script($more, $less)
    {
        $script = <<<EOT
        <script>
            function flipBtn() {
                var detail = false;
                var label = document.getElementById("infoBtn").value;
                if ("{$more}" === label) {
                    detail = true;
                    label = "{$less}";
                }else{
                    label = "{$more}";
                }
                document.getElementById("infoBtn").value = label;
                document.getElementById("infoDetail").value = detail;
            }
        </script>
EOT;
        return $script;
    }

    public function admin_page_button_form($args)
    {
        if('' === $args['url']) {
            $title = $args['title'];
        }else{
            $title = '<a href="' . $args['url'] . '">' . $args['title'] . '</a>';
        }
        $icon = $args['icon'];
        $label = $args['label'];
        $page = $args['page'];
        $detail = $args['detail'];
        $html = <<<EOT
        <form method="get" onSubmit="flipBtn()">
            <h1>
                <span
                    class="dashicons {$icon}"
                    style="font-size: 200%; width:50px; height:50px; color:#f2bb1d"
                >
                </span>
                &nbsp&nbsp&nbsp
                <span>{$title}</span>
                &nbsp&nbsp&nbsp
                <input 
                    type="submit" 
                    id="infoBtn"
                    value="{$label}"
                >
                <input
                    type="hidden"
                    id="infoFile"
                    name="page"
                    value="{$page}"
                >
                <input
                    type="hidden"
                    id="infoDetail"
                    name="detail"
                    value="{$detail}"
                >
            </h1>
        </form>
EOT;
        return $html;
    }

    public function admin_page_no_button_form($args)
    {
        if('' === $args['url']) {
            $title = $args['title'];
        }else{
            $title = '<a href="' . $args['url'] . '">' . $args['title'] . '</a>';
        }
        $icon = $args['icon'];
        $page = $args['page'];
        $html = <<<EOT
        <form method="get" onSubmit="flipBtn()">
            <h1>
                <span
                    class="dashicons {$icon}"
                    style="font-size: 200%; width:50px; height:50px; color:#f2bb1d"
                >
                </span>
                &nbsp&nbsp&nbsp
                <span>{$title}</span>
            </h1>
        </form>
EOT;
        return $html;
    }
 
}
