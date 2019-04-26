/**
 * @Author: Andrea F. Daniele <afdaniele>
 * @Date:   Wednesday, December 28th 2016
 * @Email:  afdaniele@ttic.edu
 * @Last modified by:   afdaniele
 * @Last modified time: Sunday, January 14th 2018
 */


window.chartColors = {
    red: 'rgb(255, 99, 132)',
    orange: 'rgb(255, 159, 64)',
    yellow: 'rgb(255, 205, 86)',
    green: 'rgb(75, 192, 192)',
    blue: 'rgb(54, 162, 235)',
    purple: 'rgb(153, 102, 255)',
    grey: 'rgb(201, 203, 207)'
};

function range (start, end, step) {
    var range = [];
    var typeofStart = typeof start;
    var typeofEnd = typeof end;
    if (step === 0) {
        throw TypeError("Step cannot be zero.");
    }
    if (typeofStart == "undefined" || typeofEnd == "undefined") {
        throw TypeError("Must pass start and end arguments.");
    } else if (typeofStart != typeofEnd) {
        throw TypeError("Start and end arguments must be of same type.");
    }
    typeof step == "undefined" && (step = 1);
    if (end < start) {
        step = -step;
    }
    if (typeofStart == "number") {
        while (step > 0 ? end >= start : end <= start) {
            range.push(start);
            start += step;
        }
    } else if (typeofStart == "string") {
        if (start.length != 1 || end.length != 1) {
            throw TypeError("Only strings with one character are supported.");
        }
        start = start.charCodeAt(0);
        end = end.charCodeAt(0);
        while (step > 0 ? end >= start : end <= start) {
            range.push(String.fromCharCode(start));
            start += step;
        }
    } else {
        throw TypeError("Only string and number types are supported");
    }
    return range;
}

// open popover after 'showDelay' ms and keep it visible for 'duration' ms
function openPop( targetID, title, content, placement, showDelay, duration, fixed, closeOthers ){
    if(typeof(fixed)==='undefined') fixed = false;
    if( closeOthers == undefined ) closeOthers = false;
    //
    if( closeOthers )
        closeAllPops();
    //
    var target = $( '#'+targetID );
    target.popover({
        animation: true,
        html: true,
        title: '<span class="text-danger">'+title+'</span>&nbsp;&nbsp;&nbsp;'+
            '<button type="button" id="close" class="close" onclick="$(&quot;#'+targetID+'&quot;).popover(&quot;hide&quot;);">&times;</button>',
        content: content,
        container: 'body',
        placement: placement,
        delay: { "show": showDelay, "hide": 100 }
    });
    target.popover('toggle');
    if( !fixed ){
        setTimeout(function(){  target.popover('destroy'); }, showDelay+duration);
    }
}

// close all popovers
function closeAllPops(){
    $(document).find('.popover').each(function() {
        $(this).popover('hide');
    });
}

// enable popover on hover event
function enablePopOnHover( targetID, title, content, placement, hideCloseButton ){
    if( hideCloseButton == undefined ) hideCloseButton = false;
    //
    var target = $( '#'+targetID );
    target.popover({
        animation: true,
        html: true,
        title: '<span class="text-danger"><strong>'+title+'</strong></span>'+
            ( (hideCloseButton)? '' : '<button type="button" id="close" class="close" onclick="$(&quot;#'+targetID+'&quot;).popover(&quot;hide&quot;);">&times;</button>' ),
        content: content,
        container: 'body',
        placement: placement
    });
    target.hover( function(){ target.popover('show'); }, function(){ target.popover('hide'); } );
}

// Utility

function toHHMMSS( sec_num ) {
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    var time    = hours+':'+minutes+':'+seconds;
    return time;
}

function toHHMM( sec_num ) {
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    var time    = hours+':'+minutes;
    return time;
}

function secondsSinceMidnight(){
    var now = new Date(), then = new Date(now.getFullYear(), now.getMonth(), now.getDate(),0,0,0), diff = (now.getTime() - then.getTime())/1000; // difference in seconds
    return diff;
}

function openAlert(type, messageHTML){
    $('#page_alert_object').attr( 'class', 'alert alert-'+type+' alert-dismissible' );
    $('#page_alert_content').html( messageHTML );
    // show
    $('#page_alert_container').css('display', '');
    // move to the page header
    $('html, body').animate({ scrollTop: 0 }, 'slow');
    // trigger the 'alert show event'
    $( document ).trigger( "show.bs.alert" );
}

function closeAlert(){
    // hide
    $('#page_alert_container').css('display', 'none');
    // trigger the 'alert hide event'
    $( document ).trigger( "hide.bs.alert" );
}


function centerModal() {
    $(this).css('display', 'block');
    var $dialog = $(this).find(".modal-dialog");
    var offset = ($(window).height() - $dialog.height()) / 2;
    // Center modal vertically in window
    $dialog.css("margin-top", offset);
}


