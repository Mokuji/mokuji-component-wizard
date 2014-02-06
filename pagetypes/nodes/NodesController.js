(function($, exports){
  
  var NodesController = PageType.sub({
    
    //Define the tabs to be created.
    tabs: {
      'Content': 'contentTab'
    },
    
    //Define the elements for jsFramework to keep track of.
    elements: {
      // 'title': '#text-contentTab-form .title',
      'nodeEditForm': '.wizard-edit-node',
      'optionsRadio': '.option input[type="radio"]'
    },
    
    events:{
      
      'change on optionsRadio': function(e){
        
        //Disable all options.
        this.nodeEditForm
          .find('.option-based :input:not([name="option"])')
          .attr('disabled', 'disabled');
        
        //Enable currently selected option.
        this.nodeEditForm
          .find('[name="option"]:checked')
          .closest('.option-based').find(':disabled')
          .removeAttr('disabled');
        
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
      
      //Turn the form on the content tab into a REST form.
      this.nodeEditForm.restForm({success: this.proxy(this.afterSave)});
      this.optionsRadio.trigger('change');
      
    },
    
    //Saves the data currently present in the different tabs controlled by this controller.
    save: function(e, pageId){
      
      // return this.nodeEditForm.trigger('submit');
      
    },
    
    afterSave: function(data){
      this.nodeEditForm.attr('method', 'PUT');
    }
    
  });
  
  //Export the namespaced class.
  NodesController.exportTo(exports, 'cmsBackend.wizard.NodesController');
  
})(jQuery, window);
