<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

class Modules extends \dependencies\BaseViews
{

  protected function wizard($options)
  {
    
    // $qid = (tx('Data')->get->aid->is_set()
      // ? $this->table('Answers')->pk(tx('Data')->get->aid)->join('Questions', $q)->execute_single($q)->id
      // : tx('Data')->get->qid
    // );
    
    $qid = 2;
    
    //trace($options);
    
    return array(
      'id' => Data($options)->id->is('set')->failure(function(){return Data(1);}),
      'question' => $this->table('Questions')->pk($qid)->execute_single(),
      'answers' => $this->table('Questions')->pk($qid)->join('Answers', $a)->inner()->execute($a)
    );
    
  }

}