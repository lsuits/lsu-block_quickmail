define(['jquery', 'core/modal_factory', 'core/modal_events', 'core/str'], function($, ModalFactory, ModalEvents, Str) {
 
    return {
        init: function(courseid) {
            window.onbeforeunload = null;

            // handle deletion modal/request
            var trigger = $('#id_reset');

            ModalFactory.create({
                type: ModalFactory.types.CONFIRM,
                title: Str.get_string('restore_default_modal_title', 'block_quickmail'),
                body: Str.get_string('restore_default_confirm_message', 'block_quickmail')
            }, trigger).done(function(modal) {
                modal.getRoot().on(ModalEvents.yes, function(e) {
                    e.preventDefault();
                    
                    // change value of hidden input
                    $('input[name="restore_flag"]').val('1');

                    // submit the form
                    $('#mform-course-config').submit();
                });
            });
        }
    };
});