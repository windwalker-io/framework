/*!
 * AKQuickAdd JS
 *
 * Copyright 2013 Asikart.com
 * License GNU General Public License version 2 or later; see LICENSE.txt, see LICENSE.php
 *
 * Generator: AKHelper
 * Author: Asika
 */



var AKQuickAdd = ({
    
    init : function(id, option){
        var inputs  = $$('#'+id).getElements('input, select, textarea, button') ;

        
        // Set Option
        option.task    = 'quickAddAjax' ;
        option.option  = option.quickadd_extension ;
        option.ajax    = 1 ;
        
        this.option = Array();
        this.option[id] = option ;
        
        this.send_setted = Array();
        
        // Remove Required
        inputs.each( function(e){
            e.removeClass('required');
        });
    }
    ,
    submit : function(id, event){
        
        var button  = event.target;
        var area    = $$('#'+id)[0] ;
        var option  = this.option[id];
        var uri     = new URI();

        // Prevent Event
        event.preventDefault();
        
        // Set button disabled
        button.addClass('disabled');
        button.set('disabled', true);
        
        
        // Set Request Option
        var requestSetting = {
            url : 'index.php'
            ,
            onSuccess : function(responseText){
                // Response
                var response = JSON.decode(responseText) ;
                
                // Set Result Action
                if (response.Result) {
                    // Hide Modal
                    jQuery('#'+id).modal('hide');
                    
                    // Reset inputs
                    var inputs = area.getElements('input, select, textarea');
                    inputs.set('value', null);
                    
                    var data        = response.data ;
                    var select_id   = '#'+id.replace('_quickadd', '');
                    var select      = jQuery(select_id) ;
                    
                    // Add new Option in Select
                    select.append(new Option(data[option.value_field], data[option.key_field], true, true));
                    
                    // Wait and highlight
                    setTimeout(function(){
                        $$(select_id).highlight();
                    } ,500);
                    
                    // Wait and highlight for chosen
                    var chzn = $$(select_id+'_chzn .chzn-single span');
                    if (chzn) {
                        setTimeout(function(){
                            select.trigger("liszt:updated");
                            chzn.highlight();
                        } ,500);
                    }
                }else{
                    // Warning
                    alert(response.errorMsg);
                }
                
            }
            ,
            onComplete : function(){
                // Reset button
                button.removeClass('disabled');
                button.set('disabled', null);
            }
        };
        
        // Set Request Option once
        if (!this.send_setted[id]) {
            area.set('send', requestSetting );
            this.send_setted[id] = 1;
        }
        
        // Send Request
        uri.setData(option);
        
        area.send(uri.toString());
        
    }

});