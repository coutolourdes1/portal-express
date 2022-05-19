jQuery(document).ready(function ($) {

  $("[data-toggle=popover]").popover({
    html : true,
    sanitize: false,
    content: function() {

      var content = $(this).attr("data-popover-content");
      return $(content).children(".popover-body").html();

    }
  });

});
