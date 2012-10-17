<?php namespace components\wizard\models; if(!defined('TX')) die('No direct access.');

class Questions extends \dependencies\BaseModel
{
  
  protected static 
  
    $table_name = 'wizard_questions',
    
    $hierarchy = array(
      'left' => 'lft',
      'right' => 'rgt'
    ),
    
    $relations = array(
      'Answers'=>array('id' => 'Answers.source_question_id')
    ),
    
    $secondary_keys = array(
      'wizard_id'
    );
  
  public function get_answers()
  {
    
    return tx('Sql')
      ->table('wizard', 'Answers')
      ->where('source_question_id', $this->id)
      ->execute();
    
  }
  
}

