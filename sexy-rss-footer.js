jQuery(document).ready( function($) {
  var textarea = $("textarea#srf_footer_template");

  $("table.srf_help_table span").click( function() {
    textarea.val(textarea.val() + $(this).html());
    return false;
  });
});
