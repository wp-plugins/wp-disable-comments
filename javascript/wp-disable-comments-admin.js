/**
 * Wrapper function to safely use $
 */
function wpdcAdminWrapper($) {
    var wpdcAdmin = {

        /**
         * Main entry point
         */
        init: function () {
        }

    }; // end wpdcAdmin

    $(document).ready(wpdcAdmin.init);

} // end wpdcAdminWrapper()

wpdcAdminWrapper(jQuery);
