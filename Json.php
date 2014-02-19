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
      // ->url->validate('string', array('url'))->back()
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
      // ->url->validate('URL', array('url'))->back()
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
 
  /**
   * Nodes
   */
  protected function get_nodes($options, $params)
  {
    
    $params->{0}->is('set', function($page_id)use($options){
      $options->merge(array(
        'page_id' => $page_id
      ));
    });
    
    $options = $options->having('page_id')
      ->page_id->validate('Page ID', array('number'=>'integer', 'gt'=>0))->back()
    ;

    $options->page_id->set($options->page_id->otherwise(mk('Url')->url->data->pid));
    
    return tx('Sql')
      ->table('wizard', 'Nodes')
      ->is($options->page_id->is_set(), function($q)use($options){
        $q->sk($options->page_id);
      })
      ->add_absolute_depth('depth')
      ->order('lft')
      ->execute();
    
  }

  protected function get_node($options, $params)
  {
    
    $params->{0}->is('set', function($node_id)use($options){
      $options->merge(array(
        'node_id' => $node_id
      ));
    });
    
    // $options = $options->having('node_id')
    //   ->node_id->validate('Node ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
    // ;
    
    return tx('Sql')
      ->table('wizard', 'Nodes')
      ->pk($options->node_id)
      ->execute_single()
      ->is('empty', function(){
        return tx('Sql')->model('wizard', 'Nodes');
      });
    
  }

  protected function post_node_below($data, $params)
  {
    
    if(!$this->table('Nodes')
        ->where('id', $params->{0})
        ->count()->get('boolean'))
      throw new \exception\NotFound('Invalid ID given for parent node.');
    
    return tx('Sql')
      ->model('wizard', 'Nodes')
      ->set($data->having('page_id'))
      ->hsave($params->{0});
    
  }

  protected function create_node($data, $params)
  {
    
    // $data = $data->having('wizard_id', 'title', 'description')
    //   ->wizard_id->validate('Wizard ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
    //   ->title->validate('Title', array('required', 'string', 'not_empty'))->back()
    //   ->description->validate('Description', array('string'))->back()
    // ;
    
    return tx('Sql')
      ->model('wizard', 'Nodes')
      ->set($data)
      ->hsave();
    
  }
  
  protected function update_node($data, $params)
  {
    
    $node_id = $params->{0}
      ->validate('Node ID', array('required', 'number'=>'integer', 'gt'=>0));
    
    // $data = $data->having('node_id', 'title', 'description')
    //   ->wizard_id->validate('Wizard ID', array('required', 'number'=>'integer', 'gt'=>0))->back()
    //   ->title->validate('Title', array('required', 'string', 'not_empty'))->back()
    //   ->description->validate('Description', array('string'))->back()
    // ;
    
    return tx('Sql')
      ->table('wizard', 'Nodes')
      ->pk($node_id)
      ->execute_single()
      ->is('empty', function(){
        throw new \exception\NotFound();
      })
      ->merge($data)
      ->hsave();
    
  }
  
  protected function delete_node($data, $params)
  {
    
    $qid = $params->{0}
      ->validate('Node ID', array('required', 'number'=>'integer', 'gt'=>0));
    
    tx('Sql')
      ->table('wizard', 'Nodes')
      ->pk($qid)
      ->execute_single()
      ->is('empty', function(){
        throw new \exception\NotFound();
      })
      ->hdelete();
    
    return true;
    
  }

  protected function update_nodes_hierarchy($data, $params)
  {

    $data->nodes->each(function($q){

      tx('Sql')->table('wizard', 'Nodes')->pk($q->item_id)->execute_single()
          ->not('empty', function($row)use($q){

            $row->merge($q->having(array(
              'id' => 'item_id',
              'lft' => 'left',
              'rgt' => 'right'
            )))
            
            ->save();

          })->failure(function()use($q){

            tx('Sql')->model('wizard', 'Nodes')->merge($q->having(array(
              'id' => 'item_id',
              'lft' => 'left',
              'rgt' => 'right'
            )))
            
            ->save();

          });
      
    });
    
    return $this->get_nodes(Data(), $params);
    
  }

}
