<?php
namespace framework\core;

use framework\exception\SystemException;
use framework\library\Css;
use framework\library\Javascript;
use framework\library\Log;
use framework\library\Optimizer;
use framework\library\Time;

class Response
{
    private static $instance;

    private $response_data = null;
    private $content_type = 'text/html';
    private $view_path = null;
    private $layout_path = 'default';
    private $redirect_url = null;
    private $meta_data = array();
    private $header_tag;
    private $title;
    private $external_js_list = array();
    private $external_css_list = array();
    private $js_list = array();
    private $css_list = array();
    private $js_code_list = array();
    private $css_code_list = array();
    private $layout_data = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setContentType($content_type)
    {
        $this->$content_type = $content_type;
    }

    public function setRedirect($url)
    {
        $this->redirect_url = $url;
    }

    public function getResponseCode()
    {
        return http_response_code();
    }

    public function setResponseCode($code)
    {
        http_response_code($code);
    }

    public function setView($view_path)
    {
        $this->view_path = $view_path;
    }

    public function setLayout($layout_path)
    {
        $this->layout_path = $layout_path;
    }

    public function setViewData($response)
    {
        $this->response_data = $response;
    }

    public function addViewData($key_or_array, $value = null)
    {
        if (!is_array($this->response_data)) {
            $this->response_data = array();
        }

        if (!isset($value)) {
            if (!is_array($key_or_array)) {
                return false;
            }
            $this->response_data = array_merge($key_or_array, $this->response_data);
        } else {
            $this->response_data[$key_or_array] = $value;
        }
    }

    public function getLayoutData()
    {
        return $this->layout_data;
    }

    public function setLayoutData($layout_data)
    {
        $this->layout_data = $layout_data;
    }

