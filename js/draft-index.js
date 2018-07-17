$(function() {
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
            e.preventDefault();
            
            var deleteDraftId = $(e.target).attr("data-draft-id");

            openConfirm('Delete this draft?', function() {
                // change value of hidden input
                $('input[name="delete_draft_id"]').val(deleteDraftId);

                // submit the form
                $('#mform-manage-drafts').submit();
            });
        } else if ($(e.target).hasClass("btn-duplicate-draft")) {
            e.preventDefault();
            
            var duplicateDraftId = $(e.target).attr("data-draft-id");
            
            openConfirm('Duplicate this draft?', function() {
                // change value of hidden input
                $('input[name="duplicate_draft_id"]').val(duplicateDraftId);
                // submit the form
                $('#mform-manage-drafts').submit();
            });
        }
    });

    function openConfirm(msg, callback) {
        if (confirm(msg)) {
            callback();
        }
    }
});