/**
 * Wrapper function to safely use $
 */
function wpdcWrapper($) {
    var wpdc = {

        /**
         * Main entry point
         */
        init: function () {
            wpdc.prefix = 'wpdc_';
            wpdc.templateURL = $('#template-url').val();
            wpdc.ajaxPostURL = $('#ajax-post-url').val();

            wpdc.registerEventHandlers();
        },

        /**
         * Registers event handlers
         */
        registerEventHandlers: function () {
        }
    }; // end wpdc

    $(document).ready(wpdc.init);

} // end wpdcWrapper()

wpdcWrapper(jQuery);
