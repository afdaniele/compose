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

window.compose = {};

window.COMPOSE_API_VERSION = '1.0';

function humanFileSize(size) {
    if (size <= 0) return '0 B';
    let i = Math.floor( Math.log(size) / Math.log(1024) );
    return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
};

/**
 * Translates seconds into human readable format of seconds, minutes, hours, days, and years
 *
 * @param  {number} seconds     The number of seconds to be processed
 * @param  {boolean} compact    Generate compact string
 * @param  {string} precision   Specify the last quantity to show. Choose between [y, d, h, m, s].
 * @return {string}         The phrase describing the the amount of time
 */
function humanTime (seconds, compact = false, precision= 's') {
    let levels = [
        [Math.floor(seconds / 31536000), (compact)? 'y' : 'years'],
        [Math.floor((seconds % 31536000) / 86400), (compact) ? 'd' : 'days'],
        [Math.floor(((seconds % 31536000) % 86400) / 3600), (compact) ? 'h' : 'hours'],
        [Math.floor((((seconds % 31536000) % 86400) % 3600) / 60), (compact) ? 'm' : 'minutes'],
        [(((seconds % 31536000) % 86400) % 3600) % 60, (compact) ? 's' : 'seconds'],
    ];
    let returntext = '';

    let precision_lvls = {'y': 1, 'd': 2, 'h': 3, 'm': 4, 's': 5};

    for (let i = 0, max = precision_lvls[precision]; i < max; i++) {
        if ( levels[i][0] === 0 ) continue;
        returntext += ' ' + levels[i][0] + (compact ? '' : ' ') + (levels[i][0] === 1 ? levels[i][1].substr(0, levels[i][1].length-1) : levels[i][1]);
    };
    return returntext.trim();
}

function range(start, end, step) {
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
function openPop(targetID, title, content, placement, showDelay, duration, fixed, closeOthers) {
    if (typeof (fixed) === 'undefined') fixed = false;
    if (closeOthers == undefined) closeOthers = false;
    //
    if (closeOthers)
        closeAllPops();
    //
    var target = $('#' + targetID);
    target.popover({
        animation: true,
        html: true,
        title: '<span class="text-danger">' + title + '</span>&nbsp;&nbsp;&nbsp;' +
            '<button type="button" id="close" class="close" onclick="$(&quot;#' + targetID + '&quot;).popover(&quot;hide&quot;);">&times;</button>',
        content: content,
        container: 'body',
        placement: placement,
        delay: {"show": showDelay, "hide": 100}
    });
    target.popover('toggle');
    if (!fixed) {
        setTimeout(function () {
            target.popover('destroy');
        }, showDelay + duration);
    }
}

// close all popovers
function closeAllPops() {
    $(document).find('.popover').each(function () {
        $(this).popover('hide');
    });
}

// enable popover on hover event
function enablePopOnHover(targetID, title, content, placement, hideCloseButton) {
    if (hideCloseButton == undefined) hideCloseButton = false;
    //
    var target = $('#' + targetID);
    target.popover({
        animation: true,
        html: true,
        title: '<span class="text-danger"><strong>' + title + '</strong></span>' +
            ((hideCloseButton) ? '' : '<button type="button" id="close" class="close" onclick="$(&quot;#' + targetID + '&quot;).popover(&quot;hide&quot;);">&times;</button>'),
        content: content,
        container: 'body',
        placement: placement
    });
    target.hover(function () {
        target.popover('show');
    }, function () {
        target.popover('hide');
    });
}

// Utility

function toHHMMSS(sec_num) {
    var hours = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours < 10) {
        hours = "0" + hours;
    }
    if (minutes < 10) {
        minutes = "0" + minutes;
    }
    if (seconds < 10) {
        seconds = "0" + seconds;
    }
    var time = hours + ':' + minutes + ':' + seconds;
    return time;
}

function toHHMM(sec_num) {
    var hours = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);

    if (hours < 10) {
        hours = "0" + hours;
    }
    if (minutes < 10) {
        minutes = "0" + minutes;
    }
    var time = hours + ':' + minutes;
    return time;
}

