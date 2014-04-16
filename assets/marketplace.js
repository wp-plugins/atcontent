(function () {
    'use strict';

    var hints,
        $todoItems,
        $postsList,
        $todoList,
        $hintsList,
        $hintsArrow,
        nickname;

    (function ($) {
        $(function () {
            nickname = document.getElementById('_nickname');
            if (nickname) {
                nickname = nickname.value;
            }
            new CustomSelect('select[name="tags"]');
            new CustomSelect('select[name="country"]');

            $todoList = $('#todoList');
            $hintsList = $('#hintsList');
            $hintsArrow = $('#hintsArrow');

            window.CPlase = window.CPlase || {};
            CPlase.evt = CPlase.evt || [];
            CPlase.evt.push(function (event, p, w) {
                var o = $('#' + ('CPlase_' + p + '_' + w + '_panel').addSlashes()),
                    cost = CPlase.id('campaignCost' + p);
                if (cost && !o.prev('.CPlase_publicationLink').size()) {
                    var panel = tmpl('tmpl_repost', {
                        cost: cost.value
                    }, function () {
                        $(this.querySelector('.ac-rpst-b__b')).on('click', function (e) {
                            e.stopPropagation();
                            CPlase.text.embed(p, w, this);
                        });
                    });
                    o.before(panel);
                }
            });

            $postsList = $('#postsList');

            /* ===== hints ===== */
            window.hints = hints;

            hints = {};

            hints.join = {};
            hints.join.next = 'tags';
            hints.join.init = function () {
                var joinHint;

                joinHint = new Hint({
                    state: {
                        isJoined: document.getElementById('_nickname').value.length > 0
                    },
                    completenessCheck: function () {
                        return this.state.isJoined
                    },
                    onComplete: function (wasCompletedInPast) {
                        $('#hintTodo_join')
                            .removeClass('b-hints__todo-item_undone')
                            .addClass('b-hints__todo-item_done');
                        if (!wasCompletedInPast) {
                            hints.show('tags');
                        }
                    },
                    iteration: function () {
                        this.state.isJoined = true;
                    }
                });
                hints.join.init = function () { };
                hints.join.isCompleted = joinHint.isCompleted;
            };

            hints.tags = {};
            hints.tags.next = 'country';
            hints.tags.init = function () {
                var tagsHint;

                tagsHint = new Hint({
                    state: {
                        tagsSet: $('select[name="tags"]').val() !== null
                    },
                    completenessCheck: function () {
                        return this.state.tagsSet;
                    },
                    onComplete: function (wasCompletedInPast) {
                        $('#hintTodo_tags')
                            .removeClass('b-hints__todo-item_undone')
                            .addClass('b-hints__todo-item_done');
                        if (!wasCompletedInPast) {
                            if (hints.country.isCompleted && hints.country.isCompleted()) {
                                $postsList.removeClass('blocked');
                            }
                            hints.show('country');
                        }
                    },
                    onCompleteCancel: function () {
                        $postsList.addClass('blocked');
                        $('#hintTodo_tags')
                            .addClass('b-hints__todo-item_undone')
                            .removeClass('b-hints__todo-item_done');
                    },
                    iteration: function (state) {
                        this.state.tagsSet = state;
                    }
                });
                hints.tags.init = function () { };
                hints.tags.isCompleted = tagsHint.isCompleted;

                $('#tagsHint').on('submit', function (e) {
                    e.preventDefault();
                    var loader = $(loaderHtml._0),
                        saveConfirm = $('#tagsSaved'),
                        select = $('[name="tags"]');
                    saveConfirm.hide();
                    $('#tagsSubmit').attr('disabled', '').prepend(loader);
                    $.ajax({
                        url: ajaxUrl,
                        data: {
                            action: "atcontent_save_tags",
                            tags: select.val()
                        },
                        dataType: 'json',
                        type: 'post',
                        traditional: true,
                        success: function (d) {
                            if (d.TagsUpdated && select.val() !== null) {
                                tagsHint.iterate(true);
                            } else {
                                tagsHint.iterate(false);
                            }
                            saveConfirm.show();
                            setTimeout(function () {
                                saveConfirm.fadeOut('slow', function () {
                                    saveConfirm.hide();
                                });
                            }, 2000);
                        },
                        complete: function () {
                            loader.remove();
                            $('#tagsSubmit').removeAttr('disabled');
                        }
                    });
                });
            };

            hints.country = {};
            hints.country.next = 'earnings';
            hints.country.init = function () {
                var countryHint;

                countryHint = new Hint({
                    state: {
                        country: $('select[name="country"]').val() !== '0'
                    },
                    completenessCheck: function () {
                        return this.state.country;
                    },
                    onComplete: function (wasCompletedInPast) {
                        $('#hintTodo_country')
                            .removeClass('b-hints__todo-item_undone')
                            .addClass('b-hints__todo-item_done');
                        if (!wasCompletedInPast) {
                            $postsList.removeClass('blocked');
                            hints.show('earnings');
                        }
                    },
                    onCompleteCancel: function () {
                        $postsList.addClass('blocked');
                        $('#hintTodo_country')
                            .addClass('b-hints__todo-item_undone')
                            .removeClass('b-hints__todo-item_done');
                    },
                    iteration: function (state) {
                        this.state.country = state;
                    }
                });
                hints.country.init = function () { };
                hints.country.isCompleted = countryHint.isCompleted;

                $('#countryHint').on('submit', function (e) {
                    e.preventDefault();
                    var loader = $(loaderHtml._0),
                        saveConfirm = $('#countrySaved'),
                        $select = $('[name="country"]');
                    saveConfirm.hide();
                    $('#countrySubmit').attr('disabled', '').prepend(loader);
                    $.ajax({
                        url: ajaxUrl,
                        data: {
                            action: "atcontent_save_country",
                            country: $select.val()
                        },
                        dataType: 'json',
                        type: 'post',
                        traditional: true,
                        success: function (d) {
                            if (d.CountryUpdated && $select.val() !== '0') {
                                countryHint.iterate(true);
                            } else {
                                countryHint.iterate(false);
                            }
                            saveConfirm.show();
                            setTimeout(function () {
                                saveConfirm.fadeOut('slow', function () {
                                    saveConfirm.hide();
                                });
                            }, 2000);
                        },
                        complete: function () {
                            loader.remove();
                            $('#countrySubmit').removeAttr('disabled');
                        }
                    });
                });
            };

            hints.earnings = {};
            hints.earnings.init = function () {
                var defContent = $('#hint_earnings').html(),
                    earningsHint = new Hint({
                        state: {
                            completed: hints.join.isCompleted() && hints.tags.isCompleted() && hints.country.isCompleted()
                        },
                        completenessCheck: function () {
                            return this.state.completed;
                        },
                        onComplete: function (wasCompletedInPast) {
                            $hintsList.addClass('docked');
                            $postsList.removeClass('blocked');
                            window.ac_allow_repost = true;
                            $('#hint_earnings').html(defContent);
                        },
                        iteration: function () {
                            this.state.completed = true;
                        }
                    });
                hints.earnings.init = function () { };
                hints.earnings.isCompleted = earningsHint.isCompleted;

                if (!earningsHint.isCompleted()) {
                    $('#hint_earnings').empty().append(tmpl('tmpl_congrats'));
                    $('#startBtn').on('click', function () {
                        earningsHint.iterate();
                    });
                }
            };

            hints.show = function (hint, isManual) {
                while (!isManual && hints[hint].next && hints[hint].isCompleted()) {
                    hint = hints[hint].next;
                }
                $('.b-hints__item').removeClass('b-hints__item_visible');
                $('.b-hints__todo-item').removeClass('b-hints__todo-item_current');
                $('#hint_' + hint).addClass('b-hints__item_visible');
                var $currentTodoItem = $('#hintTodo_' + hint);
                $currentTodoItem.addClass('b-hints__todo-item_current');
                $hintsArrow.css('top', $currentTodoItem.offset().top - $todoList.offset().top + $currentTodoItem.height() / 2 + 'px');
            };


            $todoItems = $('.b-hints__todo-item-title');

            $todoItems.on('click', function () {
                var $this = $(this);
                if ($this.parent().prevAll().not('.b-hints__todo-item_done').size() === 0) {
                    hints.show($this.attr('data-step'), true);
                }
            });

            for (var step in hints) {
                if (hints[step].init instanceof Function) {
                    hints[step].init();
                }
            }
            $hintsList.show();
            hints.show('join');
        });
    })(jQuery);
})();

var ac = {},

    defAjaxErrorMsg = 'Sorry, but server is temporary unavailable. Please try again later.',
    defAjaxErrorDelay = 6000,
    loaderHtml = {
        _0: '<span class="icon-loader"></span>',
        _16: '<span class="icon-loader icon-loader_16"></span>',
        _32: '<span class="icon-loader icon-loader_32"></span>',
        _64: '<span class="icon-loader icon-loader_64"></span>'
    },
    ajaxQuery = {},
    emptyMsg = 'Nothing yet';

(function () {
    var n = navigator.userAgent;
    if (n.indexOf('MSIE') >= 0 && parseInt(n.split('MSIE ')[1], 10) < 9) {
        document.createElement('details');
        document.createElement('summary');
        document.createElement('footer');
        document.createElement('header');
        document.createElement('article');
        document.createElement('section');
        document.createElement('figure');
        document.createElement('aside');
        document.createElement('datalist');
        document.createElement('main');
        document.createElement('nav');
    }
})();