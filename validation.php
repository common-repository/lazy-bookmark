<?php
/*
Class Name: Validation
Description: Class to validate lots of parameters
Author: Freelynx
Version: 1.0
Author URI: http://codeindesign.com/id/about/
 
*************************************************************************

Copyright 2010  Freelynx (email : frelynx@codindesign.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

**************************************************************************
*/

class Validation {
  
  //--[ ATTRIBUTES
  private $_version = "1.0";
  private 
    $_value,
    $_regexNumber,
    $_regexLowercase,
    $_regexUppercase,
    $_regexAlpha,
    $_regexAlphanumeric,
    $_regexPhone,
    $_regexEmail,
    $_regexUrl,
    $_regexIpv4,
    $_regexIpv6,
    $_regexCustom,
    $_regexPostalCode;    
  
  //--[ CONSTRUCTOR          
  public function __construct($value="") { 
    $this->_value             = $value; 
    $this->_regexNumber       = "/^[0-9]{1,}$/";
    $this->_regexLowercase    = "/^[a-z]{1,}$/";
    $this->_regexUppercase    = "/^[A-Z]{1,}$/";
    $this->_regexAlpha        = "/^[A-Za-z]{1,}$/";
    $this->_regexAlphanumeric = "/^[A-Za-z0-9]{1,}$/";
    $this->_regexPhone        = "/^\+[0-9]{2,3}[0-9 \-]{1,}$/";
    $this->_regexEmail        = "/^[a-zA-Z0-9\._]+@[a-z0-9\-_]+\.[a-z\.]{2,6}$/";
    $this->_regexUrl          = "/^(http|https|ftp):\/\/[a-zA-Z0-9\.-]+\.[a-z\.]{2,6}$/";
    $this->_regexIpv4         = "/^((\d{1,3})\.){3}(\d{1,3})$/";
    $this->_regexIpv6         = "/^([a-fA-F0-9]{0,4}\:){7}([a-fA-F0-9]{0,4})$/";
    $this->_regexCustom       = "";
    $this->_regexPostalCode   = array(
                                  "af" => "{}",
                                  "ax" => "/^[0-9]{5}$/",
                                  "al" => "/^[0-9]{4}$/",
                                  "dz" => "/^[0-9]{5}$/",
                                  "ad" => "/^[a-zA-Z]{2}+[0-9]{3}$/",
                                  "ao" => "{}",
                                  "ar" => "/^[a-zA-Z]{1}+[0-9]{4}+[a-zA-Z]{3}$/",
                                  "as" => "/^[0-9]{5}$/",
                                  "am" => "/^[0-9]{4}$/",
                                  "ac" => "/^[a-zA-Z]{4}+[0-9]{1}+[a-zA-Z]{2}$/",
                                  "au" => "/^[0-9]{4}$/",
                                  "at" => "/^[0-9]{4}$/",
                                  "az" => "/^[a-zA-Z]{2}+[0-9]{4}$/",
                                  "bd" => "/^[0-9]{4}$/",
                                  "bb" => "/^[a-zA-Z]{2}+[0-9]{5}$/",
                                  "by" => "/^[0-9]{6}$/",
                                  "be" => "/^[0-9]{4}$/",
                                  "bz" => "{}",
                                  "bj" => "{}",
                                  "br" => "/^([0-9]{5}|[0-9]{8}|[0-9]{5}\-[0-9]{3})$/",
                                  "io" => "/^[a-zA-Z]{4}+[0-9]{1}+[a-zA-Z]{2}$/",
                                  "vg" => "/^[a-zA-Z]{2}+[0-9]{4}$/",
                                  "bn" => "/^[a-zA-Z]{2}+[0-9]{4}$/",
                                  "bg" => "/^[0-9]{4}$/",
                                  "kh" => "/^[0-9]{5}$/",
                                  "ca" => "/^[a-zA-Z]{1}+[0-9]{1}+[a-zA-Z]{1}+ +[0-9]{1}+[a-zA-Z]{1}+[0-9]{1}$/",
                                  "cv" => "/^[0-9]{4}$/",
                                  "cl" => "/^([0-9]{7}|[0-9]{3}\-[0-9]{4})$/",
                                  "cn" => "/^[0-9]{6}$/",
                                  "co" => "/^[0-9]{6}$/",
                                  "cr" => "/^[0-9]{5}$/",
                                  "hr" => "/^[0-9]{5}$/",
                                  "cy" => "/^[0-9]{4}$/",
                                  "cz" => "/^([0-9]{5}|[0-9]{3}+ +[0-9]{2})$/",
                                  "dk" => "/^[0-9]{4}$/",
                                  "ec" => "/^[a-zA-Z]{2}+[0-9]{6}$/",
                                  "eg" => "/^[0-9]{5}$/",
                                  "ee" => "/^[0-9]{5}$/",
                                  "fk" => "/^[a-zA-Z]{4}+[0-9]{1}+[a-zA-Z]{2}$/",
                                  "fi" => "/^[0-9]{5}$/",
                                  "fr" => "/^[0-9]{5}$/",
                                  "ge" => "/^[0-9]{4}$/",
                                  "de" => "/^[0-9]{5}$/",
                                  "gr" => "/^[0-9]{5}$/",
                                  "gu" => "/^[0-9]{5}$/",
                                  "gg" => "/^[a-zA-Z]{2}+[0-9]{1}+ +[0-9]{1}+[a-zA-Z]{2}$/",
                                  "hk" => "{}",
                                  "hu" => "/^[0-9]{4}$/",
                                  "is" => "/^[0-9]{3}$/",
                                  "in" => "/^([0-9]{6}|[0-9]{3}+ +[0-9]{3})$/",
                                  "id" => "/^[0-9]{5}$/",
                                  "ir" => "/^[0-9]{5}+\-+[0-9]{5}$/",
                                  "iq" => "/^[0-9]{5}$/",
                                  "im" => "/^[a-zA-Z]{2}+[0-9]{1,2}+ +[0-9]{1}+[a-zA-Z]{2}$/",
                                  "il" => "/^[0-9]{5}$/",
                                  "it" => "/^[0-9]{5}$/",
                                  "jp" => "/^([0-9]{7}|[0-9]{3}+ +[0-9]{4})$/",
                                  "je" => "/^[a-zA-Z]{2}+[0-9]{1}+ +[0-9]{1}+[a-zA-Z]{2}$/",
                                  "kz" => "/^[0-9]{6}$/",
                                  "lv" => "/^[a-zA-Z]{2}+\-+[0-9]{4}$/",
                                  "li" => "/^[0-9]{4}$/",
                                  "lt" => "/^[0-9]{5}$/",
                                  "lu" => "/^[0-9]{4}$/",
                                  "my" => "/^[0-9]{5}$/",
                                  "mt" => "/^([a-zA-Z]{3}+[0-9]{4}|[a-zA-Z]{3}+ +[0-9]{4})$/",
                                  "mh" => "/^[0-9]{5}$/",
                                  "mx" => "/^[0-9]{5}$/",
                                  "fm" => "/^[0-9]{5}$/",
                                  "md" => "/^([a-zA-Z]{2}+[0-9]{4}|[a-zA-Z]{2}+\-+[0-9]{4})$/",
                                  "mc" => "/^980+[0-9]{2}$/",
                                  "me" => "/^[0-9]{5}$/",
                                  "ma" => "/^[0-9]{5}$/",
                                  "nl" => "/^([a-zA-Z]{4}+[0-9]{2}|[a-zA-Z]{4}+ +[0-9]{2})$/",
                                  "nz" => "/^[0-9]{4}$/",
                                  "ni" => "/^[0-9]{6}$/",
                                  "mp" => "/^[0-9]{5}$/",
                                  "no" => "/^[0-9]{4}$/",
                                  "pw" => "/^[0-9]{5}$/",
                                  "pa" => "/^[0-9]{6}$/",
                                  "pk" => "/^[0-9]{6}$/",
                                  "ph" => "/^[0-9]{4}$/",
                                  "pn" => "/^[a-zA-z]{4}+[0-9]{1}+[a-zA-Z]{2}$/",
                                  "pl" => "/^([0-9]{5}|[0-9]{2}+\-+[0-9]{5})$/",
                                  "pt" => "/^([0-9]{4}+ +[0-9]{3}|[0-9]{4}+\-+[0-9]{3})$/",
                                  "pr" => "/^[0-9]{5}$/",
                                  "ro" => "/^[0-9]{6}$/",
                                  "ru" => "/^[0-9]{6}$/",
                                  "sm" => "/^[0-9]{5}$/",
                                  "rs" => "/^[0-9]{5}$/",
                                  "sg" => "/^[0-9]{6}$/",
                                  "sk" => "/^([0-9]{5}|[0-9]{3}+ +[0-9]{2})$/",
                                  "si" => "/^([a-zA-Z]{2}+[0-9]{4}|[a-zA-Z]{2}+\-+[0-9]{4})$/",
                                  "za" => "/^[0-9]{4}$/",
                                  "gs" => "/^[a-zA-Z]{4}+[0-9]{1}+[a-zA-Z]{4}$/",
                                  "kr" => "/^([0-9]{6}|[0-9]{6}+\-+[0-9]{6})$/",
                                  "es" => "/^[0-9]{5}$/",
                                  "lk" => "/^[0-9]{5}$/",
                                  "se" => "/^([0-9]{5}|[0-9]{3}+ +[0-9]{2})$/",
                                  "ch" => "/^[0-9]{4}$/",
                                  "tw" => "/^[0-9]{5}$/",
                                  "th" => "/^[0-9]{5}$/",
                                  "tn" => "/^[0-9]{4}$/",
                                  "tc" => "/^[a-zA-Z]{4}+[0-9]{1}+[a-zA-Z]{4}$/",
                                  "tr" => "/^[0-9]{5}$/",
                                  "ua" => "/^[0-9]{5}$/",
                                  "gb" => "/^([a-zA-Z]{1}+[0-9]{1}+ +[0-9]{1}+[a-zA-Z]{2}|[a-zA-Z]{1}+[0-9]{2}+ +[0-9]{1}+[a-zA-Z]{2}|[a-zA-Z]{1}+[0-9]{1}+[a-zA-Z]{1}+ +[0-9]{1}+[a-zA-Z]{2}|[a-zA-Z]{2}+[0-9]{1}+ +[0-9]{1}+[a-zA-Z]{2}|[a-zA-Z]{2}+[0-9]{2}+ +[0-9]{1}+[a-zA-Z]{2}|[a-zA-Z]{2}+[0-9]{1}+[a-zA-Z]{1}+ +[0-9]{1}+[a-zA-Z]{2})$/",
                                  "us" => "/^[0-9]{5}$/",
                                  "vi" => "/^[0-9]{5}$/",
                                  "va" => "/^[0-9]{5}$/",
                                  "vn" => "/^[0-9]{6}$/",
                                );
  }            
  
