<?php

//$uri    = '/abc/{x:?}/{y:\d}';
//$params = [
////    'x' => 1,
//        'y' => 2
//];
//echo preg_replace_callback(['/\{(\w+)\}/', '/\{(\w+)\:(.+)\}/'], static function ($match) use ($params) {
//    var_dump($match);
//    if (!isset($params[$match[1]])) {
//        if (!endsWith(trim($match[2]), '?')) {
//            throw new \RuntimeException('Not Found Parameter ' . $match[1]);
//        }
//        return '';
//    }
//
//    return $params[$match[1]];
//}, $uri);
//
//function startsWith($haystack, $needle): bool
//{
//    $length = strlen($needle);
//
//    return substr($haystack, 0, $length) === $needle;
//}
//
//function endsWith($haystack, $needle): bool
//{
//    $length = strlen($needle);
//    if (!$length) {
//        return true;
//    }
//
//    return substr($haystack, -$length) === $needle;
//}

//var_dump(startsWith('abc', ''));

$uri = "/abc/{ x_? }/{y:\d}/{z}";
$uri = filter_var($uri, FILTER_SANITIZE_URL);
$uri = trim($uri, '/');
// Parse pattern from uri
$pattern = preg_replace('/\//', '\\/', $uri);
$pattern = preg_replace_callback('/\{\s*(\w+[?]?)\s*\}/', static function ($match) {
    $endCharacter = substr($match[1], -1);
    if ($endCharacter === '?') {
        $groupName = rtrim($match[1], $endCharacter);
        $pattern   = ".$endCharacter";
    } else {
        $groupName = $match[1];
        $pattern   = ".+";
    }

    return "(?<$groupName>$pattern)";
}, $pattern);
//$pattern = preg_replace('/\{(\w+)\:(.+)\}/', '(?<$1>$2)', $pattern);
$pattern = "/^$pattern$/i";

echo $pattern;
