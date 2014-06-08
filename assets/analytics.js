var ac_ga;

(function () {
    'use strict';
    
    var postMessage, createGateway, createID, events, scroll;

    postMessage = function (target, src) {
        if (target == null) return;
        this._target = target;
        if (typeof window.postMessage === 'undefined') {
            var self = this;
            this._targetSrc = src;
            this._confirm = [];
            this._query = [];
            this._receivedAnswers = {};
            this._interval = setInterval(function () {
                self._checkHash();
            }, 100);
            this._receivers = [];
        }
    };
    postMessage.prototype = {
        _checkHash: function () {
            var hash = window.location.hash.substring(1).split('::cp_pm::');
            if (hash[1]) {
                var cp_pm = hash[1] && hash[1].split('::confirm::') || ['', ''],
                    answer = cp_pm[0].split('||'),
                    confirm = cp_pm[1].split('|'),
                    preventScrollRequired = hash[0] === '' || hash[0] === '#';
                if (preventScrollRequired) {
                    // prevent from jump to top because of changing location.hash to '#'
                    var s = scroll.get();
                }
                window.location.hash = hash[0];
                if (preventScrollRequired) {
                    scroll.set(s);
                }
                if (cp_pm[0] !== '') {
                    for (var i = 0, l = answer.length; i < l; i++) {
                        var a = answer[i].split('|');
                        this._performReceivers(a[0], a[1]);
                        this._confirm.push(a[0]);
                    }
                    this._send(); // send confirm
                }
                if (cp_pm[1] !== '') {
                    for (var i = 0, l = confirm.length; i < l; i++) {
                        for (var j = this._query.length; j--;) {
                            if (this._query[j].split('|')[0] === confirm[i]) {
                                this._query.splice(j, 1);
                            }
                        }
                    }
                }
            }
        },
        send: function (msg, origin) {
            if (!msg) return;
            if (typeof window.postMessage !== 'undefined') {
                this._target.postMessage(msg, origin || '*');
            } else {
                // ie7 and ie6
                this._query.push(createID() + '|' + msg);
                this._send();
            }
        },
        _send: function () {
            this._target.location = this._targetSrc + '#' + window.location.href + '::url::' + this._query.join('||') + '::confirm::' + this._confirm.join('|');
        },
        addReceiver: function (handler) {
            if (typeof window.postMessage !== 'undefined') {
                events.add(window, 'message', handler);
            } else {
                this._receivers.push(handler);
            }
        },
        _performReceivers: function (id, d) {
            if (this._receivedAnswers[id] === true) return;
            this._receivedAnswers[id] = true;
            for (var i = 0, l = this._receivers.length; i < l; i++) {
                this._receivers[i]({ data: d });
            }
        }
    };
    
    createGateway = function (params) {
        var f = document.createElement('iframe');
        if (params.onload instanceof Function) f.onload = params.onload;
        f.src = params.src;
        f.style.cssText = 'width:0;height:0;position:absolute;border:none;top:0;left:0';
        document.body.appendChild(f);
        return f.contentWindow;
    };

    if (typeof JSON === 'undefined') {
        window.JSON = {
            parse: function (text) {
                var isJSON = !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(text.replace(/"(\\.|[^"\\])*"/g, '')));
                if (isJSON) {
                    return eval('(' + text + ')');
                } else {
                    throw new SyntaxError('JSON.parse: unexpected keyword');
                }
            },
            stringify: function (obj) {
                var t = typeof (obj);
                if (t != "object" || obj === null) {
                    // simple data type
                    if (t == "string") obj = '"' + obj + '"';
                    return String(obj);
                }
                else {
                    // recurse array or object
                    var n, v, json = [], arr = (obj && obj.constructor == Array);
                    for (n in obj) {
                        v = obj[n]; t = typeof (v);
                        if (t == "string") v = '"' + v + '"';
                        else if (t == "object" && v !== null) v = JSON.stringify(v);
                        json.push((arr ? "" : '"' + n + '":') + String(v));
                    }
                    return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
                }
            }
        };
    }
    
    createID = (function () {
        var src = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-',
            srcLength = src.length,
            _createdIDs = {};
        return function () {
            var id = '';
            do {
                for (var i = 11; i--;) id += src.substr(Math.floor(Math.random() * srcLength), 1);
            } while (typeof _createdIDs[id] !== 'undefined');
            _createdIDs[id] = true;
            return id;
        };
    })();
    
    events = {
        add: function (target, eventType, callback) {
            if (target.addEventListener) {
                target.addEventListener(eventType, callback, false); 
            } else if (target.attachEvent) {
                target.attachEvent('on' + eventType, function (e) {
                    e = e || window.event;
                    callback(e);
                });
            }
        }
    };
    
    scroll = {
        target: window,
        get: function () {
            var x, y;
            if (this.target === window) {
                if ('pageXOffset' in window) {
                    x = this.target.pageXOffset;
                    y = this.target.pageYOffset;
                } else {
                    var d = this.target.document.documentElement,
                        b = this.target.document.body;
                    x = d.clientHeight ? d.scrollLeft : b.scrollLeft;
                    y = d.clientHeight ? d.scrollTop : b.scrollTop;
                }
            } else {
                x = this.target.scrollLeft;
                y = this.target.scrollTop;
            }
            return { x: x, y: y };
        },
        set: function (x, y) {
            if (typeof x === 'object') {
                y = x.y;
                x = x.x;
            }
            if (isNaN(parseInt(x))) x = this.get().x;
            if (isNaN(parseInt(y))) y = this.get().y;
            if (this.target === window) this.target.scrollTo(x, y);
            else {
                this.target.scrollLeft = x;
                this.target.scrollTop = y;
            }
            return this;
        }
    };
    
    var requests;
    events.add(window, 'load', function () {
        requests = ac_ga || [];
        ac_ga = (function () {
            var iframeLoaded = false,
                beforeLoadQueryStack = [],
                origin = 'https://atcontent.com',
                src = 'https://atcontent.com/ajax/wordpress/gateway.cshtml',
                postMsg = new postMessage(createGateway({
                    src: src,
                    onload: function () {
                        iframeLoaded = true;
                        for (var i in beforeLoadQueryStack) postMsg.send(beforeLoadQueryStack[i], origin);
                        beforeLoadQueryStack = [];
                    }
                }), src);
        
            return {
                push: function (params) {
                    try {
                        var _str_params = JSON.stringify(params);
                    } catch (e) {
                        return;
                    }
                    if (iframeLoaded) postMsg.send(_str_params, origin);
                    else beforeLoadQueryStack.push(_str_params);
                }
            };
        })();
        for (var i in requests) {
            ac_ga.push(requests[i]);
        }
    });
})();
