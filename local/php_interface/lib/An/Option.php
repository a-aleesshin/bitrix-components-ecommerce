<?php

namespace Local\An;

class Option {
    private static $arOptions = array(
        '' => array(
            '',
        ),
    );

    public static function get($stParamName, $mxDefault = '') {

        if (!isset(self::$arOptions[$stParamName]))
            return $mxDefault;

        return self::$arOptions[$stParamName];

    }
}