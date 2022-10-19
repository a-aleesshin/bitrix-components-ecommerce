<?php

namespace Local\Helper;

class Utility
{

    /**
     * Возвращает случайную строку
     * @param int $inLength
     * @return bool|string
     */
    public function generationUid($inLength = 10) {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, $inLength);
    }
}