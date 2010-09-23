<?php
/**
 * Created by IDEA.
 * User: guigouz
 * Date: Sep 22, 2010
 * Time: 1:06:55 PM
 * To change this template use File | Settings | File Templates.
 */

// global vars
$_handlers = array();


// bultin handlers
register_handler('dump', 'handler_dump');
register_handler('csv', 'handler_csv');

// external handlers
include dirname(__FILE__)."/fftxt.xls.php";


/**
 * Registers a handler for a certain source type
 * @param  $type String A string representing the type
 * @param  $callback String A callback that will receive the query
 * @return Boolean TRUE when ok, exception if handler is already registered
 */
function register_handler($type, $callback) {
    global $_handlers;
    if (isset($_handlers[$type])) {
        throw new Exception("Handler already registered for $type");
    }

    if (!is_callable($callback)) {
        throw new Exception("Invalid callback $callback for $type");
    }

    $_handlers[$type] = $callback;
}

function call_handler($type, $args) {
    global $_handlers;

    if (!isset($_handlers[$type])) {
        throw new Exception("No handler defined for $type");
    }

    return call_user_func($_handlers[$type], $args);

}

function parse($src) {
    $pattern = '/^([^:]+):(.+)$/';

    if (preg_match($pattern, $src, $matches)) {

        // this currently loads all data into memory and returns it
        // it would be better if we had some kind of cursor (handlers may be objects)
        return call_handler($matches[1], $matches[2]);
    }
    else {
        throw new Exception("Invalid source format: $src");
    }
}

function render($data, $template = null, $map = array()) {


    $tr = array('\n' => "\n", '\t' => "\t");

    // preprocess data if it's an array
    if (is_array($data)) {

        foreach ($data as $key => $value) {
            $tr["\$$key"] = $value;

            if (isset($map[$key])) {
                $tr["\${$map[$key]}"] = $value;
            }
        }
    }
    else {
        $tr['$data'] = $data;
    }


    // TODO special templates, like 'json', 'dump', etc
    if ($template) {
        if (is_readable($template)) {
            // TODO cache!
            $template = file_get_contents($template);
        }
        
        return strtr($template, $tr);
        //return utf8_encode(strtr($template, $tr));

    }
    else {
        return print_r($data, true);
    }


}


function handler_dump($args) {
    //cho $args;

    return array(split(',', $args));
}

function handler_csv($args) {
    if (!is_readable($args)) {
        throw new Exception("$args is not readable");
    }

    $fp = fopen($args, 'r');

    $return = array();
    // TODO make delimiter and enclosure configurable
    while ($row = fgetcsv($fp)) {
        $return[] = $row;
    }

    return $return;
}




?>