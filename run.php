<?php

require './vendor/autoload.php';

require './companyBaseData.php';
require './companyDetail.php';

$time = date("Y-m-d H:i:s",time());
var_dump($time);

$app = new ReptilePage();
$app->start();

$app2 = new CompanyDetail();
$app2->start();

echo '爬去完毕，爬去完毕，爬去完毕';






