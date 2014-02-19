<?php namespace components\wizard; if(!defined('TX')) die('No direct access.'); ?>

<li class="question{{if !data.answer_title}} untitled{{/if}}" rel="${data.id}">
  <div>
    <span class="small-icon icon-collapse icon-toggle"></span>
    <a href="#" data-id="${data.id}">
      {{if data.answer_title}}${data.answer_title}{{else}}Nog in te vullen{{/if}}
      <small>{{if data.question_title}}${data.question_title}{{else}}(geen vervolgvraag){{/if}}</small>
    </a>
    <button class="add-node-below" data-id="${data.id}">+</button>
  </div>
</li>
