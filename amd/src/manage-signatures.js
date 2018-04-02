define(['jquery', 'core/modal_factory', 'core/modal_events', 'core/str'], function($, ModalFactory, ModalEvents, Str) {
 
    return {
        init: function(courseid) {
            window.onbeforeunload = null;

            // handle change of "select signature to edit"
            var originalSelectValue = $('#id_select_signature_id').val();

            // when select signature id changes
            $('#id_select_signature_id').change(function(e) {
                e.preventDefault();

                // if the value actually changed, redirect to edit the selected signature id
                if (originalSelectValue !== this.value) {
                    $('label[for=id_select_signature_id] img.spinner-img').css('display', 'inline-block');
                    window.location.href = 'signatures.php?id=' + this.value + '&courseid=' + courseid;
                }
            });

            // handle deletion modal/request
            var trigger = $('#id_delete');

            ModalFactory.create({
                type: ModalFactory.types.CONFIRM,
                title: Str.get_string('delete_signature_modal_title', 'block_quickmail'),
                body: '<p>' + Str.get_string('delete_signature_confirm_message', 'block_quickmail') + '</p>'
            }, trigger).done(function(modal) {
                modal.getRoot().on(ModalEvents.yes, function(e) {
                    e.preventDefault();
                    
                    // change value of hidden input
                    $('input[name="delete_signature_flag"]').val('1');

                    // submit the form
                    $('#mform-manage-signatures').submit();
                });
            });
        }
    };
});