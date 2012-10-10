<?php namespace components\wizard\models; if(!defined('TX')) die('No direct access.');

class Answers extends \dependencies\BaseModel
{
  
  protected static 
  
    $table_name = 'wizard_answers';
  
  public function get_source_question()
  {
    
    return tx('Sql')
      ->table('wizard', 'Questions')
      ->pk($this->source_question_id)
      ->execute_single();
    
  }
  
  public function get_target_question()
  {
    
    return tx('Sql')
      ->table('wizard', 'Questions')
      ->pk($this->target_question_id)
      ->execute_single();
    
  }
  
}

