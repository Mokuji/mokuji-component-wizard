(function($){

  $.fn.txWizard = function(options){
    init_wizard(this, options);
  };
  
  //initiate wizards
  function init_wizard(el, options){
    
    //select the elements we are turning into wizards
    $(el)
    
    //set data for each individual wizard
    .each(function(){
      $(this).data().wizard = {
        crumpath: [],
        options: options
      };
    })
    
    //put on their robes and wizard hats
    .append([
      
      $('<div/>', {
        'class': 'question',
        'html': [
          $('<h4/>', {'class': 'question-title'})[0],
          $('<p/>', {'class': 'question-description'})[0],
          $('<button/>', {'class': 'back', 'text': 'Terug', 'disabled':'disabled'})[0]
        ]
      })[0],
      
      $('<div/>', {
        'class': 'answers'
      }).disableSelection()[0]
      
    ])
    
    //bind events
    .on('click.wizard', 'div.answer', function(e){
      
      var el = e.delegateTarget, clicked = this;
      
      load_followup_question($(clicked).attr('rel'))
        .done(function(data){
          change_wizard(data, el);
          $(el).find('.back').removeAttr('disabled');
        })
        .fail(function(error){
          $(clicked).find('p').text(error).css('color', 'red');
        });
      
    })
    .on('click.wizard', 'button.back', function(e){
      
      var el = e.delegateTarget;
      var data = $(el).data().wizard.crumpath.length > 0 && $(el).data().wizard.crumpath.pop() && $(el).data().wizard.crumpath.pop();
      
      if(!data){
        alert('Failed to remember previous question. Sorry :(');
        return;
      }
      
      if($(el).data().wizard.crumpath.length == 0){
        $(el).find('.back').attr('disabled', 'disabled');
      }
      
      change_wizard(data, el);
      
    })
    
    //initiate first question
    .each(function(){
      
      var el = this;
      
      load_followup_question($(el).data().wizard.options.root)
        .done(function(data){
          change_wizard(data, el);
        })
        .fail(function(error){
          $(el).text(error);
        });
    
    });
    
  }
  
  //change the HTML of a wizard
  function change_wizard(data, el){
    
    $(el)
      .find('.question')
        .find('h4')
        .text(data.question.title)
      .end()
        .find('p')
        .text(data.question.description)
      .end()
    
    .end()
      .find('.answers')
      .wrap($('<div/>', {
        'class': 'animation'
      }))
      .wrap($('<div/>', {
        'class': 'animation-inner'
      }))
    
    $('.animation-inner').append($('<div/>', {
      'class': 'answers',
      'html': $.map(data.answers, function(answer){
      
        var prop = {
          'class': 'answer',
          'rel': answer.id,
          'html': '<h5>'+answer.title+'</h5><p>'+answer.description+'</p>'
        };
        
        if(answer.url.length > 0){
          var a=true;
          $.extend(prop, {
            href: answer.url,
            target: (answer.url_target.length > 0 ? answer.url_target : '_blank')
          });
        }
        
        return $((a ? '<a/>' : '<div/>'), prop)[0];
        
      })
    }).disableSelection())
      
      .find('.answers:first')
      .animate({'margin-left': '-100%'}, 500, function(){
        $(this).unwrap().unwrap().remove();
      })
      
    $(el).data().wizard.crumpath.push(data);
    
  }
  
  //get information about a question based on answer_id
  function load_followup_question(id){
    
    var D = $.Deferred();
      
    $.getJSON(window.location.href, {
      ajax_action: 'wizard/load_followup_question',
      answer_id: id,
    })
    
    .done(function(d){
      if(d.question == undefined){
        D.reject('Question does not exist.');
      }else if(d.answers == undefined){
        D.reject('Question has no answers.');
      }else{
        D.resolve(d);
      }
    })
    
    .fail(function(){
      D.reject('Loading question failed.');
    });
      
    return D.promise();
    
  }
  
  //get information about an answer based on answer_id
  function load_answer(id){
  
    var D = $.Deferred();
    
    $.getJSON(window.location.href, {
      ajax_action: 'wizard/load_answer',
      answer_id: id,
    })
    
    .done(function(d){
      if(d.id == undefined){
        D.reject('Answer does not exist.');
      }else{
        D.resolve(d);
      }
    })
    
    .fail(function(){
      D.reject('Failed to load answer.');
    });
    
    return D.promise();
  
  }
  
  //get information about a question based on question_id
  function load_question(id){
    
    var D = $.Deferred();
      
    $.getJSON(window.location.href, {
      ajax_action: 'wizard/load_question',
      answer_id: id,
    })
    
    .done(function(d){
      if(d.question == undefined){
        D.reject('Question does not exist.');
      }else if(d.answers == undefined){
        D.reject('Question has no answers.');
      }else{
        D.resolve(d);
      }
    })
    
    .fail(function(){
      D.reject('Loading question failed.');
    });
      
    return D.promise();
    
  }
  
})(jQuery);
