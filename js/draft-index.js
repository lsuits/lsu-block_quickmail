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
            if ( ! confirm('Delete this draft?')) {
                e.preventDefault();
            }
        } else if ($(e.target).hasClass("btn-duplicate-draft")) {
            if ( ! confirm('Duplicate this draft?')) {
                e.preventDefault();
            }
        }
    });
});