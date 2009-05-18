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

jQuery.fn.changeSpace = function () {
    var currentContainer = "#content-flash";
    
    this.click(function () {
        if ($("#content-body").css("display") == "block")
            $("a#content-body-toggle").click();
        
        var space = $(this).attr("href");
        var container = "#content-flash";
        if (space == "#content-images") {
            container = space;
        }
        
        if (container != currentContainer) {
            $(currentContainer).hide();//css({position: "absolute"}).animate({opacity: 0, display: "none"}, 1000);//, null, function () { $(this).hide();});
            $(container).show();//.css({position: "static", opacity: 0}).animate({opacity: 1, display: "block"}, 1000);            
            currentContainer = container;
            if (space == "#content-images") {
                var loaded = false;
                $("#content-images ul").galleria({
                    clickNext : true,
                    history : false,
                    insert : "#the-image",
                    onImage   : function(image,caption,thumb) {
                		if(! ($.browser.mozilla && navigator.appVersion.indexOf("Win")!=-1) ) {
                			image.css("display","none").fadeIn(1000);
                		}
                		caption.css("display","none").fadeIn(1000);
            			var _li = thumb.parents("li");
            			_li.siblings().children("img.selected").fadeTo(500,0.3);
            			thumb.fadeTo("fast", 1).addClass("selected");
            			image.attr("title","Seuraava kuva >>");
            		},
            		onThumb : function(thumb) {
            			var _li = thumb.parents("li");
            			var _fadeTo = _li.is(".active") ? "1" : "0.3";
            			thumb.css({display:"none",opacity:_fadeTo}).fadeIn(1500);
            			thumb.hover(
            				function() { thumb.fadeTo("fast",1); },
            				function() { _li.not(".active").children("img").fadeTo("fast",0.3); }
            			)
            			if (!loaded)
                            $("#content-images ul img:first").click();
                        loaded = true;
            		}
                });
            }
        }
        
        if (space != container) {
            var swf = window["flash-el"] || document["flash-el"];
            space = space.split("#")[1];
            try {
                swf.loadSpace(space);
            } catch (e) {
                window.setTimeout(function () {
                    swf.loadSpace(space);
                }, 1000)
            }
        }
        if (space != "3d")
            $("input[name='3d']").attr("checked", "");
        $("#sidebar li.current").removeClass("current");
        $(this).parents("li").addClass("current");
        return false;
    });
};

jQuery.fn.toggleFromSpace = function () {
    this.click(function () {
        var el = $(this);
        var value = el.val();
        var checked = el.attr("checked");
        
        if (value == "content-body") {
            var target = $("#"+ value);
            if (target.css("display") == "block")
                $("#"+ value).hide();
            else
                $("#"+ value).show();
        }
        else {
            if (!el.parents("li").hasClass("current"))
                el.parents("li").find("a").click();
            
            var name = el.attr("name");
            var space = name;
            $("input[name='" + name + "']").each(function () {
                if ($(this).attr("checked"))
                    space = (space != name) ? space.replace("-1", "-3") : $(this).val();
            });
            var swf = window["flash-el"] || document["flash-el"];
            swf.loadSpace(space);
        }
    });
};

jQuery.fn.swf = function (id) {
    if (!this.length) return;
    var href = this.attr("href");
    if (href) {
        var swf = new SWFObject(href, "flash-el", "100%", "600", "9", "#ffffff"); 	
        //swf.setProxy(null, "flash/swfobject_js_gateway.swf")
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
    //$("#sidebar").sidebar();
    $("#sidebar a").changeSpace();
    $("#sidebar input").toggleFromSpace();
    $("#content").click(function () {
        var el = $("input#content-body-toggle");
        if (el.attr("checked")) el.click();
    });
    $("#content-body-toggle").attr("checked", "checked");
    $("input[name='3d']").attr("checked", "");
});
