<?php

require './vendor/autoload.php';





class ReptilePage{
    protected $client = '';
    protected $pdo = '';
    protected $startIndex = 1;


    protected $cateArr = [];

    public function __construct()
    {
        $this->client = new GuzzleHttp\Client();

        try {
            $this->pdo = new PDO('mysql:host=10.5.110.241:3307;dbname=test','xhxy','ts123456');
            $this->pdo->exec("SET names utf8");

        } catch (PDOException $exception) {
            echo "Connection error message: " . $exception->getMessage();
            die;
        }

    }



    protected function processor($arr){
        $StageOne = $arr[0];
        $StageTwo = $arr[1];
        $StagThree = $arr[2];


        $PageIndex = 1;


        try{

            while (true){
                $res = $this->client->request('POST', "http://i.cantonfair.org.cn/DataTransfer/Do", [
                    'form_params'=>
                        [
                            'strData' => '{"QueryType":"1","Keyword":"","CategoryNo":"","StageOne":"'.$StageOne.'","StageTwo":"'.$StageTwo.'","StageThree":"'.$StagThree.'","Export":"0","Import":"0","PageIndex":"'.$PageIndex.'","PageSize":"15","Provinces":"","Countries":"","OrderBy":"1","Language":"1","NewExhibitor":"0","BrandsExhibitor":"0","ProduceExhibitor":"0","ForeignTradeExhibitor":"0","CFExhibitor":"0","OtherExhibitor":"0","OEMExhibitor":"0","ODMExhibitor":"0","OBMExhibitor":"0"}',
                            'interfaceSet' => "ExhibitorListNew",
                            'uri' => "http://i.cantonfair.org.cn/cn/SearchResult/Index?QueryType=1&KeyWord=&CategoryNo=&StageOne={$StageOne}&StageTwo={$StageTwo}&StageThree={$StagThree}&Export=0&Import=0&Provinces=&Countries=&ShowMode=1&NewProduct=0&CF=0&OwnProduct=0&PayMode=&NewCompany=0&BrandCompany=0&ForeignTradeCompany=0&ManufacturCompany=0&CFCompany=0&OtherCompany=0&OEM=0&ODM=0&OBM=0&OrderBy=1&producttab=2",
                        ]
                ]);


                $resultDejson = json_decode($res->getBody(),true);

                if(!isset($resultDejson['ReturnData']) || !isset($resultDejson['ReturnData']['Exhibitors'])){
                    var_dump($resultDejson);
                    sleep(10);
                    continue;
                }


                $result = $resultDejson['ReturnData']['Exhibitors'];

                if(!empty($result)){
                    foreach ($result as $kk=>$vv){
                        $companyName = $vv['Name'];
                        $rt = $StageOne.'-'.$StageTwo.'-'.$StagThree.'-'.$companyName.'-'.$PageIndex;
                        $gps = $StageOne.'-'.$StageTwo.'-'.$StagThree.'-'.$PageIndex;
                        var_dump($rt);

                        $companyProduct = $vv;
                        $this->insertCompanyName($companyName,$companyProduct,$gps);
                    }
                }else{
                    var_dump($result);
                    return;
                }

                if($PageIndex > 100000){
                    var_dump('最大page错误');
                    die;
                }
                $PageIndex++;

            }

        }catch (Exception $e){
            var_dump($e->getMessage(),999999999999);
            return;
        }


    }


    public function insertCompanyName($name,$companyProduct,$gps){
        $pdo = $this->pdo;
//        $sql = "SELECT id FROM ts_company where company_name=:company_name";
//        $ps = $pdo->prepare($sql);
//        $ps->bindParam("company_name",$name);
//        //执行SQL语句
//        $ps->execute();
//
//        $r = $ps -> fetch();
//
//        if(!empty($r)){
//            return;
//        }



        $companyProductJson = json_encode($companyProduct,JSON_UNESCAPED_UNICODE);
        //使用PDO中的方法执行SQL语句
        $sql = "INSERT INTO reptile_product(company_name,product_info,gps) VALUES (:company_name,:product_info,:gps)";
        $ps = $pdo->prepare($sql);
        //数据绑定
        $ps->bindParam("company_name",$name);
        $ps->bindParam("product_info",$companyProductJson);
        $ps->bindParam("gps",$gps);

        $ps->execute();
    }

    public function start()
    {
        $arr = [[1,0,0],[0,1,0],[0,0,1]];

        $pdo = $this->pdo;
        $sql = "SELECT gps FROM reptile_product order by id desc";
        $ps = $pdo->prepare($sql);
        $ps->bindParam("company_name",$name);
        //执行SQL语句
        $ps->execute();

        $gpsInfo = $ps -> fetch();
        if(!empty($gpsInfo['gps'])){
            $this->startIndex = explode($gpsInfo['gps'],'-')[3];
            switch ($gpsInfo['gps']){
                case '1-0-0':
                    $arr = [[1,0,0],[0,1,0],[0,0,1]];
                    break;
                case '0-1-0':
                    $arr = [[0,1,0],[0,0,1]];
                    break;
                case '0-0-1':
                    $arr = [[0,0,1]];
                    break;
            }

        }


        foreach ($arr as $k=>$ar){
            $this->processor($ar);
        }

    }

    //根据分类数据依次遍历组装url，并调取uri


}


$app = new ReptilePage();

$app->start();






