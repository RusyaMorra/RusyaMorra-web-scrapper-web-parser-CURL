<?php

namespace modules;

use Krugozor\Database\Mysql;

class DbParserConnectorClass {

  static private $_instance = null;
  static public $db = null;

  static public function createConnection(){
    self::$db = Mysql::create("localhost", "root", "")->setDatabaseName("parser")->setCharset("utf8");
    
  }

    /**
     *  singleton
     */

  static public function getInstance(){
    if(self::$_instance == null){
      return self::$_instance = new self;
        
    }

    return self::$_instance;
  }

  public function insertParserList($cards){
  
    self::$db->query('INSERT INTO `cards` SET ?As', $cards);
  }


}
