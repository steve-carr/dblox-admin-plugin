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
 * @package         Framework\Controller\Traits\Tools\Urls
 * @subpackage      
 * @version         1.0.0
 * @since           1.0.0
 */

namespace Dblox\Controller\Traits\Tools;

trait Urls
{
    //** ******* <!--/ URLS /--> ******** **//

    private $headers;

    final public function get_ctrl_url($addition='')
    {
        $ctrl_name = str_replace('_', '-', $this->vars['label']);
        if ($this->vars['is_plugin']) {
            $url = plugins_url('', $this->vars['dir']) . '/' . $ctrl_name;
        }else{
            $url = get_stylesheet_directory_uri() . '/frameworks/' . $ctrl_name;
        }
        if ('' !== $addition) {
            $addition = str_replace('_', '-', str_replace(' ', '-', strtolower($addition)));
        }
        $url .= $addition;
        return $url;
    }

    final public function url_exists($url)
    {
        /** NOTES
         *  To avoid "SSL operation failed with code 1." make a temporary
         *  change 'ssl' defaults before calling get_headers(). Then, change back.
         *  Reference: https://stackoverflow.com/questions/37274206/get-headers-ssl-operation-failed-with-code-1
         * 
         *  The call to get_headers() can be expensive, timewise. To help this, 
         *  cache the results from the first execution to speed up the next.
         *  Reference: https://codereview.stackexchange.com/questions/80272/url-checker-is-very-slow
         */
        if (('' == $url) || (NULL == $url) || !isset($url) || empty($url)){ return FALSE; }
        //* cache the results, as get_headers() is expensive time-wise
        if (!$this->headers[$url]) {
            //* change ssl default values
            stream_context_set_default( [
                'ssl' => [
                    'verify_peer' => FALSE,
                    'verify_peer_name' => FALSE,
                ],
            ]);
            $this->headers[$url] = get_headers($url);
            //* reset ssl default values
            stream_context_set_default( [
                'ssl' => [
                    'verify_peer' => TRUE,
                    'verify_peer_name' => TRUE,
                ],
            ]);
        }
        return stripos($this->headers[$url][0],"200 OK") ? TRUE : FALSE;
    }

    final public function flush_url_exists($url) 
    {
        //* If you need to flush the buffer...
        if ('all' === $url) {
            unset($this->headers);
        } else {
            unset($this->headers[$url]);
        }
    }
 
}
