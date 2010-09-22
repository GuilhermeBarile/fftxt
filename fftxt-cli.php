#!/usr/bin/php
<?php

include dirname(__FILE__) . '/fftxt.php';

echo "Running on commandline\n";
// TODO parse arguments

$options = parseArgs($argv);
//print_r($options);
//print_r($argv);

if (empty($options[0])) {
    exit("Specify a source, for example csv:file.csv\n");
}

$data = parse($options[0]);

// options and default values
$map = isset($options['map']) ? json_decode($options['map'], true) : array();
$slice = isset($options['slice']) ? split(',', $options['slice']) : null;
$template = isset($options['template']) ? $options['template'] : null;
$output = isset($options['out']) ? $options['out'] : null;
$header = isset($options['header']) ? $options['header'] : null;
$footer = isset($options['footer']) ? $options['footer'] : null;
//$map = json_encode(array('0' => 'name', 'asdf' => 'outro'));
//print_r($map);

if ($slice) {
    //print_r($slice);
    $data = array_slice($data, @$slice[0], @$slice[1]);
}

if ($output) {
    // TODO verificar se termina com + e fazer append
    $fp = fopen($output, 'w');

    if ($header) {
        fputs($fp, render(array(), $header, $map));
    }
    foreach ($data as $row) {
        fputs($fp, render($row, $template, $map));
    }

    if ($footer) {
        fputs($fp, render(array(), $footer, $map));
    }
    fclose($fp);
}
else {
    if ($header) {
        echo render(array(), $header, $map);
    }
    foreach ($data as $row) {
        echo render($row, $template, $map);
    }
    if ($footer) {
        render(array(), $footer, $map);
    }

}


function parseArgs($argv) {
    array_shift($argv);
    $out = array();
    foreach ($argv as $arg) {
        if (substr($arg, 0, 2) == '--') {
            $eqPos = strpos($arg, '=');
            if ($eqPos === false) {
                $key = substr($arg, 2);
                $out[$key] = isset($out[$key]) ? $out[$key] : true;
            } else {
                $key = substr($arg, 2, $eqPos - 2);
                $out[$key] = substr($arg, $eqPos + 1);
            }
        } else if (substr($arg, 0, 1) == '-') {
            if (substr($arg, 2, 1) == '=') {
                $key = substr($arg, 1, 1);
                $out[$key] = substr($arg, 3);
            } else {
                $chars = str_split(substr($arg, 1));
                foreach ($chars as $char) {
                    $key = $char;
                    $out[$key] = isset($out[$key]) ? $out[$key] : true;
                }
            }
        } else {
            $out[] = $arg;
        }
    }
    return $out;
}