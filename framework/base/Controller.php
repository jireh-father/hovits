<?php
namespace framework\base;

use framework\core\Request;
use framework\core\Response;
use framework\library\Javascript;
use framework\library\Redirect;

abstract class Controller
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    protected function getRequest()
    {
        return $this->request;
    }

    protected function getResponse()
    {
        return $this->response;
    }

    protected function getControllerName()
    {
        return $this->request->getControllerName();
    }

    protected function getActionName()
    {
        return $this->request->getActionName();
    }

    protected function getParam($key, $default = null)
    {
        return $this->request->getParam($key, $default);
    }

    protected function getParams($keys = null, $replace_keys = null)
    {
        return $this->request->getParams($keys, $replace_keys);
    }

    protected function setContentType($content_type)
    {
        $this->response->setContentType($content_type);
    }

    protected function setResponseCode($code)
    {
        $this->response->setResponseCode($code);
    }

    protected function getResponseCode()
    {
        return $this->response->getResponseCode();
    }

    protected function setView($view_path, $view_data = null)
    {
        $this->response->setView($view_path);
        if (empty($view_data) === false) {
            $this->response->setViewData($view_data);
        }
    }

    protected function setLayout($layout_path)
    {
        $this->response->setLayout($layout_path);
    }

    protected function setViewData($view_data)
    {
        $this->response->setViewData($view_data);
    }

    protected function addViewData($key, $value = null)
    {
        $this->response->addViewData($key, $value);
    }

    protected function getLayoutData()
    {
        return $this->response->getLayoutData();
    }

    protected function setLayoutData($layout_data)
    {
        $this->response->setLayoutData($layout_data);
    }

    protected function addLayoutData($key, $value = null)
    {
        $this->response->addLayoutData($key, $value);
    }

    protected function setRedirect($url)
    {
        $this->response->setRedirect($url);
    }

    protected function addMetaData($name, $contents)
    {
        $this->response->addMetaData($name, $contents);
    }

    protected function setMetaData(array $meta_data)
    {
        $this->response->setMetaData($meta_data);
    }

    protected function setTitle($title)
    {
        $this->response->setTitle($title);
    }

    protected function setHtmlHeader($header_tag)
    {
        $this->response->setHtmlHeader($header_tag);
    }

    protected function addExternalJs($path)
    {
        $this->response->addExternalJs($path);
    }

    protected function addExternalCss($path)
    {
        $this->response->addExternalCss($path);
    }

    protected function addJs($path)
    {
        $this->response->addJs($path);
    }

    protected function addCss($path)
    {
        $this->response->addCss($path);
    }

    protected function addJsCode($code)
    {
        $this->response->addJsCode($code);
    }

    protected function addCssCode($code)
    {
        $this->response->addCssCode($code);
    }

    protected function getMethod()
    {
        return $this->request->getMethod();
    }

    public function redirect($url, $msg = null)
    {
        Redirect::redirect($url, $msg);
    }

    public function back($msg = null)
    {
        Redirect::back($msg);
    }

    public function __init(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function __before()
    {
    }

    public function __after()
    {
    }

    protected function ajaxFail($msg = null, $data = null)
    {
        $result = array('error' => true, 'message' => $msg, 'data' => $data);
        echo json_encode($result);
        exit;
    }

    protected function ajaxSuccess($msg = null, $data = null)
    {
        $result = array('error' => false, 'message' => $msg, 'data' => $data);
        echo json_encode($result);
        exit;
    }

    protected function ajax($result)
    {
        if (is_object($result)) {
            $result = (array)$result;
        }

        if (is_array($result)) {
            $result = json_encode($result);
        }

        echo $result;
        exit;
    }

    protected function validateParam($required_param)
    {
        if (empty($required_param)) {
            return null;
        }

        $value = $this->getParam($required_param);
        if (empty($value) === true) {
            $this->ajaxFail('필수 파라미터가 없습니다.', $required_param);
        } else {
            return $value;
        }
    }

    protected function validateParams($required_params, $is_assoc_return = false, array &$error_keys = array(), array &$relate_keys = array(), $is_first = true)
    {
        if (empty($required_params)) {
            return null;
        }

        $return_values = array();
        $param_keys = array();
        foreach ($required_params as $param_key => $param_default) {
            if (is_array($param_default)) {
                $return_values = array_merge($return_values, $this->validateParams($param_default, $is_assoc_return, $error_keys, $relate_keys, false));
            } elseif (is_string($param_key) && !is_numeric($param_key)) {
                $param_keys[] = $param_key;
                if (strlen($param_default) > 1 && $param_default[0] === '$') {
                    $relate_keys[$param_key] = $param_default;
                    $error_keys[] = $param_key;
                } else {
                    $return_values[$param_key] = $this->getParam($param_key, $param_default);
                }
            } else {
                $param_keys[] = $param_default;
                $value = $this->getParam($param_default);
                if (!isset($value) === true) {
                    $error_keys[] = $param_default;
                } else {
                    $return_values[$param_default] = $value;
                }
            }
        }

        if ($is_first && !empty($error_keys)) {
            $this->ajaxFail('필수 파라미터가 없습니다.', $error_keys);
        }

        if (!$is_first && !empty($error_keys)) {
            $error_cnt = 0;
            foreach ($param_keys as $param_key) {
                if (in_array($param_key, $error_keys)) {
                    $error_cnt++;
                }
            }
            if (count($required_params) !== $error_cnt) {
                foreach ($param_keys as $param_key) {
                    unset($error_keys[array_search($param_key, $error_keys)]);
                }
            }
        }

        if ($is_first && !empty($relate_keys)) {
            foreach ($relate_keys as $key => $relate_key) {
                $return_values[$key] = $return_values[substr($relate_key, 1)];
            }
        }

        if ($is_assoc_return === true || !$is_first) {
            return $return_values;
        } else {
            return array_values($return_values);
        }
    }
}
