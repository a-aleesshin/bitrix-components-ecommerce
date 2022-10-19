<?php

namespace Local\Service;

class Log {

    /**
     * Делает запись в файловом логе
     * @param $stLogName
     * @param $stText
     * @param null $mxStatus
     * @param string $stPrefix
     * @param string $stUserLogin
     * @return bool
     */
    public static function set($stLogName, $stText, $mxStatus = null, $stPrefix = '', $stUserLogin = '') {

        if ($stUserLogin) {

            global $USER;
            if ($USER->GetLogin() !== $stUserLogin)
                return false;

        }

        $stStatus = '';
        if ($mxStatus !== null)
            $stStatus = ($mxStatus?'OK':'NO').' | ';

        $stLog = date('d-m-Y H:i:s').' | '.$stStatus.$stText."\n";

        return file_put_contents('/home/____/public_html/.&logs/'.$stLogName.$stPrefix.'.log', $stLog, FILE_APPEND | LOCK_EX);


    }
}