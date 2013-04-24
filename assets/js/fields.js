 var AKFields = new function(){
    return {
        changeType : function(e){
            var url = new URI(window.location.toString()) ;
            url.setData('field_type', e.options[e.selectedIndex].value);
            url.setData('retain', 1) ;
            
            var f = $$('form[name=adminForm]')[0];
            f.set('action', url.toString());
            f.submit();
        }
    }
 }();