    public function addLayoutData($key_or_array, $value = null)
    {
        if (!is_array($this->layout_data)) {
            $this->layout_data = array();
        }

        if (!isset($value)) {
            if (!is_array($key_or_array)) {
                return false;
            }
            $this->layout_data = array_merge($key_or_array, $this->layout_data);
        } else {
            $this->layout_data[$key_or_array] = $value;
        }
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setMetaData(array $meta_data)
    {
        $this->meta_data = $meta_data;
    }

    public function addMetaData($name, $contents)
    {
        $this->meta_data[$name] = $contents;
    }

    public function setHtmlHeader($header_tag)
    {
        $this->header_tag = $header_tag;
    }

    public function addExternalJs($path)
    {
        $this->external_js_list[] = Javascript::wrapJsScriptTag($path, true);;
    }

    public function addExternalCss($path)
    {
        $this->external_css_list[] = Css::wrapCssLinkTag($path);
    }

    public function addJs($path)
    {
        $this->js_list[] = $path;
    }

    public function addCss($path)
    {
        $this->css_list[] = $path;
    }

    public function addJsCode($code)
    {
        $this->js_code_list[] = $code;
    }

    public function addCssCode($code)
    {
        $this->css_code_list[] = $code;
    }

    /**
     * @param Request $request
     * @throws SystemException
     */
    public function response($request)
    {
        if (!empty($this->redirect_url)) {
            $this->_redirect();
        } elseif (!empty($this->view_path)) {
            $this->_renderViewFile();
        } else {
            $controller_name = str_replace('\\middleware\\controller\\', '', $request->getControllerName());
            $controller_name = str_replace('\\controller\\', '', $controller_name);
            $controller_path = strtolower(str_replace('\\', '/', $controller_name));
            if ($request->getActionName() !== 'index') {
                $controller_path = $controller_path . '/' . $request->getActionName();
            }
            $this->setView($controller_path);
            try {
                $this->_renderViewFile();
            } catch (SystemException $e) {
                echo $this->response_data;
            }
        }
    }

    private function _redirect($http_code = '301')
    {
        header('Location: ' . $this->redirect_url, true, $http_code);
        $log_data = array('request_time_spent' => Time::getExecutionTime(), 'http_code' => $http_code, 'trace' => getBackTrace());
        Log::info('REDIRECT', $log_data);
        exit;
    }

    private function _renderViewFile()
    {
        $app_view_path = PATH_APP_VIEW . '/' . $this->view_path;
        $ret = $this->__renderViewFile($app_view_path);
        if ($ret === false) {
            if (\Config::$ENABLE_MIDDLEWARE === true) {
                $mw_view_path = PATH_MW_VIEW . '/' . $this->view_path;
                $ret = $this->__renderViewFile($mw_view_path);
            }
            if ($ret === false) {
                throw new SystemException('뷰 파일이 없습니다.', $this->view_path);
            }
        }
    }

    private function __renderViewFile($view_path)
    {
        if (is_file($view_path . '.php')) {
            $this->_renderPhp($view_path . '.php');
        } elseif (is_file($view_path . '.twig')) {
            $this->_renderTwig('content/' . $this->view_path . '.twig');
        } else {
            debug('view 파일을 찾을 수 없습니다. ' . $view_path);

            return false;
        }

        return true;
    }

    private function _renderTwig($path, $is_app_view = true)
    {
        $layout_data = $this->_buildLayoutData();

        View::getInstance()->renderTwig($path, array_merge((array)$this->response_data, $layout_data), true, $is_app_view);
    }

    private function _renderPhp($path)
    {
        $response_html = View::getInstance()->renderPhp($path, $this->response_data, false);

        if (empty($this->layout_path)) {
            $layout_data = $this->_buildLayoutData();
            $response_html = sprintf(
                "%s%s%s{$response_html}%s%s%s",
                $layout_data['external_css_link'],
                $layout_data['optimized_css_link'],
                $layout_data['optimized_css_inline'],
                $layout_data['external_js_link'],
                $layout_data['optimized_js_link'],
                $layout_data['optimized_js_inline']
            );
            echo $response_html;
        } else {
            $app_layout_path = PATH_APP_LAYOUT . '/' . $this->layout_path . '.php';
            if (is_file($app_layout_path)) {
                $path = $app_layout_path;
            } else {
                if (\Config::$ENABLE_MIDDLEWARE === true) {
                    $mw_layout_path = PATH_MW_LAYOUT . '/' . $this->layout_path . '.php';
                    if (is_file($mw_layout_path)) {
                        $path = $mw_layout_path;
                    } else {
                        throw new SystemException('레이아웃 파일이 없습니다.', $this->layout_path);
                    }
                } else {
                    throw new SystemException('레이아웃 파일이 없습니다.', $this->layout_path);
                }
            }

            $layout_data = $this->_buildLayoutData();
            $layout_data['view_contents'] = $response_html;
            View::getInstance()->renderPhp($path, $layout_data);
        }
    }

    private function _buildLayoutData()
    {
        $meta_tag = '';
        foreach ($this->meta_data as $name => $content) {
            $meta_tag .= sprintf('<meta name="%s" content="%s">', $name, $content);
        }

        $layout_data = array(
            'title'      => $this->title,
            'header_tag' => $this->header_tag,
            'meta_tag'   => $meta_tag,
        );

        if (\Config::$ENABLE_OPTIMIZE === true) {
            $optimized_js_link = Optimizer::optimizeJs($this->js_list);
            if (!empty($optimized_js_link)) {
                $optimized_js_link = Javascript::wrapJsScriptTag("/_optimizer?file_name={$optimized_js_link}&file_type=js", true);
            }
            $optimized_css_link = Optimizer::optimizeCss($this->css_list);
            if (!empty($optimized_css_link)) {
                $optimized_css_link = Css::wrapCssLinkTag("/_optimizer?file_name={$optimized_css_link}&file_type=css");
            }
        } else {
            //TODO:: 그냥 링크걸기
            $optimized_js_link = null;
            $optimized_css_link = null;
        }
        $optimized_js_inline = Javascript::wrapJsScriptTag(Optimizer::optimizeJsCode($this->js_code_list));
        $optimized_css_inline = Css::wrapStyleTag(Optimizer::optimizeCssCode($this->css_code_list));
        $external_js_link = implode(PHP_EOL, $this->external_js_list);
        $external_css_link = implode(PHP_EOL, $this->external_css_list);

        $layout_data = array_merge(
            $layout_data,
            compact(
                'optimized_js_link',
                'optimized_css_link',
                'optimized_js_inline',
                'optimized_css_inline',
                'external_js_link',
                'external_css_link'
            )
        );
        if (!empty($this->layout_data)) {
            $layout_data['layout_data'] = $this->layout_data;
        }

        return $layout_data;
    }
}
