$(function() {
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
});