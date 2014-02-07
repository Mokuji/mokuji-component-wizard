<?php namespace components\wizard\models; if(!defined('TX')) die('No direct access.');

class Nodes extends \dependencies\BaseModel
{
  
  protected static
  
    $table_name = 'wizard__nodes',
  
    $relations = array(
      'Pages' => array('page_id' => 'Cms.Pages.id')
    ),
    
    $hierarchy = array(
      'left' => 'lft',
      'right' => 'rgt'
    ),
    
    $secondary_keys = array(
      'page_id'
    );

}
