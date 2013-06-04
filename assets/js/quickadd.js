/*!
 * Windwalker JS
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
        
        // Remove Required
        inputs.each( function(e){
            e.removeClass('required');
        });
    }
    ,
    submit : function(id, event){
        
        event.preventDefault();
        
        var area = $$('#'+id)[0] ;
        var option = this.option[id];
        
        var requestSetting = {
            url : 'index.php' ,
            onSuccess : function(responseText){
                var response = JSON.decode(responseText) ;
                console.log(response);
            }
        };
        
        area.set('send', requestSetting );
        area.send('index.php?option='+option.option+'&task='+option.task+'&ajax=1');
        
        area.get('send');
    }

});