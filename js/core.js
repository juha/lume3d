var swf;

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

jQuery.fn.changeSpace = function (id) {
    this.click(function () {
        if ($("#content-body").css("display") == "block")
            $("a#content-body-toggle").click();        
        var swf = document["flash-el"];
        var space = $(this).attr("href");
        if (space.split("#").length > 1)
            swf.loadSpace(space.split("#")[1]);
        return false;
    });
};

function alertFromFlash(txt) {
    alert(txt);
}

jQuery.fn.swf = function (id) {
    if (!this.length) return;
    var href = this.attr("href");
    if (href) {
        var swf = new SWFObject(href, "flash-el", "100%", "600", "9", "#ffffff"); 	
        //swf.setProxy(null, 'flash/swfobject_js_gateway.swf')
    	swf.addParam("allowScriptAccess","always");
        swf.addParam("allowFullScreen","true");
        swf.addParam("wmode", "opaque");
    	swf.write(id);
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
    $("#sidebar a.flash").changeSpace("content-flash");
    $("#sidebar a.toggle").toggleTarget();
    $("#content-body").click(function () {
        $("a#content-body-toggle").click();
    });
});
