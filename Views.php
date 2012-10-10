<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

class Views extends \dependencies\BaseViews
{
  
  protected function wizard($options)
  {
    
    //Backend
    if(tx('Config')->system()->check('backend'))
    {
      
      $wizard = null;
      
      //If no wizard ID has been given, create new Wizard.
      $options->wizard_id->is('empty', function()use($options, &$wizard){
        
        $page = tx('Sql')
          ->table('cms', 'Pages')
          ->pk($options->pid->value)
          ->execute_single()
          ->is('empty', function(){
            throw new \exception\InvalidArgument('Neither page ID nor Wizard ID have been provided');
          });
        
        $optset = tx('Sql')
          ->table('cms', 'OptionSets')
          ->pk($page->optset_id)
          ->execute_single()
          
          //Create option set and bind it to page if need be.
          ->is('empty', function()use($page){
            
            $optset = tx('Sql')
              ->model('cms', 'OptionSets')
              ->set(array(
                'title' => 'Wizard ID for this page'
              ))
              ->save();
            
            $page->optset_id->set($optset->id);
            $page->save();
            
            return $optset;
            
          });
        
        $wiz_opt = tx('Sql')
          ->table('cms', 'Options')
          ->join('OptionSets', $OS)
          ->where("$OS.id", $optset->id)
          ->where('key', "'wizard_id'")
          ->execute_single()
          
          //Add option to option set if need be.
          ->is('empty', function()use($optset, &$wizard){
            
            $wizard = tx('Sql')
              ->model('wizard', 'Wizards')
              ->set(array(
                'title' => ___('New wizard', 'ucfirst'),
                'description' => '',
                'start_question_id' => 0
              ))
              ->save();
            
            $option = tx('Sql')
              ->model('cms', 'Options')
              ->set(array(
                'key' => 'wizard_id',
                'value' => $wizard->id
              ))
              ->save();
            
            $optlink = tx('Sql')
              ->model('cms', 'OptionsLink')
              ->set(array(
                'optset_id' => $optset->id,
                'option_id' => $option->id
              ))
              ->save();
            
            return $option;
            
          });
        
      });
      
      return array(
        'wizard' => $wizard !== null ? $wizard : tx('Sql')
          ->table('wizard', 'Wizards')
          ->pk($options->wizard_id->value)
          ->execute_single()
      );
      
    }
    
    //Frontend
    else
    {
      
      return array(
        $this->module('wizard', array('wizard_id'=>$options->wizard_id->value))
      );
      
    }
  }
  
  protected function wizards($options)
  {
    
    return array();
    
  }
  
}
