define(['jquery', 'core/modal_factory', 'core/modal_events', 'core/str'], function($, ModalFactory, ModalEvents, Str) {
 
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
                    window.location.href = 'drafts.php?courseid=' + this.value;
                }
            });

            $(document).click(function(e) {
                if ($(e.target).hasClass("btn-delete-draft")) {
                    deleteDraftId = $(e.target).attr("data-draft-id");
                } else if ($(e.target).hasClass("btn-duplicate-draft")) {
                    duplicateDraftId = $(e.target).attr("data-draft-id");
                }
            });

            // handle deletion modal/request
            var deleteTrigger = $('.btn-delete-draft');

            ModalFactory.create({
                type: ModalFactory.types.CONFIRM,
                title: Str.get_string('delete_draft_modal_title', 'block_quickmail'),
                body: '<p>' + Str.get_string('delete_draft_confirm_message', 'block_quickmail') + '</p>'
            }, deleteTrigger).done(function(modal) {
                modal.getRoot().on(ModalEvents.yes, function(e) {
                    e.preventDefault();
                    
                    // change value of hidden input
                    $('input[name="delete_draft_id"]').val(deleteDraftId);

                    // submit the form
                    $('#mform-manage-drafts').submit();
                });
            });
            
            var duplicateTrigger = $('.btn-duplicate-draft');

            ModalFactory.create({
                type: ModalFactory.types.CONFIRM,
                title: Str.get_string('duplicate_draft_modal_title', 'block_quickmail'),
                body: '<p>' + Str.get_string('duplicate_draft_confirm_message', 'block_quickmail') + '</p>'
            }, duplicateTrigger).done(function(modal) {
                modal.getRoot().on(ModalEvents.yes, function(e) {
                    e.preventDefault();
                    
                    // change value of hidden input
                    $('input[name="duplicate_draft_id"]').val(duplicateDraftId);

                    // submit the form
                    $('#mform-manage-drafts').submit();
                });
            });
        }
    };
});