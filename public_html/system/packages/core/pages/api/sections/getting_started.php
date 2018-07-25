<?php
include_once __DIR__.'/../parts/common_style.php';

use \system\classes\Configuration;
use \system\classes\RESTfulAPI;

function _api_page_getting_started_section( &$api_setup, &$version, &$sget, &$aget ){
?>
    <div class="api-breadcrumb">
        <table>
            <tr>
                <td>
                    <h2 style="margin:0; padding-right:24px">
                        <span class="glyphicon glyphicon-book" aria-hidden="true"></span>
                    </h2>
                </td>
                <td class="mono">
                    <h3 class="text-right" style="margin:0">Getting Started</h3>
                </td>
            </tr>
        </table>
    </div>

    <div class="api-service-box-container" style="position:relative">
        <div class="api-service-section" style="padding-top:40px">
            <h3>
                Table of contents
            </h3>
            <div class="api-service-toc">
                <ul>
                    <li>
                        <a href="#begin">Introduction</a>
                    </li>
                    <ul>
                        <li>
                            <a href="#service-help">What is a Service?</a>
                        </li>
                        <li>
                            <a href="#action-help">What is an Action?</a>
                        </li>
                    </ul>
                </ul>
                <ul>
                    <li>
                        <a href="#request">The HTTP Request</a>
                    </li>
                    <ul>
                        <li>
                            <a href="#reply-format">Response format</a>
                        </li>
                        <li>
                            <a href="#reply-parameters">Parameters</a>
                        </li>
                    </ul>
                </ul>
                <ul>
                    <li>
                        <a href="#reply">The HTTP Response</a>
                    </li>
                    <ul>
                        <li>
                            <a href="#success-codes">Success codes</a>
                        </li>
                        <li>
                            <a href="#error-codes">Error codes</a>
                        </li>
                    </ul>
                </ul>
            </div>
        </div>


        <div class="api-service-section">
            <a class="anchor" id="begin"></a>
            <h3>
                Introduction
            </h3>
            <div>
                Let's start with a quick introduction to the terms <bold>Service</bold> and <bold>Action</bold>.

                <div class="api-service-subsection">
                    <a class="anchor" id="service-help"></a>
                    <h3>
                        What is a Service?
                    </h3>
                    <div>
                        <p>
                            A <bold>Service</bold> is a collection of functions accessible through HTTP requests.
                            The functions of a service are called <bold>Actions</bold>.
                        </p>
                        <p>
                            A service can be temporarily or permanently disabled by the Administrator.
                            A service can be either <bold>Online</bold>, thus ready to accept requests, or <bold>Offline</bold>, in which case it will result as unreachable via HTTP.
                        </p>
                        <p>
                            When the Administrator disables a service, all the actions belonging to that service will go Offline.
                        </p>
                    </div>
                </div>

                <div class="api-service-subsection">
                    <a class="anchor" id="action-help"></a>
                    <h3>
                        What is an Action?
                    </h3>
                    <div>
                        <p>
                            An <bold>Action</bold> defines a possible interaction between the user (who generates the HTTP request) and the system (that answers the HTTP request).
                            It is executed by the server on the server itself.
                        </p>
                        <p>
                            An action can be temporarily or permanently disabled by the Administrator.
                            An action can be either <bold>Online</bold>, thus ready to accept requests, or <bold>Offline</bold>, in which case it will result as unreachable via HTTP.
                        </p>
                        <p>
                            Each action defines a list of <emph>parameters</emph>. Parameters are used by the user to pass arguments to the action.
                        </p>
                    </div>
                </div>
            </div>
        </div>


        <div class="api-service-section">
            <a class="anchor" id="request"></a>
            <h3>
                The HTTP Request
            </h3>
            <div>
                <p>
                    The following box shows the structure of the URL for a generic API request executed using the HTTP protocol.
                </p>
                <div class="mono api-url-container">
                    <p>
                        <?php echo Configuration::$BASE.'web-api/' ?><span class="emph param">version</span>/<span class="emph param">service</span>/<span class="emph param">action</span>/<span class="emph param">format</span>?<span class="emph param">parameters</span>
                    </p>
                </div>
                <p>
                    <br/>
                    A URL for an API request is defined by 6 parts:
                    <span class="param emph" style="color:black">base_url</span>,
                    <span class="param emph">version</span>,
                    <span class="param emph">service</span>,
                    <span class="param emph">action</span>,
                    <span class="param emph">format</span>,
                    <span class="param emph">parameters</span>
                    .
                </p>

                <div class="api-service-subsection">
                    <a class="anchor" id="reply-base_url"></a>
                    <h4>
                        &bullet; <span class="param emph" style="color:black">base_url</span>:
                    </h4>
                    <div>
                        <p>
                            The first part of the URL is the base URL and it is fixed. It has the form <span class="mono">your_domain.tld/web-api</span>&nbsp;
                            (e.g., "<span class="mono"><?php echo Configuration::$BASE.'web-api/' ?></span>").
                        </p>
                    </div>
                </div>

                <div class="api-service-subsection">
                    <a class="anchor" id="reply-version"></a>
                    <h4>
                        &bullet; <span class="param emph">version</span>:
                    </h4>
                    <div>
                        <p>
                            Indicates the version of the API to use (e.g., <span class="emph param">1.0</span>).
                        </p>
                    </div>
                </div>

                <div class="api-service-subsection">
                    <a class="anchor" id="reply-service"></a>
                    <h4>
                        &bullet; <span class="param emph">service</span>:
                    </h4>
                    <div>
                        <p>
                            Indicates the service requested by the user among those exported by the API selected (e.g., <span class="param emph">duckiebot</span>).
                        </p>
                    </div>
                </div>

                <div class="api-service-subsection">
                    <a class="anchor" id="reply-action"></a>
                    <h4>
                        &bullet; <span class="param emph">action</span>:
                    </h4>
                    <div>
                        <p>
                            Indicates the action that the user wants to perform among those published by the service selected (e.g., <span class="param emph">status</span>).
                        </p>
                    </div>
                </div>

                <div class="api-service-subsection">
                    <a class="anchor" id="reply-format"></a>
                    <h4>
                        &bullet; <span class="param emph">format</span>:
                    </h4>
                    <div>
                        <p>
                            Specifies how to format the response provided by the server. The formats available are:
                        </p>
                        <p>
                    <span style="padding-left:30px">
                        { <?php echo '<span class="param">' . implode( '</span>, <span class="param">', $api_setup[$version]['global']['parameters']['embedded']['format']['values'] ) . '</span>' ?> }
                    </span>
                        </p>
                    </div>
                </div>

                <div class="api-service-subsection">
                    <a class="anchor" id="reply-parameters"></a>
                    <h4>
                        &bullet; <span class="param emph">parameters</span>:
                    </h4>
                    <div>
                        <p>
                            It is a string containing a contenation of <span class="param emph">parameter=value</span> pairs expressed in the standard Query string format.
                            A generic Query string has the form:
                        </p>
                        <p style="padding-left:30px">
                            <span class="param">parameter1</span>=<span class="param">value1</span>&<span class="param">parameter2</span>=<span class="param">value2</span>&...</span>
                        </p>
                        <p>
                            Select a service and an action from the menu on the left hand side of this page to see the list of parameters for a specific action.
                        </p>
                    </div>
                </div>

            </div>
        </div>


        <div class="api-service-section">
            <a class="anchor" id="reply"></a>
            <h3>
                The HTTP Response
            </h3>
            <div>
                <p>
                    A generic response generated by the server as result of an action is the following:
                </p>
                <p>
                    When <span class="param emph">format</span>=<span class="param emph" style="color:black">json</span>:
                </p>
                <div class="mono api-url-container">
                    <p>
                        {<br/>
                        &nbsp;&nbsp;&nbsp;"code" : 200,<br/>
                        &nbsp;&nbsp;&nbsp;"status" : "OK",<br/>
                        &nbsp;&nbsp;&nbsp;"data" : {<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br/>
                        &nbsp;&nbsp;&nbsp;}<br/>
                        }
                    </p>
                </div>
                <br>
                <p>
                    When <span class="param emph">format</span>=<span class="param emph" style="color:black">xml</span>:
                </p>
                <div class="mono api-url-container">
                    <p>
                        &lt;?xml version="1.0" encoding="UTF-8"?&gt;<br/>
                        &lt;result&gt;<br/>
                        &nbsp;&nbsp;&nbsp;&lt;code&gt;200&lt;/code&gt;<br/>
                        &nbsp;&nbsp;&nbsp;&lt;status&gt;OK&lt;/status&gt;<br/>
                        &nbsp;&nbsp;&nbsp;&lt;data&gt;<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br/>
                        &nbsp;&nbsp;&nbsp;&lt;/data&gt;<br/>
                        &lt;/result&gt;
                    </p>
                </div>

                <p>
                    <br/>
                    The object <span class="param emph">code</span> contains the status of the API call using standard HTTP status codes. The sections <a href="#success-codes">Success codes</a> and <a href="#error-codes">Error codes</a> explains the meaning of the status codes used by this API implementation.
                </p>
                <p>
                    The object <span class="param emph">status</span> contains the status of the API call as a status string. Each error code is associated with a status string. The sections <a href="#success-codes">Success codes</a> and <a href="#error-codes">Error codes</a> contains a list of status strings used by this API implementation.
                </p>
                <p>
                    The object <span class="param emph">data</span> contains the data returned by the execution of the selected action. The structure of this object depends on the specific action called. Use the menu on the left hand side of this page to access the documentation for specific actions to see the format of this object.
                </p>

                <div class="api-service-subsection">
                    <a class="anchor" id="success-codes"></a>
                    <h3>
                        Success codes
                    </h3>
                    <div>
                        <p>
                            All the HTTP success codes belong to the family of codes <span class="mono">20X</span>.
                            The success codes used in this API are:
                        </p>
                        <p style="padding-left:30px">
                            <span class="param">200</span> &nbsp;&nbsp;&nbsp;&nbsp; "OK"
                            <br/>
                            <small>The API call was successfull.</small>
                        </p>
                        <br/>
                        <p style="padding-left:30px">
                            <span class="param">204</span> &nbsp;&nbsp;&nbsp;&nbsp; "No Content"
                            <br/>
                            <small>The API call was successfull but no data was retrieved. The server returns an empty response.</small>
                        </p>
                    </div>
                </div>

                <div class="api-service-subsection">
                    <a class="anchor" id="error-codes"></a>
                    <h3>
                        Error codes
                    </h3>
                    <div>
                        <p>
                            We distinguish between client-side errors (<span class="mono">4XX</span> erros) ed server-side errors (<span class="mono">5XX</span> errors).
                            The error codes used in this API are:
                        </p>
                        <p style="padding-left:30px">
                            <span class="param">400</span> &nbsp;&nbsp;&nbsp;&nbsp; "Bad Request"
                            <br/>
                            <small>The server cannot or will not process the request due to an apparent client error (e.g., malformed request syntax)</small>
                        </p>
                        <br/>
                        <p style="padding-left:30px">
                            <span class="param">401</span> &nbsp;&nbsp;&nbsp;&nbsp; "Unauthorized"
                            <br/>
                            <small>The request was valid, but the server is refusing action. The user might not have the necessary permissions.</small>
                        </p>
                        <br/>
                        <p style="padding-left:30px">
                            <span class="param">404</span> &nbsp;&nbsp;&nbsp;&nbsp; "Not Found"
                            <br/>
                            <small>The server does not recognize the requested service/action.</small>
                        </p>
                        <br/>
                        <p style="padding-left:30px">
                            <span class="param">426</span> &nbsp;&nbsp;&nbsp;&nbsp; "Upgrade Required"
                            <br/>
                            <small>The client should switch to a different version of the API. The API used is obsolete.</small>
                        </p>
                        <br/>
                        <p style="padding-left:30px">
                            <span class="param">500</span> &nbsp;&nbsp;&nbsp;&nbsp; "Internal Server Error"
                            <br/>
                            <small>A generic error message, given when an unexpected condition was encountered.</small>
                        </p>
                        <br/>
                        <p style="padding-left:30px">
                            <span class="param">503</span> &nbsp;&nbsp;&nbsp;&nbsp; "Service Unavailable"
                            <br/>
                            <small>The requested service/action is currently Offline.</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>
