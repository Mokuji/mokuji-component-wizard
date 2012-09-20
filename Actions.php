<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

class Actions extends \dependencies\BaseComponent
{

  protected function load_followup_question($data)
  {
    $qid = tx('Sql')->table('wizard', 'Answers')->pk($data->answer_id)->join('Questions', $q)->execute_single($q)->id;
    
    return Data(array(
      'question' => tx('Sql')->table('wizard', 'Questions')->pk($qid)->execute_single(),
      'answers' => tx('Sql')->table('wizard', 'Questions')->pk($qid)->join('Answers', $a)->inner()->execute($a)
    ))->as_json();
    
  }
  
  protected function load_answer($data)
  {
    
    return tx('Sql')->table('wizard', 'Answers')->pk($data->answer_id)->execute_single()->as_json();
    
  }
  
}
