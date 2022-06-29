<?php

//*************************************************************************
//* namespaces
//*************************************************************************
namespace Controllers;



use Models\ParserModel;
use modules\ParserClass;

class ParserController {
 
    public $parserModelClass= null;

    public function __construct() {
        $this->Cors();
        $parserClass = new ParserClass('https://belwood.kz/catalog/mezhkomnatnye_dveri/?utm_source=yandex&utm_medium=cpc&utm_campaign=search_almaty&utm_term=магазин%20дверей&roistat=direct3_search_9433701298_магазин%20дверей&roistat_referrer=none&roistat_pos=premium_1&yadclid=88032016&yadordid=34135772&yclid=12857280838015123455');
        $this->loadModel();
       // $this->ParserAction();
        
    }
    
    //*************************************************************************
    //* Подключены заголовки для Cors
    //*************************************************************************

    public function Cors(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Credentials: true');
        header('Content-type: json/application');
        header('Content-Type: text/html; charset=utf-8');
    
    }

    public function loadModel() {
        $this->parserModelClass = new ParserModel();
    }

    public function ParserAction() {
        $this->parserModelClass->saveCardsInDb();
    }

}