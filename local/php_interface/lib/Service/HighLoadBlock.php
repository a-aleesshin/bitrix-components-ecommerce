<?php

namespace Local\Service;

class HighLoadBlock {
    private static function getHLDataClass($inHLId) {
        if (!$inHLId)
            return false;

        \Bitrix\Main\Loader::includeModule('highloadblock');

        $obHLBlock = is_numeric($inHLId)?
            \Bitrix\Highloadblock\HighloadBlockTable::getById($inHLId)->fetch():
            array_pop(\Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter' => array('TABLE_NAME' => $inHLId)))->fetchAll());

        $obHLTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($obHLBlock);

        return $obHLTable->getDataClass();
    }

    /*
	 * Получение записей
	 *
	 * @param int $inHLId - ID или имя таблицы HL-блока
	 * @param array $arParams
	 * @param bool $boRemoveUf
	 *
	 * @return array|null
	 */
    public function getHLRecords($inHLId, array $arParams = array(), bool $boRemoveUf = false) :? array {
        $obHLClass = self::getHLDataClass($inHLId);

        if (!$obHLClass)
            return null;

        $arRecords = array();

        $rsRecords = $obHLClass::getList($arParams);
        while ($arRecord = $rsRecords->fetch()) {

            if ($boRemoveUf) {
                $arTempRecord = array();

                foreach ($arRecord as $mxKey => $mxValue)
                    $arTempRecord[str_replace('UF_', '', $mxKey)] = $mxValue;

                $arRecord = $arTempRecord;
            }

            $arRecords[] = $arRecord;

        }

        return $arRecords ?: null;
    }

    public function getList($inHLId, array  $arParameters = array()) {

        $obHLClass = self::getHLDataClass($inHLId);
        return $obHLClass::getList($arParameters);

    }


    /*
    * Получить запись из БД
    */
    public static function getRecords($inHLId, $arSelect = array('*'), $arFilter = array(), $arOrder = array('ID' => 'DESC'), $inLimit = null) {

        $arRecords = array();
        $obHLClass = self::getHLDataClass($inHLId);
        //xmp($obHLClass, '$obHLClass');
        $rsRecords = $obHLClass::getList(array(
            'select' => $arSelect,
            'filter' => $arFilter,
            'order' => $arOrder,
            'limit' => $inLimit
        ));
        while ($arRecord = $rsRecords->fetch())
            $arRecords[] = $arRecord;

        return $arRecords;
    }

    /*
     * Запись в БД
     */
    public static function addRecords($inHLId, $arFields) {

        $obHLClass = self::getHLDataClass($inHLId);

        $result = $obHLClass::add(
            $arFields
        );

        if ($result->isSuccess())
            return $result->getId();
        else
            return $result->getErrors();

    }

}