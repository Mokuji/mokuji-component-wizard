<?php namespace components\wizard\models; if(!defined('TX')) die('No direct access.');

class Link extends \dependencies\BaseModel
{
  
  protected static 
  
    $table_name = 'wizard_link',
    
    $relations = array(
      'Answers'=>array('anode_id' => 'Answers.id'),
      'Questions'=>array('qnode_id' => 'Questions.id')
    );
  
}

