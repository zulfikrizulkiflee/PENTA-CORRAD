var Mkup = {};
Mkup.mkup = function (b) {
    function a(d) {
        var h = document.createDocumentFragment(),
            e = h,
            c = {}, f = false,
            g = this,
            k = ["a", "abbr", "acronym", "address", "area", "b", "base", "basefont", "bdo", "big", "blockquote", "br", "button", "caption", "center", "cite", "code", "col", "colgroup", "dd", "del", "dfn", "dir", "div", "dl", "dt", "em", "fieldset", "font", "form", "h1", "h2", "h3", "h4", "h5", "h6", "head", "hr", "html", "i", "iframe", "img", "input", "ins", "isindex", "kbd", "label", "legend", "li", "link", "map", "menu", "meta", "object", "ol", "optgroup", "option", "p", "param", "pre", "q", "s", "samp", "script", "select", "small", "span", "strike", "strong", "style", "sub", "sup", "table", "tbody", "td", "textarea", "tfoot", "th", "thead", "title", "tr", "tt", "u", "ul"];
        k.each(function (l) {
            g[l] = function () {
                return g.element.apply(g, [l].concat($A(arguments)))
            };
            g["child" + l.capitalize()] = function () {
                return g.child.apply(g, [l].concat($A(arguments)))
            }
        });
        this.appendTo = d;

        function i(l, m, n) {
            var o = (l.tagName) ? $(l) : new Element(l);
            if (m) {
                if (typeof m === "string") {
                    o.addClassName(m)
                } else {
                    o.writeAttribute(m)
                }
            }
            if (n) {
                if (c[n]) {
                    throw new Error("Element name already in use. tag=" + l + ", name=" + n)
                }
                c[n] = o
            }
            return o
        }

        function j(m, l, o) {
            var n = g.createdElements(l);
            m.appendChild(o ? h.cloneNode(true) : h);
            return n
        }
        this.createdElements = function (m) {
            var n = c,
                l;
            if (m) {
                if (typeof m === "string") {
                    l = c[m];
                    if (!l) {
                        throw new Error("agimatec.util.mkup()#createdElements - unknown element name=" + m)
                    }
                    return c[m]
                } else {
                    Object.extend(m, c)
                }
            }
            return n
        };
        this.child = function (l, m, n) {
            if (arguments.length === 0) {
                f = true;
                return this
            }
            var o = i(l, m, n);
            e.appendChild(o);
            e = o;
            return this
        };
        this.element = function (l, m, n) {
            if (f || e === h) {
                f = false;
                return this.child(l, m, n)
            }
            var o = i(l, m, n);
            e.parentNode.appendChild(o);
            e = o;
            return this
        };
        this.up = function (l, m, n) {
            var o = e.parentNode;
            if (!o) {
                throw new Error("requested up() is useless - already root node")
            }
            e = o;
            if (l) {
                return this.element(l, m, n)
            }
            return this
        };
        this.upTo = function (m) {
            var l = c[m];
            if (!l) {
                throw new Error("reference '" + m + "' for upTo () not found!")
            }
            e = l;
            return this
        };
        this.text = function (l) {
            e.appendChild(document.createTextNode(l));
            return this
        };
        this.markup = function (l) {
            e.innerHTML = l;
            return this
        };
        this.root = function () {
            return h
        };
        this.reset = function () {
            h = document.createDocumentFragment();
            e = h;
            c = {};
            f = false;
            return this
        };
        this.write = function (l) {
            var m;
            m = j(this.appendTo, l, false);
            this.reset();
            return m
        };
        this.writeCopy = function (m, l) {
            return j(m, l, true)
        }
    }
    return new a(b)
};
