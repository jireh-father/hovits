<?php
namespace middleware\library;

use framework\library\String;
use QueryPath\DOMQuery;

class QpWrapper extends DOMQuery
{
    private static $is_auto_decode_utf8 = false;
    private static $cache_doms = array();

    public function onAutoDecodeUtf8()
    {
        self::$is_auto_decode_utf8 = true;
    }

    public function offAutoDecodeUtf8()
    {
        self::$is_auto_decode_utf8 = false;
    }

    /**
     * @param string $selector
     * @return QpWrapper
     */
    public function find($selector)
    {
        return parent::find($selector);
    }

    /**
     * @param $selector
     * @return QpWrapper[]
     */
    public function findAll($selector)
    {
        $list = array();
        foreach ($this->find($selector) as $item) {
            $list[] = $item;
        }

        return empty($list) ? null : $list;
    }

    /**
     * @return QpWrapper
     */
    public function first()
    {
        return parent::first();
    }

    /**
     * @return QpWrapper
     */
    public function firstChild()
    {
        return parent::firstChild();
    }

    /**
     * @param null $selector
     * @return QpWrapper
     */
    public function next($selector = null)
    {
        return parent::next($selector);
    }

    /**
     * @param null $selector
     * @return QpWrapper
     */
    public function nextAll($selector = null)
    {
        return parent::nextAll($selector);
    }

    /**
     * @param null $selector
     * @return QpWrapper
     */
    public function parent($selector = null)
    {
        return parent::parent($selector);
    }

    /**
     * @param null $selector
     * @return QpWrapper
     */
    public function parents($selector = null)
    {
        return parent::parents($selector);
    }

    public function getIterator()
    {
        $i = new QueryPathIteratorWrapper($this->matches);
        $i->options = $this->getOptions();

        return $i;
    }

    public function text($text = null, $strip_all_white_spaces = true)
    {
        $text = parent::text($text);

        return $this->_stripAllWhiteSpaces($text, $strip_all_white_spaces);
    }

    /**
     * @param null $markup
     * @return QpWrapper
     */
    public function html($markup = null)
    {
        $html = parent::html($markup);
        if (self::$is_auto_decode_utf8 === true) {
            $html = utf8_decode($html);
        }

        return $html;
    }

    public function attr($name = null, $value = null, $strip_all_white_spaces = true)
    {
        $attr = parent::attr($name, $value);

        return $this->_stripAllWhiteSpaces($attr, $strip_all_white_spaces);
    }

    public function exists()
    {
        return $this->count() > 0;
    }

    private function _stripAllWhiteSpaces($string, $strip_all_white_spaces)
    {
        if (self::$is_auto_decode_utf8 === true) {
            $string = utf8_decode($string);
        }
        if ($strip_all_white_spaces === true) {
            $string = String::stripAllWhiteSpaces($string);
        }

        return $string;
    }

    /**
     * @param null $document
     * @param null $selector
     * @param null $static_key
     * @param array $options
     * @return QpWrapper
     */
    public static function getInstance($document = null, $selector = null, $static_key = null, $options = array("convert_to_encoding" => "utf-8"))
    {
        if ($document instanceof QpWrapper) {
            return $document;
        }

        if (!empty($static_key)) {
            if (!empty($selector)) {
                $static_key .= "_{$selector}";
            }

            if (empty(self::$cache_doms[$static_key])) {
                self::$cache_doms[$static_key] = self::htmlqp($document, $selector, $options);
            }

            return self::$cache_doms[$static_key];
        }

        return self::htmlqp($document, $selector, $options);
    }

    public static function htmlqp($document = null, $selector = null, $options = array())
    {
        return self::withHTML($document, $selector, $options);
    }

    public static function withHTML($source = null, $selector = null, $options = array())
    {
        // Need a way to force an HTML parse instead of an XML parse when the
        // doctype is XHTML, since many XHTML documents are not valid XML
        // (because of coding errors, not by design).

        $options += array(
            'ignore_parser_warnings' => true,
            'convert_to_encoding'    => 'ISO-8859-1',
            'convert_from_encoding'  => 'auto',
            //            'replace_entities' => TRUE,
            'use_parser'             => 'html',
            // This is stripping actually necessary low ASCII.
            //            'strip_low_ascii' => TRUE,
        );

        return @self::with($source, $selector, $options);
    }

    public static function with($document = null, $selector = null, $options = array())
    {
        $qpClass = isset($options['QueryPath_class']) ? $options['QueryPath_class'] : '\QueryPath\DOMQuery';

        if ($qpClass == '\QueryPath\DOMQuery') {
            $qp = new self($document, $selector, $options);
        } else {
            $qp = new $qpClass($document, $selector, $options);
        }

        return $qp;
    }
}

class QueryPathIteratorWrapper extends \IteratorIterator
{
    public $options = array();
    private $qp = null;

    /**
     * @return QpWrapper
     */
    public function current()
    {
        if (!isset($this->qp)) {
            $this->qp = QpWrapper::with(parent::current(), null, $this->options);
        } else {
            $splos = new \SplObjectStorage();
            $splos->attach(parent::current());
            $this->qp->setMatches($splos);
        }

        return $this->qp;
    }
}