function secondsSinceMidnight() {
    var now = new Date(),
        then = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0),
        diff = (now.getTime() - then.getTime()) / 1000; // difference in seconds
    return diff;
}

function openAlert(type, messageHTML) {
    $('#page_alert_object').attr('class', 'alert alert-' + type + ' alert-dismissible');
    $('#page_alert_content').html(messageHTML);
    // show
    $('#page_alert_container').css('display', '');
    // move to the page header
    $('._ctheme_content').animate({scrollTop: 0}, 'slow');
    // trigger the 'alert show event'
    $(document).trigger("show.bs.alert");
}

function closeAlert() {
    // hide
    $('#page_alert_container').css('display', 'none');
    // trigger the 'alert hide event'
    $(document).trigger("hide.bs.alert");
}


function centerModal() {
    $(this).css('display', 'block');
    var $dialog = $(this).find(".modal-dialog");
    var offset = ($(window).height() - $dialog.height()) / 2;
    // Center modal vertically in window
    $dialog.css("margin-top", offset);
}


function showPleaseWait() {
    try {
        $('#pleaseWaitModal').modal('show');
    } catch (e) {
    }
}

function hidePleaseWait() {
    try {
        $('#pleaseWaitModal').modal('hide');
    } catch (e) {
    }
}


function userLogInWithGoogle(baseurl, apiversion, token, id_token, successFcn) {
    if (successFcn == undefined) successFcn = function () {
        window.location.reload();
    };
    showPleaseWait();
    // compile URI
    var uri = "web-api/" + apiversion + "/userprofile/login_with_google/json?token=" + token;
    // compile URL
    var url = baseurl + encodeURI(uri);
    // call the API
    $.ajax({
        type: 'POST',
        url: url,
        dataType: 'json',
        data: {'id_token': id_token},
        success: function (result) {
            if (result.code == 200) {
                // success, reload page
                hidePleaseWait();
                successFcn();
            } else {
                // error
                hidePleaseWait();
                openAlert('danger', result.message);
                // Sign-out from Google
                try {
                    gapi.auth2.getAuthInstance().signOut();
                } catch (e) {
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            hidePleaseWait();
            openAlert('danger', errorThrown); //'An error occurred, please retry!' );
        }
    });
}

function developerLogIn() {
    showPleaseWait();
    let base = Configuration.get('core', 'BASE');
    let apiversion = Configuration.get('core', 'WEBAPI_VERSION');
    let token = Configuration.get('core', 'TOKEN');
    // compile URI
    var uri = "web-api/" + apiversion + "/userprofile/login_as_developer/json?token=" + token;
    // compile URL
    var url = base + encodeURI(uri);
    // call the API
    callAPI(url, false, true);
}

function userLogOut(baseurl, apiversion, token, successFcn) {
    if (successFcn == undefined) successFcn = function (res) { /* do nothing! */
    };
    showPleaseWait();
    //
    var uri = "web-api/" + apiversion + "/userprofile/logout/json?token=" + token;
    //
    var url = baseurl + encodeURI(uri);
    // call the API
    $.ajax({
        type: 'GET', url: url, dataType: 'json', success: function (result) {
            if (result.code == 200) {
                // success, redirect
                successFcn();
            } else {
                // error
                hidePleaseWait();
                openAlert('danger', 'An error occurred while trying to log you out. Please, retry.');
            }
        }, error: function () {
            hidePleaseWait();
        }
    });
}

function showSuccessDialog(duration, funct) {
    $('#successDialog').modal('show');
    setTimeout(function () {
        $('#successDialog').modal('hide');
        funct();
    }, duration);
}

function printElement(elem) {
    html2canvas($(elem), {
        onrendered: function (canvas) {
            popup(canvas);
        }
    });
}

function popup(data) {
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

/*
@param service: Name of the service
@param action: Name of the action within the service
@param args: An object containing the following data:

    {
        'method': either 'GET' or 'POST'
        'arguments': {
            'key1': 'value1',
            'key2': 'value2',
            ...
        },
        'data': {
            JS object to be sent as body of the request
        },
        'on_success': a callable object,
        'on_error': a callable object,
        'block': boolean, whether to show the loading modal until completed (default: false)
        'confirm': boolean, whether to show the confirmation dialog on success (default: false)
        'quiet': boolean, indicates whether to suppress errors and warnings (default: false)
        'reload': boolean, reload the page on success (default: false)
        'host': hostname of the \compose\ instance to call the API on (default: LOCAL)
        'version': version of the \compose\ API to call (default: LOCAL)
        'auth': {
            'token': \compose\ token (optional)
            'app_id': App IP for API call authentication (optional)
            'app_secret': App Secret Key for API call authentication (optional)
        }
    }
*/
function smartAPI(service, action, args) {
    let base = window.COMPOSE_BASE;
    if (args['host']) {
        base = (args['host'].startsWith('http')) ? args['host'] : 'http://' + args['host'];
    }
    base = base.endsWith('/')? base : base + '/';
    let version = args['version'] || window.COMPOSE_API_VERSION;
    let auth = {'auth': 'token={0}'.format(window.COMPOSE_TOKEN)};
    if (args['auth'] !== undefined && args['auth']['app_id'] !== undefined && args['auth']['app_secret'] !== undefined) {
        auth = {'auth': 'app_id={0}&app_secret={1}'.format(args['auth']['app_id'], args['auth']['app_secret'])};
    }
    // form URL
    let url = '{base}web-api/{version}/{service}/{action}/json?{auth}&{arguments}'.format({
        'base': base,
        'version': version,
        'service': service,
        'action': action,
        'arguments': $.param(args['arguments'] || {}),
        ...auth
    });
    // sanitize url
    url = url.strip('/');
    // call API
    callAPI(
        url,
        args['confirm'] || false,
        args['reload'] || false,
        args['on_success'] || function () {
        },
        !args['block'] || false,
        args['quiet'] || false,
        args['on_error'] || function () {
        },
        args['method'] || 'GET',
        args['data'] || {}
    );
}//smartAPI

function callAPI(url, successDialog, reload, funct, silentMode, suppressErrors, errorFcn, transportType, bodyData) {
    if (successDialog === undefined) successDialog = false;
    if (reload === undefined) reload = false;
    if (funct === undefined) funct = function (res) { /* do nothing! */ };
    if (silentMode === undefined) silentMode = false;
    if (suppressErrors === undefined) suppressErrors = false;
    if (errorFcn === undefined) errorFcn = function (res) { /* do nothing! */ };
    if (transportType === undefined) transportType = 'GET';
    if (bodyData === undefined) bodyData = {};
    //
    let postData = "";
    if (transportType === 'POST' && bodyData === {}) {
        let dataIndex = url.indexOf('?');
        if (dataIndex !== -1) {
            postData = url.substr(dataIndex + 1);
            url = url.substr(0, dataIndex);
        }
    } else {
        postData = bodyData;
    }
    //
    // sanitize url
    url = url.strip('/');
    url = encodeURI(url);
    //
    if (!silentMode) {
        showPleaseWait();
    }
    //
    $.ajax({
        type: transportType,
        url: url,
        dataType: 'json',
        data: postData,
        success: function (result) {
            if (result.code === 200) {
                // success
                // call the callback function
                funct(result);
                //
                hidePleaseWait();
                //
                if (successDialog) {
                    showSuccessDialog(2000, ((reload) ? function () {
                        window.location.reload();
                    } : function () {
                    }));
                } else {
                    if (reload) {
                        window.location.reload();
                    }
                }
            } else {
                // error
                // close any modal
                $(".modal").modal('hide');
                // call the callback function
                errorFcn(result);
                //open an alert
                hidePleaseWait();
                if (!suppressErrors) {
                    openAlert('danger', result.message);
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // error
            // close any modal
            $(".modal").modal('hide');
            // call the callback function
            errorFcn(errorThrown);
            // open an alert
            hidePleaseWait();
            if (!suppressErrors) {
                openAlert('danger', 'An error occurred while trying to communicate with the server. Details: ' + errorThrown);
            }
        }
    });
}


function callExternalAPI(url, callType, resultDataType, successDialog, reload, funct, silentMode, suppressErrors, errorFcn, errorArgs, customHeaders) {
    if (successDialog === undefined) successDialog = false;
    if (reload === undefined) reload = false;
    if (funct === undefined) funct = function (res) { /* do nothing! */ };
    if (silentMode === undefined) silentMode = false;
    if (suppressErrors === undefined) suppressErrors = false;
    if (errorFcn === undefined) errorFcn = function (res) { /* do nothing! */ };
    if (customHeaders === undefined) customHeaders = {};
    //
    url = encodeURI(url);
    //
    if (!silentMode) {
        showPleaseWait();
    }
    //
    $.ajax({
        type: callType,
        url: url,
        dataType: resultDataType,
        headers: customHeaders,
        success: function (result, status, xhr) {
            // success
            // call the callback function
            funct(result, status, xhr);
            //
            hidePleaseWait();
            //
            if (successDialog) {
                showSuccessDialog(2000, ((reload) ? function () {
                    window.location.reload();
                } : function () {
                }));
            } else {
                if (reload) {
                    window.location.reload();
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // error
            // call the callback function
            errorFcn(errorThrown);
            // open an alert
            hidePleaseWait();
            if (!suppressErrors) {
                openAlert('danger', 'An error occurred while trying to communicate with the server. Details: `{0}`'.format(errorThrown));
            }
        }
    });
}

function serializeForm(formID, excludeDisabled) {
    if (excludeDisabled == undefined) excludeDisabled = false;
    //
    var str = '';
    //
    $(formID).find('input').each(
        function () {
            if (!excludeDisabled || !$(this).prop("disabled")) {
                switch ($(this).attr('type')) {
                    case 'checkbox':
                        str += '&' + $(this).attr('name') + '=' + ((this.checked) ? 1 : 0);
                        break;
                    default:
                        str += '&' + $(this).attr('name') + '=' + encodeURIComponent($(this).val());
                        break;
                }
            }
        }
    );
    //
    $(formID).find('select').each(
        function () {
            if (!excludeDisabled || !$(this).prop("disabled")) {
                if ($(this).find(':selected').attr('value') != undefined && $(this).find(':selected').attr('value') != '') {
                    str += '&' + $(this).attr('name') + '=' + $(this).find(':selected').attr('value');
                } else {
                    str += '&' + $(this).attr('name') + '=' + $(this).find(':selected').text();
                }
            }
        }
    );
    //
    $(formID).find('textarea').each(
        function () {
            if (!excludeDisabled || !$(this).prop("disabled")) {
                str += '&' + $(this).attr('name') + '=' + encodeURIComponent($(this).val());
            }
        }
    );
    //
    return ((str.length > 0) ? str.slice(1) : str);
}//serializeForm

function serializeFormToJSON(formID, excludeDisabled, blacklist_keys) {
    if (excludeDisabled == undefined) excludeDisabled = false;
    if (blacklist_keys == undefined) blacklist_keys = [];
    //
    let res = {};
    //
    $(formID).find('input').each(
        function () {
            let key = $(this).attr('name');
            if (blacklist_keys.indexOf(key) != -1)
                return;
            if (!excludeDisabled || !$(this).prop("disabled")) {
                switch ($(this).attr('type')) {
                    case 'checkbox':
                        res[key] = (this.checked) ? 1 : 0;
                        break;
                    default:
                        res[key] = encodeURIComponent($(this).val());
                        break;
                }
            }
        }
    );
    //
    $(formID).find('select').each(
        function () {
            let key = $(this).attr('name');
            if (blacklist_keys.indexOf(key) != -1)
                return;
            if (!excludeDisabled || !$(this).prop("disabled")) {
                if ($(this).find(':selected').attr('value') != undefined && $(this).find(':selected').attr('value') != '') {
                    res[key] = $(this).find(':selected').attr('value');
                } else {
                    res[key] = $(this).find(':selected').text();
                }
            }
        }
    );
    //
    $(formID).find('textarea').each(
        function () {
            let key = $(this).attr('name');
            if (blacklist_keys.indexOf(key) != -1)
                return;
            if (!excludeDisabled || !$(this).prop("disabled")) {
                res[key] = encodeURIComponent($(this).val());
            }
        }
    );
    //
    return res;
}//serializeFormToJSON

function money(num) {
    return parseFloat(Math.round(num * 100) / 100).toFixed(2);
}//money

function hmsToSeconds(str) {
    var p = str.split(':'), s = 0, m = 1;
    //
    while (p.length > 0) {
        s += m * parseInt(p.pop(), 10);
        m *= 60;
    }
    //
    return s;
}//hmsToSeconds

function redirectTo(page, action, arg1, arg2, query_array) {
    let url = window.Configuration.get('core', 'BASE');
    // append PAGE
    if (page != null)
        url += page;
    // append ACTION
    if (action != null)
        url += '/' + action;
    // append arguments
    if (arg1 != null)
        url += '/' + arg1;
    if (arg2 != null)
        url += '/' + arg2;
    // create query string
    if (query_array != null)
        url += '?' + $.param(query_array);
    // move to new url
    window.location = url;
}//redirectTo


// form element to associative array
$.fn.toAssociativeArray = function () {
    var formData = {};
    this.find('[name]').each(function () {
        formData[this.name] = this.value;
    });
    return formData;
};

// enable popovers
$(function () {
    try {
        $('[data-toggle="popover"]').popover();
    } catch (e) {
    }
});

// disable popover on click
$(document).on("click", "a", function () {
    $(this).popover('hide');
});

// close all popovers when opening a modal dialog
$(document).on("show.bs.modal", '.modal', function () {
    $(document).find('.popover').each(function () {
        $(this).popover('hide');
    });
});

// Enable Tooltips
$(function () {
    try {
        $('[data-toggle~="tooltip"]').tooltip()
    } catch (e) {
    }
});

$(document).on('ready', function () {
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
            ,
            $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) //strip for ie7
            ,
            option = $target.data('modal') ? 'toggle' : $.extend({remote: !/#/.test(href) && href}, $target.data(), $this.data());
        //
        $target.modal(option, this);
        $target.one('hide', function () {
            $this.focus()
        });
    });
});


// Check for updates
function checkForUpdates(git_provider, git_owner, git_repo, git_local_head, allow_unstable, on_success_fcn, on_error_fcn, ignore_cache) {
    // 0: git_owner, 1: git_repo, 2: action, 3: arguments
    var api_url = '';
    if (git_provider === 'github.com') {
        api_url = 'https://api.github.com/repos/{0}/{1}/{2}/{3}';
    }
    var headers = {};
    if (ignore_cache === undefined || !ignore_cache) {
        headers = {
            'release': {
                'If-Modified-Since': localStorage.getItem('github_compose_release_last_modified'),
                'If-None-Match': localStorage.getItem('github_compose_release_etag')
            },
            'compare': {
                'If-Modified-Since': localStorage.getItem('github_compose_compare_last_modified'),
                'If-None-Match': localStorage.getItem('github_compose_compare_etag')
            }
        };
    }

    // function that compares two heads
    function compareHeads(local_head, remote_head) {
        let args_str = '{0}...{1}'.format(local_head, remote_head);
        let url_compare_commits = api_url.format(git_owner, git_repo, 'compare', args_str);

        function fmt_fcn1(result, status, xhr) {
            if (xhr.status === 304) {
                // use cache values
                let needs_update = localStorage.getItem('github_compose_needs_update') === 'true';
                return on_success_fcn(needs_update);
            } else {
                let needs_update = result.status === 'ahead';
                localStorage.setItem('github_compose_compare_last_modified', xhr.getResponseHeader("Last-Modified"));
                localStorage.setItem('github_compose_compare_etag', xhr.getResponseHeader("ETag"));
                localStorage.setItem('github_compose_needs_update', needs_update);
                return on_success_fcn(needs_update);
            }
        }

        callExternalAPI(url_compare_commits, 'GET', 'json', false, false, fmt_fcn1, true, true, on_error_fcn, [], headers['compare']);
    }

    // ---
    if (allow_unstable) {
        compareHeads(git_local_head, 'devel');
    } else {
        // get latest release
        let url_latest_release = api_url.format(git_owner, git_repo, 'releases', 'latest');

        function fmt_fcn2(result, status, xhr) {
            localStorage.setItem('github_compose_release_last_modified', xhr.getResponseHeader("Last-Modified"));
            localStorage.setItem('github_compose_release_etag', xhr.getResponseHeader("ETag"));
            let tag_name = "";
            if (xhr.status === 304) {
                // use cache values
                tag_name = localStorage.getItem('github_compose_latest_tag_name');
            } else {
                localStorage.setItem('github_compose_latest_tag_name', result.tag_name);
                tag_name = result.tag_name;
            }
            compareHeads(git_local_head, tag_name);
        }

        callExternalAPI(url_latest_release, 'GET', 'json', false, false, fmt_fcn2, true, true, on_error_fcn, [], headers['release']);
    }
}//checkForUpdates


function clearUpdatesCache() {
    localStorage.removeItem('github_compose_compare_last_modified');
    localStorage.removeItem('github_compose_compare_etag');
    localStorage.removeItem('github_compose_needs_update');
}//clearUpdatesCache


function tableToObject(table_id) {
    var cols = [];
    var result = [];
    $('{0}>tbody>tr>th'.format(table_id)).each(function () {
        cols.push($(this).text().toLowerCase());
    });
    $('{0}>tbody>tr'.format(table_id)).each(function (id) {
        var row = {};
        if ($(this).find('td').length == 0) return;
        $(this).find('td').each(function (index) {
            row[cols[index]] = $(this).text();
        });
        result.push(row);
    });
    return result;
}//tableToObject

function toHumanReadableSize(fileSizeInBytes) {
    var i = -1;
    var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
    do {
        fileSizeInBytes = fileSizeInBytes / 1024;
        i++;
    } while (fileSizeInBytes > 1024);

    return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];
}//toHumanReadableSize

function clearCache() {
    let url = "{0}script.php?script=clearcache".format(Configuration.get('core', 'BASE'));
    let successDialog = true;
    let reload = true;
    let callType = 'GET';
    let resultDataType = 'text';
    callExternalAPI(url, callType, resultDataType, successDialog, reload);
}

function copyToClipboard (text) {
    let dummy = document.createElement("textarea");
    document.body.appendChild(dummy);
    dummy.value = text;
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
}

function getAbsoluteURLtoResource(resource, protocol = true, host = true, path = true, qs = true, hash = true) {
    if (resource === undefined) {
        resource = "";
    }
    let link = document.createElement("a");
    link.href = resource;
    let s_protocol = protocol ? "{0}//".format(link.protocol) : "";
    let s_host = host ? link.host : "";
    let s_path = path ? link.pathname : "";
    let s_qs = qs ? link.search : "";
    let s_hash = hash ? link.hash : "";
    return "{0}{1}{2}{3}{4}".format(s_protocol, s_host, s_path, s_qs, s_hash);
}

function getCurrentAbsoluteURL(protocol = true, host = true, path = true, qs = true, hash = true) {
    return getAbsoluteURLtoResource("", protocol, host, path, qs, hash);
}

function _compose_vertical_fit() {
    $('.vertical_fit').each(function (_, dom_elem) {
        let elem = $(dom_elem);
        let container = $(elem.data('vertical-fit-parent') ?? "._ctheme_content");
        let elem_pos = elem.offset();
        let container_pos = container.offset();
        let container_h = container.height();
        let container_padding = parseInt(container.css('padding-top'));
        let space = container_h + container_padding - (elem_pos.top - container_pos.top);
        let height = Math.max(1, space);
        elem.css('height', '{0}px'.format(height));
    });
}

$(document).ready(function () {
    $(window).resize(function() {
        _compose_vertical_fit();
    });
    // get all elements that need to be vertically fit
    let elems = $('.vertical_fit');
    // refit when the object is ready
    elems.ready(function(){
        _compose_vertical_fit();
    });
    // refit when the object is loaded
    elems.load(function(){
        _compose_vertical_fit();
    });
    // refit when the object changes shape
    elems.resize(function(){
        _compose_vertical_fit();
    });
    // refit right now
    _compose_vertical_fit();
});