define(['jquery', 'core/notification', 'core/custom_interaction_events', 'core/modal', 'core/modal_registry', 'core/modal_events'],
        function($, Notification, CustomEvents, Modal, ModalRegistry, ModalEvents) {
 
    var registered = false;
    var SELECTORS = {
        CREATE_BUTTON: '[data-action="create"]',
        CANCEL_BUTTON: '[data-action="cancel"]',
    };
 
    /**
     * Constructor for the Modal.
     *
     * @param {object} root The root jQuery element for the modal
     */
    var ModalCreateAlternate = function(root) {
        Modal.call(this, root);
 
        if (!this.getFooter().find(SELECTORS.CREATE_BUTTON).length) {
            Notification.exception({message: 'No create button found'});
        }
 
        if (!this.getFooter().find(SELECTORS.CANCEL_BUTTON).length) {
            Notification.exception({message: 'No cancel button found'});
        }
    };
 
    ModalCreateAlternate.TYPE = 'block_quickmail-create_alternate';
    ModalCreateAlternate.prototype = Object.create(Modal.prototype);
    ModalCreateAlternate.prototype.constructor = ModalCreateAlternate;
 
    /**
     * Set up all of the event handling for the modal.
     *
     * @method registerEventListeners
     */
    ModalCreateAlternate.prototype.registerEventListeners = function() {
        // Apply parent event listeners.
        Modal.prototype.registerEventListeners.call(this);
 
        this.getModal().on(CustomEvents.events.activate, SELECTORS.CREATE_BUTTON, function(e, data) {
            console.log(data);

            var saveEvent = $.Event(ModalEvents.save);
            this.getRoot().trigger(saveEvent, this);

            if (!saveEvent.isDefaultPrevented()) {
                this.hide();
                data.originalEvent.preventDefault();
            }
        }.bind(this));
 
        this.getModal().on(CustomEvents.events.activate, SELECTORS.CANCEL_BUTTON, function(e, data) {
            var cancelEvent = $.Event(ModalEvents.cancel);
            this.getRoot().trigger(cancelEvent, this);

            if (!cancelEvent.isDefaultPrevented()) {
                this.hide();
                data.originalEvent.preventDefault();
            }
        }.bind(this));
    };
 
    // Automatically register with the modal registry the first time this module is imported so that you can create modals
    // of this type using the modal factory.
    if (!registered) {
        ModalRegistry.register(ModalCreateAlternate.TYPE, ModalCreateAlternate, 'block_quickmail/modal_create_alternate');
        registered = true;
    }
 
    return ModalCreateAlternate;
});