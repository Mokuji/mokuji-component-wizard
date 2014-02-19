/**
 * txWizardEditor
 *
 * @author Beanow
 */
(function($){
  
  $.txWizardEditor = function(wiz_id, qlist, qview, squestion)
  {
    
    var wizard_id = 0
      , wizard = {}
      , active_question = 0
      , questions = {}
      , question_hierarchy = []
      , answers = {}
      , question_list = null
      , question_view = null
      , start_question = null;
    
    if(wiz_id <= 0){
      alert('Fatal error: no wizard ID given for wizard editor.');
      return;
    }
    
    wizard_id = wiz_id;
    question_list = $(qlist);
    question_view = $(qview);
    start_question = $(squestion);
    
    if(question_list.size() <= 0){
      alert('Fatal error: no valid question list selector provided for wizard editor.');
      return;
    }
    
    if(question_view.size() <= 0){
      alert('Fatal error: no valid question view selector provided for wizard editor.');
      return;
    }
    
    if(start_question.size() <= 0){
      alert('Fatal error: no valid start question selector provided for wizard editor.');
      return;
    }
    
    init_view(question_view);
    init_menu();
    
    var getq = $.rest('GET', '?rest=wizard/questions/'+wizard_id)
      .done(function(result){
        $.each(result, function(i){
          questions[result[i].id] = result[i];
        });
        question_hierarchy = toHierarchy('lft', 'rgt', result);
      });
    
    var getw = $.rest('GET', '?rest=wizard/wizard/'+wizard_id)
      .done(function(result){
        wizard = result;
      });
    
    $.when(getq, getw)
      .done(function(){
        render_menu();
      });
    
    function init_view(target){
      
      target
        
        /* ---------- Click edit_question button ---------- */
        .on('click', '.edit_question', function(e){
          e.preventDefault();
          edit_question($(e.target).closest('.question').attr('data-id'));
        })
        
        /* ---------- Click delete_question button ---------- */
        .on('click', '.delete_question', function(e){
          e.preventDefault();
          if(confirm("Really?")){
            var qid = $(e.target).closest('.question').attr('data-id');
            $.rest('DELETE', '?rest=wizard/question/'+qid)
              .done(function(result){
                delete questions[qid];
                remove_menu_item(qid);
                question_list.trigger('sortupdate');
                question_view.html('');
              });
          }
        })
        
        /* ---------- Click cancel button for question editing ---------- */
        .on('click', '.edit-question-form .cancel', function(e){
          e.preventDefault();
          if(active_question > 0)
            to_question(active_question);
          else
            question_view.html('');
        })
        
        /* ---------- Click edit_answer button ---------- */
        .on('click', '.edit_answer', function(e){
          e.preventDefault();
          edit_answer($(e.target).closest('.answer').attr('data-id'));
        })
        
        /* ---------- Click delete_answer button ---------- */
        .on('click', '.delete_answer', function(e){
          e.preventDefault();
          if(confirm("Really?")){
            var aid = $(e.target).closest('.answer').attr('data-id');
            $.rest('DELETE', '?rest=wizard/answer/'+aid)
              .done(function(result){
                delete answers[aid];
                $(e.target).closest('.answer').remove();
              });
          }
        })
        
        /* ---------- Click cancel button for answer editing ---------- */
        .on('click', '.edit-answer-form .cancel', function(e){
          e.preventDefault();
          var answer = $(e.target).closest('.answer');
          if(answer.attr('data-id') == '')
            answer.remove();
          else
            answer.replaceWith($('#tx-wizard-answer-view').tmpl(answers[answer.attr('data-id')]));
        })
        
        /* ---------- Click add_answer button ---------- */
        .on('click', '.add_answer', function(e){
          e.preventDefault();
          edit_answer('new');
        })

      ;
      
    }
    
    function init_menu(){
      
      start_question
        
        /* ---------- Change start_question_id ---------- */
        .on('change', function(e){
          e.preventDefault();
          wizard.start_question_id = $(e.target).val();
          $.rest('PUT', '?rest=wizard/wizard/'+wizard.id, wizard)
            .done(function(result){
              wizard = result;
            });
        })
        
      ;
      
      question_list
        
        /* ---------- Click question ---------- */
        .on('click', 'li.question a', function(e){
          e.preventDefault();
          to_question($(e.target).attr('data-id'));
        })
        
        /* ---------- Nested sortable ---------- */
        .nestedSortable({
			    disableNesting: 'no-nest',
			    forcePlaceholderSize: true,
			    handle: 'div',
			    helper:	'clone',
			    listType: 'ul',
			    items: 'li',
			    maxLevels: 7,
			    opacity: .6,
			    placeholder: 'placeholder',
			    revert: 250,
			    tabSize: 25,
			    tolerance: 'pointer',
			    toleranceElement: '> div'
		    })
        
        /* ---------- Sort update ---------- */
        .on('sortupdate', function(e){
          
          $.rest(
            'PUT',
            '?rest=wizard/questions_hierarchy/'+wizard_id,
            {questions: $(e.target).nestedSortable('toArray', {startDepthCount: 0, attribute: 'rel', expression: (/()([0-9]+)/), omitRoot: true})}
          ).done(function(result){
            questions = {};
            $.each(result, function(i){
              questions[result[i].id] = result[i];
            });
            question_hierarchy = toHierarchy('lft', 'rgt', result);
            render_menu();
          });
          
        })
        
        .parent()//Question list wrapper.
        
          /* ---------- Click new question ---------- */
          .on('click', '.new_question', function(e){
            e.preventDefault();
            edit_question('new');
          })
        
      ;
            
    }
    
    function render_menu(){
      
      start_question.find('option').remove();
      question_list.find('.question').remove();
      
      var renderer = function(list_target, option_target, data, depth){
        
        for(var i = 0; i < data.length; i++){
          
          var li = $('#tx-wizard-question-li').tmpl(data[i]);
          list_target.append(li);
          
          option_target.append(
            $('#tx-wizard-question-opt').tmpl($.extend({start_question_id: wizard.start_question_id, depth:depth}, data[i]))
          );
          
          renderer($('<ul>').appendTo(li), option_target, data[i]._children, depth+1);
          
        }
        
      };
      
      renderer(question_list, start_question, question_hierarchy, 0);
      
    }
    
    function insert_menu_item(data)
    {
      
      if(!data.id){
        if(console && console.log) console.log('No data.id for insert_menu_item');
        else alert('No data.id for insert_menu_item');
        return;
      }
      
      questions[data.id] = data;
      question_list.prepend($('#tx-wizard-question-li').tmpl(data));
      start_question.prepend($('#tx-wizard-question-opt').tmpl(data));
      
    }
    
    function remove_menu_item(id){
      
      if(!id){
        if(console && console.log) console.log('No id for remove_menu_item');
        else alert('No id for remove_menu_item');
        return;
      }
      
      question_list.find('li[rel='+id+']').remove();
      start_question.find('option[value='+id+']').remove();
      
    }
    
    function render_answers(target){
      
      target.find('.answer').remove();
      $.each(answers, function(i){
        target.append($('#tx-wizard-answer-view').tmpl(answers[i]));
      });
      
    }
    
    function to_question(qid){

      var question = questions[qid] ? questions[qid] : {};
      question_list.find('li').removeClass('active');
      question_list.find('a[data-id="'+question.id+'"]').closest('li').addClass('active');
      question_view.html($('#tx-wizard-question-view').tmpl(question));
      get_answers(qid);
      
    }
    
    function edit_question(qid){
      
      var question = {wizard_id: wizard_id};
      if(qid !== 'new'){
        $.extend(question, questions[qid]);
        active_question = qid;
      }
      else{
        active_question = 0;
      }
      
      question_view.html($('#tx-wizard-question-edit').tmpl(question));
      
      question_view.find('.tx-editor').each(function(){
        $(this).attr('id', 'tx-editor_'+Math.floor((Math.random()*1000)+1))
        tx_editor.init({selector: '#'+$(this).attr('id')});
      });
      
      question_view.find('.edit-question-form').restForm({
        success: function(question){
          
          //If we had no questions yet.
          //Set this as the start question.
          var has_more = false;
          for(var k in questions)
          {
            
            if(Object.prototype.hasOwnProperty.call(questions, k)){
              has_more = true;
              break;
            }
            
          }
          
          if(!has_more && wizard.start_question_id != question.id){
            wizard.start_question_id = question.id;
            $.rest('PUT', '?rest=wizard/wizard/'+wizard.id, wizard)
              .done(function(result){
                wizard = result;
              });
          }
          
          //See if we're inserting or updating.
          if(!questions[question.id]){
            insert_menu_item(question);
            question_list.trigger('sortupdate');
          } else {
            $.extend(questions[question.id], question);
            render_menu();
          }
          
          to_question(question.id);
          
        }
      })
      
    }
    
    function edit_answer(aid){
      
      var target
        , answer;

      if(aid === 'new'){
        answer = {
          source_question_id: active_question,
          questions: questions
        };
        target = $('#tx-wizard-answer-edit').tmpl(answer).appendTo(question_view.find('.answers'));
      }
      
      else{
        answer = $.extend({questions: questions, active_question: active_question}, answers[aid]);
        target = $('#tx-wizard-answer-edit').tmpl(answer);
        question_view.find('.answer[data-id='+aid+']').replaceWith(target);
      }
      
      target.find('[name="target_question_id"]').on('change', function(e){
        
        e.preventDefault();

        if( ! $(this).find('option:selected').hasClass('new_question') )
          return

        $.rest('POST', '?rest=wizard/question', {
          wizard_id: wizard_id,
          title: window.prompt('Question title?')
        }).done(function(question){
          insert_menu_item(question);
          $('#tx-wizard-question-opt').tmpl(question)
            .insertAfter(target.find('option.new_question'))
            .prop('selected', 'selected');
        });
      });
      
      target.find('.elfinder').elFinderButton({
        closeOnGetFile: true,
        getFileCallback: function(file){
          target.find(':input[name=url]').val(file);
        }
      });
      
      target.find('.tx-editor').each(function(){
        $(this).attr('id', 'tx-editor_'+Math.floor((Math.random()*1000)+1))
        tx_editor.init({selector: '#'+$(this).attr('id')});
      });
      
      target.find('.edit-answer-form').restForm({
        success: function(answer){
          
          answers[answer.id] = answer;
          render_answers(question_view.find('.answers'));
          
        }
      })
      
    }
    
    function get_answers(qid){
      
      var process = function(result){
        
        if(result){
          answers = {};
          $.each(result, function(i){
            answers[result[i].id] = result[i];
          });
        }
        
        active_question = qid;
        render_answers(question_view.find('.answers'));
        
      }
      
      if(active_question > 0 && active_question == qid)
        process();
      else
        $.rest('GET', '?rest=wizard/answers/'+qid).done(process);
      
    }
    
  };
  
})(jQuery);


