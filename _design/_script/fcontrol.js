$(document).ready(function () {
    $('#fcontrol>div.fcontrol-button').click(function () {
        if ($('#fcontrol').hasClass('hidden')) {
            wep.setCookie('wepfcontrol', 2, 99999);
            wep.ajaxLoadContent($(this).attr('data-id'), '#fcontrol>div.fcontrol-text', function () {
                //$('#fcontrol>.fcontrol-text').show();
                $('#fcontrol').removeClass('hidden');
            });
        }
        else {
            wep.deleteCookie('wepfcontrol');
            $('#fcontrol').addClass('hidden');
        }
    });
});