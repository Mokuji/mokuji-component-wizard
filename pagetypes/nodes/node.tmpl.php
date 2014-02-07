<?php namespace components\wizard; if(!defined('TX')) die('No direct access.'); ?>

<li class="question{{if !data.answer_title}} untitled{{/if}}" rel="${id}">
  <div>
    <a href="#" data-id="${data.id}">{{if data.answer_title}}${data.answer_title}{{else}}Untitled{{/if}}</a>
    <button class="add-node-below" data-id="${data.id}">+</button>
  </div>
</li>
