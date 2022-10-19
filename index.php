<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->SetPageProperty('title', '');
$APPLICATION->SetPageProperty('description', '');

$APPLICATION->IncludeComponent('an:product.slider', '', array(
    'CACHE_TYPE' => 'Y',
    'CACHE_TIME' => CACHE_TIME,
    'TYPE' => '',
    'NAME' => '',
    'LINK' => '',
    'SORT' => array('ID' => 'DESC'),
    'FILTER' => array(
        '=ACTIVE' => 'Y',
        '=AVALIBLE' => 'Y',
        '=IBLOCK_SECTION_ID' => 107,
        '!=DETAIL_PICTURE' => false,
        'OFFERS' => array(
            '=ACTIVE' => 'Y',
            '=AVALIBLE' => 'Y',
        ),
    ),
), false);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');