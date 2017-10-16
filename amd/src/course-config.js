define(['jquery', 'core/modal_factory', 'core/modal_events'], function($, ModalFactory, ModalEvents) {
 
    return {
        init: function(courseid) {
            window.onbeforeunload = null;

            // handle deletion modal/request
            var trigger = $('#id_reset');

            ModalFactory.create({
                type: ModalFactory.types.CONFIRM,
                title: 'Restore Default Configuration',
                body: '<p>This will permanently delete you course configuration, are you sure?</p>',
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