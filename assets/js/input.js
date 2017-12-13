(function($){
  "use strict";

  function initialize_field( field ) {
    const wrap = $(field).find('.acf-field-sortable-post-object-wrap').not('.initialized');
    const input = $(field).find('input');

    wrap.addClass('initialized').children('ul').sortable({
      connectWith: '#' + wrap.attr('id') + ' > ul',
      start: function (event, ui) {
        ui.item.toggleClass("highlight");
      },
      stop: function (event, ui) {
        ui.item.toggleClass("highlight");
        let value = [];
        wrap.find('.acf-field-sortable-post-object-chosen > li').each(function() {
          value.push(parseInt($(this).attr('data-id')));
        });
        wrap.find('input').val(value.join(','));
      }
    }).disableSelection();
  }
  
  if( typeof acf.add_action !== 'undefined' ) {
    acf.add_action('ready_field/type=sortable_post_object', 'initialize_field');
    acf.add_action('append_field/type=sortable_post_object', 'initialize_field');
  } else {
    $(document).on('acf/setup_fields', function(e, postbox){
      $(postbox).find('.field[data-field_type="sortable_post_object"]').each(function(){
        initialize_field( $(this) );
      });
    });
  }
})(jQuery);