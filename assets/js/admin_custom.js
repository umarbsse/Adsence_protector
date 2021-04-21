var admin_ajax_url = ajaxurl ;
$(document).on('change', '.bann_countries_checkbox', function() {
  var token = $(this).attr("data-token");
  //console.log(token);
  if(this.checked) {
   ban_countries(token,1);
  }else {
    ban_countries(token,0);
  }
});
function ban_countries(token,is_ban){
  $.ajax({
    url: admin_ajax_url,
    type: 'POST',
    data: {
      action: "handle_ban_countries",
      token: token,
      is_ban: is_ban,
    },
    success: function (data, textStatus, jqXHR) {
      
    },
    error: function(xhr, textStatus, errorThrown){
      /*console.log(xhr);
      console.log(textStatus);
      console.log(errorThrown);*/
    }
  });
}

$(document).on('click', '.block_ip', function() {
  var ip = $(this).attr("data-ip");
  var status = $(this).attr("data-status");
  $.ajax({
    url: admin_ajax_url,
    type: 'POST',
    data: {
      action: "handle_ban_ip",
      ip: ip,
      status: status,
    },
    success: function (data, textStatus, jqXHR) {
      ban_set_html(ip,status);
    },
    error: function(xhr, textStatus, errorThrown){
      /*console.log(xhr);
      console.log(textStatus);
      console.log(errorThrown);*/
    }
  });
});
function ban_set_html(ip,status){
  $( ".block_ip" ).each(function( index ) {
    //console.log( index + ": " + $( this ).text() );
    var ips = $(this).attr("data-ip");
    var statuss = $(this).attr("data-status");
    if (ip==ips) {
      if (statuss=="unblock") {
        $(this).text("Unblock IP");
        $(this).css({"background": "yellowgreen"});
        $(this).attr("data-status","block");
      }
      else if (statuss=="block") {
        $(this).text("Block IP");
        $(this).css({"background": "red"});
        $(this).attr("data-status","unblock");
      }
    }
  }); 
}
