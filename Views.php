<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

class Views extends \dependencies\BaseViews
{

  protected function walkthrough($options)
  {
    
    return array(
      $this->module('wizard', array('id'=>1))
      //$this->module('wizard', array('id'=>6))
    );
    
  }
  
}
