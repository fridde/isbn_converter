<?php
	
	/* PREAMBLE */
    $url = "https://raw.githubusercontent.com/fridde/friddes_php_functions/master/include.php";
    $filename = "include.php";
    copy($url, $filename);
    include $filename;
	/* END OF PREAMBLE */
	inc("fnc");
	
	$ini_array = parse_ini_file("config.ini", TRUE);
	list($html, $head, $body, $form, $h1, $ul) = array_fill(0,20,""); 
	
	
	//http://isbndb.com/api/v2/json/CONXBUAW/book/978382184888X
	//https://www.googleapis.com/books/v1/volumes?q=isbn:9783893924028
	
	if(isset($_REQUEST["isbn"])){
		$isbnArray = explode(PHP_EOL, $_REQUEST["isbn"]);
		$resultArray = array();
		$isbnNotFound = array();
		foreach($isbnArray as $isbn){
			$found = FALSE;
			$isbn = trim($isbn);
			
			foreach($ini_array["adresslist"] as $domain => $adress){
				$apiKey = (isset($ini_array["api_keys"][$domain]) ? $ini_array["api_keys"][$domain] : "");
				$adress = str_replace("[API-KEY]", $apiKey, $adress);
				$adress = str_replace("[ISBN]", $isbn, $adress);
				
				$jsonString = file_get_contents($adress);
				$searchResult = json_decode($jsonString, TRUE);
				
				switch($domain){
					case "isdbn":
					$found = !isset($searchResult["error"]);
					break;
					
					case "google":
					$found = $searchResult["totalItems"] > 0;
					break;
					
				}
				
				if($found){
					break;
				}
			}
			
			echo print_r($searchResult) . "<br><br><br>";
		}
		
		} else {
		$form .= tag("input", "", array("type" => "submit", "value" => "Convert"));
		$form .= tag("textarea", "", array("name" => "isbn", "rows" => "20", "cols" => "20"));
		$body .= tag("form", $form, array("action" => "", "method" => "post"));
		
	}
	$html .= tag("head", $head);
	$html .= tag("body", $body);
echo $html;