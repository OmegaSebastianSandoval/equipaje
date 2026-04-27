<?php
/**
*
*/

class Core_Model_Csrf
{
   public function __construct($section){
        $this->generateCode($section);
   }

   public function  deleteCode($section){
        $csrf =  Session::getInstance()->get('csrf');
        if($csrf != '' && $csrf[$section]){
            unset($csrf[$section]);
            Session::getInstance()->set('csrf',$csrf);
        }
   }

   public function  generateCode($section){
        $csrf =  Session::getInstance()->get('csrf');
        if(!is_array($csrf)){
          $csrf = array();
        }
        $csrf[$section] = $this->getRandomCode(20);
        Session::getInstance()->set('csrf',$csrf);
   }


   public function getRandomCode($length){
        $chain="";
        $an = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-)(.:,;";
        $su = strlen($an)- 1;
        for($i=0;$i<$length;$i++){
           $chain=$chain.substr($an, rand(0, $su), 1);
        }
        return $chain;
    }
}