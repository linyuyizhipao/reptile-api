<?php

require './vendor/autoload.php';





class ReptilePage{
    protected $client = '';
    protected $pdo = '';


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

    //获取所有分类格式化后的数据
    protected function getCateData()
    {
        $client = $this->client;

        $res = $client->request('POST', "http://i.cantonfair.org.cn/DataTransfer/Do", [
            'form_params'=>
                [
                    'strData' => 1,
                    'interfaceSet' => "AreaCategoryList",
                    'uri' => "http://i.cantonfair.org.cn/cn/SearchResultEmpty/Index",
                ]
        ]);

        $dataArr = json_decode($res->getBody(),true);

        $runData = isset($dataArr['ReturnData']) ? $dataArr['ReturnData'] : [];

        foreach ($runData as $k=>$v){
            if(is_array($v)){
                foreach ($v as $kk=>$vv){
                    $this->cateArr [] = $vv['CATEGORYNO'];

                }
            }
        }


    }


    protected function processor($cateNum){
        $PageIndex = 1;
        while (true){
            $res = $this->client->request('POST', "http://i.cantonfair.org.cn/DataTransfer/Do", [
                'form_params'=>
                    [
                        'strData' => '{"Keyword":"","CategoryNo":"'.$cateNum.'","StageOne":"0","StageTwo":"0","StageThree":"0","Export":"0","Import":"0","NewProduct":"0","CF":"0","ISBRIGHTSPOT":"0","PageIndex":"'.$PageIndex.'","PageSize":"15","Provinces":"","Countries":"","OrderBy":"1","Language":"1","NewExhibitor":"0","BrandsExhibitor":"0","ProduceExhibitor":"0","ForeignTradeExhibitor":"0","CFExhibitor":"0","OtherExhibitor":"0","OEMExhibitor":"0","ODMExhibitor":"0","OBMExhibitor":"0"}',
                        'interfaceSet' => "ProductListNew",
                        'uri' => "http://i.cantonfair.org.cn/cn/SearchResult/Index?QueryType=2&Keyword=&CategoryNo={$cateNum}&Export=0&Import=0&NewProduct=0&CF=0&OwnProduct=0&CategoryName=undefined&NewCompany=0&ShopWindow=undefined",
                    ]
            ]);

            $result = json_decode($res->getBody(),true)['ReturnData']['Products'];

            if(!empty($result)){
                foreach ($result as $kk=>$vv){
                    $companyName = $vv['Name'];
                    $rt = $cateNum.'-'.$companyName.'-'.$PageIndex;
                    var_dump($rt);

                    $companyProduct = $vv['Products'];
                    $this->insertCompanyName($companyName,$companyProduct);
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

    }


    public function insertCompanyName($name,$companyProduct){
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
        $sql = "INSERT INTO reptile_product(company_name,product_info) VALUES (:company_name,:product_info)";
        $ps = $pdo->prepare($sql);
        //数据绑定
        $ps->bindParam("company_name",$name);
        $ps->bindParam("product_info",$companyProductJson);

        $ps->execute();
    }

    public function start()
    {
        $this->getCateData();
        foreach ($this->cateArr as $k=>$v){
            $this->processor($v);
        }
    }

    //根据分类数据依次遍历组装url，并调取uri


}


$app = new ReptilePage();

$app->start();






