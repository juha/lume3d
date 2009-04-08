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

jQuery.fn.sidebar = function () {
    var start = parseInt(this.css("right"));
    this.toggle(
        function () {
            $(this).animate({right: -160}, 250);
        },
        function () {
            $(this).animate({right: start}, 250);
        }
    );
};

jQuery.fn.changeFlash = function (id) {
    this.click(function () {
        if ($("#content-body").css("display") == "block")
            $("a#content-body-toggle").click();
        $(this).swf(id);
        return false;
    });
};


jQuery.fn.swf = function (id) {
    if (!this.length) return;
    var href = this.attr("href");
    if (href) {
        var so = new SWFObject(href, "flash-el", "100%", "600", "9", "#ffffff"); 	
        so.addParam("allowFullScreen","true");
    	so.addParam("allowScriptAccess","sameDomain");
    	so.write(id);
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
    $("#content-flash a").swf("content-flash");
    $("#sidebar").sidebar();
    $("#sidebar a.flash").changeFlash("content-flash");
    $("#sidebar a.toggle").toggleTarget();
    $("#content-body").click(function () {
        $("a#content-body-toggle").click();
    });
});
