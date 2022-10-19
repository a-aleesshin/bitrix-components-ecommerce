<?php

namespace Local\Service;

class ImageResize {
    public static function get($mxFile, $arSize, $inQuality = 85, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL, $mxWtermark = false) {
        $arFilters = array();

        if ($mxWtermark == 1) {
            $arFilters = array(array(
                'name' => 'watermark',
                'position' => 'br',
                'type' => 'image',
                'size' => 'real',
                'file' => $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/img/pixel.png',
            ));
        }

        $boIsModuleImageCompress = \Bitrix\Main\Loader::IncludeModule('dev2fun.imagecompress') && $inQuality < 100;
        if ($boIsModuleImageCompress) {
            \Dev2fun\ImageCompress\Compress::getInstance()->setEnableResize(true);
            \Dev2fun\ImageCompress\Compress::getInstance()->setJpegQuality($inQuality);
        }

        $mxResult = \CFile::ResizeImageGet(
            $mxFile,
            $arSize,
            $resizeType, // BX_RESIZE_IMAGE_PROPORTIONAL
            true,
            $arFilters,
            false,
            $inQuality
        );

        if ($boIsModuleImageCompress) {
            \Dev2fun\ImageCompress\Compress::getInstance()->setJpegQualityDefault();
            \Dev2fun\ImageCompress\Compress::getInstance()->setEnableResize(false);
        }

        return $mxResult;
    }

    function OnBeforeResizeImage($arFile, $arResizeParams, &$callbackData, &$bNeedResize, &$sourceImageFile, &$cacheImageFileTmp) {
        if (!$arFile['PRODUCT_PATH']) {
            return false;
        }

        $arParamsCode = array(
            'max_len' => 200,
            'replace_space' => '-',
            'replace_other' => '-',
            'delete_repeat_replace' => true,
        );

        $arNewPath = array();
        foreach ($arFile['PRODUCT_PATH'] as $stItem) {

            if (!$stItem)
                return false;

            $arNewPath[] = \CUtil::translit(trim($stItem), 'ru', $arParamsCode);

        }

        $stSize = implode('x', $arResizeParams[0]);
        $stNewPath = '/images/'.implode('/', $arNewPath).'-'.$stSize.'.'.mb_strtolower(explode('.', $arFile['FILE_NAME'])[1]);

        if (strripos($stNewPath, '/images/-') === false)
            $cacheImageFileTmp = $_SERVER['DOCUMENT_ROOT'].$stNewPath;

        if ($GLOBALS['USER'] && $GLOBALS['USER']->IsAdmin() && $_REQUEST['clear_img'] == 'Y')
            \Bitrix\Main\IO\File::deleteFile($cacheImageFileTmp);

        // Отслеживает создавать новый файл или нет, во избежание повторного сжатия.
        $GLOBALS['cacheImageFileTmpFileExists'] = '';
        if (file_exists($cacheImageFileTmp))
            $GLOBALS['cacheImageFileTmpFileExists'] = md5($cacheImageFileTmp);

        return true;
    }
}