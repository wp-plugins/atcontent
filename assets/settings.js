(function ($) {
    'use strict';

    var $page,

        $panels,
        $panelsTitles,
        $panelsContents,
        $currentPanel = null,

        CLASS_NAME_OPEN_ACC_PANEL = 'b-ac-acc__pane_open',

        $benefits,
        $benefitsToggle,

        CLASS_NAME_TOGGLABLE_BENEFIT = 'b-ac-togglable-benefit',
        CLASS_NAME_BENEFIT_HIDDEN = 'b-ac-togglable-benefit_hidden',
        CLASS_NAME_PANELS_BLOCKED = 'b-ac-page_incomplete',
        CLASS_NAME_PANELS_UNREAD = 'b-ac-acc__pane_unread',

        COOKIE_GUIDE_NAME = 'acguide',
        COOKIE_GUIDE_VALUE = '=)';

    function openPanel($panel) {
        if ($currentPanel) {
            closePanel($currentPanel);
        }

        if ($panel.is($currentPanel)) {
            $currentPanel = null;
            return;
        }

        $currentPanel = $panel;

        $currentPanel.addClass(CLASS_NAME_OPEN_ACC_PANEL);
        ac_ga_s('settings', 'openpanel' + $panel.attr('data-id'));

        //if ($currentPanel.attr('data-id') != 'guide') {
        //    $currentPanel.removeClass(CLASS_NAME_PANELS_UNREAD);
        //    $.post('admin-ajax.php', {
        //        action: 'atcontent_settings_tab',
        //        id: $currentPanel.attr('data-id')
        //    });
        //}
    }

    function closePanel($panel) {
        $panel.removeClass(CLASS_NAME_OPEN_ACC_PANEL);
    }

    function releasePanels() {
        $page.removeClass(CLASS_NAME_PANELS_BLOCKED);
        ac.setCookie(COOKIE_GUIDE_NAME, COOKIE_GUIDE_VALUE, 1923);
        //$.post('admin-ajax.php', {
        //    action: 'atcontent_settings_tab',
        //    id: 'guide'
        //});
        //$('#ac_tab_guide').removeClass(CLASS_NAME_PANELS_UNREAD);
    }

    $(function () {
        $page = $('#ac-page');

        $panels = $('.b-ac-acc__pane');
        $panelsTitles = $panels.find('.b-ac-acc__pane-title');
        $panelsContents = $panels.find('.b-ac-acc__pane-content');

        $panelsTitles.on('click', function () {
            openPanel($(this.parentNode));
        });

        var $panelToOpen = $panels.filter('[open]');
        if (!$panelToOpen.length) {
            $panelToOpen = $panels.eq(0);
        } else {
            $panelToOpen = $panelToOpen.eq(0);
        }
        openPanel($panelToOpen);

        $('[data-role="acc-nav"]').on('click', function () {
            openPanel($panels.eq($(this).attr('data-target')));
        });


        $benefitsToggle = $('#benefitsToggle');
        $benefits = $('.' + CLASS_NAME_TOGGLABLE_BENEFIT);

        $benefitsToggle.on('click', function (e) {
            e.preventDefault();

            var $this = $(this),
                alt = $(this).attr('data-alt');
            $this.attr('data-alt', $this.text()).text(alt);

            $benefits.toggleClass(CLASS_NAME_BENEFIT_HIDDEN);
        });

        if (ac.getCookie(COOKIE_GUIDE_NAME) === COOKIE_GUIDE_VALUE) {
            releasePanels();
        } else {
            $('#follow_bloggers_button').on('click', releasePanels);
        }

        if (!document.createElementNS || !document.createElementNS('http://www.w3.org/2000/svg', 'svg').createSVGRect) {
            $page.addClass('no-svg');
        }
    });
})(jQuery);

(function ($) {
    'use strict';
    
    var CLASS_NAME_SLIDER_PANE = 'b-ac-slider__pane',
        CLASS_NAME_SLIDER_PANE_VISIBLE = 'b-ac-slider__pane_visible',
        CLASS_NAME_SLIDER_NAV_DOTS = 'b-ac-slider__nav-dots',
        CLASS_NAME_SLIDER_NAV_DOT = 'b-ac-slider__nav-dot',
        CLASS_NAME_SLIDER_NAV_DOT_ACTIVE = 'b-ac-slider__nav-dot_active';

    function showPane($slider, index) {
        var $panes = $slider.find('.' + CLASS_NAME_SLIDER_PANE);
        if (index < 0 || index >= $panes.length) {
            return;
        }
        $panes
            .removeClass(CLASS_NAME_SLIDER_PANE_VISIBLE)
                .eq(index)
                .addClass(CLASS_NAME_SLIDER_PANE_VISIBLE);
        $('[data-slider="' + $slider[0].id + '"]')
            .find('.' + CLASS_NAME_SLIDER_NAV_DOT)
                .removeClass(CLASS_NAME_SLIDER_NAV_DOT_ACTIVE)
                .slice(0, index + 1).addClass(CLASS_NAME_SLIDER_NAV_DOT_ACTIVE);
        updateButtonsState($slider);
    }

    function showNext($slider) {
        var $currentPane = $slider.find('.' + CLASS_NAME_SLIDER_PANE_VISIBLE),
            currentIndex = $slider.find('.' + CLASS_NAME_SLIDER_PANE).index($currentPane);
        showPane($slider, currentIndex + 1);
        updateButtonsState($slider);
    }

    function showPrev($slider) {
        var $currentPane = $slider.find('.' + CLASS_NAME_SLIDER_PANE_VISIBLE),
            currentIndex = $slider.find('.' + CLASS_NAME_SLIDER_PANE).index($currentPane);
        showPane($slider, currentIndex - 1);
        updateButtonsState($slider);
    }

    function updateButtonsState($slider) {
        var $panes = $slider.find('.' + CLASS_NAME_SLIDER_PANE),
            $currentPane = $slider.find('.' + CLASS_NAME_SLIDER_PANE_VISIBLE),
            currentIndex = $panes.index($currentPane),
            $nav = $('[data-slider="' + $slider[0].id + '"]'),
            $button_prev = $nav.find('[data-role="prev"]'),
            $button_next = $nav.find('[data-role="next"]');
        $button_prev.prop('disabled', currentIndex === 0);
        $button_next.prop('disabled', currentIndex === $panes.length - 1);
    }

    $(function () {
        $('.b-ac-slider').each(function () {
            var $this = $(this),
                $panes = $this.find('.' + CLASS_NAME_SLIDER_PANE),
                $nav = $('[data-slider="' + this.id + '"]'),
                $buttons = $nav.find('button'),
                    
                $dots = $();

            $panes.each(function (index) {
                var $dot = $('<div>').addClass(CLASS_NAME_SLIDER_NAV_DOT).on('click', function () {
                    showPane($this, index);
                });
                $dots = $dots.add($dot);
            });

            $buttons.on('click', function () {
                $(this).data().role === 'prev' ? showPrev($this) : showNext($this);
            });

            $nav
                .on('click', function (e) {
                    if ($nav.closest('.b-ac-acc__pane_open').length) {
                        e.stopPropagation();
                    }
                })
                .find('.' + CLASS_NAME_SLIDER_NAV_DOTS)
                    .append($dots);

            showPane($this, 0);
        });

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