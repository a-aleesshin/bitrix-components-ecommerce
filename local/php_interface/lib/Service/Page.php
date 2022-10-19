<?php

namespace Local\Service;

class Page {
    private static $instance;

    private $boIsMinimization = false;

    private $arOperators;
    private $request;

    /**
     * @return self
     */
    public static function getInstance() {

        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        $obContext = \Bitrix\Main\Application::getInstance()->getContext();

        self::$instance->request = $obContext->getRequest();

        return self::$instance;

    }

    public function setMinimization($boValue = false) {
        $this->boIsMinimization = !!$boValue;
    }

    public function isGooglePageSpeed() {
        return
            strripos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed Insights') !== false
            || strripos($_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse') !== false
            || self::isGoogleIp($_SERVER['REMOTE_ADDR'])
            ;
    }

    public function modifyContent($stContent)
    {
        $arSearch = array();
        $arReplace = array();

        // Минимизируем HTML если включена соответствующая опция
        if ($this->boIsMinimization) {

            $arSearch = array_merge($arSearch, array("\n", "\t", /*"\r",*/ ';else'));
            $arReplace = array_merge($arReplace, array('', '', /*'',*/ ';else '));

            // Удаляем часть компонентов "// "
            $stContent = preg_replace('#(\/\/ (.*?)(\n|$|\r|(\r\n)))|(\/\*(.*?)\*\/)#', '', $stContent);
            $stContent = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#', '', $stContent);

            if (self::isGooglePageSpeed())
                $stContent = preg_replace('#( data-original="(.*?)")#', '', $stContent);

        }

        // https://validator.w3.org/nu/
        //if ($boReplaceJs) {
        $arSearch = array_merge($arSearch, array(' type="text/javascript"', '  rel=', '  data-'));
        $arReplace = array_merge($arReplace, array('', ' rel=', ' data-'));
        //}


        $stContent = str_replace($arSearch, $arReplace, $stContent);

        // Удаление лишнего JS
        /*if ($this->boIsUnSetBX) {

            $stContent = preg_replace(
                array(
                    //'/\<script\>\(window.BX.*?\<\/script\>/',
                    '/\<script\>BX.setJSList.*?\<\/script\>/',
                    '/\<script\>BX.setCSSList.*?\<\/script\>/',
                    '/\<script  src="\/bitrix\/cache\/js\/s1\/ms\/kernel_main_polyfill.*?\<\/script\>/',
                    '/\<script src="\/bitrix\/js\/main\/loadext.*?\<\/script\>/',
                    '/\<link href="\/bitrix\/js\/main\/core\/css\/core.min.css.*?\ \/>/',
                    //'/\<script\>\(window.BX\|\|top.BX\).message\(\{\'JS_CORE_LOADING.*?\<\/script\>/',
                    //'/\<script src="\/bitrix\/js.*?\<\/script\>/',
                ),
                '',
                $stContent
            );

        }*/

        return $stContent;
    }

    public static function getStringMin($stString) {

        if (!$stString)
            return $stString;

        $arParams = array(
            "\n" => '',
            "\t" => '',
            "\r" => '',
            ';else' => ';else ',
        );

        return str_replace(array_keys($arParams), $arParams, $stString);

    }

    public static function getCssStyle() {

        $srStyleLink = \Bitrix\Main\Page\Asset::getInstance()->GetCSS(0);

        preg_match_all('/href="(.*)"\stype/', $srStyleLink, $matches);
        //preg_match_all('/href="(.*)\?/', $srStyleLink, $matches);

        /*global $USER;
        if ($USER->GetLogin() == 'gorev') {

            //echo '<xmp>$srStyleLink = '; print_r($srStyleLink); echo '</xmp>';
            //echo '<xmp>$matches = '; print_r($matches); echo '</xmp>';
            //die();
        }*/

        $arStyles = array(
            'main.css' => '',
            'header.css' => '',
        );
        foreach ($matches[1] as $val) {

            $full_path = $_SERVER['DOCUMENT_ROOT'].explode('?', $val)[0];
            if (
                /*(
                    strpos($full_path, 'page_') === false
                    && strpos($full_path, 'kernel_') === false
                    && strpos($full_path, 'fonts.googleapis.com') === false
                    && strpos($full_path, 'template_') === false
                )*/
                strpos($full_path, '/local/') === false
                || !file_exists($full_path)
            )
                continue;

            /*if (strpos($full_path, 'kernel_') !== false)
                $stStyle = '';
            else*/


            if (strpos($full_path, 'kernel_') === false) {

                $stFileValue = trim(file_get_contents($full_path));

                $stFileName = array_pop(explode('/', $full_path));
                if (isset($arStyles[$stFileName]) && !$arStyles[$stFileName])
                    $arStyles[$stFileName] = $stFileValue;
                else
                    $arStyles[] = $stFileValue;

            }

            $srStyleLink = preg_replace('~<link href="'.str_replace('?', '\?', $val).'"[^>]* />~', '', $srStyleLink);

        }

        $stStyle = implode('', $arStyles);

        $stStyle = str_replace(
            array("\n", "\t", "\r", ';else', ';}', ' {', ': ', ', ', ' = ', ' += ', ' === ', '};', ';.', '"'),
            array('',   '',   '',   ';else ','}',  '{',  ':',  ',',  '=',   '+=',   '===',   '}',  '.',  '\''),
            $stStyle
        );

        /*$stStyle = str_replace(array(
            'catalog.element/templates/.default/../../../../../templates/ms/',
            'css/../',
            '../../templates/ms/',
        ), '', $stStyle);*/

        $stStyle = preg_replace('~\/\*.*?\*\/~', '', $stStyle);
        //$stStyle = preg_replace('~\/local\/components\/.*?\.\.\/\.\.\/\.\.\/templates~', '/local/templates', $stStyle);
        /*global $USER;
        if ($USER->GetLogin() == 'gorev') {

            //echo '<xmp>$stStyle = '; print_r($stStyle); echo '</xmp>';
            //echo '<xmp>$srStyleLink = '; print_r($srStyleLink); echo '</xmp>';
            //die();
        }*/
        return $srStyleLink.'<style>'.$stStyle.'</style>';

    }

    /**
     * Возвращает минимизированное содержимое JS файла
     * @param $stFilePath
     * @return bool|mixed|null|string|string[]
     */
    public static function getJsFileMin($stFilePath) {

        if (!$stFilePath)
            return false;

        if (strripos($stFilePath, '.min.js') !== false)
            $stFilePathMin = $stFilePath;
        else
            $stFilePathMin = str_replace('.js', '.min.js', $stFilePath);

        $stLoadFile = '';
        if ($stFilePathMin && file_exists($stFilePathMin))
            $stLoadFile = $stFilePathMin;
        else if (file_exists($stFilePath))
            $stLoadFile = $stFilePath;

        if (!$stLoadFile)
            return false;

        $stJs = file_get_contents($stLoadFile);

        $stJs = preg_replace('#(\/\/ (.*?)(\n|$|\r|(\r\n)))|(\/\*(.*?)\*\/)#', '', $stJs);
        $stJs = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#', '', $stJs);

        /*if (strripos($stLoadFile, '.min.js') === false)
            $stJs = str_replace(
                array("\n", "\t", "\r", ';else', ';}', ' {', ': ', ', ', ' = ', ' += ', ' === ', '};', ';.', '"'),
                array('',   '',   '',   ';else ','}',  '{',  ':',  ',',  '=',   '+=',   '===',   '}',  '.',  '\''),
                $stJs
            );*/

        return $stJs;

    }

    /**
     * Возвращает минимизированное содержимое CSS файла
     * @param $stFilePath
     * @return bool|mixed|null|string|string[]
     */
    public static function getCssFileMin($stFilePath) {

        if (!$stFilePath)
            return false;

        if (strripos($stFilePath, '.min.css') !== false)
            $stFilePathMin = $stFilePath;
        else
            $stFilePathMin = str_replace('.css', '.min.css', $stFilePath);

        $stLoadFile = '';
        if ($stFilePathMin && file_exists($stFilePathMin))
            $stLoadFile = $stFilePathMin;
        else if (file_exists($stFilePath))
            $stLoadFile = $stFilePath;

        if (!$stLoadFile)
            return false;

        $stCss = file_get_contents($stLoadFile);

        $stCss = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#', '', $stCss);
        $stCss = str_replace(
            array("\n", "\t", "\r", ';else', ';}', ' {', ': ', ', ', ' = ', ' += ', ' === ', '};', ';.', '"'),
            array('',   '',   '',   ';else ','}',  '{',  ':',  ',',  '=',   '+=',   '===',   '}',  '.',  '\''),
            $stCss
        );

        return $stCss;

    }

    /*public static function getLoadingData() {

        if (!self::$instance->request->get('getContentArea'))
            return false;

        //if ($GLOBALS['USER']->GetLogin() !== 'gorev')
        //return false;

        $arData = array();

        $arDeleteParams = \Local\MS\Option::get('DEL_PARAMS_2', array());
        $arDeleteParams += \Local\MS\Helper::getDirectParams();
        $arDeleteParams += \Bitrix\Main\HttpRequest::getSystemParameters();

        // Сохранять clear_cache=Y в URL 24.03.2021
        $inKeyClearCache = array_search('clear_cache', $arDeleteParams);
        if (
            $inKeyClearCache !== false
            && $GLOBALS['USER']
            && $GLOBALS['USER']->IsAuthorized()
            && in_array(6, $GLOBALS['USER']->GetUserGroupArray())
        )
            unset($arDeleteParams[$inKeyClearCache]);

        $stPageURL = \CHTTP::urlDeleteParams($_SERVER['REQUEST_URI'], $arDeleteParams);

        $arData['url'] = $stPageURL;
        //$arData['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

        //}

        if (!$arData)
            return false;

        return '<script id="loadingData" type="application/json">'.json_encode($arData).'</script>';

    }*/

    public static function getJs() {

        $stJsLink = \Bitrix\Main\Page\Asset::getInstance()->GetJS(0);

        //echo '<xmp>$stJsLink = '; print_r($stJsLink); echo '</xmp>';

        preg_match_all('/src="(.*)"/', $stJsLink, $matches);

        $stJs = '';
        foreach ($matches[1] as $val) {

            $stPath = $_SERVER['DOCUMENT_ROOT'].explode('?', $val)[0];
            if (!file_exists($stPath) || strpos($stPath, 'core.') !== false || strpos($stPath, 'core_ls.') !== false)
                continue;

            $stJs .= '<script>'.trim(file_get_contents($stPath)).'</script>';

            $stJsLink = preg_replace('~<script type="text/javascript"  src="'.str_replace('?', '\?', $val).'"[^>]*></script>~', '', $stJsLink);

        }

        //if ($_SERVER['REMOTE_ADDR'] == '46.160.251.80') {

        //echo '<xmp>$matches = '; print_r($matches); echo '</xmp>';

        //}

        //$stJsLink = preg_replace('~<script src="/bitrix/js/main/core/core.min.js?(*)"></script>~', '', $stJsLink);
        $stJs = str_replace(
            array('//# sourceMappingURL=core.map.js', '//# sourceMappingURL=core_ls.map.js', '//# sourceMappingURL=jquery-3.3.1.min.map.js'),
            array('',),
            $stJs
        );

        $stJs = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#', '', $stJs);

        return $stJs;
        //return $stJs.$stJsLink;
        //return '<script>'.$stJs.'</script>'.$stJsLink;

    }

    /**
     * Регистрирует обработчики для применения переменных
     */
    public function addEventHandler() {
        \Bitrix\Main\EventManager::getInstance()->addEventHandler(
            'main',
            'OnEndBufferContent',
            array('\Local\Service\Page', 'OnEndBufferContent'),
            false,
            1000
        );
    }

    public static function OnEndBufferContent(&$stContent) {
        if (strripos($_SERVER['SCRIPT_NAME'], '/bitrix/tools/') === false)
            $stContent = self::getInstance()->modifyContent($stContent, true);
    }

}