function showPleaseWait() {
    $('#pleaseWaitModal').modal('show');
};
function hidePleaseWait() {
    $('#pleaseWaitModal').modal('hide');
};


function userLogInWithGoogle(baseurl, apiversion, token, id_token, successFcn){
    if( successFcn == undefined ) successFcn = function(){window.location.reload(true);};
    showPleaseWait();
    // compile URI
    var uri = "web-api/"+apiversion+"/userprofile/login_with_google/json?token="+token;
    // compile URL
    var url = baseurl + encodeURI( uri );
    // call the API
    $.ajax({type:'POST', url:url, dataType:'json', data:{'id_token':id_token}, success:function( result ){
        if( result.code == 200 ){
            // success, reload page
            hidePleaseWait();
            successFcn();
        }else{
            // error
            hidePleaseWait();
            openAlert( 'danger', result.message );
            // Sign-out from Google
    		    gapi.auth2.getAuthInstance().signOut();
        }
    }, error:function( jqXHR, textStatus, errorThrown ){
        hidePleaseWait();
        openAlert( 'danger', errorThrown ); //'An error occurred, please retry!' );
    }});
}

function userLogOut( baseurl, apiversion, token, successFcn ){
    if( successFcn == undefined ) successFcn = function( res ){ /* do nothing! */ };
    showPleaseWait();
    //
    var uri = "web-api/"+apiversion+"/userprofile/logout/json?token="+token;
    //
    var url = baseurl + encodeURI( uri );
    // call the API
    $.ajax({type: 'GET', url:url, dataType: 'json', success:function( result ){
        if( result.code == 200 ){
            // success, redirect
            successFcn();
        }else{
            // error
            hidePleaseWait();
            openAlert( 'danger', 'An error occurred while trying to log you out. Please, retry.' );
        }
    }, error:function(){
        hidePleaseWait();
    }});
}

function showSuccessDialog( duration, funct ) {
    $('#successDialog').modal('show');
    setTimeout( function(){
        $('#successDialog').modal('hide');
        funct();
    } , duration );
}

function printElement(elem){
    html2canvas($(elem), {
        onrendered: function(canvas) {
            popup(canvas);
        }
    });
}

function popup(data){
    var mywindow = window.open('', 'Print', 'height=400,width=600');
    mywindow.document.write('<html><head><title>Print</title>');
    mywindow.document.write('</head><body >');
    mywindow.document.write('</body></html>');
    mywindow.document.body.appendChild(data);

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10

    mywindow.print();
    mywindow.close();

    return true;
}

function callAPI( url, successDialog, reload, funct, silentMode, suppressErrors, errorFcn, transportType ){
    if( successDialog == undefined ) successDialog = false;
    if( reload == undefined ) reload = false;
    if( funct == undefined ) funct = function( res ){ /* do nothing! */ };
    if( silentMode == undefined ) silentMode = false;
    if( suppressErrors == undefined ) suppressErrors = false;
    if( errorFcn == undefined ) errorFcn = function( res ){ /* do nothing! */ };
    if( transportType == undefined ) transportType = 'GET';
    //
    postData = "";
    if( transportType == 'POST' ){
        dataIndex = url.indexOf('?');
        if( dataIndex != -1 ){
            postData = url.substr( dataIndex+1 );
            url = url.substr( 0, dataIndex );
        }
    }
    //
    url = encodeURI( url );
    //
    if( !silentMode ){
        showPleaseWait();
    }
    //
    $.ajax({
        type: transportType,
        url:url,
        dataType: 'json',
        data: postData,
        success:function( result ){
            if( result.code == 200 ){
                // success
                // call the callback function
                funct( result );
                //
                hidePleaseWait();
                //
                if( successDialog ){
                    showSuccessDialog( 2000, ( (reload)? function(){ window.location.reload(true); } : function(){} ) );
                }else{
                    if( reload ){
                        window.location.reload(true);
                    }
                }
            }else{
                // error
                // call the callback function
                errorFcn( result );
                //open an alert
                hidePleaseWait();
                if( !suppressErrors ){
                    openAlert( 'danger', result.message );
                }
            }
        },
        error:function( jqXHR, textStatus, errorThrown ){
            // error
            // call the callback function
            errorFcn( errorThrown );
            // open an alert
            hidePleaseWait();
            if( !suppressErrors ){
                openAlert( 'danger', 'An error occurred while trying to communicate with the server. Details: `{0}`'.format(errorThrown) );
            }
        }
    });
}


