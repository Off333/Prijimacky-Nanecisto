var savedText;
function myAjax(e, specailParam = {}){
    let data = {type: $(e)[0].name};

    console.log(data.type);

    if($(e).attr("data-loading") == "true" && ($(e).attr("data-retry") == 0 || $(e).attr("data-retry") == undefined)) {
        savedText = $(e).html();
        $("#loading svg").width($(e).width());
        $("#loading svg").height($(e).height());
        $(e).html($("#loading").html());
    }

    $('#searchInput').val() != "" ? data['search'] = $('#searchInput').val() : null;

    hideAll = [];
    for (let index = 0; index < $('.hide').length; index++) {
        hideAll.push($('.hide')[index].checked);
    }
    data['hide'] = hideAll;
    
    if(data['type'] == "noRestriction") {
        $("#check1").prop('checked', false);
        $("#check3").prop('checked', false);
        $("#check2").prop('checked', false);
        $("#check4").prop('checked', false);
        $('#select').val("");
    }

    $("#check1").prop('checked') ? data['logedOut'] = "Ano" : null ;
    $("#check3").prop('checked') ? data['logedOut'] = "Ne" : null ;
    $("#check4").prop('checked') ? data['paid'] = "Ano" : null ;
    $("#check2").prop('checked') ? data['paid'] = "Ne" : null ;

    $('#select').val() != "" ? data['monthRes'] = $('#select').val() : null;

    data['sendEmail'] = $("#sendEmail").prop('checked');

    if(data['type'] == "tableHeader" || $(".order[data-selected=true]").length > 0) {
        let header = undefined;
        data['type'] == "tableHeader" ? header = $(e)[0] : header = $(".order[data-selected=true]")[0];
        data['order'] = $(header).data('order');
        if(data['type'] == "tableHeader") {
            if($(header).data('selected') == true) {
                $(header).data('sort') == "ASC" ? data['sort'] = "DESC" : data['sort'] = "ASC";
            } else {
                data['sort'] = "ASC";
            }
        } else {
            data['sort'] = $(header).data('sort');
        }
    }

    $("#colsCount").val() != undefined ? data['rowsPerPage'] = $("#colsCount").val() : data['rowsPerPage'] = "25";

    if(data['type'] == "changePage") {
        data['page'] = $(e).attr("data-page");
    } else {
        $("a[data-selectedPage=true]").text() != "" ? data['page'] = $("a[data-selectedPage=true]").text() : data['page'] = 1;
    }

    if(specailParam.order != undefined) {
        data['order'] = specailParam.order;
        data['sort'] = specailParam.sort;
    }

    if (specailParam.rowsPerPage != undefined) {
        data['rowsPerPage'] = specailParam.rowsPerPage;
    }

    if (specailParam.page != undefined) {
        data['page'] = specailParam.page;
    }
  
    if($(".rowId[data-changed=true]").length > 0 && data['type'] == "changeRows") {
        let changedRows = [];
        $.each($(".rowId[data-changed=true]"), function(key, value){
            let row = $(value).val() 
            let changedRow = {"Id": row};
            $.each($(".item-input[data-row="+row+"]"), function(key, value){
                changedRow[value.name] = $(value).val();
            });
            changedRows.push(changedRow);
        });
        data['changedRows'] = changedRows;
    }

    if(data['type'] === "changeRows") {
        var retry = $(e).attr("data-retry");
    } else {
        var retry = 0;
    }

    if(data.type == "export") {
        if(confirm("Exportovat jako excel? Jinak export jako csv.")) {
            data.exportType = "xls";
        } else {
            data.exportType = "csv"
        }
    }

    $.ajax({
        url: 'vypisData.php',
        data: data,
        dataType: 'json',
        type: 'POST',
        ifModified: true,
        timeout: 500,
        success: function(result){
            /* BEFORE */      
            $('#pagNav').html(result.pagNav);
            $('#sheetBody').html(result.sheetBody);
            $('#sheetHead').html(result.sheetHeader);
            $('#sheetFoot').html(result.sheetHeader);            

            /* AFTER */

            $('.changeBtn.order').click(function(){
                myAjax(this);
            });
            $('.changeBtn.pageNav').click(function(){
                myAjax(this);
            });
            $('.changeSelect.pageNav').change(function(){
                myAjax(this);
            });
            $('.data-item').dblclick(function(){
                changeElement(this);
            })

            if($(e).attr("data-loading") == "true") {
                $(e).html(savedText);
            }
            $("#changeRows").attr("data-retry", 0);

            if(result.download != undefined) {
                var link = document.createElement('a');
                const date = new Date();
                link.download = "vypis_prijimacky_nanecisto_"+date.getDate()+"_"+(date.getMonth()+1)+"_"+date.getFullYear()+"."+result.exportType;
                link.type = "text/csv;"
                link.charset = "charset=UTF-8";
                link.href = result.download;
                link.click();
            }
        },
        complete: function() {
        },
        error: function() {
            if(parseInt(retry) < 6) {
                console.log("retrying Ajax..."+retry);
                $("#changeRows").attr("data-retry", parseInt(retry)+1);
                setTimeout(() => {                    
                    $("#changeRows").click();
                }, 600);
            } else {
                console.log("retry fail!");
                $("#changeRows").attr("data-retry", 0);
                if($(e).attr("data-loading") == "true") {
                    console.log(savedText);
                    $(e).html(savedText);
                }
            }
        }
    });
    
}

