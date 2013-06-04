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
        option.task    = option.controller + '.quickAddAjax' ;
        option.option  = option.extension ;
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
        
        event.preventDefault();
        var button = event.target;
        button.addClass('disabled');
        button.set('disabled', true);
        
        var area = $$('#'+id)[0] ;
        var option = this.option[id];
        
        var requestSetting = {
            url : 'index.php' ,
            onSuccess : function(responseText){
                var response = JSON.decode(responseText) ;
                
                if (response.Result) {
                    var data = response.data ;
                    jQuery('#'+id).modal('hide');
                    var select_id = '#'+id.replace('_quickadd', '');
                    
                    var select = jQuery(select_id) ;
                    select.append(new Option(data[option.value_field], data[option.key_field], true, true));
                    
                    setTimeout(function(){
                        $$(select_id).highlight();
                    } ,500);
                    
                    var chzn = $$(select_id+'_chzn .chzn-single span');
                    if (chzn) {
                        setTimeout(function(){
                            select.trigger("liszt:updated");
                            chzn.highlight();
                        } ,500);
                    }
                }else{
                    console.log(response.errorMsg);
                    alert(response.errorMsg);
                }
                
            }
            ,
            onComplete : function(){
                button.removeClass('disabled');
                button.set('disabled', null);
                
                var inputs = area.getElements('input, select, textarea');
                inputs.set('value', null);
            }
        };
        
        
        if (!this.send_setted[id]) {
            area.set('send', requestSetting );
            this.send_setted[id] = 1;
        }
        
        area.send('index.php?option='+option.option+'&task='+option.task+'&ajax=1');
        
        area.get('send');
    }

});