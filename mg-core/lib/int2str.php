<?php
/**
 * Класс int2str - предназначен для подстановки правильного окончания к числительным.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class int2str {

  private $rank = array(
    1 => array('рубль', 'рубля', 'рублей'),
    2 => array('тысяча', 'тысячи', 'тысяч'),
    3 => array('миллион', 'миллиона', 'миллионов'),
    4 => array('миллиард', 'миллиарда', 'миллиардов'),
    5 => array('триллион', 'триллиона', 'триллионов')
  );
  private $A0_9 = array(0 => 'ноль', 1 => 'один', 2 => 'два', 3 => 'три', 4 => 'четыре', 5 => 'пять', 6 => 'шесть', 7 => 'семь', 8 => 'восемь', 9 => 'девять');
  private $A0_9_ = array(0 => 'ноль', 1 => 'одна', 2 => 'две', 3 => 'три', 4 => 'четыре', 5 => 'пять', 6 => 'шесть', 7 => 'семь', 8 => 'восемь', 9 => 'девять');
  private $A10_19 = array(10 => 'десять', 11 => 'одиннадцать', 12 => 'двенадцать', 13 => 'тринадцать', 14 => 'четырнадцать', 15 => 'пятнадцать',
    16 => 'шестнадцать', 17 => 'семнадцать', 18 => 'восемнадцать', 19 => 'девятнадцать');
  private $A20_90 = array(2 => 'двадцать', 3 => 'тридцать', 4 => 'сорок', 5 => 'пятьдесят', 6 => 'шестьдесят', 7 => 'семьдесят', 8 => 'восемьдесят',
    9 => 'девяносто');
  private $A100_900 = array(1 => 'сто', 2 => 'двести', 3 => 'триста', 4 => 'четыреста', 5 => 'пятьсот', 6 => 'шестьсот', 7 => 'семьсот', 8 => 'восемьсот',
    9 => 'девятьсот');
  public $num;
  public $triada;
  public $out;
  public $kop = '';
  public $kopShort = 'коп.';
  public $currencyString = ' рублей';

  function __construct($x,$kop=true, $currencyString =' рублей', $kopShort ='коп.') {
    $this->kopShort = $kopShort;
    $this->currencyString = $currencyString;
    $propertyOrder = MG::getOption('propertyOrder');
    $propertyOrder = stripslashes($propertyOrder);
    $propertyOrder = unserialize($propertyOrder);   
    $rankUser = explode(",",$propertyOrder["currency"]);   
    if(count($rankUser)==3){      
      $this->rank[1] = $rankUser;       
    }
    $this->num = $this->prepare($x);
    $this->test();
    $this->rub($kop);
  }

  function prepare($x) {
    $search = array(',', '/');
    $x = str_replace($search, '.', $x);
    $x = explode('.', $x);
    if (empty($x[1])) {
      $this->kop = '00';
    } else {
      $this->kop = $x[1];
    }

    return $x[0];
  }

  function add_null($x) {
    switch ($x) {
      case 0;
        $v = "".$this->num;
        break;
      case 1:$v = "0".$this->num;
        break;
      case 2:$v = "00".$this->num;
        break;
    }
    $this->num = $v;
  }

  function test() {
    $x = mb_strlen($this->num);
    if ($x<=3) {
      $z = 1;
      $this->add_null(3-$x);
    } else {
      $y = $x%3;
      if ($y==0) {
        $z = $x/3;
      } else {
        $this->add_null(3-$y);
        $z = ($x-$y)/3+1;
      }
    }
    return $this->triada = $z;
  }

  private function lexem($x) {
    if ($x==11||$x==12||$x==13||$x==14) {
      $x = 2;
    } else {
      $x = substr($x, -1);
      if ($x==1):$x = 0;
      elseif ($x>1&&$x<=4):$x = 1;
      else:$x = 2;
      endif;
    }
    return $x;
  }

  function parse($x, $i) {
    $a = substr($x, 0, 1);
    $b = substr($x, 1, 2);
    $c = substr($x, 1, 1);
    $d = substr($x, 2, 1);

    if ($i==2) {
      $A = $this->A0_9_[$d];
    } else {
      $A = $this->A0_9[$d];
    }
    if ($x=='000'&&$i==1) {
      return $string = $this->currencyString;
    } else {
      if ($x=='000') {
        return $string = '';
      }
    }
    if ($a>=1) {
      $string = $this->A100_900[$a];
    }
    if ($b<=9&&$b!=0) {
      $string.=' '.$A;
    }
    if ($b<=19) {
      $string.=' '.$this->A10_19[$b];
    }
    if ($b>=20&&$d==0) {
      $string.=' '.$this->A20_90[$c];
    }
    if ($b>=20&&$d!=0) {
      $string.=' '.$this->A20_90[$c].' '.$A;
    }

    return $string.' '.$this->rank[$i][$this->lexem($b)].' ';
  }

  function rub($kop = true) {
    for ($i = $this->triada; $i>0; $i--) {
      $x.=$this->parse(substr($this->num, -$i*3, 3), $i);
    }
    if($kop){
      $this->rub = ucfirst(trim($x)).' '.$this->kop.' '.$this->kopShort;    
    }else{
      $this->rub = ucfirst(trim($x));    
    }
  }

  function ucfirst($str) {
    $tmp = preg_split("//u", $str, 2, PREG_SPLIT_NO_EMPTY);
    return mb_convert_case(
        str_replace("i", "İ", $tmp[0]), MB_CASE_TITLE, "UTF-8").
        $tmp[1];
 }

}