  //--[ ACCESSORS
  public function getVersion()          { return $this->_version; }
  public function getValue()            { return $this->_value; }
  public function getRegexNumber()      { return $this->_regexNumber; }
  public function getRegexLowercase()   { return $this->_regexLowercase; }
  public function getRegexUppercase()   { return $this->_regexUppercase; }
  public function getRegexAlpha()       { return $this->_regexAlpha; }
  public function getRegexAlphanumeric(){ return $this->_regexAlphanumeric; }
  public function getRegexPhone()       { return $this->_regexPhone; }
  public function getRegexEmail()       { return $this->_regexEmail; }
  public function getRegexUrl()         { return $this->_regexUrl; }
  public function getRegexIpv4()        { return $this->_regexIpv4; }
  public function getRegexIpv6()        { return $this->_regexIpv6; }
  public function getRegexPostalCode()  { return $this->_regexPostalCode; }
  public function getRegexCustom()      { return $this->_regexCustom; }
  
  //--[ MUTATORS
  public function setValue($value)              { $this->_value = $value; }
  public function setRegexNumber($regex)        { $this->_regexNumber = $regex; }
  public function setRegexLowercase($regex)     { $this->_regexLowercase = $regex; }
  public function setRegexUppercase($regex)     { $this->_regexUppercase = $regex; }
  public function setRegexAlpha($regex)         { $this->_regexAlpha = $regex; }
  public function setRegexAlphanumeric($regex)  { $this->_regexAlphanumeric = $regex; }
  public function setRegexPhone($regex)         { $this->_regexPhone = $regex; }
  public function setRegexEmail($regex)         { $this->_regexEmail = $regex; }
  public function setRegexUrl($regex)           { $this->_regexUrl = $regex; }
  public function setRegexIpv4($regex)          { $this->_regexIp4 = $regex; }
  public function setRegexIpv6($regex)          { $this->_regexIpv6 = $regex; }
  public function setRegexPostalCode($regex)    { $this->_regexPostalCode = $regex; }
  public function setRegexCustom($regex)        { $this->_regexCustom = $regex; }
  
