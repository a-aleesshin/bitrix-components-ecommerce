<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */

if (!$arResult['ITEMS'])
    return;

CJSCore::Init(array('product-list-item'));

if ($arParams['IS_LAZY_LOAD'] == 'Y')
    CJSCore::Init(array('lazyload'));

$stLink = $arParams['LINK'];
$boIsLink = $arParams['TYPE'] == 'SECTION' && $stLink;

?><div id="<?=$arResult['SLIDER_ID']?>" class="products-slider<?=!$boIsLink?' _no-link':''?>"><?

    if ($arParams['NAME']) {
    ?><div class="products-slider_title"><<?=$stLink?'a href="'.$stLink.'"':'div'?> class="h2"><?=htmlspecialcharsBack($arParams['NAME'])?></<?=$stLink?'a':'div'?>></div><?
}

if ($boIsLink) {
    ?><a href="<?=$stLink?>" class="products-slider_link-all">Все</a><?
}

?><div class="products-slider_items"><?

    $inCell = 0;
    foreach ($arResult['ITEMS'] as $arItem) {

        $arOfferCurrent = $arItem['OFFERS'][$arItem['SELECTED_OFFER_ID']];

        $inPrice = $arOfferCurrent['PRICES']['VALUE'];
        $inPriceOld = $arOfferCurrent['PRICES']['OLD_VALUE'];

        $inPercent = 0;
        if ($inPrice < $inPriceOld)
            $inPercent = round((($inPrice - $inPriceOld)/(($inPrice + $inPriceOld)/2)) * 100);

        $arData = array();

        if ($arItem['MORE_PHOTO'])
            $arData['gallery'] = $arItem['MORE_PHOTO'];

        if ($arItem['OFFERS']) {

            $arData['sizes'] = array_column($arItem['OFFERS'], 'SIZE');
            //sort($arData['sizes']);
            $arData['sizes'] = array_slice($arData['sizes'], 0, 6);

        }

        ?><div class="products-item" data-id="<?=$arItem['ID']?>" data-offer-id="<?=$arOfferCurrent['ID']?>"<?=$arData?' data-info=\''.json_encode($arData).'\'':''?> data-name0="<?=$arItem['NAME0']?>"<?=$arItem['CATEGORY_NAME']?' data-category="'.$arItem['CATEGORY_NAME'].'"':''?>><?

        $stDataSrc = '';
        $stImgSrc = SITE_TEMPLATE_PATH.'/img/pixel.webp';
        if ($arParams['IS_LAZY_LOAD'] !== 'Y' || $inCell < (int)$arParams['START_LAZY_LOAD_CELL'])
            $stImgSrc = $arItem['MORE_PHOTO'][0];
        else
            $stDataSrc = $arItem['MORE_PHOTO'][0];

        // Подгружать в максимальном качестве
        if ($stDataSrc && $arItem['MORE_PHOTO_ONE_FULL_QUALITY_SRC'] && !\Local\Service\Page::isGooglePageSpeed())
            $stDataSrc = $arItem['MORE_PHOTO_ONE_FULL_QUALITY_SRC'];

        ?><div class="products-item_favorite" data-type="addDelay"></div>
        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="products-item_img">
            <img width="282" height="375" src="<?=$stImgSrc?>"<?=$stDataSrc?' data-src="'.$stDataSrc.'"':''?><?=!$stDataSrc&&$arItem['MORE_PHOTO_ONE_FULL_QUALITY_SRC']?' data-original="'.$arItem['MORE_PHOTO_ONE_FULL_QUALITY_SRC'].'"':''?> alt="<?=$arItem['NAME']?>"<?=$arItem['MORE_PHOTO_ONE_ORIGINAL_SRC']?' data-original="'.$arItem['MORE_PHOTO_ONE_ORIGINAL_SRC'].'"':''?>/>
        </a>
        <div class="products-item_prop _flex_space-between-center"><?

            foreach ($arItem['TAGS'] as $arTagValue) {
                ?><span><?=$arTagValue?></span><?
            }

            ?><span class="_code"><?=$arItem['ID']?></span>
        </div>
        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="products-item_name"><?=$arItem['NAME']?></a>
        <div class="products-item_price">
            <span class="_base"><?=$arOfferCurrent['PRICES']['FORMAT']?></span><?
            if ($inPercent) {
                ?><span class="_discount"><?=$arOfferCurrent['PRICES']['OLD_FORMAT']?></span><?
            }
            ?></div>
        </div><?

        $inCell++;

    }

    ?></div>
    </div>
    <script>new anSliderProducts().init('<?=$arResult['SLIDER_ID']?>', <?=$arParams['IS_LAZY_LOAD']=='Y'?'true':'false'?>);</script><?

//xmp($arResult['ITEMS'], '$arResult[\'ITEMS\']');