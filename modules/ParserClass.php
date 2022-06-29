<?php

namespace modules;

use \phpQuery;

class ParserClass {

    public $siteURL = null;
    protected $dataCards = [];
    protected $cardsDataList = [];

    public function __construct(string $url) {
        $this->errorDetector();
        $this->siteURL = $url;
        $this->parserStarter();
    
    }

    public function errorDetector(){
        setlocale(LC_ALL, 'ru_RU.UTF-8');
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

    }

    private function parserStarter() {
      $this->parserCards($this->ParserSiteRequest($this->siteURL));
    }



    private function ParserSiteRequest(string $url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        /*$info = curl_getinfo($curl);
        print_r($info);*/
        $result = curl_exec($curl);
        return $result;
    }

    private function parserCards($result){
    
        if($result == false) {     
            echo "Ошибка CURL: " . curl_error($curl);
            return false;
        }else {
            $pq = phpQuery::newDocument('<meta charset="utf-8">' . $result); 
            $listlinks = $pq->find('.catalog-item .catalog-item__title-container a');
        
            foreach($listlinks as $link){
                $this->dataCards[] = pq($link)->attr('href');
            }

            /* получаем изображения */
            $listImages = $pq->find("a.js-varaint-image");
            foreach($listImages as $image) {
                $elemImage = pq($image);
                $arrListImages[] = $elemImage->attr("data-image");
            }

                
            foreach($this->dataCards as $dataCardlink){
                $resultCard = $this->ParserSiteRequest('https://belwood.kz'. $dataCardlink);
                $pq = phpQuery::newDocument('<meta charset="utf-8">' . $resultCard); 
                
                $this->cardsDataList[] = [
                    "name" => $pq->find('.product-top__title')->text(),
                    "price"=> intval(preg_replace('/[^0-9]/', '', $pq->find('.total-price')->text())),
                   // "oldprice" =>intval(preg_replace('/[^0-9]/', '', $pq->find("#old-price-field")->text())),
                   // "currencyId" => 'RUR',
                   // "categoryId" => '21',
                   // "store" => 'false',
                   //"pickup" => 'true',
                   // "delivery" => 'true',
                   // "vendor" => 'Elektronika',
                    "url" => "https://belwood.kz". $dataCardlink,
                    "description" => $pq->find('.product-info__text p')->text()
                ];

            }

            
            $this->saving();
           
        }

    }


    private function saving(){
        $this->jsonSave();
        $this->TXTSave();
        $this->preFormat($this->cardsDataList);
    }



    private function jsonSave(){
        $jsonData = json_encode($this->cardsDataList, JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);
        file_put_contents("./data/json/json_data.json", $jsonData);

    }


    private function TXTSave(){
        $jsonData = json_encode($this->cardsDataList, JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);
        file_put_contents("./data/txt/parsedData.txt", $jsonData);

    }


    private function preFormat($arratOfValues){
        $arrayLength = count($arratOfValues);
        $numTitle = 'Карточка';
        $resArray = [];
        for($i = 0 ; $i < $arrayLength; $i++ ){
            $resArray[] =  $numTitle . $i;      
        }
        print_r($resArray);
    }

    

    /**
     * Создает CSV файл из переданных в массиве данных.
     *
     * @param array  $create_data   Массив данных из которых нужно созать CSV файл.
     * @param string $file          Путь до файла 'path/to/test.csv'. Если не указать, то просто вернет результат.
     * @param string $col_delimiter Разделитель колонок. Default: `;`.
     * @param string $row_delimiter Разделитель рядов. Default: `\r\n`.
     *
     * @return false|string CSV строку или false, если не удалось создать файл.
     *
     * @version 2
     * 
     * Формат Array
     * $create_data = array(
     *       array(
     *          'Заголовок 1',
     *          'Заголовок 2',
     *           'Заголовок 3',
     *      ),
     *      array(
     *           'строка 2 "столбец 1"',
     *           '4799,01',
     *          'строка 2 "столбец 3"',
     *      ),
     *      array(
     *           '"Ёлочки"',
     *           4900.01,
     *          'красный, зелёный',
     *      )
     * );
     */
    
    private  function kama_create_csv_file( $create_data, $file = null, $col_delimiter = ';', $row_delimiter = "\r\n" ){

        if( ! is_array( $create_data ) ){
            return false;
        }

        if( $file && ! is_dir( dirname( $file ) ) ){
            return false;
        }

        // строка, которая будет записана в csv файл
        $CSV_str = '';

        // перебираем все данные
        foreach( $create_data as $row ){
            $cols = array();

            foreach( $row as $col_val ){
                // строки должны быть в кавычках ""
                // кавычки " внутри строк нужно предварить такой же кавычкой "
                if( $col_val && preg_match('/[",;\r\n]/', $col_val) ){
                    // поправим перенос строки
                    if( $row_delimiter === "\r\n" ){
                        $col_val = str_replace( [ "\r\n", "\r" ], [ '\n', '' ], $col_val );
                    }
                    elseif( $row_delimiter === "\n" ){
                        $col_val = str_replace( [ "\n", "\r\r" ], '\r', $col_val );
                    }

                    $col_val = str_replace( '"', '""', $col_val ); // предваряем "
                    $col_val = '"'. $col_val .'"'; // обрамляем в "
                }

                $cols[] = $col_val; // добавляем колонку в данные
            }

            $CSV_str .= implode( $col_delimiter, $cols ) . $row_delimiter; // добавляем строку в данные
        }

        $CSV_str = rtrim( $CSV_str, $row_delimiter );

        // задаем кодировку windows-1251 для строки
        if( $file ){
            $CSV_str = iconv( "UTF-8", "cp1251",  $CSV_str );

            // создаем csv файл и записываем в него строку
            $done = file_put_contents( $file, $CSV_str );

            return $done ? $CSV_str : false;
        }

        return $CSV_str;

    }


}








/* получаем дополнительные параметры для  */
/*
$listParamsProduct = $pq->find(".product-properties p");
foreach($listParamsProduct as $param) {
	$elemParam = pq($param);
	$arrElemParam = explode(":", $elemParam->text());

	if(count($arrElemParam) > 1) {
		$arrDopParams[] = [
			"name" => trim($arrElemParam[0]),
			"value" => trim($arrElemParam[1])
		];
	}
}




$offers = [
	[
		'id' => '123',
		'listImages' => $arrListImages,
		'listMainParams' => $arrMainParams,
		'listDopParams' => $arrDopParams
	]
];





// Создаём XML-документ
$dom = new DOMDocument('1.0', 'utf-8');
// Создаём корневой элемент <offers>
$root = $dom->createElement('offers');
$dom->appendChild($root);


foreach($offers as $valueParam) {

	// Создаём узел <offer>
	$offer = $dom->createElement('offer');

	// Добавляем дочерний элемент для <offers>
	$root->appendChild($offer);

	// Устанавливаем атрибут id для узла <offer>
	$offer->setAttribute('id', $valueParam['id']);

	foreach($valueParam["listMainParams"] as $key=>$val) {
		$params = $dom->createElement($key, $val);
		$offer->appendChild($params);
	}

	foreach($valueParam["listImages"] as $image) {
		$params = $dom->createElement("picture", $image);
		$offer->appendChild($params);
	}

	foreach($valueParam["listDopParams"] as $dopParam) {
		$params = $dom->createElement("param", $dopParam["value"]);
		$params->setAttribute('name', $dopParam["name"]);
		$offer->appendChild($params);
	}

}

// Сохраняем полученный XML-документ в файл
$dom->save('offers.xml');

*/