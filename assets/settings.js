(function ($) {
    'use strict';

    var $page;
    $(function () {
        $page = $('#ac-page');        

        if (!document.createElementNS || !document.createElementNS('http://www.w3.org/2000/svg', 'svg').createSVGRect) {
            $page.addClass('no-svg');
        }
    });
})(jQuery);

var ac = window.ac || {
    setCookie: function (name, value, days, path, domain, secure) {
        if (arguments.length < 2) return false;
        var str = name + '=' + encodeURIComponent(value);

        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            str += '; expires=' + date.toGMTString();
        }
        str += '; path=' + (path || '/');
        if (domain) str += '; domain=' + domain;
        if (secure) str += '; secure';

        document.cookie = str;
        return true;
    },

    getCookie: function (name) {
        var nameEQ = name + "=",
            ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
        return null;
    }
};