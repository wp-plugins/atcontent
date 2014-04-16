String.prototype.addSlashes = function () {
    if (!this) return '';
    return this.replace(/([.[\]()#\/+,|])/gi, '\\$1');
}

function isSupported(attr, tag) {
    if (attr === 'svg') {
        return !!document.createElementNS &&
               !!document.createElementNS(
                     'http://www.w3.org/2000/svg',
                     'svg').createSVGRect;
    } else if (attr === 'inputTypeToggle') {
        var i = document.createElement('input');
        i.type = 'password';
        try {
            i.type = 'text';
            return true;
        } catch (e) {
            return false;
        }
    } else {
        var i = document.createElement(tag || 'input');
        return !!(attr in i);
    }
}

(function () {
    'use strict';

    var cache = {}, complie,
        templateSupport = isSupported('content', 'template');

    // Simple JavaScript Templating
    // John Resig - http://ejohn.org/ - MIT Licensed
    // (edited)
    complie = function complie(str, data) {
        var fn = !/[^\w-]/.test(str)
                ? cache[str] = cache[str] ||
                  complie(document.getElementById(str).innerHTML)
                : new Function("obj",
                    "var p=[],print=function(){p.push.apply(p,arguments);};" +
                    "with(obj){p.push('" +
                    str
                        .replace(/[\r\t\n]/g, " ")
                        .split("<%").join("\t")
                        .replace(/((^|%>)[^\t]*)/g, function (s) { return s.split("'").join("\\'").split("-").join('\\-') })
                        .replace(/\t=(.*?)%>/g, "',$1,'")
                        .split("\t").join("');")
                        .split("%>").join("p.push('")
                    + "');}return p.join('');");

        return data ? fn(data) : fn;
    };

    window.tmpl = function tmpl(str, data, opt_tmplDOMManage) {
        var tmp_container, content, children, index, length;

        if (templateSupport) {
            tmp_container = document.createElement('template');
            tmp_container.innerHTML = complie(str, data || {});
            content = tmp_container.content.cloneNode(true);
        } else {
            tmp_container = document.createElement('div');
            tmp_container.innerHTML = complie(str, data || {});
            content = document.createDocumentFragment();
            children = tmp_container.childNodes;
            for (index = 0, length = children.length; index < length; index++) {
                content.appendChild(children[index].cloneNode(true));
            }
        }

        if (opt_tmplDOMManage instanceof Function) {
            opt_tmplDOMManage.apply(content);
        }

        return content;
    };

})();