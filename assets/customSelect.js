(function($) {
window.CustomSelect = function(select) {
    if (!(this instanceof CustomSelect)) return new CustomSelect(select);
    if (!select) return false;
    if (typeof select === 'string') {
        if (select === 'close') return this.close();
        else if (!select.indexOf('CustomSelect-')) return this._cache[select];
        select = $(select);
    }
    if (!select.size()) return false;
    this._initOnce();
    if (select.size() > 1) {
        select.each(function () {
            new CustomSelect($(this));
        });
    }
    var ID = select.attr('data-custom-select-id');
    if (ID) return this._cache[ID];
    this._select = select;
    this._ID = 'CustomSelect-' + (new Date()).getTime() + String.fromCharCode(Math.floor(Math.random() * 26) + 65) + String.fromCharCode(Math.floor(Math.random() * 26) + 97);
    this._select.attr('data-custom-select-id', this._ID);
    this._cache[this._ID] = this;
    this._draw();
}
CustomSelect.prototype = {
    _cache: {},
    _inited: false,
    _open: false,
    _select: null,
    _replacer: null,
    _value: null,
    _list: null,
    _ID: null,
    _multiple: false,
    _multiple_lastItem: null,
    _multiple_dragStart: false,
    _multiple_maxItems: -1,
    _editable: false,
    _editableInput: null,
    _editableEmptyMsg: null,
    _initOnce: function () {
        if (this._inited) return;
        CustomSelect.prototype._inited = true;
        $(document).on('mousedown', function () {
            CustomSelect('close');
        }).on('mouseup', function () {
            $('.b-select-multiple').each(function () {
                var ID = $(this).attr('id'),
                    s = CustomSelect(ID);
                if (s._multiple) s._multiple_dragStart = false;
            });
        }).on('keydown', function (e) {
            if (e.which == 27) {
                CustomSelect('close');
            }
        });
    },
    _draw: function () {
        this._editable = !!(this._select.attr('data-editable') === 'true');
        this._multiple = !!this._select.attr('multiple');
        this._select.after(this._getHTML())
            .on('change', this._listeners.select.change);
        if (this._editable) {
            this._select.attr('data-default-tabindex', this._select.attr('tabindex') || '').attr('tabindex', '-1');
        } else {
            this._select
            .on('keydown', this._listeners.select.keydown)
            .on('focus', this._listeners.select.focus)
            .on('blur', this._listeners.select.blur);
        }
        this._replacer = $('#' + this._ID);
        this._replacer.append(this._select);
        this._value = $('#' + this._ID + '-value');
        this._list = $('#' + this._ID + '-list');
        this._list.find('.b-select-option')
                .on('mousedown', this._listeners.replacer.options.mousedown)
                .on('mouseup', this._listeners.replacer.options.mouseup)
                .on('mouseenter', this._listeners.replacer.options.mouseenter);
        if (!this._multiple || this._editable) {
            this._replacer.append(this._select).on('mousedown', this._listeners.replacer.mousedown);
        }
        if (this._editable) {
            this._editableInput = $('#' + this._ID + '-value-input');
            this._editableInput
                .on('focus', this._listeners.replacer.editableInput.focus)
                .on('blur', this._listeners.replacer.editableInput.blur)
                .on('keydown', this._listeners.replacer.editableInput.keydown)
                .on('keyup', this._listeners.replacer.editableInput.keyup);
            this._value
                .on('click', '.b-select-value-tag-remove', this._listeners.replacer.tags.click)
                .on('mousedown', '.b-select-value-tag-remove', this._listeners.stopPropagation);
            if (!isSupported('placeholder')) this._editableInput.blur();
        }
        this._list.on('mousedown', this._listeners.stopPropagation)
        this._multiple_lastItem = this._select.find(':selected:first');
        this._multiple_maxItems = parseInt(this._select.attr('data-max'));
        if (isNaN(this._multiple_maxItems)) {
            this._multiple_maxItems = -1;
        }
        this.updateValue();
    },
    destroy: function () {
        this._select.off('change', this._listeners.select.change);
        if (this._editable) {
            this._select.attr('tabindex', this._select.attr('data-default-tabindex'));
        } else {
            this._select
                .off('keydown', this._listeners.select.keydown)
                .off('focus', this._listeners.select.focus)
                .off('blur', this._listeners.select.blur);
        }
        this._list.find('.b-select-option')
                .off('mousedown', this._listeners.replacer.options.mousedown)
                .off('mouseup', this._listeners.replacer.options.mouseup)
                .off('mouseenter', this._listeners.replacer.options.mouseenter);
        if (!this._multiple || this._editable) {
            this._replacer.off('mousedown', this._listeners.replacer.mousedown);
        }
        if (this._editable) {
            this._editableInput
                .off('focus', this._listeners.replacer.editableInput.focus)
                .off('blur', this._listeners.replacer.editableInput.blur)
                .off('keydown', this._listeners.replacer.editableInput.keydown)
                .off('keyup', this._listeners.replacer.editableInput.keyup);
            this._value
                .off('click', '.b-select-value-tag-remove', this._listeners.replacer.tags.click)
                .off('mousedown', '.b-select-value-tag-remove', this._listeners.stopPropagation);
        }
        this._list.off('mousedown', this._listeners.stopPropagation)
        this._replacer.after(this._select).remove();
        var ID = this._select.attr('data-custom-select-id');
        this._select.removeAttr('data-custom-select-id');
        delete CustomSelect.prototype._cache[ID];
    },
    _getHTML: function () {
        var self = this,
            tabindex = this._select.attr('tabindex') || null,
            placeholder = this._select.attr('data-placeholder') || 'Click to see options…';
        return '<div class="b-select' + (this._editable ? ' b-select-editable' : this._multiple ? ' b-select-multiple' : '') + (this._select[0].className ? ' ' + this._select[0].className : '') + '"' + (this._select.attr('style') ? ' style="' + this._select.attr('style') + '"' : '') + ' id="' + this._ID + '">' +
                (this._multiple && !this._editable ? '' : '<span class="b-select-arrow"></span>') +
                (this._editable ? '<div class="b-select-value" id="' + this._ID + '-value"> ' +
                    '<input type="text" class="b-select-value-input" id="' + this._ID + '-value-input" name="" data-custom-select-id="' + this._ID + '" autocomplete="off" placeholder="' + placeholder + '"' + (tabindex !== null ? ' tabindex="' + tabindex + '"' : '') + '>' +
                '</div>'
                : this._multiple ? ''
                : '<div class="b-select-value" id="' + this._ID + '-value">' + this._select.find(':selected').text() + '</div>') +
                '<ul class="b-select-list" id="' + this._ID + '-list" data-custom-select-id="' + this._ID + '">' +
                    (function () {
                        var h = '', optgroup = false;
                        self._select.find('> option, > optgroup').each(function () {
                            var $this = $(this);
                            if ($this.is('optgroup')) {
                                h += '<li class="b-select-list-item b-select-optgroup">' + $this.attr('label') + '</li>';
                                optgroup = true;
                                $this.find('option').each(arguments.callee);
                                optgroup = false;
                            } else {
                                var img = $this.attr('data-image'),
                                    val = (img ? '<img src="' + img + '" alt="" class="b-select-item-image">' : '') + $this.text();
                                h += '<li class="b-select-list-item b-select-option' + (optgroup ? ' b-select-option-grouped' : '') + '" data-value="' + this.value + '" data-custom-select-id="' + self._ID + '">' + val + '</li>';
                            }
                        });
                        return h;
                    })()
        '</ul>' +
    '</div>';
    },
    _getTagHTML: function (value, caption) {
        return '<span class="b-select-value-tag" data-value="' + value + '">' +
                   caption +
                   ' <span class="b-select-value-tag-remove icon-del" data-value="' + value + '" data-custom-select-id="' + this._ID + '"></span>' +
               '</span>';
    },
    open: function () {
        this.close();
        this._replacer.addClass('b-select-open');
        this._open = true;
        this._editable || this._select.focus();
        var self = this,
            fontSize = this._replacer.css('font-size'),
            minWidth = Math.floor(this._replacer.innerWidth()),

            selectTop = this._replacer.offset().top,
            scrollTop = $(window).scrollTop(),
            selectScrollTop = this._list.scrollTop(), // keep scroll position after moving list in DOM
            lineHeight = parseInt(fontSize) * 2,
            rowsNumber = this._list.find('.b-select-list-item').size(),
            listHeight = 0;
        rowsNumber = rowsNumber > 12 ? 12 : rowsNumber;
        listHeight = lineHeight * rowsNumber;
        // if no space below select
        if (selectTop + this._replacer.height() + listHeight > scrollTop + $(window).height()) {
            // and enough space above select
            if (selectTop - listHeight > scrollTop) {
                // then the list should turn up
                this._replacer.addClass('b-select-upside-down');
                this._list.addClass('b-select-upside-down');
            }
        }
        var left = this._list.offset().left,
            top = this._list.offset().top;
        this._list.appendTo('body').css({
            bottom: 'auto',
            left: left + 'px',
            top: top + 'px',
            minWidth: minWidth + 'px',
            fontSize: fontSize
        }).find('.b-select-option-active').removeClass('b-select-option-active');
        if (typeof this._select.val() === 'string') {
            this._list.find('.b-select-option[data-value="' + this._select.val().addSlashes() + '"]').addClass('b-select-option-active');
        }
        // timeout is for css3 animation
        setTimeout(function () {
            self._list.addClass('b-select-open-list').scrollTop(selectScrollTop);
        }, 0);
        this._updateScroll();
    },
    _updatePosition: function () {
        var selectOffset = this._replacer.offset(),
            left = selectOffset.left,
            top = selectOffset.top;
        if (this._replacer.hasClass('b-select-upside-down')) {
            top -= this._list.outerHeight() - 1; //1 is for border
        } else {
            top += this._replacer.outerHeight() - 1; // 1 is for border
        }
        this._list.css({
            left: left + 'px',
            top: top + 'px'
        });
    },
    close: function () {
        $('.b-select-open-list').each(function () {
            var $this = $(this),
                ID = $this.attr('data-custom-select-id'),
                replacer = $('#' + ID),
                scrollTop = $this.scrollTop(); // keep scroll position after moving list in DOM
            replacer.removeClass('b-select-open b-select-upside-down');
            CustomSelect(ID)._open = false;
            $this.css({
                bottom: '',
                left: '',
                top: '',
                minWidth: ''
            }).removeClass('b-select-open-list b-select-upside-down').appendTo(replacer).scrollTop(scrollTop);
        });
    },
    toggle: function () {
        if (this._open) this.close();
        else this.open();
    },
    updateValue: function (isChangedUp) {
        if (!this._multiple) {
            var selected = this._select.find(':selected');
            if (this._editable) {
                if (selected.size()) {
                    this._editableInput.val(selected.text());
                }
            } else {
                var img = selected.attr('data-image'),
                val = (img ? '<img src="' + img + '" alt="" class="b-select-item-image">' : '') + selected.text();
                this._value.html(val);
            }
            this._list.find('.b-select-option-active').removeClass('b-select-option-active');
            if (this._select.val() != null)
                this._list.find('.b-select-option[data-value="' + this._select.val().addSlashes() + '"]').addClass('b-select-option-active');
        } else {
            var self = this;
            if (this._editable) {
                var tags = this._value.find('.b-select-value-tag');
                // remove tag if value is not selected anymore
                tags.each(function () {
                    var $this = $(this),
                        val = $this.attr('data-value');
                    if (!self._select.find('option[value=' + val.addSlashes() + ']:selected').size()) {
                        $this.remove();
                    }
                });
                // add new tags
                this._select.find('option').each(function () {
                    if (!this.selected) return;
                    if (tags.filter('[data-value=' + this.value.addSlashes() + ']').size()) return;
                    var $this = $(this),
                        img = $this.attr('data-image'),
                        val = (img ? '<img src="' + img + '" alt="" class="b-select-item-image">' : '') + $this.text();
                    self._editableInput.before(self._getTagHTML(this.value, val));
                });
                // adjust input width
                var lastTag = this._value.find('.b-select-value-tag:last');
                this._editableInput.width('5px');
                if (!lastTag[0] || lastTag[0].offsetTop < this._editableInput[0].offsetTop) {
                    this._editableInput.width('');
                } else {
                    var valueWidth = this._value.innerWidth(),
                        lastTagWidth = Math.ceil(lastTag.outerWidth(true)),
                        inputWidth = valueWidth - lastTagWidth - lastTag.offset().left + this._value.offset().left - Math.ceil(parseFloat(this._value.css('paddingLeft')));
                    this._editableInput.width(inputWidth + 'px');
                }
            } else {
                this._list.find('.b-select-option-active').removeClass('b-select-option-active');
                this._select.find('option').each(function () {
                    if (!this.selected) return;
                    var $this = $(this),
                        img = $this.attr('data-image'),
                        val = (img ? '<img src="' + img + '" alt="" class="b-select-item-image">' : '') + $this.text();
                    self._list.find('.b-select-option[data-value="' + $this.val().addSlashes() + '"]').addClass('b-select-option-active');
                });
            }
        }
        this._updateScroll(isChangedUp);
    },
    setValue: function (val, ctrlKey, shiftKey) {
        var changed = false;
        if (this._multiple_maxItems >= 0 && this._select.find(':selected').size() >= this._multiple_maxItems) {
            return;
        }
        if (!this._multiple || this._editable) {
            this.close();
            if (this._editable) {
                var options = this._select.find('option');
                if (val === false) { // then user wishes to leave a field empty; remove all `selected` attributes
                    options.each(function () {
                        if (this.selected) {
                            changed = true;
                            this.selected = false;
                        }
                    });
                } else {
                    var target = options.filter('[value=' + val.addSlashes() + ']');
                    if (!target[0].selected) {
                        changed = true;
                        target[0].selected = true;
                    }
                }
                if (this._multiple) this._editableInput.val('');
            } else {
                if (this._select.val() != val) {
                    changed = true;
                    this._select.val(val);
                }
            }
        } else {
            var options = this._select.find('option'),
                target = options.filter('[value=' + val.addSlashes() + ']'),
                lastVal = this._select.val();
            if (ctrlKey) {
                target[0].selected = !target[0].selected;
                this._multiple_lastItem = target;
            } else if (shiftKey) {
                var lastIndex = options.index(this._multiple_lastItem),
                    index = options.index(target);
                this._select.find('option').each(function (i) {
                    if (lastIndex <= index) {
                        this.selected = i >= lastIndex && i <= index ? true : false;
                    } else {
                        this.selected = i <= lastIndex && i >= index ? true : false;
                    }
                });
            } else {
                this._select.find('option').each(function () {
                    this.selected = false;
                });
                target[0].selected = true;
                this._multiple_lastItem = target;
            }
            var newVal = this._select.val();
            if (lastVal && newVal) {
                if (lastVal.length != newVal.length) changed = true;
                else for (var i = lastVal.length; i--;) if (lastVal[i] != newVal[i]) { changed = true; break; }
            } else if (lastVal != newVal) changed = true;
        }
        this._editable || this._select.focus();
        changed && this._select.trigger('change');
    },
    removeVal: function (val) {
        if (!this._multiple) return false;
        var options = this._select.find('option'),
            target = options.filter('[value=' + val.addSlashes() + ']');
        if (target[0].selected) {
            target[0].selected = false;
            if (this._open) this._updatePosition(); // adjust dropdown list position if neccessary
            this._select.trigger('change');
        }
    },
    filterOptions: function (query) {
        query = query.split(' ');
        var options = this._list.find('.b-select-option'),
            defOptions = this._select.find('option'),
            reg = [], text, $this, match, val;
        for (var i = query.length; i--;) reg.push(new RegExp(query[i], 'i'));
        options.each(function () {
            $this = $(this);
            text = $this.text();
            val = $this.attr('data-value');
            match = !defOptions.filter('[value=' + val.addSlashes() + ']')[0].selected;
            if (match) {
                for (var i = reg.length; i--;) match = match && reg[i].test(text);
            }
            if (match) $this.show();
            else $this.hide().removeClass('b-select-option-active');
        });
        if (!options.filter(':visible').size()) {
            if (!this._editableEmptyMsg) {
                this._editableEmptyMsg = $('<li></li>', {
                    id: this._ID + '-emptyMsg',
                    text: 'No mathces found'
                }).addClass('b-select-list-item b-select-optgroup');
                this._list.append(this._editableEmptyMsg);
            }
        } else {
            if (this._editableEmptyMsg) {
                this._editableEmptyMsg.remove();
                this._editableEmptyMsg = null;
            }
            if (!options.filter('.b-select-option-active:visible').size()) {
                this._moveSelection();
            }
        }
        if ($.browser.msie) {
            // prevent items disappear, even in ie10. =___= CSS changes just for repaint
            options.css('z-index', '10');
            setTimeout(function () { options.css('z-index', '') }, 5);
        }
        if (this._open) this._updatePosition(); // adjust dropdown list position if neccessary
    },
    setTabindex: function (i) {
        if (this._editable) {
            this._select.attr('data-default-tabindex', i);
            this._editableInput.attr('tabindex', i);
        } else {
            this._select.attr('tabindex', i);
        }
    },
    _updateScroll: function (isChangedUp) {
        var selected = isChangedUp ? this._list.find('.b-select-option-active:first') : this._list.find('.b-select-option-active:last');
        if (!selected.size()) return;
        var itemTop = selected.offset().top,
            itemBottom = itemTop + selected.height(),
            listTop = this._list.offset().top,
            listBottom = listTop + this._list.outerHeight(),
            listScroll = this._list.scrollTop();
        if (itemBottom > listBottom) {
            this._list.scrollTop(listScroll + itemBottom - listBottom);
        } else if (itemTop < listTop) {
            this._list.scrollTop(listScroll + itemTop - listTop);
        }
    },
    _moveSelection: function (isChangedUp) {
        var selected = this._list.find('.b-select-option-active:visible'),
            target;
        if (!selected.size()) {
            target = this._list.find('.b-select-option:visible:' + (isChangedUp ? 'last' : 'first'));
        } else {
            target = isChangedUp ? selected.prev() : selected.next();
            while (target.size() && !target.is(':visible')) {
                target = isChangedUp ? target.prev() : target.next();
            }
        }
        if (target.size()) {
            selected.removeClass('b-select-option-active');
            target.addClass('b-select-option-active')
            this._updateScroll();
        }
    },
    _listeners: {
        select: {
            change: function (e) {
                var ID = $(this).attr('data-custom-select-id');
                CustomSelect(ID).updateValue();
            },
            keydown: function (e) {
                if (e.which >= 33 && e.which <= 40) { // arrows, Home, End, PgUp, PgDwn
                    var ID = $(this).attr('data-custom-select-id');
                    setTimeout(function () {
                        CustomSelect(ID).updateValue(e.which == 33 || e.which == 36 || e.which == 37 || e.which == 38);
                    }, 0);
                } else if (e.which == 13) { // Enter
                    CustomSelect('close');
                }
            },
            focus: function () {
                var ID = $(this).attr('data-custom-select-id');
                $('#' + ID).addClass('b-select-focused');
            },
            blur: function () {
                var ID = $(this).attr('data-custom-select-id');
                $('#' + ID).removeClass('b-select-focused');
                CustomSelect('close');
            }
        },
        replacer: {
            mousedown: function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (e.which == 1) {
                    var ID = $(this).attr('id'),
                        s = CustomSelect(ID);
                    if (s._editable) s._editableInput.focus();
                    else s.toggle();
                }
            },
            tags: {
                click: function (e) {
                    var $this = $(this),
                        ID = $this.attr('data-custom-select-id'),
                        val = $this.attr('data-value');
                    CustomSelect(ID).removeVal(val);
                }
            },
            editableInput: {
                focus: function (e) {
                    var $this = $(this),
                        ID = $this.attr('data-custom-select-id'),
                        s = CustomSelect(ID);
                    s.filterOptions($this.val());
                    s.open();
                },
                blur: function (e) {
                    var $this = $(this),
                        ID = $this.attr('data-custom-select-id'),
                        s = CustomSelect(ID);
                    if (!$.trim($this.val()) && !s._multiple) s.setValue(false);
                    s.close();
                },
                keydown: function (e) {
                    var $this = $(this),
                        ID = $this.attr('data-custom-select-id'),
                        s = CustomSelect(ID);
                    if (e.which == 27) {
                        e.stopImmediatePropagation();
                        $this.blur();
                    }
                    else if (e.which == 38 || e.which == 40) { // arrow up || arrow down
                        e.preventDefault();
                        if (!s._open) s.open();
                        s._moveSelection(e.which == 38);
                    } else if (e.which == 13) { // Enter
                        e.preventDefault();
                        var selected = s._list.find('.b-select-option-active');
                        if (selected.size()) {
                            s.setValue(selected.attr('data-value'));
                        }
                    } else if (e.which == 8) { // backspace
                        var tags = s._value.find('.b-select-value-tag');
                        // delete last tag if backspace was pressed in empty field
                        if ($.trim($this.val()) === '' && tags.size()) s.removeVal(tags.filter(':last').attr('data-value'));
                    }
                },
                keyup: function (e) {
                    if (e.which == 13) return; // Enter
                    var $this = $(this),
                        ID = $(this).attr('data-custom-select-id'),
                        s = CustomSelect(ID);
                    if (!s._open) s.open();
                    s.filterOptions($this.val());
                }
            },
            options: {
                mouseenter: function (e) {
                    var $this = $(this),
                        ID = $this.attr('data-custom-select-id'),
                        s = CustomSelect(ID);
                    if (!s._multiple || s._editable) {
                        s._list.find('.b-select-option-active').removeClass('b-select-option-active');
                        $this.addClass('b-select-option-active');
                    } else if (s._multiple_dragStart) {
                        var val = $this.attr('data-value');
                        s.setValue(val, e.ctrlKey, true);
                    }
                },
                mousedown: function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.which == 1) {
                        var $this = $(this),
                            ID = $this.attr('data-custom-select-id'),
                            s = CustomSelect(ID);
                        if (s._multiple && !s._editable) {
                            s._multiple_dragStart = true;
                            var val = $this.attr('data-value');
                            s.setValue(val, e.ctrlKey, e.shiftKey);
                        }
                    }
                },
                mouseup: function (e) {
                    e.stopPropagation();
                    if (e.which == 1) {
                        var $this = $(this),
                            ID = $this.attr('data-custom-select-id'),
                            s = CustomSelect(ID);
                        if (s._multiple && !s._editable) s._multiple_dragStart = false;
                        else {
                            var val = $this.attr('data-value');
                            s.setValue(val, e.ctrlKey, e.shiftKey);
                        }
                    }
                }
            }
        },
        stopPropagation: function (e) { e.stopPropagation() }
    }
}
})( jQuery );