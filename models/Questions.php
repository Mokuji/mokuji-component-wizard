<?php namespace components\wizard\models; if(!defined('TX')) die('No direct access.');

class Questions extends \dependencies\BaseModel
{
  
  protected static 
  
    $table_name = 'wizard_nodes',
    
    $relations = array(
      'Answers'=>array('id' => 'Link.qnode_id')
    );
  
}

