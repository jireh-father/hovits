<?php
function setUnlimitTimeout()
{
    ini_set('mysql.connect_timeout', -1);
    ini_set('default_socket_timeout', -1);
    ini_set('max_execution_time', -1);
    ini_set('memory_limit', -1);
}

function mergeArray($array1, $array2 = null, $_ = null)
{
    $arg_cnt = func_num_args();
    if ($arg_cnt < 2) {
        return null;
    }
    $args = func_get_args();
    $merged_array = array();
    foreach ($args as $arg) {
        if (!is_array($arg)) {
            $arg = (array)$arg;
        }
        $merged_array = array_merge($merged_array, $arg);
    }

    return $merged_array;
}

function pr($value, $key = null, $is_print = true)
{
    if (is_object($value)) {
        $value = (array)$value;
    }

    if (is_array($value)) {
        if (empty($key)) {
            $value = null;
        } else {
            if (is_array($key)) {
                foreach ($key as $k) {
                    if (!isset($value[$k])) {
                        $value = null;
                        break;
                    }
                    $value = $value[$k];
                }
            } else {
                if (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    $value = null;
                }
            }
        }
    }

    if (isset($value)) {
        if ($is_print) {
            echo $value;
        } else {
            return $value;
        }
    }
}

function get($value, $key)
{
    return pr($value, $key, false);
}

function getShortMethodName($method_name)
{
    $method_name = explode('::', $method_name);
    if ($method_name > 1) {
        return $method_name[1];
    } else {
        return $method_name[0];
    }
}

function toAssoc($arr)
{
    return \framework\library\ArrayUtil::toAssoc($arr);
}

function joins($glue, $string1, $string2 = null, $_ = null)
{
    $arg_cnt = func_num_args();
    if ($arg_cnt < 2) {
        return null;
    }
    $args = func_get_args();

    return implode($glue, $args);
}

class Value
{
    private static $value_container = array();
    private static $current_key;

    public static function setValueContainer($value, $key)
    {
        if (empty($value) || empty($key)) {
            return false;
        }

        if (!is_array($value)) {
            $value = (array)$value;
        }

        self::$value_container[$key] = $value;
        self::$current_key = $key;

        return true;
    }

    public static function setContainerKey($key)
    {
        if (empty($key)) {
            return false;
        }
        self::$current_key = $key;
    }

    public static function get($key, $container_key = null)
    {
        if (empty($key)) {
            return null;
        }
        if (empty($container_key)) {
            $container_key = self::$current_key;
        }

        if (isset(self::$value_container[$container_key])) {
            return get(self::$value_container[$container_key], $key);
        }

        return null;
    }

    public static function pr($key, $container_key = null)
    {
        if (empty($key)) {
            return;
        }
        if (empty($container_key)) {
            $container_key = self::$current_key;
        }

        if (isset(self::$value_container[$container_key])) {
            pr(self::$value_container[$container_key], $key);
        }
    }
}

class HtmlTag
{
    public static function tag($tag_name, $attributes = null, $body = null, $has_close_tag = true, $is_new_line = false)
    {
        if (empty($tag_name)) {
            return null;
        }

        $new_line = $is_new_line ? PHP_EOL : '';

        $tag = "<{$tag_name}";
        $attr_string = null;
        if (!empty($attributes)) {
            if (is_array($attributes)) {
                $attr_list = array();
                foreach ($attributes as $attr_key => $attr_val) {
                    $attr_list[] = "{$attr_key}=\"{$attr_val}\"";
                }
                $attr_string = implode(' ', $attr_list);
            } else if (is_string($attributes)) {
                $attr_string = $attributes;
            }
        }

        $tag .= " {$attr_string}";

        if (!empty($body)) {
            $tag .= ">{$new_line}{$body}{$new_line}</{$tag_name}>";
        } else {
            if ($has_close_tag) {
                $tag .= "></{$tag_name}>";
            } else {
                $tag .= "/>";
            }
        }

        return $tag;
    }

    public static function select($option_data, $attributes = null, $selected_value = null, $default_value = null)
    {
        $options = '';
        if (!empty($default_value)) {
            if (is_array($default_value)) {
                $key = key($default_value);
                $options = self::option($key, $default_value[$key], $selected_value);
            } elseif (is_string($default_value)) {
                $options = self::option('', $default_value, $selected_value);
            }
            $options .= PHP_EOL;
        }
        $options .= self::options($option_data, $selected_value);

        return self::tag('select', $attributes, $options, true, true);
    }

    public static function options($data, $selected_value = null)
    {
        if (empty($data)) {
            return null;
        }

        $option_list = array();
        foreach ($data as $key => $val) {
            $option_list[] = self::option($key, $val, $selected_value);
        }

        return implode(PHP_EOL, $option_list);
    }

    public static function option($key, $value, $selected_value = null, array $attributes = array())
    {
        $attributes['value'] = $key;
        if ($key === $selected_value) {
            $attributes['selected'] = 'selected';
        }

        return self::tag('option', $attributes, $value);
    }

    public static function attributes($attr_list = null)
    {
        if (is_string($attr_list)) {
            return $attr_list;
        } elseif (is_array($attr_list)) {
            $attribute_list = array();
            foreach ($attr_list as $key => $val) {
                $attribute_list[] = "{$key}={$val}";
            }

            return implode(' ', $attribute_list);
        }

        return null;
    }
}

function getImageUri($image, $image_size_var)
{
    return \middleware\service\contents\thumb\ThumbMaker::getImageUri($image, $image_size_var);
}

function isMobile()
{
    $useragent = $_SERVER['HTTP_USER_AGENT'];

    return preg_match(
        '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',
        $useragent
    ) || preg_match(
        '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
        substr($useragent, 0, 4)
    );
}

function getUri()
{
    $uri = $_SERVER['PHP_SELF'];
    if (empty($uri)) {
        return null;
    }

    $uri = \framework\library\String::stripMultiSlash($uri);
    if (strlen($uri) > 1 && $uri[strlen($uri) - 1] === '/') {
        $uri = \framework\library\String::cutTail($uri, 1);
    }

    return $uri;
}

function createSalt()
{
    return bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
}