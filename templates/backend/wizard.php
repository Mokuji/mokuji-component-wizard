<?php namespace components\wizard; if(!defined('TX')) die('No direct access.');
echo load_plugin('to_hierarchy');
echo load_plugin('elfinder');
?>

<script id="tx-wizard-question-opt" type="text/x-jquery-tmpl">
  <option value="${id}"{{if depth}} style="padding-left:${10*depth}px;"{{/if}}{{if id == start_question_id}} selected="selected"{{/if}}>${title}</option>
</script>

<script id="tx-wizard-question-li" type="text/x-jquery-tmpl">
  <li class="question{{if !title}} untitled{{/if}}" rel="${id}">
    <div>
      <a href="#" data-id="${id}" >{{if title}}${title}{{else}}<?php __($names->component, "Untitled", "ucfirst"); ?>{{/if}}</a>
    </div>
  </li>
</script>

<script id="tx-wizard-question-view" type="text/x-jquery-tmpl">
  <div class="question" data-id="${id}">
    <input type="button" class="button icon-edit edit_question" value="<?php __($names->component, 'Edit question', 'ucfirst'); ?>" />
    <input type="button" class="button icon-delete delete_question" value="<?php __($names->component, 'Delete question', 'ucfirst'); ?>" />
    <h4>${title}</h4>
    <p>{{html $description}}</p>
  </div>
  <div class="answers"></div>
  <input type="button" class="button black add_answer" value="<?php __($names->component, 'Add answer', 'ucfirst'); ?>" />
</script>

<script id="tx-wizard-question-edit" type="text/x-jquery-tmpl">
  <div class="question" data-id="${id}">
    <form class="edit-question-form form" method="{{if id}}PUT{{else}}POST{{/if}}" action="?rest=wizard/question{{if id}}/${id}{{/if}}">
      <input type="hidden" name="wizard_id" value="${wizard_id}" />
      <input type="text" class="big large" name="title" value="${title}" placeholder="<?php __('Title', 0, 'ucfirst'); ?>" /><br>
      <textarea name="description" class="big large tx-editor" placeholder="<?php __('Description', 0, 'ucfirst'); ?>">${description}</textarea>
      <div class="buttonHolder">
        <input type="button" class="button grey cancel" value="<?php __('Cancel', 0, 'ucfirst'); ?>" />
        <input type="submit" class="button black" value="<?php __($names->component, 'Save question', 'ucfirst'); ?>" />
      </div>
    </form>
  </div>
</script>

<script id="tx-wizard-answer-view" type="text/x-jquery-tmpl">
  <div class="answer" data-id="${id}">
    <input type="button" class="button icon-edit edit_answer" value="<?php __($names->component, 'Edit answer', 'ucfirst'); ?>" />
    <input type="button" class="button icon-delete delete_answer" value="<?php __($names->component, 'Delete answer', 'ucfirst'); ?>" />
    <!--<p class="breadcrumb">${breadcrumb}</p>-->
    <h5>${title}</h5>
    <p>{{html $description}}</p>
  </div>
</script>

<script id="tx-wizard-answer-edit" type="text/x-jquery-tmpl">
  <div class="answer" data-id="${id}">
    <form class="edit-answer-form form" method="{{if id}}PUT{{else}}POST{{/if}}" action="?rest=wizard/answer{{if id}}/${id}{{/if}}">
      <input type="hidden" name="source_question_id" value="${source_question_id}" />
      
      <h3><?php __('Title', 0, 'ucfirst'); ?></h3>
      
      <p>
        <input type="text" class="big large" name="title" value="${title}" placeholder="<?php __('Title', 0, 'ucfirst'); ?>" />
      </p>
      
      <br /><h3><?php __('Description', 0, 'ucfirst'); ?></h3>
      <textarea name="description" class="big large tx-editor" placeholder="<?php __('Description', 0, 'ucfirst'); ?>">${description}</textarea><br>
      
      <h3><?php __($names->component, 'Breadcrumb'); ?></h3>
      <textarea name="breadcrumb" class="big large tx-editor" placeholder="<?php __($names->component, 'Breadcrumb', 'ucfirst'); ?>">${breadcrumb}</textarea>
      
      <div class="question_refer_box">
        <br /><h3><?php __($names->component, 'This answer refers to', 'ucfirst'); ?>:</h3>
        <label>1. <input type="radio"  selected name="refer_to" value="question" hidden /> <?php __($names->component, 'A different question', 'ucfirst'); ?></label>
        
        <div class="refer-to-question-wrapper">
          <select class="big large" name="target_question_id">
            <option value="">-- Questions --</option>
            <option value="" class="new_question"><?php __($names->component, 'Add new question', 'ucfirst'); ?></option>
            {{each questions}}
              {{if $value.id != source_question_id}}
                <option value="${$value.id}"{{if _depth}} style="padding-left:${10*_depth}px;"{{/if}}{{if target_question_id}}{{if target_question_id == $value.id}} selected="selected"{{/if}}{{/if}}>${$value.title}</option>
              {{/if}}
            {{/each}}
          </select>
        </div>

        <label><?php __('Or'); ?> 2. <input type="radio" name="refer_to" value="url" hidden /> <?php __($names->component, 'A URL'); ?>/<?php __('File', 0, 'l'); ?></label>
        
        <div class="refer-to-url-wrapper">
          <input type="text" class="medium" name="url" value="${url}" placeholder="<?php __('URL', 0, 'u'); ?>" />
          <input type="text" class="small" name="url_target" value="{{if url_target }}${url_target}{{else}}_blank{{/if}}" placeholder="<?php __($names->component, 'URL target', 'ucfirst'); ?>" />
          <a class="button grey elfinder"><?php __('Browse'); ?>...</a>
        </div>
        
      </div>
      
      <div class="buttonHolder">
        <input type="button" class="button grey cancel" value="<?php __('Cancel', 0, 'ucfirst'); ?>" />
        <input type="submit" class="button black" value="<?php __($names->component, 'Save answer', 'ucfirst'); ?>" />
      </div>
    </form>
  </div>
</script>

<div id="start_question_wrapper">
  <h2><?php __($names->component, 'Start question', 'ucfirst'); ?></h2>
  <select id="start_question_id" name="start_question_id"></select>
</div>

<div id="question_list_wrapper">

  <h2><?php __($names->component, 'Question list', 'ucfirst'); ?></h2>
  
  <a class="new_question button black" href="#"><?php __($names->component, 'Add question', 'ucfirst'); ?></a>
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
  window.elFinder.PLUGIN_URL = "<?php echo URL_PLUGINS.'elfinder/php/connector.php'; ?>";
  $.txWizardEditor(<?php echo $data->wizard->id; ?>, '#question_list', '#question_wrapper', '#start_question_id');
});
</script>
