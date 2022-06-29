<?php

//*************************************************************************
//* namespaces
//*************************************************************************

namespace Models;

use modules\DbParserConnectorClass;


class ParserModel {

    public $DbParser = null;

    public function __construct() {
        $this->DbParser = new DbParserConnectorClass;
        $this->connection();
        
    }

    public function connection(){
        $this->DbParser::createConnection();
    }

    public function saveCardsInDb(){
        $jsonTakeData = file_get_contents("./data/json/json_data.json");
        $arrDataCards = json_decode($jsonTakeData, true);

        foreach($arrDataCards as $cards){
            $this->DbParser->insertParserList($cards);
        }
    
    }


}