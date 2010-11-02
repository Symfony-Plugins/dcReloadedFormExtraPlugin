function pmWidgetFormJQuerySearch()
{
  this.url = "";
  this.search_widget_id = "";
  this.update_div_id = "";
  this.hidden_widget_id = "";
  this.preview_div_id = "";
  this.select_image = "";
  this.deselect_image = "";
  this.serialized_options = "";
  this.js_var_name = "";
    
  this.search = function()
  {
    value = jQuery(this.search_widget_id).val();
    
    var instance = this;
  
    jQuery.ajax({
      url: this.url,
      data:
      {
        search: value,
        serialized_options: this.serialized_options,
        js_var_name: this.js_var_name
      },
      success: function(data)
      {
        jQuery(eval(instance.js_var_name+".update_div_id")).html(data);
        jQuery(eval(instance.js_var_name+".update_div_id")).show();
      }
    });
  };
  
  this.select = function(value, text)
  {
    jQuery(this.hidden_widget_id).val(value);
    jQuery(this.preview_div_id).html(text);
    jQuery(this.update_div_id).hide();
    
    this.getDeselectLink();
  };
  
  this.deselect = function()
  {
    jQuery(eval(this.js_var_name+".hidden_widget_id")).val("");
  	jQuery(eval(this.js_var_name+".preview_div_id")).html("");
  };
  
  this.getSelectLink = function(value, text)
  {
    instance = this;
    jQuery("<a><img src='"+eval(this.js_var_name+".select_image")+"'/></a>")
    	.click(function ()
    	{
    	  eval(instance.js_var_name+".select("+value+", '"+text+"');")
    	})
    	.css("margin-left", "10px")
    	.css("vertical-align", "middle")
    	.appendTo(jQuery("#result_"+value));
  }
  
  this.getDeselectLink = function()
  {
    var instance = this;
  
    jQuery("<a><img src='"+this.deselect_image+"'/></a>")
  	  .click(function()
  	  {
  	    eval(instance.js_var_name+".deselect();");
  	  })
  	  .css("margin-left", "10px")
  	  .css("vertical-align", "middle")
  		.appendTo(jQuery(this.preview_div_id));
  }
  
  this.displayNoResultsFoundLabel = function()
  {
    jQuery("<div>"+eval(this.js_var_name+".no_results_found_label")+"</div>").appendTo(jQuery(eval(this.js_var_name+".update_div_id")));
  }
}
