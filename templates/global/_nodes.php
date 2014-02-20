<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');

$uid = tx('Security')->random_string(20);

echo load_plugin('jquery_rest');
echo load_plugin('jquery_tmpl');
echo load_plugin('to_hierarchy');

$data->wizard->is('empty')
  
  ->success(function()use($names){
    __($names->component, 'Wizard could not be found', 0, 'ucfirst');
  })
  
  ->failure(function($wizard)use($uid, $names){
    
    ?>
    <div class="tx-wizard wizard_<?php echo $uid ?>"></div>
    
    <?php tx('ob')->script('wizard_module', "templates"); ?>
      
      <script id="tx-wizard-question-tmpl" type="text/x-jquery-tmpl">
        <ul class="breadcrumbs"></ul>
        <div class="question" data-id="${id}">
          <input type="button" class="back_button" value="<?php __($names->component, 'Go back', 'ucfirst'); ?>" />
          <h4>${question_title}&nbsp;</h4>
        </div>
        <div class="answers"></div>
      </script>
      
      <script id="tx-wizard-answer-tmpl" type="text/x-jquery-tmpl">
        <a class="answer" data-id="${id}" data-nr="${nr}" {{if url}}href="${url}" target="${url_target}"{{/if}}>
          <h5>${answer_title}</h5>
        </a>
      </script>
      
      <script id="tx-wizard-breadcrumb-tmpl" type="text/x-jquery-tmpl">
        <li>
          <a href="#" class="breadcrumb" {{if id}}data-id="${id}" {{/if}}>{{html question_title}}</a>
        </li>
      </script>
      
      <script id="tx-wizard-notfound-tmpl" type="text/x-jquery-tmpl">
        <div class="error"><?php __($names->component, 'Unable to load start question for wizard ID'); ?> ${id}.</div>
      </script>
      
    <?php tx('ob')->end(); ?>
    
    <?php tx('ob')->script('wizard_module', "wiz_{$uid}"); ?>
      
      <script type="text/javascript">
        
        $(function(){
          $('.wizard_<?php echo $uid ?>').txNodes({
            page_id: <?php echo mk('Url')->url->data->pid; ?>,
            home_title: "<?php __($names->component, 'Start', 'ucfirst'); ?>"
          });
        });
        
      </script>
      
    <?php
    tx('ob')->end();
    
  });
  
?>
