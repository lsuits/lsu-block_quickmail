define(['jquery', 'core/modal_factory', 'core/modal_events'], function($, ModalFactory, ModalEvents) {
 
    return {
        init: function() {
            window.onbeforeunload = null;

            // handle change of "select course filter"
            var originalSelectValue = $('#select_course_filter').val();

            // when selected course id changes
            $('#select_course_filter').change(function(e) {
                e.preventDefault();

                // if the value actually changed, redirect to the correct page
                if (originalSelectValue !== this.value) {
                    window.location.href = 'queued.php?courseid=' + this.value;
                }
            });

            $(document).click(function(e) {
                if ($(e.target).hasClass("btn-unqueue-message")) {
                    unqueueMessageId = $(e.target).attr("data-queued-id");
                }
            });

            // handle deletion modal/request
            var unqueueTrigger = $('.btn-unqueue-message');

            ModalFactory.create({
                type: ModalFactory.types.CONFIRM,
                title: 'Unqueue Scheduled Message',
                body: '<p>This will unschedule this message to be sent and save the message as a draft, are you sure?</p>',
            }, unqueueTrigger).done(function(modal) {
                modal.getRoot().on(ModalEvents.yes, function(e) {
                    e.preventDefault();
                    
                    // change value of hidden input
                    $('input[name="unqueue_message_id"]').val(unqueueMessageId);

                    // submit the form
                    $('#mform-manage-queued').submit();
                });
            });
        }
    };
});