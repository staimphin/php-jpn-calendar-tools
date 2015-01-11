<?php 
/**
 * Tool for convert and format a date to the Japanese Format.
 *
 * PHP 5
 *
 * LICENSE: This source file is subject to version 3.0 of the  GNU GPL
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * tool_jpn_calendar.php
 *
 * @category	: Japanese tools
 * @author	: gregory.staimphin@free.fr
 * @license	: GNU GPL v3.0
 * @version	: 1.0
 */
 class convertCal{
	private $_dateFormating='.';
	private $_dateFormatingYear='年';
	private $_dateFormatingMonth='月';
	private $_dateFormatingDay='日';
	private $_JPCAL=array(
		array('PERIODE'=>'縄文','START'=>'-14000','END'=>'-300'),
		array('PERIODE'=>'弥生','START'=>'-300','END'=>'250'),
		array('PERIODE'=>'古墳','START'=>'250','END'=>'592'),
		array('PERIODE'=>'飛鳥','START'=>'592','END'=>'710'),
		array('PERIODE'=>'奈良','START'=>'710','END'=>'794'),
		array('PERIODE'=>'平安','START'=>'794','END'=>'1185'),
		array('PERIODE'=>'鎌倉','START'=>'1185','END'=>'1333'),
		array('PERIODE'=>'建武の新政','START'=>'1333','END'=>'1336'),
		array('PERIODE'=>'室町','START'=>'1336','END'=>'1573'),
		array('PERIODE'=>'安土桃山','START'=>'1573','END'=>'1603'),
		array('PERIODE'=>'江戸','START'=>'1603','END'=>'1868'),
		array('PERIODE'=>'明治','START'=>'1868','END'=>'1912'),
		array('PERIODE'=>'大正','START'=>'1912','END'=>'1926'),
		array('PERIODE'=>'昭和','START'=>'1926','END'=>'1989'),
		array('PERIODE'=>'平成','START'=>'1989','END'=>'2034'),
	);

	public function __construct()
	{
	}
	
	/**
	 * equivalent to: date("Y年m月d日", $date)
	 */	
	public function dateJpnFormat($dateTime)
	{
		$dateTime= strtotime($dateTime);
		return  date("Y".$this->_dateFormatingYear."m".$this->_dateFormatingMonth."d".$this->_dateFormatingDay." H:i:s",$dateTime);  ;
	}
	
	/**
	 * Convert japanese period date to gregorian date
	 * use:
	 * $year: a japanese period name
	 * $showunit:shows date formated as Y年m月d日
	 */
	public function convertJpnToGre($year, $showunit=0)
	{
		$periode= $this->getJapPeriodeStart($year);
		$jpn_Y =$this-> validDateElem($periode[0],$showunit, $this->_dateFormatingYear);
		$jpn_M=$this-> validDateElem($periode[1],$showunit, $this->_dateFormatingMonth);
		$jpn_D=$this-> validDateElem($periode[2],$showunit, $this->_dateFormatingDay);
			
		return $jpn_Y.$jpn_M.$jpn_D;
	}

	/**
	 * Ensure a date element is properly formated with unit if requiered
	 */
	private function validDateElem($value='',$showunit=0, $unit='')
	{
		if($value!='' && $showunit!=''){
			$value .=$unit;}
		return $value;
	}

	/**
	 * This function does the following:
	 *  ensure to convert "zen-kaku" numbers to "han-kaku" 
	 *  remove the usual japanese kanji for Y-M-D
	 * returns an array with Y-m-d
	 */
	private function cleanJapDate($string)
	{
		$string= mb_convert_kana ($string,"na");
		$string= str_replace(' ','',$string);
		$string= str_replace('年',$this->_dateFormating,$string);
		$string= str_replace('月',$this->_dateFormating,$string);
		$string= str_replace('日',$this->_dateFormating,$string);
		return explode($this->_dateFormating,$string);
	}	
	
	/**
	 * Convert a gregorian string to a japanese format 
	 * Use:
	 * $year : string or int : 1905
	 * $display: display format:
	 * 0: year of the searched period : 38
	 * 1: Japanese period name only :  明治
	 * 2:  Japanese period name only AND year of the searched period :  明治38
	 * $showunit: shows the year kanji ( "年") : 1905年
	 * 
	 */
	public function convertGreToJpn($year, $display=0, $showunit=0)
	{
		if($showunit==1){
			$yearKanji = "年";
		} else{
			$yearKanji = '';
		}
			
		$tmp= $this->getJapYear($year);
		switch($display){
			case 0: return $tmp['YEAR'].$yearKanji  ;break;//years
			case 1: return $tmp['PERIODE'] ;break;
			case 2: return $tmp['PERIODE'].$tmp['YEAR'].$yearKanji ;break;
		}
	}

	/**
	 * Find the 1st year of japanese period:
	 * string must starts with the japanese period: 明治38年03月10日
	 * will return 1905
	 * returns an array with Y-m-d
	 * Y= 1st year of the searched period
	 * M = searched month (if any)
	 * D = search day (if any)
	 */
	private function getJapPeriodeStart($jpnYear)
	{
		for($i=count($this->_JPCAL)-1;$i>=0 ;$i--){
			$max=mb_strlen($this->_JPCAL[$i]['PERIODE']);
			if(mb_substr($jpnYear,0,$max)==$this->_JPCAL[$i]['PERIODE'] ){ 
				$string=$this->cleanJapDate(mb_substr($jpnYear,$max));
				$gregYear=$this->_JPCAL[$i]['START'] +$string[0] -1;
				if(is_numeric($string[0])){
					if(isset($string[1])){
						$gregMonth=$string[1];
					}else{
						$gregMonth=1;
					}
					if(isset($string[2])){
						$gregDay=$string[2];
					}else{
						$gregDay=1;
					}
					
					return  array($gregYear,$string[1],$string[2]);
				} else {return  array($this->_JPCAL[$i]['START'] ,'','');}
				break;
			}
		}
	}

	/**
	 * This function returns  an array contenaining
	 *  PERIODE: UTF8 string : Japanese period name.
	 *  YEAR: Year within the japanese period for the requested $year
	 *
	 */
	private function getJapYear($year)
	{
		for($i=count($this->_JPCAL)-1;$i>=0 ;$i--){
			if ($year>= $this->_JPCAL[$i]['START'] && $year<  $this->_JPCAL[$i]['END']){
				$tmp_year= $year - $this->_JPCAL[$i]['START'];
				return array('PERIODE'=> $this->_JPCAL[$i]['PERIODE'] ,'YEAR'=>intval($tmp_year+1) ) ;//Year 0 is called 1st year!!
				break;
			}
		}
	}
}
?>