  //--[ METHODS
  
  /*****************************************************************************
   * Name     : lowercase
   * Type     : BOOLEAN 
   * Function : Validate lowercase
   ****************************************************************************/
  public function lowercase() {
    return preg_match($this->_regexLowercase, $this->_value);
  }
  
  /*****************************************************************************
   * Name     : uppercase
   * Type     : BOOLEAN 
   * Function : Validate uppercase
   ****************************************************************************/
  public function uppercase() {
    return preg_match($this->_regexUppercase, $this->_value);
  }
  
  /*****************************************************************************
   * Name     : alpha
   * Type     : BOOLEAN 
   * Function : Validate alphabet
   ****************************************************************************/
  public function alpha() {
    return preg_match($this->_regexAlpha, $this->_value);
  }
  
  /*****************************************************************************
   * Name     : alpha
   * Type     : BOOLEAN 
   * Function : Validate alphanumeric
   ****************************************************************************/
  public function alphanumeric() {
    return preg_match($this->_regexAlphanumeric, $this->_value);
  }
  
  /*****************************************************************************
   * Name     : number
   * Type     : BOOLEAN 
   * Function : Validate number
   ****************************************************************************/
  public function number() {
    return preg_match($this->_regexNumber, $this->_value);
  }
  
  /*****************************************************************************
   * Name     : phone
   * Type     : BOOLEAN 
   * Function : Validate phone number
   ****************************************************************************/
  public function phone() {
    return preg_match($this->_regexPhone, $this->_value);
  }
  
