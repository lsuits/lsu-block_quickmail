(function(){
  // Written at Louisiana State University
  $(document).ready(function() {
    var clear_selections, mailed, move, potentials, quickmail_changer, selectors;
    potentials = "#from_users";
    mailed = "#mail_users";
    selectors = [potentials, mailed];
    clear_selections = function() {
      var clear;
      clear = function(index, selector) {
        return $(selector).children(":selected").attr("selected", false);
      };
      return $(selectors).each(clear);
    };
    quickmail_changer = function() {
      var role;
      role = $("#roles").val();
      clear_selections();
      return $("#groups").children(":selected").each(function(outer, group) {
        return $(selectors).each(function(inner, selector) {
          return $(selector).children("*").each(function(select, option) {
            var groups, in_list, roles, selected, values;
            values = $(option).val().split(' ');
            roles = values[2].split(',');
            groups = values[1].split(',');
            in_list = function(obj, list) {
              var filter;
              filter = function() {
                return String(this) === obj;
              };
              return $(list).filter(filter).length > 0;
            };
            selected = true;
            if (in_list(role, roles) && in_list($(group).val(), groups)) {
              return $(option).attr('selected', selected);
            }
          });
        });
      });
    };
    move = function(from, to, filter) {
      return function() {
        $(from).children(filter).appendTo(to);
        return $(from).children(filter).remove();
      };
    };
    $("#groups").change(quickmail_changer);
    $("#roles").change(quickmail_changer);
    $("#add_button").click(move(potentials, mailed, ':selected'));
    $("#add_all").click(move(potentials, mailed, '*'));
    $("#remove_button").click(move(mailed, potentials, ':selected'));
    $("#remove_all").click(move(mailed, potentials, '*'));
    return $("#mform1").submit(function() {
      var ids, mapper;
      mapper = function(index, elem) {
        return $(elem).val().split(' ')[0];
      };
      ids = $(mailed).children("*").map(mapper).get().join(',');
      $("input[name=mailto]").val(ids);
      return true;
    });
  });
})();
