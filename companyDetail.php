<?php

class CompanyDetail{
    protected $RESERVE_TABLE_NAME = 'reptile_product';
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

            $date = date("Ymd",time());
            $this->RESERVE_TABLE_NAME = $this->RESERVE_TABLE_NAME.$date;

        } catch (PDOException $exception) {
            echo "Connection error message: " . $exception->getMessage();
            die;
        }

    }



    protected function processor($ExhibitorID,$CorpType){

        $url = 'http://i.cantonfair.org.cn/DataTransfer/Do';
        $setData = <<<setd
{"ExhibitorID":"{$ExhibitorID}","IsCN":"1","IsAD":"0","CorpType":"{$CorpType}"}
setd;
        $interfaceSet = <<<sdfd
ExhibitorDetail
sdfd;

    $uri = <<<dsgf
http://i.cantonfair.org.cn/cn/Company/Index?corpid={$ExhibitorID}&corptype={$CorpType}&ad=0#
dsgf;

        try{

            while (true){
                $res = $this->client->request('POST', $url, [
                    'form_params'=>
                        [
                            'strData' => $setData,
                            'interfaceSet' => $interfaceSet,
                            'uri' => $uri,
                        ]
                ]);


                $resultDejson = json_decode($res->getBody(),true);

                if(!isset($resultDejson['ReturnData'])){
                    var_dump($resultDejson);
                    sleep(10);
                    continue;
                }


                $result = $resultDejson['ReturnData'];

                if(!empty($result)){
                    $companyName = $result['ExhibitorName'];
                    $this->updateCompanyDetail($companyName,$result);
                    return;

                }

                throw  new Exception("数据异常");

            }

        }catch (Exception $e){
            var_dump($e->getMessage(),8888888);
            sleep(5);
            try{
                while (true){
                    $res = $this->client->request('POST', $url, [
                        'form_params'=>
                            [
                                'strData' => $setData,
                                'interfaceSet' => $interfaceSet,
                                'uri' => $uri,
                            ]
                    ]);


                    $resultDejson = json_decode($res->getBody(),true);

                    if(!isset($resultDejson['ReturnData'])){
                        var_dump($resultDejson);
                        sleep(10);
                        continue;
                    }


                    $result = $resultDejson['ReturnData'];

                    if(!empty($result)){
                        $companyName = $result['ExhibitorName'];
                        $this->updateCompanyDetail($companyName,$result);
                        return;

                    }

                    throw  new Exception("数据异常");

                }
            }catch (Exception $exception){
                var_dump($e->getMessage(),777777);
                sleep(5);
                while (true){
                    $res = $this->client->request('POST', $url, [
                        'form_params'=>
                            [
                                'strData' => $setData,
                                'interfaceSet' => $interfaceSet,
                                'uri' => $uri,
                            ]
                    ]);


                    $resultDejson = json_decode($res->getBody(),true);

                    if(!isset($resultDejson['ReturnData'])){
                        var_dump($resultDejson);
                        sleep(10);
                        continue;
                    }


                    $result = $resultDejson['ReturnData'];

                    if(!empty($result)){
                        $companyName = $result['ExhibitorName'];
                        $this->updateCompanyDetail($companyName,$result);
                        return;

                    }

                    throw  new Exception("数据异常");

                }
            }

        }


    }


    public function updateCompanyDetail($name,$result){
        $pdo = $this->pdo;

        $resultJson = json_encode($result,JSON_UNESCAPED_UNICODE);
        //使用PDO中的方法执行SQL语句
        $sql = "update {$this->RESERVE_TABLE_NAME} set company_detail = :company_detail where company_name = :company_name";
        $ps = $pdo->prepare($sql);
        //数据绑定
        $ps->bindParam("company_name",$name);
        $ps->bindParam("company_detail",$resultJson);

        $ps->execute();
    }

    public function start()
    {
        $id = null;
        for($i=1;$i<100;$i++){
            var_dump($i);
            $pdo = $this->pdo;
            if($id){
                $sql = "SELECT * FROM {$this->RESERVE_TABLE_NAME} where id > {$id} order by id ASC limit 500 ";
            }else{
                $sql = "SELECT * FROM {$this->RESERVE_TABLE_NAME} order by id ASC limit 500 ";
            }
            $ps = $pdo->prepare($sql);
            //执行SQL语句
            $ps->execute();

            while ($res = $ps -> fetch(PDO::FETCH_ASSOC)){
                $productInfo = json_decode($res['product_info'],true);
                $ExhibitorID = $productInfo['ExhibitorID'];
                $CorpType = $productInfo['CorpType'];
                $this->processor($ExhibitorID,$CorpType);
                $id = $res['id'];
            }
        }





    }
}






