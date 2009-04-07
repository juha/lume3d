jQuery.fn.navigation = function () {
    var h3 = this.find("h3");
    var ul = this.find("ul");
    h3.toggle(
        function () {
            ul.show();
            $("body").click(function () {
                ul.hide();
            })
        },
        function () {
            ul.hide();
        }
    );
};

jQuery.fn.swf = function (xml) {
    if (!this.length) return;
    var href = this.find("a").attr("href");
    if (href) {
        var so = new SWFObject(href, "flash-el", "100%", "600", "9", "#ffffff"); 	
        so.addParam("allowFullScreen","true");
    	so.addParam("allowScriptAccess","sameDomain");
    	so.write(this.attr("id"));
    }
};

jQuery.fn.toggleTarget = function () {
    var id = this.attr("href");
    var el = $(id);
    if (el.length) {
        this.toggle(function () {
            this._innerHTML = this.innerHTML
            this.innerHTML = this.innerHTML.replace("Piilota", "Näytä");
            el.hide();
        }, function () {
            this.innerHTML = this._innerHTML;
            el.show();
        });
    }
};

$(document).ready(function () {
    $("#top-nav").navigation();
    $("#content-flash").swf();
    $("#sidebar a.toggle").toggleTarget();
    $("#content-body").click(function () {
        $(this).hide();
    });
});
