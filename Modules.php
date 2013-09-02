<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

class Modules extends \dependencies\BaseViews
{

  protected
    $permissions = array(
      'wizard' => 0
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

}
