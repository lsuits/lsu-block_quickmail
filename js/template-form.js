$(function() {

    //  ¯\_(ツ)_/¯
    $('#id_delete').removeClass('btn-primary');
    $('#id_delete').addClass('btn-danger');

    // handle change of "select template to edit"
    var selectedTemplateId = $('#id_select_template_id').val();

    // when select template id changes
    $('#id_select_template_id').change(function(e) {
        e.preventDefault();

        // if the value actually changed, redirect to edit the selected template id
        if (selectedTemplateId != this.value) {
            let qs = {
                id: this.value,
            };

            window.location.href = 'templates.php?' + $.param(qs);
        }
    });

    $('#id_delete').click(function(e) {
        if ( ! confirm('Delete this template?')) {
            e.preventDefault();
        }
    });
});