/**
 * txWizard
 *
 * @author Beanow
 */

(function($){

  $.fn.txWizard = function(options){
    
    var wizard = {}
      , question = {}
      , answers = {}
      , view = $(this)
      , answer_history = [];
    
    if(!options)
     options = {};
    
    if(!options.wizard_id){
      alert('Fatal error: no wizard ID given for wizard.');
      return;
    }
    
    function init(){
      
      wizard.id = options.wizard_id;
      
      bind_events();
      
      $.rest('GET', '?rest=wizard/wizard/'+options.wizard_id)
        .done(function(result){
          wizard = result;
          answer_history.push({target_question_id:wizard.start_question_id, breadcrumb: options.home_title || "home"});
          load_question(wizard.start_question_id);
        });
      
    }
    
    function load_question(question_id)
    {
      
      var getq = $.rest('GET', '?rest=wizard/question/'+question_id)
        .done(function(result){
          question = result;
        })
        .error(function(){
          view.html($('#tx-wizard-notfound-tmpl').tmpl({id:options.wizard_id}));
        });
      
      var geta = $.rest('GET', '?rest=wizard/answers/'+question_id)
        .done(function(result){
          answers = {};
          $.each(result, function(i){
            answers[result[i].id] = result[i];
          });
        });
      
      $.when(getq, geta)
        .done(function(){
          render_question();
        });
      
    }
    
    function render_question(){
      view.html($('#tx-wizard-question-tmpl').tmpl(question));
      render_answers();
      render_breadcrumbs();
    };
    
    function render_answers()
    {
      
      var av = view.find('.answers').html('');
      $.each(answers, function(i){
        av.append($('#tx-wizard-answer-tmpl').tmpl(answers[i]));
      });
      
      if(answer_history.length <= 1){
        $('.back_button').attr('disabled', 'disabled')
      }else{
        $('.back_button').removeAttr('disabled')
      }
    
    }
    
    function render_breadcrumbs()
    {
      
      var bc = view.find('.breadcrumbs').html('');
      $.each(answer_history, function(i){
        bc.append($('#tx-wizard-breadcrumb-tmpl').tmpl(answer_history[i]));
      });
      
    }
    
    function bind_events()
    {
      
      view
        
        /* ---------- Answer click ---------- */
        .on('click', '.answer', function(e){
          var target_question_id = $(this).attr('data-target-question-id');
          if(target_question_id !== undefined){
            e.preventDefault();
            answer_history.push(answers[$(this).attr('data-id')]);
            load_question(target_question_id);
          }
        })
        
        /* ---------- Back button ---------- */
        .on('click', '.back_button:not(:disabled)', function(e){
          e.preventDefault();
          answer_history.pop();
          load_question(answer_history[answer_history.length-1].target_question_id);
        })
        
        /* ---------- Breadcrumb click ---------- */
        .on('click', '.breadcrumbs a.breadcrumb', function(e){
          e.preventDefault();
          var target_index = $(e.target).closest('li').index();
          while(answer_history.length > target_index + 1)
            answer_history.pop();
          load_question(answer_history[answer_history.length-1].target_question_id);
        })
        
      ;
      
    }
    
    init();
    
  };
  
})(jQuery);


