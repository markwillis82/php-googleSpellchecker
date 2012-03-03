<?php
/*
 * originally from: http://code.google.com/p/jquery-spellchecker/
 * modified to only use google and acept GET or POST
 */
//$_GET["txt"] = "bunisess"; // cli testing
if(isset($_GET["txt"])) {
	$txt = $_GET["txt"];
} else {
	$txt = $_GET["txt"];
}

$spell = new spell($txt);

class spell {

	private $text;

	// default language
	protected $lang = 'en';

	public function __construct($text) {
		$this->text = $text;
		$this->spellText();
	}

	private function spellText() {


		// return badly spelt words from a chunk of text
		if (isset($this->text)) {
			$bad_words = array();
			foreach($matches = $this->checkGoogle($this->text) as $word) {
				// position & length of badly spelt word
				$old_word = substr($this->text, $word[1], $word[2]);
				$bad_words[$old_word] = explode("\t",$word[4]);


			}
			exit(json_encode($bad_words));
		}

	}

	private function checkGoogle($str) {
		$url = 'https://www.google.com';
		$path = '/tbproxy/spell?lang='.$this->lang.'&hl=en';

		// setup XML request
		$xml = '<?xml version="1.0" encoding="utf-8" ?>';
		$xml .= '<spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1">';
		$xml .= '<text>'.$str.'</text></spellrequest>';

		// setup headers to be sent
		$header  = "POST {$path} HTTP/1.0 \r\n";
		$header .= "MIME-Version: 1.0 \r\n";
		$header .= "Content-type: text/xml; charset=utf-8 \r\n";
		$header .= "Content-length: ".strlen($xml)." \r\n";
		$header .= "Request-number: 1 \r\n";
		$header .= "Document-type: Request \r\n";
		$header .= "Connection: close \r\n\r\n";
		$header .= $xml;
		// response data
		$xml_response = '';

		// use curl if it exists
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $header);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$xml_response = curl_exec($ch);
			curl_close($ch);
		} else {
			echo "curl_init does not exist";
			exit;
		}
		// grab and parse content, remove google XML formatting
		$matches = array();
		preg_match_all('/<c o="([^"]*)" l="([^"]*)" s="([^"]*)">([^<]*)<\/c>/', $xml_response, $matches, PREG_SET_ORDER);

		// note: google will return encoded data, no need to encode ut8 characters
		return $matches;
	}

	private static function sendHeaders() {
		header('Content-type: application/json');			// data type
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");		// no cache
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");	// no cache
		header("Cache-Control: no-store, no-cache, must-revalidate");	// no cache
		header("Cache-Control: post-check=0, pre-check=0", false);	// no cache
	}
}
