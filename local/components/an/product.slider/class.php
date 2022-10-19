<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CAnProductSlider extends CBitrixComponent {
    var $arPops = array(
        'IM_NAIMENOVANIE',
        'MORE_PHOTO',
        'DISCOUNT',
        'IS_NEW',
    );

    public function executeComponent() {

        if ($this->StartResultCache()) {

            if (!\Bitrix\Main\Loader::IncludeModule('iblock')) {
                $this->abortResultCache();
                return false;
            }

            $arSort = $this->arParams['SORT'];
            if (!$arSort) {
                $arSort = array('ID' => 'ASC');
            }

            $this->arResult['SLIDER_ID'] = 'products_'.\Local\Helper\Utility::generationUid(8);
            $this->arResult['ITEMS'] = array();

            $arFilter = (array)$this->arParams['FILTER'];
            $arFilter['IBLOCK_ID'] = 1;

            if ($arFilter['OFFERS']) {

                if ($arFilter['=ID'])
                    $arFilter['OFFERS']['PROPERTY_CML2_LINK'] = $arFilter['=ID'];

                $arFilter['=ID'] = \CIBlockElement::SubQuery('PROPERTY_CML2_LINK', $arFilter['OFFERS']);
                unset($arFilter['OFFERS']);

            }

            $rsProducts = \CIBlockElement::GetList($arSort, $arFilter, false, array('nTopCount' => 20), array(
                'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'DETAIL_PAGE_URL', 'DETAIL_PICTURE', 'IBLOCK_SECTION_ID'
            ));

            if (!$rsProducts->AffectedRowsCount()) {
                $this->AbortResultCache();
                return false;
            }

            while ($obProduct = $rsProducts->GetNextElement(true, false)) {

                $arProduct = $obProduct->GetFields();
                $arProduct['PRICES'] = array();
                $arProduct['PROPERTIES'] = array();

                $this->arResult['ITEMS'][$arProduct['ID']] = $arProduct;

            }

            $this->arResult['ITEMS'] = \Local\Helper\Product::getProductsProps($this->arResult['ITEMS'], $this->arPops);

            foreach ($this->arResult['ITEMS'] as &$arProduct) {

                $arProduct['NAME0'] = $arProduct['NAME'];
                if ($arProduct['PROPERTIES']['IM_NAIMENOVANIE']['VALUE'])
                    $arProduct['NAME'] = $arProduct['PROPERTIES']['IM_NAIMENOVANIE']['VALUE'];

                $arFiles = array();
                if ($arProduct['DETAIL_PICTURE'])
                    $arFiles[] = $arProduct['DETAIL_PICTURE'];
                if ($arProduct['PROPERTIES']['MORE_PHOTO']['VALUE'])
                    $arFiles = array_merge($arFiles, $arProduct['PROPERTIES']['MORE_PHOTO']['VALUE']);

                foreach ($arFiles as $inCell => $inFileId) {

                    $arFile = CFile::GetFileArray($inFileId);
                    if (!is_array($arFile))
                        continue;

                    /*if (!$inCell) // Надо вотермарк
                        $arProduct['MORE_PHOTO_ONE_ORIGINAL_SRC'] = \Local\Service\ImageResize::get(
                            $arFile,
                            array('width' => 282, 'height' => 375),
                            100,
                            BX_RESIZE_IMAGE_PROPORTIONAL
                        )['src'];*/

                    $arProduct['MORE_PHOTO'][] = \Local\Service\ImageResize::get(
                        $arFile,
                        array('width' => 282, 'height' => 375),
                        $inCell?100:70,
                        BX_RESIZE_IMAGE_PROPORTIONAL
                    )['src'];

                    if (!$inCell) {

                        //$arItem['MORE_PHOTO_ONE_FULL_QUALITY_SRC'] = $arFile['SRC'];

                        $arProduct['MORE_PHOTO_ONE_FULL_QUALITY_SRC'] = \Local\Service\ImageResize::get(
                            $arFile,
                            array('width' => 282, 'height' => 375),
                            100,
                            BX_RESIZE_IMAGE_PROPORTIONAL,
                            1
                        )['src'];

                    }

                    if (count($arProduct['MORE_PHOTO']) == 6)
                        break;

                }

                $arProduct['TAGS'] = $this->getTags($arProduct);

            }

            $this->arResult['ITEMS'] = $this->getOffers($this->arResult['ITEMS']);

            if ($this->arParams['IS_DL_CATEGORY'] == 'Y')
                $this->setCategoryName();
        }

        $this->endResultCache();

        $this->IncludeComponentTemplate();

        $arReturn = array(
            'ITEM_COUNT' => count($this->arResult['ITEMS']),
        );

        return $arReturn;
    }

    private function getTags($arProduct) {

        $arTags = array();

        if ($arProduct['PROPERTIES']['IS_NEW']['VALUE'])
            $arTags['IS_NEW'] = 'NEW';

        return $arTags;

    }

    private function getOffers($arProducts) {

        $arOffers = array();

        $rsOffers = \CIBlockElement::GetList(array('SORT' => 'ASC'), array(
            '=ACTIVE' => 'Y',
            '=AVALIBLE' => 'Y',
            '=PROPERTY_CML2_LINK' => array_keys($arProducts),
            '=IBLOCK_ID' => 2,
        ), false, false, array(
            'ID', 'NAME', 'PROPERTY_28', 'PROPERTY_38'
        ));
        while ($arOffer = $rsOffers->Fetch())
            $arOffers[$arOffer['ID']] = $arOffer;

        foreach ($arOffers as $arOffer) {

            if (!$arProducts[$arOffer['PROPERTY_28_VALUE']]['SELECTED_OFFER_ID'])
                $arProducts[$arOffer['PROPERTY_28_VALUE']]['SELECTED_OFFER_ID'] = $arOffer['ID'];

            $arProducts[$arOffer['PROPERTY_28_VALUE']]['OFFERS'][$arOffer['ID']] = array(
                'ID' => $arOffer['ID'],
                'SIZE' => $arOffer['PROPERTY_38_VALUE'],
//                'PRICES' => \Local\Helper\Price::GetOptimalPriceFormat($arOffer['ID'])['MIN_DATA'],
            );

        }

        return $arProducts;

    }

    private function setCategoryName() {

        if (!$this->arResult['ITEMS'])
            return false;

        $arSectionIds = array_unique(array_column($this->arResult['ITEMS'], 'IBLOCK_SECTION_ID'));
        if (!$arSectionIds)
            return false;

        $arSectionNames = array();

        $rsSections = \Bitrix\Iblock\SectionTable::getList(array(
            'select' => array('ID', 'NAME'),
            'filter' => array(
                '=ID' => $arSectionIds,
            ),
        ));
        while ($arSection = $rsSections->fetch())
            $arSectionNames[$arSection['ID']] = $arSection['NAME'];

        if (!$arSectionNames)
            return false;

        foreach ($this->arResult['ITEMS'] as &$arProduct) {

            if (!$arSectionNames[$arProduct['IBLOCK_SECTION_ID']])
                continue;

            $arProduct['CATEGORY_NAME'] = $arSectionNames[$arProduct['IBLOCK_SECTION_ID']];

        }

        return true;

    }
}
