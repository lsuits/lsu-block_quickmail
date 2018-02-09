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
                    window.location.href = 'sent.php?courseid=' + this.value;
                }
            });

            // $(document).click(function(e) {
            //     if ($(e.target).hasClass("btn-delete-draft")) {
            //         deleteDraftId = $(e.target).attr("data-draft-id");
            //     } else if ($(e.target).hasClass("btn-duplicate-draft")) {
            //         duplicateDraftId = $(e.target).attr("data-draft-id");
            //     }
            // });

            // handle deletion modal/request
            // var deleteTrigger = $('.btn-delete-draft');

            // ModalFactory.create({
            //     type: ModalFactory.types.CONFIRM,
            //     title: 'Delete Message Draft',
            //     body: '<p>This will permanently delete your draft message, are you sure?</p>',
            // }, deleteTrigger).done(function(modal) {
            //     modal.getRoot().on(ModalEvents.yes, function(e) {
            //         e.preventDefault();
                    
            //         // change value of hidden input
            //         $('input[name="delete_draft_id"]').val(deleteDraftId);

            //         // submit the form
            //         $('#mform-manage-drafts').submit();
            //     });
            // });
            
            // var duplicateTrigger = $('.btn-duplicate-draft');

            // ModalFactory.create({
            //     type: ModalFactory.types.CONFIRM,
            //     title: 'Duplicate Message Draft',
            //     body: '<p>This will make a copy of the draft, are you sure?</p>',
            // }, duplicateTrigger).done(function(modal) {
            //     modal.getRoot().on(ModalEvents.yes, function(e) {
            //         e.preventDefault();
                    
            //         // change value of hidden input
            //         $('input[name="duplicate_draft_id"]').val(duplicateDraftId);

            //         // submit the form
            //         $('#mform-manage-drafts').submit();
            //     });
            // });
        }
    };
});