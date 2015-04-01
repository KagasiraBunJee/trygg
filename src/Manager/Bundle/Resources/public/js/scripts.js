jQuery("document").ready(function(){

    jQuery("#searchAjax").keyup(function(){
        var params = [];
        var searchInput = jQuery(this);
        var rejected = searchInput.attr("rejected") ? "rejected=1" : "";
        var step = searchInput.attr("step") ? "stepId="+searchInput.attr("step") : "";
        params.push(rejected);
        params.push(step);
        if(searchInput.val() != "")
        {
            params.push("smartSearch="+searchInput.val());
            params = params.filter(function(n){ return n != "" });
            //console.log(params.join("&"));
            var dropdownmenu = jQuery(".smartSearchResult");
            jQuery.ajax({
                url:"http://localhost:8080/trygg/web/app_dev.php/company/search?"+params.join("&")
            }).done(function(result) {
                if (result.result != "nothing")
                {
                    if(result.result.length > 0)
                    {
                        var resultData = result.result.reduce(function (a, b) {
                            return "<li><a href='"+a.url+"'>"+a.name+"</a></li>" + "<li><a href='"+b.url+"'>"+b.name+"</a></li>";
                        });
                        if(typeof resultData === 'object')
                        {
                            resultData = "<li><a href='"+resultData.url+"'>"+resultData.name+"</a></li>";
                        }
                        dropdownmenu.html(resultData);
                        dropdownmenu.addClass("showSmartSearch");
                        return false;
                    }
                    else
                    {
                        if(dropdownmenu.hasClass("showSmartSearch"))
                        {
                            dropdownmenu.removeClass("showSmartSearch");
                        }
                    }
                }
            });
        }

    });

});