(function($, exports){
  
  var NodesController = PageType.sub({
    
    //Define the tabs to be created.
    tabs: {
      'Content': 'contentTab'
    },
    
    //Define the elements for jsFramework to keep track of.
    elements: {
      // 'title': '#text-contentTab-form .title',
      'nodeView': '.wizard-node-view',
      'nodeList': '.wizard-node-tree',
      'nodeEditView': '.wizard-node-editor',
      'nodeEditForm': '.wizard-edit-node',
      'deleteNodeBtn': '.delete-node',
      'optionsRadio': '.option input[type="radio"]',
      'node': '.wizard-node-tree a',
      'addNodeBtn': '.add-node',
      'addNodeBelowBtn': '.add-node-below',
      'btn_collapse': '.icon-collapse',
      'btn_expand': '.icon-expand'
    },
    
    events:{
      
      'change on optionsRadio': function(e){
        
        //Disable all options.
        this.nodeEditForm
          .find('.option-based')
            .find(':input:not([name="option"])')
              .attr('disabled', 'disabled')
              .end()
            .removeClass('active');
        
        //Enable currently selected option.
        this.nodeEditForm
          .find('[name="option"]:checked')
            .closest('.option-based')
            .find(':disabled')
              .removeAttr('disabled')
              .end()
            .find('input[type="text"]')
              .focus()
              .end()
            .addClass('active');
        
      },

      'click on addNodeBtn': function(e){
        e.preventDefault();
        this.editEntry();
      },

      'click on deleteNodeBtn': function(e){
        e.preventDefault();
        if(confirm('Weet je zeker dat je deze vraag wilt verwijderen?')){
          this.deleteEntry($(e.target).data('id'));
        }
      },

      'click on addNodeBelowBtn': function(e){

        var self = this;

        e.preventDefault();

        //Expand tree at this point.
        $(e.target).closest('.question').removeClass('collapsed')
            .find('li').addClass('collapsed');

        $.rest('POST', app.options.url_base+'/rest/wizard/node_below/'+$(e.target).data('id'), {
          page_id: self.pageId
        }).done(function(){
          self.loadTree();
        });

      },

      'click on node': function(e){
        e.preventDefault();

        //Give 'active' class
        this.nodeList.find('li').removeClass('active');
        $(e.target).closest('li').addClass('active');
        this.editEntry($(e.target).closest('a').data('id'));
      },

      'click on btn_collapse': function(e){
        this.collapse($(e.target).closest('li'));
      },
      
      'click on btn_expand': function(e){
        this.expand($(e.target).closest('li'));
      }

      // //Let findability know we have a recommended default.
      // 'keyup on title': function(e){
      //   app.Page.Tabs.findabilityTab.recommendTitle(
      //     $(e.target).val(),
      //     $(e.target).closest('.multilingual-section').attr('data-language-id')
      //   );
      // }
      
    },
    
    //Retrieve input data (from the server probably).
    getData: function(pageId){
      
      var self = this
        , D = $.Deferred()
        , P = D.promise();

      this.pageId = pageId;
      this.nodes = [];

      //Retrieve input data from the server based on the page ID
      $.rest('GET', app.options.url_base+'/rest/wizard/nodes', {
        pid: pageId
      })
      
      //In case of success, this is no longer fresh.
      .done(function(d){
        D.resolve(d);
      })
      
      //In case of failure, provide default data.
      .fail(function(){
        D.resolve([]);
      });
      
      return P;
      
    },
    
    //When rendering of the tab templates has been done, do some final things.
    afterRender: function(){
      
      var self = this;

      //Turn the form on the content tab into a REST form.
      this.nodeEditForm.restForm({success: this.proxy(this.afterSave)});
      this.optionsRadio.trigger('change');
      this.initTree();
      this.loadTree();

    },
    
    //Saves the data currently present in the different tabs controlled by this controller.
    save: function(e, pageId){
      
      // return this.nodeEditForm.trigger('submit');
      
    },
    
    afterSave: function(data){
      this.nodeEditForm.attr('method', 'PUT');
    },
    
    initTree: function(data){
      
      var self = this;
      self.nodeHierarchy = null;

      self.nodeList
        
        /* ---------- Click question ---------- */
        // .on('click', 'li.question a', function(e){
        //   e.preventDefault();
        //   to_question($(e.target).attr('data-id'));
        // })
        
        /* ---------- Nested sortable ---------- */
        .nestedSortable({
          disableNesting: 'no-nest',
          forcePlaceholderSize: true,
          handle: 'div',
          helper: 'clone',
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
            '?rest=wizard/nodes_hierarchy/'+self.pageId,
            {nodes: $(e.target).nestedSortable('toArray', {startDepthCount: 0, attribute: 'rel', expression: (/()([0-9]+)/), omitRoot: true})}
          ).done(function(result){
            nodes = {};
            $.each(result, function(i){
              nodes[result[i].id] = result[i];
            });
            self.nodeHierarchy = toHierarchy('lft', 'rgt', result);
            self.loadTree();
          });
          
        });
        
        // question_list
        
        //   /* ---------- Click new question ---------- */
        //   .on('click', '.new_question', function(e){
        //     e.preventDefault();
        //     edit_question('new');
        //   });
        
      ;
            
    },

    //Collapse a menu item and its sub-items.
    collapse: function(item){
      
      $(item).find('.icon-toggle:eq(0)').removeClass('icon-collapse').addClass('icon-expand');
      $(item).addClass('collapsed');
      
      return this;
      
    },
    
    //Un-collapse.
    expand: function(item){
      
      $(item).find('.icon-toggle:eq(0)').removeClass('icon-expand').addClass('icon-collapse');
      $(item).removeClass('collapsed');
      
      return this;
      
    },
    
    //Collapse a menu item and its sub-items.
    collapseItems: function(level){
      
      var self = this;
      
      //Collapse all levels.
      $(self.el).find('li').each(function(){
        self.collapse($(this));
      });

      //Expand levels above level of active menu item.
      $(self.el).find('.menu-item.active').parents('li').each(function(){
        self.expand($(this));
      });

    },
    
    //Add the has-sub class to items that have sub-menu items.
    checkHasSub: function(){
      
      var self = this;
      
      $.after(0).done(function(){
        self.nodeList.find('li').removeClass('has-sub');
        self.nodeList.find('li:has(>ul>li)').addClass('has-sub');
      });
      
      return self;
      
    },

    loadTree: function(data){

      var self = this;

      $.rest('GET', app.options.url_base+'/rest/wizard/nodes', {
        pid: self.pageId
      })
      
      .done(function(d){

        $.each(d, function(i){
          self.nodes[d[i].id] = d[i];
        });
        self.nodeHierarchy = toHierarchy('lft', 'rgt', d);

        self.renderTree();

      });

    },

    renderTree: function(data){
      
      // if(!data){
      //   console.log('0 nodes were found.');
      //   return;
      // }

      var self = this;

      //Save 'collapsed' state.
      var collapsed_ids = [], i = 0;
      self.nodeList.find('li.collapsed').each(function(){
        collapsed_ids[i] = $(this).attr('rel');
        i++;
      });

      self.nodeList.find('li').remove();
      
      var renderer = function(list_target, data, depth){
        
        if(data.length > 0) console.log(data);
        
        for(var i = 0; i < data.length; i++){
          
          var $node = $(self.renderTemplate('node', data[i]));
          list_target.append($node);
          renderer($('<ul>').appendTo($node), data[i]._children, depth+1);
          
        }
        
      };

      renderer(self.nodeList, self.nodeHierarchy, 0);
      
      //Bring back collapsed state.
      $.each(collapsed_ids, function(i){
        self.nodeList.find('li[rel='+collapsed_ids[i]+']').addClass('collapsed');
      });

      self.checkHasSub();
      
    },
    
    //Edit entry.
    editEntry: function(id){
      
      var self = this;
      var hasFeedback = true;//#TODO

      $.rest('GET', '?rest=wizard/node/'+(id ? id : null)).done(function(data){
        
        self.nodeEditView.empty();
        
        var form = self.definition.templates.editNode.tmpl({
          data: data,
          page_id: self.pageId,
        }).appendTo(self.nodeEditView);

        form.find('input[name="answer_title"]').focus();
        
        //Enable selected option.
        $('.url-preview').hide();
        if(data.question_title && data.question_title.length > 0){
          form.find('.option-based.question').addClass('active');
          $('input[name="option"][value="question"]').attr('checked', 'checked');
        }
        else if(data.url && data.url.length > 0){
          form.find('.option-based.url').addClass('active');
          $('input[name="option"][value="url"]').attr('checked', 'checked');
          // $('.url-preview').attr('src','http://s.wordpress.com/mshots/v1/'+encodeURIComponent($('input[type="text"][name="url"]').attr('value'))+'%2F?w=150').show();
        }

        form.restForm({
          beforeSubmit: function(){
            if(hasFeedback) app.Feedback.working('Saving entry...').startBuffer();
          },
          success: function(entry){
            self.loadTree();
            if(hasFeedback) app.Feedback.success('Saving entry succeeded.').stopBuffer();
          },
          error: function(){
            if(hasFeedback) app.Feedback.error('Saving entry failed.').stopBuffer();
          }
        });

        //Refresh elements.
        self.refreshElements();

      });
      
    },

    //Edit entry.
    deleteEntry: function(id){
      
      var self = this;
      var hasFeedback = true;//#TODO

      $.rest('DELETE', '?rest=wizard/node/'+(id ? id : null)).done(function(data){
        
        //Refresh elements.
        self.refreshElements();

        //Reload tree.
        self.loadTree();

      });
      
    }

  });
  
  //Export the namespaced class.
  NodesController.exportTo(exports, 'cmsBackend.wizard.NodesController');
  
})(jQuery, window);
