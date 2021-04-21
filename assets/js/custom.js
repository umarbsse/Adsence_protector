  
  var admin_ajax_url = ajax_urls.admin_ajax_url;
  var clicked=false;
  var pageview=false;
  var click_count = 0;
    //add_pageview();
   //add_click();
    function add_pageview(){
      if (pageview==false) {
        pageview=true;
        $.ajax({
            url: admin_ajax_url,
            type: 'POST',
            data: {
                action: "handle_impression",
                adUrl:window.location.href
            },
            success: function (data, textStatus, jqXHR) {
            },
            error: function(xhr, textStatus, errorThrown){
            }
        });

      }
    }
    function add_click(){
      //click_count++;
      //console.log("Click Number "+click_count);
      if (clicked==false) {
        clicked=true;
        $.ajax({
          type: "post",
          url: admin_ajax_url,
          data: {
              action: "handle_click",
              adUrl:window.location.href
          },
        });
        process_record();
      }
    }

    function process_record(){
      setTimeout(function () {
      remove_html();
      hide_overlay();
      }, 500); // Execute something() 1 second later.
    }
    function remove_html(){
      $( ".advertisement_block" ).each(function( index ) {
        $( this ).html('');
      });
    }


  $(document).ready(function() {
    var adInterval = setInterval(function(){
      //  console.log('check');
        $( '.advertisement_block' ).each(function( index ) {
        	if($(this).find('iframe').length == 1) {
            	ads_tracker();
            	clearInterval(adInterval);
        	}
        });
    }, 200);

    function ads_tracker() {
    	   add_pageview();
        console.log('Adsens Protector started');
        $( '.advertisement_block' ).each(function( index ) {
        	$(this).find('iframe').iframeTracker({
	            blurCallback: function(){
                show_overlay();
	              add_click();
	            },
              overCallback: function(element){
                this._overId = $(element).attr('id'); // Saving the iframe wrapper id
                //console.log(this._overId);
              },
              outCallback: function(element){
                this._overId = null; // Reset hover iframe wrapper id
               // console.log(this._overId);
              },
              _overId: null
	        });
        });
    }
  });
 window.onbeforeunload = function() {
    cname  = "number_of_tabs_opened";
    cvalue = getCookie(cname)-1;
    if (cvalue<0) {cvalue=0;}
    exdays = 1;
    setCookie(cname, cvalue, exdays)
 }
 function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
$(document).ready(function(){
  var tabs = getCookie("number_of_tabs_opened");
  if (tabs=="") {tabs=1;}
  else{tabs=parseInt(tabs)+1;}
  setCookie("number_of_tabs_opened", tabs, 1)
 // console.log(tabs);
});
function show_overlay(){
  $('body').css('cursor', 'wait');
  $("#wap_myModal").css({"display": "block"});
}
function hide_overlay(){
  $('body').css('cursor', 'auto');
  $("#wap_myModal").css({"display": "none"});
}
