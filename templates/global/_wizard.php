<?php namespace components\wizard; if(!defined('TX')) die('No direct access.'); ?>

<div class="wizard<?php echo $wizard->id ?>"></div>

<?php tx('ob')->script('wizard_module', "wiz{$wizard->id}"); ?>

<script type="text/javascript">
  
  $(function(){
    $('.wizard<?php echo $wizard->id ?>').txWizard({
      root: <?php echo $wizard->id ?> 
    });
  });
  
</script>

<?php tx('ob')->end(); ?>