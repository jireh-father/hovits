<?php
namespace controller;

use framework\base\Controller;
use framework\library\String;
use middleware\library\Menu;
use middleware\model\MovieMatchChoice;
use service\User;

class Hovits extends Controller
{
    public function __before()
    {
        $request_uri = getUri();

        if (User::isLogin()) {
            $user_pk = User::getUserPk();
            $match_choice_model = MovieMatchChoice::getInstance();
            $exists_choice = $match_choice_model->exist(compact('user_pk'));
            if (!$exists_choice && !String::has($request_uri, '/tutorial')) {
                $this->redirect('/tutorial', '취향 분석 페이지로 이동합니다.');
            } elseif ($exists_choice && String::has($request_uri, '/tutorial')) {
                //                $this->redirect('/', '이미 취향 분석을 끝내셨습니다.');
            }
        }

        $this->setLayout('hovits');
        $this->addExternalCss('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css');
        $this->addExternalCss('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css');
        $this->addCss('hovits');
        $this->addExternalJs('//code.jquery.com/jquery-1.11.2.min.js');
        $this->addExternalJs('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js');
        $this->addCss('hovits');
        $this->addJs('/jquery_lazyload/jquery.lazyload.min');
        $this->addJs('hovits');
        $this->addJsCode(
            "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
              (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
              m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
              })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
              ga('create', 'UA-71509538-1', 'auto');
              ga('send', 'pageview');"
        );

        $menu_list = Menu::getMenuList(false, 1);

        $this->setTitle('호비츠');

        if (dirname($request_uri) === '/' || dirname($request_uri) === '\\') {
            $top_uri = $request_uri;
        } else {
            $top_uri = dirname($request_uri);
        }

        $this->setLayoutData(array('menu_list' => $menu_list, 'uri' => $request_uri, 'top_uri' => $top_uri));
    }

    protected function _checkLogin()
    {
        if (!User::isLogin()) {
            $this->redirect('/user/login');
        }
    }

    protected function _addJsDefault($use_fadein = true)
    {
        if (isMobile()) {
            $this->_addJsLazyImage($use_fadein);
        } else {
            $this->_addJsLazyImage($use_fadein);
            $this->_addJsToolTip();
        }
    }

    protected function _addJsLazyImage($use_fadein = true)
    {
        if ($use_fadein) {
            $this->addJsCode(
                '$(function(){
                    $(".lazy-image").lazyload(
                        {effect : "fadeIn"}
                    );
                })'
            );
        } else {
            $this->addJsCode(
                '$(function(){
                    $(".lazy-image").lazyload();
                })'
            );
        }
    }

    protected function _addJsToolTip()
    {
        $this->addJsCode(
            '$(function(){
                $(\'[data-toggle="tooltip"]\').tooltip();
            })'
        );
    }
}