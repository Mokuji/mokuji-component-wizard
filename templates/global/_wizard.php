<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

$uid = tx('Security')->random_string(20);

echo load_plugin('jquery_rest');
echo load_plugin('jquery_tmpl');

$data->wizard->is('empty')
  
  ->success(function(){
    __('Wizard could not be found', 0, 'ucfirst');
  })
  
  ->failure(function($wizard)use($uid){
    
    ?>
    <div class="wizard_<?php echo $uid ?>"></div>
    
    <?php tx('ob')->script('wizard_module', "templates"); ?>
      
      <script id="tx-wizard-question-tmpl" type="text/x-jquery-tmpl">
        <div class="breadcrumbs"></div>
        <div class="question" data-id="${id}">
          <input type="button" class="back_button" value="<?php echo ___('Go back', 'ucfirst'); ?>" />
          <h4>${title}</h4>
          <p>${description}</p>
        </div>
        <div class="answers"></div>
      </script>
      
      <script id="tx-wizard-answer-tmpl" type="text/x-jquery-tmpl">
        <a class="answer" data-id="${id}" {{if target_question_id}}data-target-question-id="${target_question_id}"{{else}}href="${url}" target="${url_target}"{{/if}}>
          <h5>${title}</h5>
          <p>${description}</p>
          <p class="breadcrumb" style="display:none;">${breadcrumb}</p>
        </a>
      </script>
      
      <script id="tx-wizard-breadcrumb-tmpl" type="text/x-jquery-tmpl">
        <a href="#" class="breadcrumb" {{if id}}data-id="${id}" {{/if}}data-target-question-id="${target_question_id}">${breadcrumb}</a>
      </script>
      
      <script id="tx-wizard-notfound-tmpl" type="text/x-jquery-tmpl">
        <div class="error"><?php echo ___('Unable to load start question for wizard ID'); ?> ${id}.</div>
      </script>
      
    <?php tx('ob')->end(); ?>
    
    <?php tx('ob')->script('wizard_module', "wiz_{$uid}"); ?>
      
      <script type="text/javascript">
        
        $(function(){
          $('.wizard_<?php echo $uid ?>').txWizard({
            wizard_id: <?php echo $wizard->id ?>,
            home_title: "<?php echo ___('Home', 'ucfirst'); ?>"
          });
        });
        
      </script>
      
    <?php
    tx('ob')->end();
    
  });
  
?>
