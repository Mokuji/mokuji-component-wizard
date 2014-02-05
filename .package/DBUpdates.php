<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

//Make sure we have the things we need for this class.
tx('Component')->check('update');
tx('Component')->load('update', 'classes\\BaseDBUpdates', false);

class DBUpdates extends \components\update\classes\BaseDBUpdates
{
  
  protected
    $component = 'wizard',
    $updates = array(
      
      '1.1' => '1.2',
      '1.2' => '1.3',
      '1.3' => '1.4',
      
      '1.4' => '0.2.0-beta'
      
    );
  
  public function update_to_0_2_0_beta($current_version, $forced)
  {
    
    if($forced){
      mk('Sql')->query("DROP TABLE IF EXISTS `#__wizard__nodes`");
    }
    
    mk('Sql')->query('
      CREATE TABLE `#__wizard__nodes` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `parent_node_id` int(10) UNSIGNED NULL DEFAULT NULL,
        `answer_title` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `question_title` varchar(255) NULL DEFAULT NULL,
        `url` varchar(255) NULL DEFAULT NULL,
        `url_target` varchar(255) NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `parent_node_id` (`parent_node_id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ');
    
    mk('Sql')->query('
      CREATE TABLE `#__wizard__node_pages` (
        `page_id` int(10) UNSIGNED NOT NULL,
        `node_id` int(10) UNSIGNED NOT NULL,
        PRIMARY KEY (`page_id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ');
    
    $this->queue(array(
      'component' => 'cms',
      'min_version' => '0.4.1-beta'
      ), function($version){
        
        //Make page type.
        mk('Component')->helpers('cms')->_call('ensure_pagetypes', array(
          array(
            'name' => 'wizard',
            'title' => 'Wizard'
          ),
          array(
            'nodes' => 'PAGETYPE'
          )
        ));
        
      }
    ); //END - Queue CMS
    
  }
  
  public function update_to_1_4($current_version, $forced)
  {
    
    //Queue translation token update with CMS component.
    $this->queue(array(
      'component' => 'cms',
      'min_version' => '1.2'
      ), function($version){
          
          $component = tx('Sql')
            ->table('cms', 'Components')
            ->where('name', "'{$this->component}'")
            ->execute_single();
          
          tx('Sql')
            ->table('cms', 'ComponentViews')
            ->where('com_id', $component->id)
            ->execute()
            ->each(function($view){
              
              //If tk_title starts with 'COMNAME_' remove it.
              if(strpos($view->tk_title->get('string'), strtoupper($this->component.'_')) === 0){
                $view->tk_title->set(
                  substr($view->tk_title->get('string'), (strlen($this->component)+1))
                );
              }
              
              //If tk_description starts with 'COMNAME_' remove it.
              if(strpos($view->tk_description->get('string'), strtoupper($this->component.'_')) === 0){
                $view->tk_description->set(
                  substr($view->tk_description->get('string'), (strlen($this->component)+1))
                );
              }
              
              $view->save();
              
            });
          
        }); //END - Queue CMS 1.2+
    
  }
  
  public function update_to_1_3($current_version, $forced)
  {
    
    tx('Sql')->query('
      ALTER TABLE `#__wizard_questions`
        ADD `lft` int(10) UNSIGNED NOT NULL AFTER `id`,
        ADD `rgt` int(10) UNSIGNED NOT NULL AFTER `lft`
    ');
    
    //Insert starting heirarchy
    $i = 1;
    tx('Sql')
      ->table('wizard', 'Questions')
      ->execute()
      ->each(function($question)use(&$i){
        $question->lft->set($i++);
        $question->rgt->set($i++);
        $question->save();
      });
    
    
  }
  
  public function update_to_1_2($current_version, $forced){
    
    tx('Sql')->query('
      ALTER TABLE `#__wizard_questions`
        DROP `breadcrumb`
    ');
    
    tx('Sql')->query('
      ALTER TABLE `#__wizard_answers`
        ADD `breadcrumb` varchar(255) DEFAULT NULL
    ');
    
  }
  
  public function install_1_1($dummydata, $forced)
  {
    
    if($forced === true){
      tx('Sql')->query('DROP TABLE IF EXISTS `#__wizard_answers`');
      tx('Sql')->query('DROP TABLE IF EXISTS `#__wizard_questions`');
      tx('Sql')->query('DROP TABLE IF EXISTS `#__wizard_wizards`');
    }
    
    tx('Sql')->query('
      CREATE TABLE `#__wizard_answers` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `source_question_id` int(10) UNSIGNED NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` longtext NOT NULL,
        `target_question_id` int(11) DEFAULT NULL,
        `url` varchar(255) DEFAULT NULL,
        `url_target` varchar(8) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ');
    tx('Sql')->query('
      CREATE TABLE `#__wizard_questions` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `wizard_id` int(10) UNSIGNED NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` longtext NOT NULL,
        `breadcrumb` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ');
    tx('Sql')->query('
      CREATE TABLE `#__wizard_wizards` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `start_question_id` int(11) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ');
    
    //Queue self-deployment with CMS component.
    $this->queue(array(
      'component' => 'cms',
      'min_version' => '1.2'
      ), function($version){
          
          //Look for the component in the CMS tables.
          $component = tx('Sql')
            ->table('cms', 'Components')
            ->where('name', "'wizard'")
            ->limit(1)
            ->execute_single()
            
            //If it's not there, create it.
            ->is('empty', function(){
              
              return tx('Sql')
                ->model('cms', 'Components')
                ->set(array(
                  'name' => 'wizard',
                  'title' => 'Wizard component'
                ))
                ->save();
              
            });
          
          //Look for the wizard view.
          tx('Sql')
            ->table('cms', 'ComponentViews')
            ->where('com_id', $component->id)
            ->where('name', "'wizard'")
            ->limit(1)
            ->execute_single()
            
            //If it's not there, create it.
            ->is('empty', function()use($component){
              
              $view = tx('Sql')
                ->model('cms', 'ComponentViews')
                ->set(array(
                  'com_id' => $component->id,
                  'name' => 'wizard',
                  'tk_title' => 'WIZARD_WIZARD_VIEW_TITLE',
                  'tk_description' => 'WIZARD_WIZARD_VIEW_DESCRIPTION',
                  'is_config' => '0'
                ))
                ->save();
              
            });
          
        }); //END - Queue CMS 1.2+
    
  }
  
}
