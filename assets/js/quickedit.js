/*!
 * Windwalker JS
 *
 * Copyright 2013 Asikart.com
 * License GNU General Public License version 2 or later; see LICENSE.txt, see LICENSE.php
 *
 * Generator: AKHelper
 * Author: Asika
 */



var AKQuickEdit = ({
    
    init : function(config){
        
        AKQuickEdit.config = config ;
        
        window.addEvent('domready', function(){
            var editFields = $$('.quick-edit-wrap') ;
            AKQuickEdit.editStatus = false ;
            
            // Set Table as Excel Cells to control selected fields.
            AKQuickEdit.setTableCellNumber();
            
            // Set Keyboard Event
            window.addEvent('keydown', AKQuickEdit.moveSelected );
            window.addEvent('keydown', function(e){
                
                // Prevent from submit Form
                if(AKQuickEdit.active && e.key == 'enter') {
                    e.stop();
                }
                
                // Press Enter to Edit
                if(e.key == 'enter' && e.target.get('id') != 'quick-edit-input' && AKQuickEdit.active) {
                    e.target = AKQuickEdit.active ;
                    AKQuickEdit.editField(e) ;
                }
            } );
            
            // If click on active element, focus on edit input.
            window.addEvent('click', AKQuickEdit.focusEditInput);
            
            
            // Set Mouse Click Event
            editFields.each( function(e){
                
                e.addEvent('mouseup', function(e2){
                    AKQuickEdit.editField(e2) ;
                });
                
            });
            
        });
    }
    
    ,
    
    editField : function(event){
        
        if(!event.target.hasClass('quick-edit-wrap')) return ;
        
        if(!AKQuickEdit.editStatus) {
            // Set Active
            AKQuickEdit.setActive(event.target) ;
            
            // Set Element in Object to temp
            AKQuickEdit.editWrap = event.target ;
            
            // Remove hover style
            //event.target.removeClass('quick-edit-hover');
            
            // Get Content
            var content = event.target.getElement('.quick-edit-content') ;
            AKQuickEdit.contentTemp = content.clone();
            
            // Destroy Content and put input in wrap
            //content.destroy();
            
            // Create Input
            input = new Element('input', {text : event.text}) ;
            input.set('id', 'quick-edit-input') ;
            input.setStyles({
                width : '100%' 
            });
            
            // Set Content into input value
            input.set('value', content.get('text').trim()) ;
            
            // inject Element
            input.replaces( content ) ;
            input.focus();
            
            // Set Edit Complete
            input.addEvent('keydown', AKQuickEdit.editComplete);
            
            
            AKQuickEdit.editStatus = true;
        }
        
    }
    
    ,
    
    editComplete : function(event){
        var actionKey = [ 'enter', 'up', 'down'] ;
        
        if( actionKey.contains(event.key) ){
            
            var wrap     = AKQuickEdit.editWrap ;
            var content = AKQuickEdit.contentTemp ;
            
            content.replaces( event.target ) ;
            var tempText = content.get('text').toString().trim() ;
            var editText = event.target.get('value').toString().trim() ;
            
            if(tempText != editText ) {
                content.set('text', event.target.get('value')) ;
                
                
                // Debug Profiler
                // -------------------------------------
                if( AKQuickEdit.profiler ) console.log('Temp Text: ' + tempText);
                if( AKQuickEdit.profiler ) console.log('Edit Text: ' + editText);
                // -------------------------------------
                
                
                event.target.destroy();
                
                // Send Request
                var uri = new URI() ;
                var view = AKQuickEdit.config.view ;
                var option = AKQuickEdit.config.option ;
                
                uri.setData( {
                    'option'     : option ,
                    'task'         : view + '.editFieldData' ,
                    'content'     : content.get('text') ,
                    id             : wrap.get('quick-edit-id') ,
                    field         : wrap.get('quick-edit-field')
                } ) ;
                
                
                // Debug Profiler
                // -------------------------------------
                if( AKQuickEdit.profiler ) console.log(uri.toString());
                // -------------------------------------
                
                
                // Send Request
                AKQuickEdit.sendEditRequest(uri) ;
                    
            }
            
            // Reset Edit Ststus
            wrap.focus() ;
            //wrap.addClass('quick-edit-hover');
            AKQuickEdit.editStatus = false ;
        }
    }
    
    ,
    
    sendEditRequest : function(uri){
        var result ;
        
        
        var request = new Request.JSON({
            url : uri.toString() ,
            onLoadstart : function(event, xhr){
                Joomla.renderMessages( [['Sending']] ) ;
            } ,
            onSuccess : function(responseJSON, responseText){
                result = responseJSON.AKResult ;
                
                
                // Debug Profiler
                // -------------------------------------
                if( AKQuickEdit.profiler ) console.log(responseJSON);
                // -------------------------------------
                
                
                if(result) {
                    Joomla.renderMessages( [['Success']] ) ;
                }else{
                    Joomla.renderMessages( [['Fail']] ) ;
                }
            }
        }).send();
        
        request.send();
        
        
        // Debug Profiler
        // -------------------------------------
        if( AKQuickEdit.profiler ) console.log('Ajax Send');
        // -------------------------------------
        
        
        return result ;
    }
    
    ,
    
    moveSelected : function(event){
        var actives = $$('.quick-edit-selected') ;
        var key     = event.key ;
        
        if( !actives.length ) return ;
        
        if( key == 'up' || key == 'down' || key == 'right' || key == 'left' ) {
            
            
            // Debug Profiler
            // -------------------------------------
            if( AKQuickEdit.profiler ) console.log('Key Down: ' + key);
            //if( AKQuickEdit.profiler ) console.log('CEdit Text: ' + editText);
            // -------------------------------------
            
            
            var active = actives[0] ;
            
            // Up & Down
            if( key == 'up' || key == 'down' ) {
                var tr = active.getParent('tr') ;
                var nextEditField ;
                var field = active.get('quick-edit-cell-num') ;
                
                
                // Action
                if( key == 'up' ) {
                    var p = tr.getPrevious('tr') ;
                    if(!p) return ;
                    nextEditField = p.getElements('td[quick-edit-cell-num='+field+']')[0] ;
                }else{
                    var n = tr.getNext('tr') ;
                    if(!n) return ;
                    nextEditField = n.getElements('td[quick-edit-cell-num='+field+']')[0] ;
                }
                
            }else if( (key == 'left' || key == 'right') && !AKQuickEdit.editStatus) {
                var nextEditField ;
                
                
                // Action
                if( key == 'right' ) {
                    nextEditField = active.getNext() ;
                }else{
                    nextEditField = active.getPrevious() ;
                }
                
                if(!nextEditField) return ;
            }
            
            
            AKQuickEdit.setActive(nextEditField) ;
        }
        
    }
    
    ,
    
    setActive : function(selector){
        
        var target ;
        
        if( typeof(selector) != 'object' ) {
            target = $$(selector)[0] ;
        }else{
            target = selector ;
        }
        
        if(!target) return ;
        
        var actives =  $$('.quick-edit-selected') ;
        actives.removeClass('quick-edit-selected');
        target.addClass('quick-edit-selected') ;
        
        AKQuickEdit.active = target ;
    }
    
    ,
    
    focusEditInput : function(event){
        
        var target = event.target ;
        var input = $$('#quick-edit-input')[0] ;
        
        if(!input) return ;
        
        if(target.getParent('.quick-edit-selected') || target == $$('.quick-edit-selected')[0] ) {
            
            // Debug Profiler
            // -------------------------------------
            if( AKQuickEdit.profiler ) console.log('Click on Edit Field');
            // -------------------------------------    
            
            input.focus();
            
        }

    }
    
    ,
    
    setTableCellNumber : function(){
        var table = $$('table') ;
        var tr ;
        var tds ;
        var td ;
        var col = 0;
        
        table.each( function(e){
            tr = e.getElements('tbody tr') ;
            
            tr.each( function(e2){
                tds = e2.getElements('td') ;
                
                tds.each( function(td){
                    td.set( 'quick-edit-cell-num' , col ) ;
                    col++ ;    
                });
                
                col = 0 ;
            });
        });
    }
}) ;

AKQuickEdit.profiler = true ;


