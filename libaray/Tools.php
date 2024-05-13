<?php

namespace libaray;
class Tools
{
    public static function isImage($url)
    {
        $headers = get_headers($url);

        foreach ($headers as $header) {
            if (strpos($header, 'Content-Type') !== false && strpos($header, 'image') !== false) {
                return true;
            }
        }

        return false;
    }

    public static function getUrlExtension($url)
    {
        $info = pathinfo($url);
        return $info['extension'];
    }

    public static function success($msg, $info = [])
    {
        echo json_encode([
            'error_code' => 0,
            'msg' => $msg,
            'info' => $info
        ]);
        exit;
    }

    public static function error($error_code, $msg, $info = [])
    {
        echo json_encode([
            'error_code' => $error_code,
            'msg' => $msg,
            'info' => $info
        ]);
        exit;
    }

}