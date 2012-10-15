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
                render_menu();
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

        .parent()//Question list wrapper.
        
          /* ---------- Click new question ---------- */
          .on('click', '.new_question', function(e){
            e.preventDefault();
            edit_question('new');
          })
        
      ;
            
    }
    
    function render_menu(){
      
      var target = question_list;
      start_question.find('option').remove();
      target.find('.question').remove();
      $.each(questions, function(i){
        target.append($('#tx-wizard-question-li').tmpl(questions[i]));
        start_question.append($('#tx-wizard-question-opt').tmpl($.extend({start_question_id: wizard.start_question_id}, questions[i])));
      });
      
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
          
          questions[question.id] = question;
          render_menu();
          
          //If this is our only question for this wizard.
          //And the start_question had not been set to this value yet.
          //Do so now.
          var count = 0;
          for(var k in questions)
          {
            
            if(Object.prototype.hasOwnProperty.call(questions, k)){
              count++;
            }
            
            //Counting to two will do.
            if(count > 1)
              break;
            
          }
          
          if(count == 1 && wizard.start_question_id != question.id){
            wizard.start_question_id = question.id;
            $.rest('PUT', '?rest=wizard/wizard/'+wizard.id, wizard)
              .done(function(result){
                wizard = result;
              });
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
          answers = result;
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
            answer_history.push({id:$(this).attr('data-id'), target_question_id:target_question_id, breadcrumb:$(this).find('.breadcrumb').html()});
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
          var target_index = $(e.target).index();
          while(answer_history.length > target_index + 1)
            answer_history.pop();
          load_question(answer_history[answer_history.length-1].target_question_id);
        })
        
      ;
      
    }
    
    init();
    
  };
  
})(jQuery);
