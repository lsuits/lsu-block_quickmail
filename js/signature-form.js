$(function() {

    //  ¯\_(ツ)_/¯
    $('#id_delete').removeClass('btn-primary');
    $('#id_delete').addClass('btn-danger');

    // handle change of "select signature to edit"
    var selectedSignatureId = $('#id_select_signature_id').val();

    // when select signature id changes
    $('#id_select_signature_id').change(function(e) {
        e.preventDefault();

        // if the value actually changed, redirect to edit the selected signature id
        if (selectedSignatureId != this.value) {
            let qs = {
                id: this.value,
                courseid: signaturedata.courseid
            };

            window.location.href = 'signatures.php?' + $.param(qs);
        }
    });

    $('#id_delete').click(function(e) {
        if ( ! confirm('Delete this signature?')) {
            e.preventDefault();
        }
    });
});