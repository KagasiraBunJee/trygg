var openedCompany = 0;
//prod
// var host = window.location.host;
//test
var host = window.location.host+"/app_dev.php"
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
                url:"http://"+host+"/company/search?"+params.join("&")
            }).done(function(result) {
                if (result.result != "nothing")
                {
                    if(result.result.length > 0)
                    {
                        var resultData = "";
                        for(var i = 0; i < result.result.length; i++)
                        {
                            var object = result.result[i];
                            resultData += "<li><a href='"+object.url+"'>"+object.name+"</a></li>";
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

    //custom selects
    $('.selectpicker-1').selectric({
        expandToItemText: true
    });
    $('.selectpicker-2').selectric({
        expandToItemText: true
    });
    $('.selectpicker-3').selectric({
        expandToItemText: true
    });

    $(window).scroll(function() {
        if($(window).scrollTop() + $(window).height() == $(document).height()) {
            //alert("bottom!");
            loadCompanies(1);
        }
    });
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
                url: "http://"+host+"/ajax/company/" + id + params
            }).done(function (result) {
                jQuery(".company-card").addClass("empty");
                jQuery(".company-card td").html("");
                if (result != "nothing") {
                    openedCompany = id;
                    jQuery(obj).next().removeClass("empty");
                    jQuery(obj).next().children("td").html(result);
                }
            });
        }
        else{
            jQuery(".company-card").addClass("empty");
            jQuery(".company-card td").html("");
            openedCompany = 0;
        }
    }
}

function setStep(id,company_id,button)
{
    if(id)
    {
        jQuery.ajax({
            url: "http://"+host+"/ajax/step/"+id+"/"+company_id
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

function loadCompanies(page)
{
    var table = document.getElementById("companies");
    var tableItems = jQuery(table).find(".companyItem").length;
    var url = "http://"+host+"/ajax/companies/"+tableItems;
    jQuery.ajax({
        url: url
    }).done(function(result){
        jQuery(table).children("tbody").append(result.render);
        jQuery(".table > tbody > tr").click(function(){
            var main_page = jQuery(this).attr('main_page');
            var company_id = jQuery(this).attr('data-id');
            showCompany(company_id,main_page,this);
        });
    });
}

function postForm( $form, callback ){

    /*
     * Get all form values
     */
    var values = {};
    $.each( $form.serializeArray(), function(i, field) {
        values[field.name] = field.value;
    });

    var formData = new FormData($form[0]);
    /*
     * Throw the form values to the server!
     */
    $.ajax({
        type        : $form.attr( 'method' ),
        url         : 'http://'+window.location.host+$form.attr( 'action' ),
        data        : formData,
        processData: false,
        contentType: false,
        success     : function(data) {
            callback( data );
        }
    });
}