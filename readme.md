# Класс BgClearHTML

PHP класс **BgClearHTML** удаляет из текста HTML-теги по принципу "что не разрешено, то запрещено".

Основная функция класса:

`public function prepare ( (string) $content, (array) $allow_attributes )`
	
Параметры:
	
`$content` - строка, содержащая HTML-разметку;
	
`$allow_attributes [ $tag => $attributes ]` - массив разрешенных тегов HTML и их атрибутов,
	
где `$tag` - ключ массива - разрешенные теги, 
	
`$attributes` - значения - список разрешенных атрибутов тега, разделенных символом вертикальной черты `|`,
если значение `*` - разрешены все атрибуты.
	
## Особенности обработки

Скрипт безусловно преобразует:
* все пробельные символы в обыкновенные пробелы после чего и удаляет все лишние пробелы;
* заменяет `<br/>` и `<hr/>` на `<br />` и `<hr />`;

### Списки

Если не разрешен тег `ol`, он заменяется в тексте на `ul`.
Если также не разрешен тег `ul`, то теги `<li>` преобразуются в `<p>• `.
Если теги `ol` и/или `ul` разрешены, то теги `li` будут также разрешены.
	
### Блоки и заголовки

Если не разрешены теги `div` и `h1 ... h6`, то они преобразуются в `p`.

### Таблицы

Если не разрешен тег `table`, то одновременно будут удалены теги `thead`, `tbody`, `tfoot`, `th`, `td`,
а тег `<tr>` - преобразован в `<p>• `. 
Если тег `table` разрешен, то теги `tr`, `th`, `td` будут также разрешены.

## Дополнительные функции

* Формирование массива разрешенных тегов и атрибутов из строки	
	
`public function strtoarray ( (string) $str )`
	
`$str` - строка, содержащая список разрешенных HTML-тегов, перечисленных через запятую. 
Рядом с тегом в квадратных скобках может быть указан перечень разрешенных атрибутов, разделенных вертикальной чертой `|`, в качестве разделителя. 

* Добавление символов конца строки к закрывающим тегам блоков и строк, в также к тегу `br`

`public function addEOL ( (string) $content )`

* Преобразование всех пробельных символов в обыкновенные пробелы и удаление лишних пробелов

`public function replaceSpaces ( (string) $content )`
