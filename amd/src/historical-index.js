define(['jquery', 'core/modal_factory', 'core/modal_events'], function($, ModalFactory, ModalEvents) {
 
    return {
        init: function() {
            window.onbeforeunload = null;

            // handle change of "select course filter"
            var originalSelectValue = $('#select_course_filter_historical').val();

            // when selected course id changes
            $('#select_course_filter_historical').change(function(e) {
                e.preventDefault();

                // if the value actually changed, redirect to the correct page
                if (originalSelectValue !== this.value) {
                    window.location.href = 'history.php?courseid=' + this.value;
                }
            });

            // $(document).click(function(e) {
            //     if ($(e.target).hasClass("btn-delete-historical")) {
            //         deleteDraftId = $(e.target).attr("data-historical-id");
            //     }
            // });

            // // handle deletion modal/request
            // var deleteTrigger = $('.btn-delete-historical');

            // ModalFactory.create({
            //     type: ModalFactory.types.CONFIRM,
            //     title: 'Delete Message Draft',
            //     body: '<p>This will permanently delete your historical message, are you sure?</p>',
            // }, deleteTrigger).done(function(modal) {
            //     modal.getRoot().on(ModalEvents.yes, function(e) {
            //         e.preventDefault();
                    
            //         // change value of hidden input
            //         $('input[name="delete_historical_id"]').val(deleteDraftId);

            //         // submit the form
            //         $('#mform-manage-historicals').submit();
            //     });
            // });
        }
    };
});