  /*****************************************************************************
   * Name     : email
   * Type     : BOOLEAN 
   * Function : Validate email address
   ****************************************************************************/
  public function email() {
    return preg_match($this->_regexEmail, $this->_value);
  }
  
  /*****************************************************************************
   * Name     : url
   * Type     : BOOLEAN 
   * Function : Validate url address
   ****************************************************************************/
  public function url() {
    return preg_match($this->_regexUrl, $this->_value);
  }
  
  /*****************************************************************************
   * Name     : ip4
   * Type     : BOOLEAN 
   * Function : Validate IPv4 address
   ****************************************************************************/
  public function ipv4($ip4='') {
    return preg_match($this->_regexIpv4, (($ip4=='')?$this->_value:$ip4));
  }
  
  /*****************************************************************************
   * Name     : ip6
   * Type     : BOOLEAN 
   * Function : Validate IPv6 address
   ****************************************************************************/
  public function ipv6() {
    return preg_match($this->_regexIpv6, $this->_value);
  }
  
  /*****************************************************************************
   * Name     : custom
   * Type     : BOOLEAN 
   * Function : Validate custom value
   ****************************************************************************/
  public function custom() {
    return preg_match($this->_regexCustom, $this->_value);
  }
  
  /*****************************************************************************
   * Name           : postal / zip code
   * Type           : BOOLEAN 
   * Responsibility : Validate Postcode
   * Parameter      : country - Country name using ISO 3161-alpha-2 format   
   ****************************************************************************/
  public function postalcode($country) {
    return preg_match($this->_regexPostalCode[$country], $this->_value);
  }
	
	
}
  /*
  $valid = new Validation();
	$valid->setValue('https://fffdsafdsaf24232.fdsfd.ajdfhs/');
	echo $valid->getValue().'<br />';
	echo$valid->url();
	*/
?>