/**
 * txWizard
 *
 * @author Beanow
 */

(function($){

  $.fn.txNodes = function(options){
    
    var wizard = {}
      , answers = {}
      , view = $(this)
      , answer_history = []
      , current_node = {}
    
    if(!options)
     options = {};
    
    if(!options.page_id){
      alert('Fatal error: no wizard ID given for wizard.');
      return;
    }
    
    function init(){
      
      bind_events();
      
      $.rest('GET', '?rest=wizard/nodes/'+options.page_id)
        .done(function(result){
          wizard = result;
          current_node = 0;
          load_node();
        });
      
    }
    
    function load_node(node_id)
    {
      
      // var getq = $.rest('GET', '?rest=wizard/question/'+question_id)
      //   .done(function(result){
      //     question = result;
      //   })
      //   .error(function(){
      //     view.html($('#tx-wizard-notfound-tmpl').tmpl({id:options.wizard_id}));
      //   });
      
      // var geta = $.rest('GET', '?rest=wizard/answers/'+question_id)
      //   .done(function(result){
      //     answers = {};
      //     $.each(result, function(i){
      //       answers[result[i].id] = result[i];
      //     });
      //   });
      
      // $.when(getq, geta)
      //   .done(function(){
      //     render_question();
      //   });
      
      if(node_id > 0){
        $.each(wizard, function(i){
          if(wizard[i].id == node_id){
            current_node = i;
          }
        });
      }

      render_question();

    }
    
    function render_question(){
      console.log('question'+current_node);
      view.html($('#tx-wizard-question-tmpl').tmpl(wizard[current_node]));
      render_answers();
      render_breadcrumbs();
    };
    
    function render_answers()
    {
      
      var av = view.find('.answers').html('');

      //Get node answers.
      var lft = wizard[current_node].lft;
      var rgt = wizard[current_node].rgt;

      //List answers in answers array.
      var nr = 0;
      answers = [];
      $.each(wizard, function(i){
        var node = wizard[i];
        if(parseInt(node.lft) > parseInt(wizard[current_node].lft)
            && parseInt(node.rgt) < parseInt(wizard[current_node].rgt)
            && parseInt(node.depth) == parseInt(wizard[current_node].depth) + 1
        ){
          answers[nr] = node;
          nr++;
        }
      });

      $.each(answers, function(i){
        av.append($('#tx-wizard-answer-tmpl').tmpl(answers[i]));
      });
      
      if(answer_history.length <= 1){
        $('.back_button').attr('disabled', 'disabled')
      }else{
        $('.back_button').removeAttr('disabled')
      }
    
    }
    
    function render_breadcrumbs()
    {
      
      var bc = view.find('.breadcrumbs').html('');
      $.each(answer_history, function(i){
        bc.append($('#tx-wizard-breadcrumb-tmpl').tmpl(answer_history[i]));
      });
      
    }
    
    function bind_events()
    {
      
      view
        
        /* ---------- Answer click ---------- */
        .on('click', '.answer', function(e){
          if($(e.target).closest('.answer').attr('href') == undefined)
          {
            e.preventDefault();
            // answer_history.push(answers[$(this).attr('data-id')]);
            load_node($(e.target).closest('.answer').data('id'));
          }
        })
        
        /* ---------- Back button ---------- */
        .on('click', '.back_button:not(:disabled)', function(e){
          e.preventDefault();
          answer_history.pop();
          load_question(answer_history[answer_history.length-1].target_question_id);
        })
        
        /* ---------- Breadcrumb click ---------- */
        .on('click', '.breadcrumbs a.breadcrumb', function(e){
          e.preventDefault();
          var target_index = $(e.target).closest('li').index();
          while(answer_history.length > target_index + 1)
            answer_history.pop();
          load_question(answer_history[answer_history.length-1].target_question_id);
        })
        
      ;
      
    }
    
    init();
    
  };
  
})(jQuery);
