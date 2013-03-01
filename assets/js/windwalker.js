/*!
 * Windwalker CSS
 *
 * Copyright 2013 Asikart.com
 * License GNU General Public License version 2 or later; see LICENSE.txt, see LICENSE.php
 *
 * Generator: AKHelper
 * Author: Asika
 */


/* Fix Bluestork and Joomla! Conflict */
window.addEvent( 'domready', function(){
	var modal = $$('#ak-panel-wrap a.modal') ;
	setTimeout(function(){ modal.removeClass('modal'); }, 500 );
} );


var WindWalker = {
	fixToolbar: function(top, duration){

        top = top || 0 ;
        duration = duration || 300 ;

        // fix sub nav on scroll    
        jQuery(document).ready(function($) {
            var $win = $(window)
            , $nav = $('.subhead')
            , navTop = $('.subhead').length && $('.subhead').offset().top - top
            , isFixed = 0

            processScroll();

            // hack sad times - holdover until rewrite for 2.1
            $nav.on('click', function () {
              if (!isFixed) setTimeout(function () {  $win.scrollTop($win.scrollTop() - 47) }, 10)
            })

            $win.on('scroll', processScroll)

            function processScroll() {
              var i, scrollTop = $win.scrollTop()
              if (scrollTop >= navTop && !isFixed) {
                  isFixed = 1
                  $nav.addClass('subhead-fixed')
                  $nav.css('left', 0) ;
                  $nav.css('top', top - $nav.height()) ;
                  $nav.animate({top: top}, duration);
              } else if (scrollTop <= navTop && isFixed) {
                  isFixed = 0
                  $nav.removeClass('subhead-fixed')
              }
            }
        });
    }
} ;

