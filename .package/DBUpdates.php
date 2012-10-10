<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

//Make sure we have the things we need for this class.
tx('Component')->load('update', 'classes\\BaseDBUpdates', false);

class DBUpdates extends \components\update\classes\BaseDBUpdates
{
  
  protected
    $component = 'wizard',
    $updates = array();
  
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
