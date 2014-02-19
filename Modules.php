<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

class Modules extends \dependencies\BaseViews
{

  protected
    $permissions = array(
      'nodes' => 0
    );

  protected function wizard($options)
  {
    
    return array(
      'wizard' => tx('Sql')
        ->table('wizard', 'Wizards')
        ->pk($options->wizard_id)
        ->execute_single()
    );
    
  }

  protected function nodes($options)
  {
    
    return array(
      'wizard' => tx('Sql')
        ->table('wizard', 'Nodes')
        ->where('page_id', mk('Url')->url->data->pid)
        ->execute_single()
    );
    
  }

}
