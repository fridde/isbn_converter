<?php
	
	/* PREAMBLE */
    $url = "https://raw.githubusercontent.com/fridde/friddes_php_functions/master/include.php";
    $filename = "include.php";
    copy($url, $filename);
    include $filename;
	/* END OF PREAMBLE */
	inc("fnc");
	error_reporting(E_ALL & ~E_NOTICE);
	
	$ini_array = parse_ini_file("config.ini", TRUE);
	list($html, $head, $body, $form, $h1, $ul) = array_fill(0,20,""); 
	
	$head .= qtag("meta");
	
	if(isset($_REQUEST["isbn"])){
		$isbnArray = explode(PHP_EOL, $_REQUEST["isbn"]);
		$resultArray = array();
		$isbnNotFound = array();
		foreach($isbnArray as $isbn){
			$found = FALSE;
			$isbn = trim($isbn);
			$thisBook = array();
			
			foreach($ini_array["adresslist"] as $domain => $adress){
				//echo $isbn . " @ " . $adress . "<br>";
				$apiKey = (isset($ini_array["api_keys"][$domain]) ? $ini_array["api_keys"][$domain] : "");
				$adress = str_replace("[API-KEY]", $apiKey, $adress);
				$adress = str_replace("[ISBN]", $isbn, $adress);
				
				$jsonString = file_get_contents($adress);
				$searchResult = json_decode($jsonString, TRUE);
				
				switch($domain){
					case "isbndb":
					$found = !isset($searchResult["error"]);
					if($found){
					$searchResult = $searchResult["data"][0];
						$thisBook["isbn"] = $isbn;
						$thisBook["title"] = $searchResult["title"];
						$thisBook["author"] = $searchResult["author_data"][0]["name"];
						$thisBook["publisher"] = $searchResult["publisher_name"];
						$thisBook["comment"] = $searchResult["edition_info"];
						$thisBook[] = "<br>";
					}
					break;
					
					case "google":
					$found = $searchResult["totalItems"] > 0;
					if($found){
						$searchResult = $searchResult["items"][0];
						$thisBook["isbn"] = $isbn;
						$thisBook["title"] = $searchResult["volumeInfo"]["title"];
						$thisBook["author"] = $searchResult["volumeInfo"]["authors"]["0"];
						$thisBook["publisher"] = $searchResult["publisher"];
						$thisBook["comment"] = $searchResult["volumeInfo"]["publishedDate"];
						$thisBook[] = "<br>";
					}
					break;
					
				}
				
				if($found){
					break;
				}
			}
			if($found){
				$resultArray[] = $thisBook;
			} 
			else {
				$isbnNotFound[] = $isbn;
			}
		}
		$body .= tag("h1", "Results");
		$body .= tag("p", array_to_csv($resultArray));
		$body .= tag("h1", "Not found");
		$body .= tag("p", implode("<br>", $isbnNotFound));
		
	} 
	else {
		$form .= tag("input", "", array("type" => "submit", "value" => "Convert"));
		$form .= tag("textarea", "", array("name" => "isbn", "rows" => "20", "cols" => "20"));
		$body .= tag("form", $form, array("action" => "", "method" => "post"));
		
	}
	$html .= tag("head", $head);
	$html .= tag("body", $body);
echo $html;