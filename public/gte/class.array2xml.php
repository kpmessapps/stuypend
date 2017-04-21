<?php
/****
* @PHPVER4.0
*
* @author	emnu
* @ver	--
* @date	12/08/08
*
* use this class to convert from mutidimensional array to xml.
* see example.php file on howto use this class
*
*/

class arr2xml
{
	var $array = array();
	var $xml = '';
    var $data_type='data';
	function arr2xml($array,$data_type)
	{
		$this->array = $array;
		$this->data_type = $data_type;
		if(is_array($array) && count($array) > 0)
		{
			$this->struct_xml($array);
		}
		else
		{
			$this->xml .= "no data";
		}
	}

	function struct_xml($array)
	{
		foreach($array as $k=>$v)
		{
			if(is_array($v))
			{
				$tag = ereg_replace('^[0-9]{1,}',$this->data_type,$k); // replace numeric key in array to 'data'
				$this->xml .= "<$tag>";
				$this->struct_xml($v);
				$this->xml .= "</$tag>";
			}
			else
			{
				$tag = ereg_replace('^[0-9]{1,}',$this->data_type,$k); // replace numeric key in array to 'data'
				$this->xml .= "<$tag>$v</$tag>\n";
			}
		}
	}
	
	function get_xml($xml_function)
	{
		$header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><$xml_function>\n";
		if($xml_function=="AlbumSongs")
			$header=$header."<Album>";
			
		$footer = "</$xml_function>";
		if($xml_function=="AlbumSongs")
			$footer="</Album>".$footer;
			
		$retrun_xml =$header;
		$retrun_xml .=$this->xml;
		$retrun_xml .=$footer;
        
        return $retrun_xml;
	}
}
?>