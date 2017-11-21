define(['jquery', 'core/modal_events'], function($, ModalEvents) {
 
    return {
        init: function() {
            window.onbeforeunload = null;

            // handle change of "select course filter"
            var originalSelectValue = $('#select_course_filter_draft').val();

            // when selected course id changes
            $('#select_course_filter_draft').change(function(e) {
                e.preventDefault();

                // if the value actually changed, redirect to the correct page
                if (originalSelectValue !== this.value) {
                    window.location.href = 'drafts.php?courseid=' + this.value;
                }
            });
        }
    };
});