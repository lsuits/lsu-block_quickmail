define(['jquery', 'core/modal_factory', 'core/modal_events', 'core/str', 'block_quickmail/modal_create_alternate'], function($, ModalFactory, ModalEvents, Str, ModalCreateAlternate) {
 
    return {
        init: function() {
            $(document).click(function(e) {
                if ($(e.target).hasClass("btn-delete-alt")) {
                    deleteAlternateId = $(e.target).attr("data-alternate-id");
                }
            });

            // handle deletion modal/request
            var deleteTrigger = $('.btn-delete-alt');

            ModalFactory.create({
                type: ModalFactory.types.CONFIRM,
                title: 'Delete Alternate Email',
                body: '<p>This will permanently delete your alternate email, are you sure?</p>',
            }, deleteTrigger).done(function(modal) {
                modal.getRoot().on(ModalEvents.yes, function(e) {
                    e.preventDefault();
                    
                    // change value of hidden input
                    $('input[name="delete_alternate_id"]').val(deleteAlternateId);

                    // submit the form
                    $('#mform-manage-alternates').submit();
                });
            });

            var createTrigger = $('.btn-create-alt');
 
            Str.get_string('alternate_new', 'block_quickmail').then(function(thetitle) {
                ModalFactory.create({
                    type: ModalCreateAlternate.TYPE,
                    title: thetitle,
                }, createTrigger).done(function(modal) {
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        
                        // reset client-side validation
                        var validData = true;
                        $('#inputEmail').removeClass('has-error');
                        $('#inputFirstname').removeClass('has-error');
                        $('#inputLastname').removeClass('has-error');
                        $('#inputAvailability').removeClass('has-error');

                        // capture the modal form input
                        var inputEmail = $('#inputEmail').val();
                        var inputFirstname = $('#inputFirstname').val();
                        var inputLastname = $('#inputLastname').val();
                        var inputAvailability = $('#inputAvailability').val();

                        // begin client-side validation
                        var emailRegex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

                        // validate email
                        if (inputEmail.length == 0 || ! emailRegex.test(inputEmail)) {
                            $('#inputEmail').addClass('has-error');
                            validData = false;
                        }

                        // validate firstname
                        if (inputFirstname.length == 0) {
                            $('#inputFirstname').addClass('has-error');
                            validData = false;
                        }

                        // validate lastname
                        if (inputLastname.length == 0) {
                            $('#inputLastname').addClass('has-error');
                            validData = false;
                        }

                        // validate availability selection
                        if (inputAvailability.length == 0) {
                            $('#inputAvailability').addClass('has-error');
                            validData = false;
                        }

                        // if not valid, do not submit form
                        if ( ! validData) {
                            return;
                        }

                        // update values of page hidden form
                        $('input[name="create_flag"]').val(1);
                        $('input[name="firstname"]').val(inputFirstname);
                        $('input[name="lastname"]').val(inputLastname);
                        $('input[name="email"]').val(inputEmail);
                        $('input[name="availability"]').val(inputAvailability);

                        // submit the page form
                        $('#mform-manage-alternates').submit();
                    });
                });
            });
        }
    };
});