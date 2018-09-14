define(['jquery'], function($) {
    let addBtn = $('#id_add_recip');
    let removeBtn = $('#id_remove_recip');
    let availablesSelect = $('#id_available_included_entity_ids');
    let selectedsSelect = $('#id_selected_included_entity_ids');
    
    let syncSelected = function() {
        $('[name="included_entity_ids"]').val('');

        let entities = [];
        $('#id_selected_included_entity_ids option').each(function() {
            entities.push($(this).val());
        });

        $('[name="included_entity_ids"]').val(entities);
    }

    return {
        init: function() {
            addBtn.click(function() {
                $('#id_available_included_entity_ids option:selected').remove().appendTo(selectedsSelect);
                $('#id_selected_included_entity_ids option:selected').prop('selected', false);
                syncSelected();
            });

            removeBtn.click(function() {
                $('#id_selected_included_entity_ids option:selected').remove().appendTo(availablesSelect);
                $('#id_available_included_entity_ids option:selected').prop('selected', false);
                syncSelected();
            });
        }
    };
});