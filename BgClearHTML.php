<?php
class BgClearHTML {
	
	// Оставляет в тексте только разрешенные теги и атрибуты
	public function prepare ($content, $allow_attributes) {
		// Удаляем JS-скрипты
		$content = preg_replace("/<script.*?script>/s", "", $content);
		// Заменяем <br/> и <hr/> на <br /> и <hr />
		$content = str_replace("<br/>", "<br />", $content);
		$content = str_replace("<hr/>", "<hr />", $content);

		// Списки
		if (!array_key_exists ( "ol" , $allow_attributes )) {
			// Заменяем нумерованный список на ненумерованный
			$content = str_replace("<оl", "<ul", $content);
			$content = str_replace("</оl>", "</ul>", $content);
		} else {
			if (!array_key_exists ( "ul" , $allow_attributes )) $allow_attributes['ul'] = "";
			if (!array_key_exists ( "li" , $allow_attributes )) $allow_attributes['li'] = "";
		}
		if (!array_key_exists ( "ul" , $allow_attributes )) {
			// Заменяем списки на абзацы
			$content = preg_replace('/<li(.*?)>/is', '<p\1>• ', $content); 		
			$content = str_replace('</li>', '</p>', $content);
		} else {
			if (!array_key_exists ( "li" , $allow_attributes )) $allow_attributes['li'] = "";
		}

		// Блоки и заголовки
		$headers = array ("div", "h1", "h2", "h3", "h4", "h5", "h6", "pre");
		foreach ($headers as $tag) {
			if (!array_key_exists ( $tag , $allow_attributes )) {
				// Заменяем блоки на абзацы 	
				$content = preg_replace('/<'.$tag.'(.*?)>/is', '<p\1>', $content);		
				$content = str_replace('</'.$tag.'>', '</p>', $content);
			}
		}
		
		// Таблицы 
		if (!array_key_exists ( "table" , $allow_attributes )) {
			unset($allow_attributes['thead'], $allow_attributes['tbody'], $allow_attributes['tfoot'], $allow_attributes['th'], $allow_attributes['td']);
			$content = preg_replace('/<tr(.*?)>/is', '<p\1>• ', $content); 		
			$content = str_replace('</tr>', '</p>', $content);
		} else {
			if (!array_key_exists ( "tr" , $allow_attributes )) $allow_attributes['tr'] = "";
			if (!array_key_exists ( "th" , $allow_attributes )) $allow_attributes['th'] = "";
			if (!array_key_exists ( "td" , $allow_attributes )) $allow_attributes['td'] = "";
		}

		// Удаляем все теги кроме разрешенных
		$allow_tags = "";
		foreach ($allow_attributes as $tag => $attr) {
			$allow_tags .= "<".$tag.">";
		}
		$content = strip_tags($content, $allow_tags);
		
		// Проверяем все оставшиеся открывающие теги и их атрибуты
		$template = '/<([a-z][a-z0-9]*\b)([^>]*?)(\/?\>)/is';
		preg_match_all($template, $content, $matches, PREG_OFFSET_CAPTURE);

		$text = "";
		$start = 0;
		$cnt = count($matches[0]);
		for ($i = 0; $i < $cnt; $i++) {	// Разбираем каждый тэг на патерны 
			preg_match($template, $matches[0][$i][0], $mt);
			$tag = $mt[1];							// Имя тега
			$newattr = "<".$tag;
			if ($allow_attributes[$tag]) {
				if ($allow_attributes[$tag] == "*") {
					$newattr .= $mt[2];				// Все атрибуты
				} else {
					$attrs = explode( "|", $allow_attributes[$tag] );
					foreach ($attrs as $attr) {		// Разрешенные атрибуты
						if (preg_match('/'.$attr.'\s*=\s*([\"\'])(.*?)(\1)/is', $mt[2], $value))
							$newattr .= ' '.$attr.'="'.str_replace( "\"", "'", $value[2] ).'"';
					}
				}
			}
			$newattr .= $mt[3];						// Закрывающие символы: /> или >
			
			$text .= substr($content, $start, $matches[0][$i][1]-$start).str_ireplace($mt[0], $newattr, $matches[0][$i][0]);
			$start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
		}
		$content = $text.substr($content, $start);
		
		return $content;
	}
	
	// Формирует массив разрешенных тегов и атрибутов из строки
	public function strtoarray ($str) {
		$allow_attributes = array ();
		// Ключ - тег, 
		// Значение - перечень разрешенных атрибутов, разделенных вертикальной чертой,
		// если Значение "*" - разрешены все атрибуты
		$str = preg_replace('/\s+/is','',$str);
		$listattr =	explode( ",", $str );
		foreach ($listattr as $attr) {
			preg_match('/([a-z0-9]+)(\[([\|a-z0-9]+)\])?/is', $attr, $mt);
			if (isset($mt[3])) $allow_attributes[$mt[1]] = $mt[3];
			else $allow_attributes[$mt[1]] = "";
		}
		return $allow_attributes;
	}
	
	// Добавляет символы конца строки к закрывающим тегам блоков и строк, в также к тегу br
	public function addEOL ($content) {
		// Делаем текст кода читабельным 
		$lines = array ("html", "head", "body", "div", "h1", "h2", "h3", "h4", "h5", "h6", "pre", "p", "ol", "ul");
		foreach ($lines as $tag) {
			$content = preg_replace('#</'.$tag.'>\s*#is', '</'.$tag.'>'.PHP_EOL, $content);
		}
		$content = preg_replace('#</li>\s*#is', '</li>'.PHP_EOL, $content);
		$content = preg_replace('#<br>\s*#is', '<br>'.PHP_EOL, $content);
		$content = preg_replace('#<br />\s*#is', '<br />'.PHP_EOL, $content);
		
		return $content;
	}
	
	public function replaceSpaces ($content) {
		
		// Заменяем &nbsp; на пробел 	
		$content = str_replace('&nbsp;', ' ', $content);
		// Удаляем двойные пробелы	
		$content = preg_replace('/\s+/s', " ", $content);
		$content = str_replace('  ', ' ', $content);
		// Удаляем пробелы из начала и конца строки
		$content = trim($content);
		
		return $content;
	}
}
?>