function callExternalAPI( url, callType, resultDataType, successDialog, reload, funct, silentMode, suppressErrors, errorFcn, errorArgs ){
    if( successDialog == undefined ) successDialog = false;
    if( reload == undefined ) reload = false;
    if( funct == undefined ) funct = function( res ){ /* do nothing! */ };
    if( silentMode == undefined ) silentMode = false;
    if( suppressErrors == undefined ) suppressErrors = false;
    if( errorFcn == undefined ) errorFcn = function( res ){ /* do nothing! */ };
    //
    url = encodeURI( url );
    //
    if( !silentMode ){
        showPleaseWait();
    }
    //
    $.ajax({type: callType, url:url, dataType: resultDataType, success:function( result ){
        // success
        // call the callback function
        funct( result );
        //
        hidePleaseWait();
        //
        if( successDialog ){
            showSuccessDialog( 2000, ( (reload)? function(){ window.location.reload(true); } : function(){} ) );
        }else{
            if( reload ){
                window.location.reload(true);
            }
        }
    }, error:function( jqXHR, textStatus, errorThrown ){
        // error
        // call the callback function
        errorFcn( errorThrown );
        // open an alert
        hidePleaseWait();
        if( !suppressErrors ){
            openAlert( 'danger', 'An error occurred while trying to communicate with the server. Details: `{0}`'.format(errorThrown) );
        }
    }});
}

function serializeForm( formID, excludeDisabled ){
    if( excludeDisabled == undefined ) excludeDisabled = false;
    //
    var str = '';
    //
    $(formID).find('input').each(
        function(){
            if( !excludeDisabled || !$(this).prop("disabled") ){
                switch( $(this).attr('type') ){
                    case 'checkbox':
                        str += '&'+$(this).attr('name')+'='+ ( (this.checked)? 1 : 0 );
                        break;
                    default:
                        str += '&'+$(this).attr('name')+'='+ encodeURIComponent($(this).val());
                        break;
                }
            }
        }
    )
    //
    $(formID).find('select').each(
        function(){
            if( !excludeDisabled || !$(this).prop("disabled") ){
                if( $(this).find(':selected').attr('value') != undefined && $(this).find(':selected').attr('value') != '' ){
                    str += '&'+$(this).attr('name')+'='+ $(this).find(':selected').attr('value');
                }else{
                    str += '&'+$(this).attr('name')+'='+ $(this).find(':selected').text();
                }
            }
        }
    )
    //
    $(formID).find('textarea').each(
        function(){
            if( !excludeDisabled || !$(this).prop("disabled") ){
                str += '&'+$(this).attr('name')+'='+ encodeURIComponent($(this).val());
            }
        }
    )
    //
    return ( (str.length > 0)? str.slice(1) : str );
}//serializeForm

function money( num ){
    return parseFloat(Math.round(num * 100) / 100).toFixed(2);
}//money

function hmsToSeconds( str ){
    var p = str.split(':'), s = 0, m = 1;
    //
    while (p.length > 0) {
        s += m * parseInt(p.pop(), 10);
        m *= 60;
    }
    //
    return s;
}//hmsToSeconds

function redirectTo( page, action, arg1, arg2, query_array ){
    var url = "{0}//{1}/".format( location.protocol, location.host );
    // append PAGE
    if( page != null && page != undefined )
        url += ( url.slice(-1)=='/'? '' : '/' )+page;
    // append ACTION
    if( action != null && action != undefined )
        url += '/'+action;
    // append arguments
    if( arg1 != null && arg1 != undefined )
        url += '/'+arg1;
    if( arg2 != null && arg2 != undefined )
        url += '/'+arg2;
    // create query string
    if( query_array != null && query_array != undefined )
        url += '?'+$.param( query_array );
    // move to new url
    window.location = url;
}//redirectTo



// form element to associative array
$.fn.toAssociativeArray = function() {
    var formData = {};
    this.find('[name]').each(function() {
        formData[this.name] = this.value;
    })
    return formData;
};

// enable popovers
$(function () {
    try{
        $('[data-toggle="popover"]').popover();
    }catch( e ){}
});

// disable popover on click
$(document).on("click", "a", function(){ $(this).popover('hide'); });

// close all popovers when opening a modal dialog
$(document).on("show.bs.modal", '.modal', function(){
    $(document).find('.popover').each(function() {
        $(this).popover('hide');
    });
});

// Enable Tooltips
$(function () {
    try{
        $('[data-toggle~="tooltip"]').tooltip()
    }catch( e ){}
});

$(document).on('ready', function(){
    $('.modal-vertical-centered').on('show.bs.modal', centerModal);
    $(window).on("resize", function () {
        $('.modal-vertical-centered:visible').each(centerModal);
    });
    // re-configure the modals
    // Modals (updated from [data-toggle~="modal"])
    $(document).on('click.modal.data-api', '[data-toggle~="dialog"]', function (e) {
        var targetID = $(this).data('target');
        //
        var $this = $(this)
            , href = $this.attr('href')
            , $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) //strip for ie7
            , option = $target.data('modal') ? 'toggle' : $.extend({ remote:!/#/.test(href) && href }, $target.data(), $this.data());
        //
        $target.modal(option, this);
        $target.one('hide', function () {
            $this.focus()
        });
    });
});