$('.change').keyup(function(){
    myAjax(this);
});

$('.changeSelect').change(function(){
    myAjax(this);
});

$('.changeBtn').click(function(){
    myAjax(this);
});

$('#select').on("click", function(event){
    event.stopPropagation();
});

$("#changeRows").click(function(){
    if($('#changeRows').attr("data-retry") == 0) {
        let rows = $(".rowId[data-changed=true]").length;
        if( rows == 1 ) {
            if(confirm('Opravdu chcete změnit '+rows+' řádek?')) {myAjax($('#changeRows'));}
        } else if( rows < 5 && rows > 1) {
            if(confirm('Opravdu chcete změnit '+rows+' řádky?')) {myAjax($('#changeRows'));}
        } else if( rows >= 5) {
            if(confirm('Opravdu chcete změnit '+rows+' řádků?')) {myAjax($('#changeRows'));}
        }
    } else {
        myAjax($('#changeRows'));
    }
})

$(".loadBtn").click(function(){
    let data = {type: $(this)[0].name};
    var hide = ["true", "false", "false", "true", "true", "false", "true", "false", "false", "true", "true", "false", "true", "true"];
    var logedOutTrue = false;
    var logedOutFalse = false;
    var PaidTrue = false;
    var PaidFalse = false;
    var monthRes = "";
    if(data['type'] == "loadParam") {
        $.ajax({
            url: 'vypisData.php',
            data: data,
            dataType: 'json',
            type: 'POST',
            success: function(result){
                hide = result.hide;
                for (let index = 0; index < $(".hide").length; index++) {
                    if(hide[index] == "true" || hide[index] == true) {
                        $($(".hide")[index]).prop("checked", true);
                    } else {
                        $($(".hide")[index]).prop("checked", false);
                    }
                }
                if(result.logedOut) {
                    result.logedOut == "true" ? logedOutTrue = true : null;
                    result.logedOut == "false" ? logedOutFalse = true : null;
                }
                if(result.paid) {
                    result.logedOut == "true" ? PaidTrue = true : null;
                    result.logedOut == "false" ? PaidFalse = true : null;
                }
                monthRes = result.monthRes;
                $("#check1").prop('checked', logedOutTrue);
                $("#check3").prop('checked', logedOutFalse);
                $("#check2").prop('checked', PaidTrue);
                $("#check4").prop('checked', PaidFalse);
                $('#select').val(monthRes);

                $(".order[data-selected=true]").attr("data-selected", false);
                let specailParam = {};
                if(result.order != undefined){
                    specailParam['order'] = result.order;
                    specailParam['sort'] = result.sort;
                }

                $("a[data-selectedPage=true]").attr("data-selectedPage", false);
                
                result.rowsPerPage != undefined ? specailParam['rowsPerPage'] = result.rowsPerPage : null;
                result.page != undefined ? specailParam['page'] = result.page : null;

                myAjax($(".changeBtn[name=resetRows]"), specailParam);
            },
            error: function(){
                console.log("error while loadng params!");
                $(".loadBtn[name=resetParam]").click();
            }
        });
    } else {
        for (let index = 0; index < $(".hide").length; index++) {
            if(hide[index] == "true" || hide[index] == true) {
                $($(".hide")[index]).prop("checked", true);
            } else {
                $($(".hide")[index]).prop("checked", false);
            }
        }
        $("#check1").prop('checked', logedOutTrue);
        $("#check3").prop('checked', logedOutFalse);
        $("#check2").prop('checked', PaidTrue);
        $("#check4").prop('checked', PaidFalse);
        $('#select').val(monthRes);
        $(".order[data-selected=true]").attr("data-selected", false);
        $(".changeBtn[name=resetRows]").click();
        $("a[data-selectedPage=true]").attr("data-selectedPage", false);
        $("#colsCount").val("25")
    }
});

$(function(){
    $(".loadBtn[name=loadParam]").click();
});

/* This is not an ajax */

function changeElement(el) {
    let row = $(el).children().attr("data-row");
    $(".rowId[value="+row+"]").attr("data-changed", true)
    $(el).children().removeClass("d-none");
    $(el).children("label").hide();
}

$("#printBtn").click(function(){
    var divToPrint = document.getElementById('print');
    var htmlToPrint = '' +
        '<style type="text/css">' +
        'table {' +
        'margin: auto;' +
        'border: solid #000;' +
        'border-width: 1px 0 0 1px;' +
        'border-collapse: collapse;' +
        '}' +
        'table th, table td {' +
        'border:1px solid #000;' +
        'padding;0.5em;' +
        '}' +
        '</style>';
    htmlToPrint += divToPrint.outerHTML;
    newWin = window.open("");
    newWin.document.write(htmlToPrint);
    var elements = newWin.document.querySelectorAll('[data-printRemove]');
    var i = 0;
    while(elements.length > i) {
        elements[i].parentElement.removeChild(elements[i]);
        i++;
    }
    var tags = newWin.document.getElementsByTagName('button');
    tags.append = newWin.document.querySelectorAll('input[style*=\'display:none\']');
    var src;
    var el;
    while(tags.length > 0) {
        src = tags[0];
        el = newWin.document.createElement('i');
        el.innerHTML = src.innerHTML;
        src.replaceWith(el);
    }
    newWin.print();
    newWin.close();
});