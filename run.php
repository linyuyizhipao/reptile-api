<?php

require './vendor/autoload.php';

require './companyBaseData.php';
require './companyDetail.php';



$app = new ReptilePage();
$app->start();

$app2 = new CompanyDetail();
$app->start();






