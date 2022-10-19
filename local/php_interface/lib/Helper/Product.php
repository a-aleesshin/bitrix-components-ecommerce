<?php

namespace Local\Helper;

class Product
{
    /**
     * Выполняет получение свойств товаров помещая из в общий массив
     * CML2_ATTRIBUTES
     * @param array $arProducts
     * @param array $arPropsCode
     * @return array
     */
    public static function getProductsProps(array $arProducts, array $arPropsCode = array()) {

        if (!$arProducts || !$arPropsCode)
            return $arProducts;

        $inIblockId = $arProducts[key($arProducts)]['IBLOCK_ID'];
        if (!$inIblockId)
            return $arProducts;

        \CIBlockElement::GetPropertyValuesArray($arProducts, $inIblockId, array(
            'ID' => array_keys($arProducts),
            'IBLOCK_ID' => $inIblockId
        ), array('CODE' => $arPropsCode));

        $arSaveKeys = array('ID', 'NAME', 'VALUE', 'VALUE_ENUM_ID');

        // Убираем не нужное барахло для уменьшения размер кеша (в пять раз)
        foreach ($arProducts as $inProductId => $arItem)
            foreach ($arItem['PROPERTIES'] as $stProductCode => $arProp) {

                $arSaveKeysItem = $arSaveKeys;
                if (in_array($stProductCode, array('ATTRIBUTES')))
                    $arSaveKeysItem[] = 'DESCRIPTION';

                foreach ($arProp as $stKey => $mxValue)
                    if (!in_array($stKey, $arSaveKeysItem))
                        unset($arProducts[$inProductId]['PROPERTIES'][$stProductCode][$stKey]);

            }

        return $arProducts;

    }
}