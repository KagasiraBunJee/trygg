var openedCompany = 0;
var host = window.location.host;
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
                url:"http://"+host+"/company/search?"+params.join("&")
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

    $('.table-scrollable').scroll(function() {
        if(($('.table-scrollable').scrollTop() + $('.table-scrollable').height()) >= $('.table.custom').height()) {

            var infiniteLoad = $('.custom').attr('infinite-load');
            if (infiniteLoad)
            {
                loadCompanies(1, $('#filter'));
            }
        }
    });

    $("#filter select").change(function(){
        jQuery("#filter").submit();
    });

    //$('.selectpicker-1').ddslick();
    //$('.selectpicker-2').ddslick();
    //$('.selectpicker-3').ddslick({
    //    onSelected: function(data) {
    //        jQuery("#filter").submit();
    //    }
    //});

    $('.selectpicker-1').selectric({
        expandToItemText: true
    });
    $('.selectpicker-2').selectric({
        expandToItemText: true
    });
    $('.selectpicker-3').selectric({
        expandToItemText: true
    });
});

function showCompany(id,main,obj)
{
    if(id)
    {
        showLoaderFor(jQuery('.company_item'));
        //if(openedCompany != id) {
            var params = "";
            if (main) {
                params = "?main_page=1";
            }
            jQuery.ajax({
                url: "http://"+host+"/ajax/company/" + id + params
            }).done(function (result) {
                //jQuery(".company-card").addClass("empty");
                //jQuery(".company-card td").html("");
                if (result != "nothing") {
                    openedCompany = id;
                    jQuery('.company_item').html(result);

                    hideLoaderFor(jQuery('.company_item'));
                    //jQuery(obj).next().removeClass("empty");
                    //jQuery(obj).next().children("td").html(result);
                }
            });
        //}
        //else{
            //jQuery(".company-card").addClass("empty");
            //jQuery(".company-card td").html("");
            //openedCompany = 0;
        //}
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
                    jQuery(button).addClass("access");
                    break;
                case "removed":
                    jQuery(button).removeClass("list-group-item-info");
                    break;
            }

            showCompany(openedCompany,false,{});
        });
    }
}

function showLoaderFor(object)
{
    $(object).waitMe({
        effect : 'roundBounce',
        bg : 'rgba(255,255,255,0.7)',
        color : '#000',
        text : 'Please wait...'
    });
}

function hideLoaderFor(object)
{
    $(object).waitMe('hide');
}

function loadCompanies(page, $form)
{
    var table = document.getElementById("companies");
    var tableItems = jQuery(table).find(".companyItem").length;
    var url = "http://"+host+"/ajax/companies/"+tableItems+window.location.search;

    $.ajax({
        url: url,
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