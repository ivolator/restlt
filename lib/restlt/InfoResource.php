<?php
namespace restlt;

use restlt\Resource;
/**
 *
 * * @resourceBaseUri /introspect
 */
class InfoResource extends Resource
{

    /**
     * This method will display all the user
     * documentation notes from the doc <br />blocks of the API calls
     *
     *
     * @method GET
     * @cacheControlMaxAge 86400
     * @methodUri /
     */
    public function getAvailableApiCals()
    {
        $resources = $this->getResponse()
            ->getRequestRouter()
            ->getResources();
        $ret = '<div style="font-size:115%; font-style: italic; font-weight:bold; color: navy">';
        $ret .= " API Documentation" . PHP_EOL;
        $ret .= '<a name="top"></a>';
        $ret .= '</div>';
        $ret .= '<div>';
        foreach ($resources as $resourceClass => $methods) {
            $html = '<div style="width:30%; border: solid 1px blue;  padding: 5px">';
            $html .= '<strong>URI</strong> : ' . $methods[0]['methodUri'] . PHP_EOL;
            $html .= '<strong>Http Method</strong> : ' . $methods[0]['method'] . PHP_EOL;
            $html .= '<strong>Description</strong> : ' . PHP_EOL;
            $html .= isset($methods[0]['comment']) ? $methods[0]['comment'] . PHP_EOL : '';
            $html .= '</div>' . PHP_EOL;
            $html .= '<a  href="#top">Top</a>' . PHP_EOL;
            $ret .= $html;
        }
        $ret .= '</div>';
        // TODO
        return nl2br($ret);
    }
}

