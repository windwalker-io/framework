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

    add : function(id){
        //var d = $$('#'+id+' input, #'+id+' select');
        //console.log(d);
        
        var form = $(id);
        form.send('index.php?option=com_flower&task=sakura.quickAddAjax&ajax=1');
        var r = form.get('send');
        
        console.log(r);
    }

});