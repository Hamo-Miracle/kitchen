jQuery(document).ready(function ($) {
    function bnfw_custom_field_init() {
        var $notification = $('#notification'),
                notificationName;

        if ($notification.length <= 0) {
            return;
        }

        notificationName = $notification.val();
        
        var checknotification = notificationName.split("-");
        
        if (checknotification[0] === 'customfield') {
            $('#bnfw-custom-field').show();
        } else {
            $('#bnfw-custom-field').hide();
        }

        if ('user-customfield' === notificationName) {
            $('#bnfw-user-custom-field').show();
        } else {
            $('#bnfw-user-custom-field').hide();
        }

        if ('user-customfieldvalue' === notificationName) {
            $('#bnfw-user-custom-field-value').show();
            $('#user-custom-field-notification').val('true');
        } else {
            $('#bnfw-user-custom-field-value').hide();
            $('#user-custom-field-notification').val('false');
        }

        if (checknotification[0] === 'customfieldvalue' && ('user-customfieldvalue' !== notificationName)) {
            $('#bnfw-custom-field-value').show();
            $('#post-custom-field-notification').val('true');
        } else {
            $('#bnfw-custom-field-value').hide();
            $('#post-custom-field-notification').val('false');
        }
    }


    bnfw_custom_field_init();

    $('#notification').on('change', function () {
        bnfw_custom_field_init();
    });
});
