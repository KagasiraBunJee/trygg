var openedCompany = 0;

jQuery("document").ready(function(){

    jQuery("#searchAjax").keydown(function(){
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
                url:"http://104.236.61.177/web/app.php/company/search?"+params.join("&")
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

    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!
    var yyyy = today.getFullYear();
    if(dd<10) {
        dd='0'+dd
    }

    if(mm<10) {
        mm='0'+mm
    }
    jQuery(".table tr a").click(function(e){
        e.stopPropagation();
    });
    jQuery(".table > tbody > tr").click(function(){
        var main_page = jQuery(this).attr('main_page');
        var company_id = jQuery(this).attr('data-id');
        showCompany(company_id,main_page,this);
    });
    $('.panel-body .form-group.date').datepicker({
        format: "yyyy-mm-dd",
        orientation: "bottom auto",
        todayHighlight: true
        //defaultViewDate: { year: yyyy, month: mm, day: dd }
    });
    var date = jQuery("#saleDate");
    if(date.val() == "")
    {
        date.val(yyyy+"-"+mm+"-"+dd);
    }
});

function showCompany(id,main,obj)
{
    if(id)
    {
        if(openedCompany != id) {
            var params = "";
            if (main) {
                params = "?main_page=1";
            }
            jQuery.ajax({
                url: "http://104.236.61.177/web/app.php/ajax/company/" + id + params
            }).done(function (result) {
                jQuery(".company-card").addClass("empty");
                jQuery(".company-card td").html("");
                if (result != "nothing") {
                    jQuery(obj).next().removeClass("empty");
                    jQuery(obj).next().children("td").html(result);
                }
            });
        }
        else{
            jQuery(".company-card").addClass("empty");
            jQuery(".company-card td").html("");
        }
    }
}

function setStep(id,company_id,button)
{
    if(id)
    {
        jQuery.ajax({
            url: "http://104.236.61.177/web/app.php/ajax/step/"+id+"/"+company_id
        }).done(function(result){
            switch (result.result){
                case "added":
                    jQuery(button).addClass("btn-success");
                    break;
                case "removed":
                    jQuery(button).removeClass("btn-success");
                    break;
            }
        });
    }
}