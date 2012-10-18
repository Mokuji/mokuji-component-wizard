<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

class Json extends \dependencies\BaseViews
{
  
  protected
    $default_premission = 2,
    $permissions = array(
      'get_wizard' => 0,
      'get_question' => 0,
      'get_questions' => 0,
      'get_answers' => 0
    );
  
  /* ---------- Wizards ---------- */
  protected function get_wizard($options, $params)
  {
    
    $params->{0}->is('set', function($wid)use($options){
      $options->merge(array(
        'wizard_id' => $wid
      ));
    });
    
    $options = $options->having('wizard_id')
      ->wizard_id->validate('Wizard ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
    ;
    
    return tx('Sql')
      ->table('wizard', 'Wizards')
      ->pk($options->wizard_id)
      ->execute_single()
      ->is('empty', function(){
        throw new \exception\NotFound();
      });
    
  }
  
  protected function update_wizard($data, $params){
    
    $wid = $params->{0}
      ->validate('Wizard ID', array('required', 'number'=>'integer', 'gt'=>0));
    
    $data = $data->having('title', 'description', 'start_question_id')
      ->title->validate('Title', array('required', 'string', 'not_empty'))->back()
      ->description->validate('Description', array('string'))->back()
      ->start_question_id->validate('Start question ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
    ;
    
    return tx('Sql')
      ->table('wizard', 'Wizards')
      ->pk($wid)
      ->execute_single()
      ->is('empty', function(){
        throw new \exception\NotFound();
      })
      ->merge($data)
      ->save();
    
  }
  
  /* ---------- Questions ---------- */
  protected function get_question($options, $params)
  {
    
    $params->{0}->is('set', function($qid)use($options){
      $options->merge(array(
        'question_id' => $qid
      ));
    });
    
    $options = $options->having('question_id')
      ->question_id->validate('Question ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
    ;
    
    return tx('Sql')
      ->table('wizard', 'Questions')
      ->pk($options->question_id)
      ->execute_single()
      ->is('empty', function(){
        throw new \exception\NotFound();
      });
    
  }
  
  protected function get_questions($options, $params)
  {
    
    $params->{0}->is('set', function($wid)use($options){
      $options->merge(array(
        'wizard_id' => $wid
      ));
    });
    
    $options = $options->having('wizard_id')
      ->wizard_id->validate('Wizard ID', array('number'=>'integer', 'gt'=>0))->back()
    ;
    
    return tx('Sql')
      ->table('wizard', 'Questions')
      ->is($options->wizard_id->is_set(), function($q)use($options){
        $q->where('wizard_id', $options->wizard_id);
      })
      ->order('lft')
      ->execute();
    
  }
  
  protected function create_question($data, $params)
  {
    
    $data = $data->having('wizard_id', 'title', 'description')
      ->wizard_id->validate('Wizard ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
      ->title->validate('Title', array('required', 'string', 'not_empty'))->back()
      ->description->validate('Description', array('string'))->back()
    ;
    
    return tx('Sql')
      ->model('wizard', 'Questions')
      ->set($data)
      ->hsave();
    
  }
  
  protected function update_question($data, $params)
  {
    
    $qid = $params->{0}
      ->validate('Question ID', array('required', 'number'=>'integer', 'gt'=>0));
    
    $data = $data->having('wizard_id', 'title', 'description')
      ->wizard_id->validate('Wizard ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
      ->title->validate('Title', array('required', 'string', 'not_empty'))->back()
      ->description->validate('Description', array('string'))->back()
    ;
    
    return tx('Sql')
      ->table('wizard', 'Questions')
      ->pk($qid)
      ->execute_single()
      ->is('empty', function(){
        throw new \exception\NotFound();
      })
      ->merge($data)
      ->hsave();
    
  }
  
  protected function update_questions_hierarchy($data, $params)
  {
    
    $data->questions->each(function($q){
      
      tx('Sql')->model('wizard', 'Questions')->merge($q->having(array(
        'id' => 'item_id',
        'lft' => 'left',
        'rgt' => 'right'
      )))
      
      ->save();
      
    });
    
    return $this->get_questions(Data(), $params);
    
  }
  
  protected function delete_question($data, $params)
  {
    
    $qid = $params->{0}
      ->validate('Question ID', array('required', 'number'=>'integer', 'gt'=>0));
    
    tx('Sql')
      ->table('wizard', 'Questions')
      ->pk($qid)
      ->execute_single()
      ->is('empty', function(){
        throw new \exception\NotFound();
      })
      ->answers
        ->each(function($answer){
          $answer->delete();
        })
      ->back()
      ->hdelete();
    
    return true;
    
  }
  
  /* ---------- Answers ---------- */
  protected function get_answers($options, $params)
  {
    
    $params->{0}->is('set', function($wid)use($options){
      $options->merge(array(
        'question_id' => $wid
      ));
    });
    
    $options = $options->having('question_id')
      ->question_id->validate('Question ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
    ;
    
    return tx('Sql')
      ->table('wizard', 'Questions')
      ->pk($options->question_id)
      ->execute_single()
      
      ->is('empty', function(){
        throw new \exception\NotFound();
      })
      
      ->answers;
    
  }
    
  protected function create_answer($data, $params)
  {

    $data = $data->having('title', 'description', 'source_question_id', 'target_question_id', 'url', 'url_target', 'breadcrumb')
      ->title->validate('Title', array('required', 'string', 'not_empty'))->back()
      ->description->validate('Description', array('string'))->back()
      ->breadcrumb->validate('Breadcrumb', array('string'))->back()
      ->source_question_id->validate('Source question ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
      ->target_question_id->validate('Target question', array('number'=>'integer', 'gt'=>0))->back()
      ->url->validate('URL', array('url'))->back()
      ->url_target->validate('URL target', array('string', 'in'=>array('_blank', '_parent', '_self', '_top')))->back()
    ;
    
    string_if_empty($data, 'target_question_id', 'url', 'url_target');
    
    return tx('Sql')
      ->model('wizard', 'Answers')
      ->set($data)
      ->save();
    
  }
  
  protected function update_answer($data, $params)
  {
    
    $aid = $params->{0}
      ->validate('Answer ID', array('required', 'number'=>'integer', 'gt'=>0));
    
    $data = $data->having('title', 'description', 'source_question_id', 'target_question_id', 'url', 'url_target', 'breadcrumb')
      ->title->validate('Title', array('required', 'string', 'not_empty'))->back()
      ->description->validate('Description', array('string'))->back()
      ->breadcrumb->validate('Breadcrumb', array('string'))->back()
      ->source_question_id->validate('Source question ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
      ->target_question_id->validate('Target question', array('number'=>'integer', 'gt'=>0))->back()
      ->url->validate('URL', array('url'))->back()
      ->url_target->validate('URL target', array('string', 'in'=>array('_blank', '_parent', '_self', '_top')))->back()
    ;
    
    string_if_empty($data, 'target_question_id', 'url', 'url_target');
    
    return tx('Sql')
      ->table('wizard', 'Answers')
      ->pk($aid)
      ->execute_single()
      ->is('empty', function(){
        throw new \exception\NotFound();
      })
      ->merge($data)
      ->save();
    
  }
  
  protected function delete_answer($data, $params)
  {
    
    $aid = $params->{0}
      ->validate('Answer ID', array('required', 'number'=>'integer', 'gt'=>0));
    
    tx('Sql')
      ->table('wizard', 'Answers')
      ->pk($aid)
      ->execute_single()
      ->is('empty', function(){
        throw new \exception\NotFound();
      })
      ->delete();
    
    return true;
    
  }
  
}
