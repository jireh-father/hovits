<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

$model = \middleware\model\MakingCountry::getInstance();
$countries = $model->getValues('country', null, null, null, null, 'country');
$country_model = \middleware\model\CountryName::getInstance();
foreach ($countries as $country_name) {
    $country_model->set(compact('country_name'), compact('country_name'));
}

