jQuery.fn.navigation = function () {
    var h3 = this.find("h3");
    var ul = this.find("ul");
    h3.toggle(
        function () {ul.show(); },
        function () {ul.hide(); }  
    );
};

jQuery.fn.swf = function (xml) {
    if (!this.length) return;
    var href = this.find("a").attr("href");
    if (href) {
        var so = new SWFObject(href, "content-flash", "800", "400", "9", "#000000"); 	
        so.addParam("allowFullScreen","true");
    	so.addParam("allowScriptAccess","sameDomain");
    	so.write(this.attr("id"));
    }
};

$(document).ready(function () {
    $("#top-nav").navigation();
    $("#content-flash").swf();
});
