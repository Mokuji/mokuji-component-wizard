<?php namespace components\wizard\models; if(!defined('TX')) die('No direct access.');

class Answers extends \dependencies\BaseModel
{
  
  protected static 
  
    $table_name = 'wizard_nodes',
    
    $relations = array(
      'Questions'=>array('qnode_id' => 'Questions.id')
    );
  
}

