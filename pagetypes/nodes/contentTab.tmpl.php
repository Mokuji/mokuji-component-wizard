<?php namespace components\wizard; if(!defined('MK')) die('No direct access.'); ?>

<div class="clearfix">
  
  <div class="wizard-node-view">
    {{html template('nodeList')}}
  </div>

  <div class="wizard-node-editor">
    {{html template('editNode')}}
  </div>
  
</div>