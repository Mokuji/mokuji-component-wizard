<?php namespace components\wizard; if(!defined('TX')) die('No direct access.'); ?>

<script id="tx-wizard-question-opt" type="text/x-jquery-tmpl">
  <option value="${id}"{{if id == start_question_id}} selected="selected"{{/if}}>${title}</option>
</script>

<script id="tx-wizard-question-li" type="text/x-jquery-tmpl">
  <li class="question{{if !title}} untitled{{/if}}">
    <a href="#" data-id="${id}" >{{if title}}${title}{{else}}<?php echo ___("Untitled", "ucfirst"); ?>{{/if}}</a>
  </li>
</script>

<script id="tx-wizard-question-view" type="text/x-jquery-tmpl">
  <div class="question" data-id="${id}">
    <input type="button" class="button icon-edit edit_question" value="<?php echo ___('Edit question', 'ucfirst'); ?>" />
    <input type="button" class="button icon-delete delete_question" value="<?php echo ___('Delete question', 'ucfirst'); ?>" />
    <h4>${title}</h4>
    <p>${description}</p>
  </div>
  <div class="answers"></div>
  <input type="button" class="button black add_answer" value="<?php echo ___('Add answer', 'ucfirst'); ?>" />
</script>

<script id="tx-wizard-question-edit" type="text/x-jquery-tmpl">
  <div class="question" data-id="${id}">
    <form class="edit-question-form form" method="{{if id}}PUT{{else}}POST{{/if}}" action="?rest=wizard/question{{if id}}/${id}{{/if}}">
      <input type="hidden" name="wizard_id" value="${wizard_id}" />
      <input type="text" class="big large" name="title" value="${title}" placeholder="<?php echo ___('Title', 'ucfirst'); ?>" /><br />
      <textarea name="description" class="big large" placeholder="<?php echo ___('Description', 'ucfirst'); ?>">${description}</textarea><br />
      <textarea name="breadcrumb" class="big large" placeholder="<?php echo ___('Breadcrumb', 'ucfirst'); ?>">${breadcrumb}</textarea>
      <div class="buttonHolder">
        <input type="button" class="button grey cancel" value="<?php echo ___('Cancel', 'ucfirst'); ?>" />
        <input type="submit" class="button black" value="<?php echo ___('Save question', 'ucfirst'); ?>" />
      </div>
    </form>
  </div>
</script>

<script id="tx-wizard-answer-view" type="text/x-jquery-tmpl">
  <div class="answer" data-id="${id}">
    <input type="button" class="button icon-edit edit_answer" value="<?php echo ___('Edit answer', 'ucfirst'); ?>" />
    <input type="button" class="button icon-delete delete_answer" value="<?php echo ___('Delete answer', 'ucfirst'); ?>" />
    <h5>${title}</h5>
    <p>${description}</p>
  </div>
</script>

<script id="tx-wizard-answer-edit" type="text/x-jquery-tmpl">
  <div class="answer" data-id="${id}">
    <form class="edit-answer-form form" method="{{if id}}PUT{{else}}POST{{/if}}" action="?rest=wizard/answer{{if id}}/${id}{{/if}}">
      <input type="hidden" name="source_question_id" value="${source_question_id}" />
      <input type="text" class="big large" name="title" value="${title}" placeholder="<?php echo ___('Title', 'ucfirst'); ?>" /><br />
      <textarea name="description" class="big large" placeholder="<?php echo ___('Description', 'ucfirst'); ?>">${description}</textarea><br />
      
      <div class="question_refer_box">
        <h3>This question refers to:</h3>
        <label>1. <input type="radio"  selected name="refer_to" value="question" hidden /> <?php echo ___('Another question'); ?></label>
        
        <div class="refer-to-question-wrapper">
          <select class="big large" name="target_question_id" value="${target_question_id}">
            <option value="">-- Questions --</option>
            {{each questions}}
              {{if $value.id != source_question_id}}
                <option value="${$value.id}"{{if target_question_id}}{{if target_question_id == $value.id}} selected="selected"{{/if}}{{/if}}>${$value.title}</option>
              {{/if}}
            {{/each}}
          </select>
        </div>

        <label>Or 2. <input type="radio" name="refer_to" value="url" hidden /> <?php echo ___('An URL'); ?></label>
        
        <div class="refer-to-url-wrapper">
          <input type="text" class="big large" name="url" value="${url}" placeholder="<?php echo ___('URL', 'u'); ?>" />
          <input type="text" class="small" name="url_target" value="{{if url_target }}${url_target}{{else}}_blank{{/if}}" placeholder="<?php echo ___('URL target', 'ucfirst'); ?>" /><br />
        </div>
        
      </div>
      
      <div class="buttonHolder">
        <input type="button" class="button grey cancel" value="<?php echo ___('Cancel', 'ucfirst'); ?>" />
        <input type="submit" class="button black" value="<?php echo ___('Save answer', 'ucfirst'); ?>" />
      </div>
    </form>
  </div>
</script>

<div id="start_question_wrapper">
  <h2><?php echo ___('Start question', 'ucfirst'); ?></h2>
  <select id="start_question_id" name="start_question_id"></select>
</div>

<div id="question_list_wrapper">

  <h2><?php echo ___('Question list', 'ucfirst'); ?></h2>
  
  <a class="new_question button black" href="#"><?php echo ___('Add question', 'ucfirst'); ?></a>
  <ul id="question_list">
  </ul>
  <script type="text/javascript">
    function resizeQuestionList(){
      var docHeight = $(document).height();
      $('#question_list').css('height' , docHeight-'583')
    }
    $(window).bind('resize', function () {         
      resizeQuestionList();
    });
    $(function(){
      resizeQuestionList();
    });
  </script>
</div>

<div id="question_wrapper"></div>
<div class="clear"></div>

<script type="text/javascript">
jQuery(function($){
  $.txWizardEditor(<?php echo $data->wizard->id; ?>, '#question_list', '#question_wrapper', '#start_question_id');
});
</script>
