
# Code reference: **system::classes::Core**

## Description
Core module of the platform \compose\. 


## Static Public Member Functions

<table class="table table-striped table-condensed">

<tr><td> static </td><td> <a href="#aac7fa2d23f36b74bd0f281aa195d02bf"><bold>initCore</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#ab7c27cbc3d8be5557f5e195248c4f229"><bold>loadPackagesModules</bold></a> ($module_family=null, $pkg_id=null) </td></tr>
<tr><td> static </td><td> <a href="#a44a2c92952563878d4e738413f161e7b"><bold>getClasses</bold></a> ($parent_class=null) </td></tr>
<tr><td> static </td><td> <a href="#a1d68d0d72e16e18dfe327f39868b8fb5"><bold>close</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#a1a6e6b48f5dee6d884f8909a27899ce0"><bold>startSession</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#a9e62e0b0bf3193051fe19bb2d2aba97d"><bold>logInUserWithGoogle</bold></a> ($id_token) </td></tr>
<tr><td> static </td><td> <a href="#a77ebd352c65e46c9add748a7f6d412cb"><bold>createNewUserAccount</bold></a> ($user_id, &$user_info) </td></tr>
<tr><td> static </td><td> <a href="#a0431918e1ae0d222801fdc4bdf90ed18"><bold>isUserLoggedIn</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#a66cb2a1b994e4a369592d88f1b619df7"><bold>getUsersList</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#a19397300ef7f50f9eec0db0fc9899e8c"><bold>logOutUser</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#a379dadcba690f7abcd650e52887504af"><bold>userExists</bold></a> ($user_id) </td></tr>
<tr><td> static </td><td> <a href="#a335ea07e24fd8d3590bfac9e3b8e7da4"><bold>openUserInfo</bold></a> ($user_id) </td></tr>
<tr><td> static </td><td> <a href="#adfa7812cff409fc6c19a6f1bfa688184"><bold>getUserInfo</bold></a> ($user_id) </td></tr>
<tr><td> static </td><td> <a href="#a0ba89ee474d44c0a0fe33983ec44221b"><bold>getUserLogged</bold></a> ($field=null) </td></tr>
<tr><td> static </td><td> <a href="#a5eea8459bc88c5b03bfdd278d05c4b2b"><bold>getUserRole</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#acf9c0e3b43df11dd723e3f74864f1083"><bold>setUserRole</bold></a> ($user_role) </td></tr>
<tr><td> static </td><td> <a href="#ab70820f943fa705ef1d175808046e7b4"><bold>getUserTypesList</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#a73eba23a4633ea1031752d423ea0c2b5"><bold>getPackagesList</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#aec538673fbbdf5ef7c4f3201930b9e5a"><bold>packageExists</bold></a> ($package) </td></tr>
<tr><td> static </td><td> <a href="#a97a85856bc814a4f1383babeec522967"><bold>isPackageEnabled</bold></a> ($package) </td></tr>
<tr><td> static </td><td> <a href="#a331ea9f7473def92c0f636303280cc92"><bold>enablePackage</bold></a> ($package) </td></tr>
<tr><td> static </td><td> <a href="#a82122568e61fa4b513dd44fa086def1f"><bold>disablePackage</bold></a> ($package) </td></tr>
<tr><td> static </td><td> <a href="#a3b887670d32301a83838299dbf6e56b9"><bold>getPackageSettings</bold></a> ($package_name) </td></tr>
<tr><td> static </td><td> <a href="#ade9eb8b4891953bd18e014385451a874"><bold>getPackageSettingsAsArray</bold></a> ($package_name) </td></tr>
<tr><td> static </td><td> <a href="#aafc1c79d1179abd8d5c906a51809344e"><bold>getSetting</bold></a> ($key, $package_name='core', $default_value=null) </td></tr>
<tr><td> static </td><td> <a href="#a251526b0d90564e1b4be318dfe724b9d"><bold>setSetting</bold></a> ($package_name, $key, $value) </td></tr>
<tr><td> static </td><td> <a href="#a4b7bafdf7b847a3a0d6c3976c6680521"><bold>getImageURL</bold></a> ($image_file_with_extension, $package_name="core") </td></tr>
<tr><td> static </td><td> <a href="#abf8818b9689322325d35a9a85debefda"><bold>getJSscriptURL</bold></a> ($js_file_with_extension, $package_name="core") </td></tr>
<tr><td> static </td><td> <a href="#aced2ad53122efd8874920fe01562557b"><bold>getCSSstylesheetURL</bold></a> ($css_file_with_extension, $package_name="core") </td></tr>
<tr><td> static </td><td> <a href="#ad15dd2d7aa16fcfe8709c3eca8d4bbd9"><bold>getPagesList</bold></a> ($order=null) </td></tr>
<tr><td> static </td><td> <a href="#a9e595d2a3312c762cf1e5c1dfd83114b"><bold>getFilteredPagesList</bold></a> ($order='list', $enabledOnly=false, $accessibleBy=null) </td></tr>
<tr><td> static </td><td> <a href="#a7d569e6ee5a63aee918fc28f8f737f7d"><bold>getPageDetails</bold></a> ($page_id, $attribute=null) </td></tr>
<tr><td> static </td><td> <a href="#a4c9eabe4876eb7647278162f783519b7"><bold>pageExists</bold></a> ($package, $page) </td></tr>
<tr><td> static </td><td> <a href="#ab53c23c392ae4c5a0fe14cf25c01d7eb"><bold>isPageEnabled</bold></a> ($package, $page) </td></tr>
<tr><td> static </td><td> <a href="#ad75cb2fbed784bed45a5906cc773fde3"><bold>enablePage</bold></a> ($package, $page) </td></tr>
<tr><td> static </td><td> <a href="#a9fd63f1e1073cbf165e80c2107766dd9"><bold>disablePage</bold></a> ($package, $page) </td></tr>
<tr><td> static </td><td> <a href="#a2a98d9c3cb11ba272d485e7d66ee48de"><bold>getFactoryDefaultPagePerRole</bold></a> ($user_role) </td></tr>
<tr><td> static </td><td> <a href="#a76efa8913ffbc183cec04e053f6e8ab3"><bold>getAPIsetup</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#a7b0bb68896afceb0e9f86755eaed09a0"><bold>APIserviceExists</bold></a> ($api_version, $service_name) </td></tr>
<tr><td> static </td><td> <a href="#a44265d026cf05ca1b5b34ce2c5012bbd"><bold>isAPIserviceEnabled</bold></a> ($api_version, $service_name) </td></tr>
<tr><td> static </td><td> <a href="#ab09ca54ba2aca018b29e1c0ec8be8823"><bold>enableAPIservice</bold></a> ($api_version, $service_name) </td></tr>
<tr><td> static </td><td> <a href="#a611499a458cc6f6222013ac57a50bbdf"><bold>disableAPIservice</bold></a> ($api_version, $service_name) </td></tr>
<tr><td> static </td><td> <a href="#ade0efafb1fabf9d2596423fc67b0bc9d"><bold>APIactionExists</bold></a> ($api_version, $service_name, $action_name) </td></tr>
<tr><td> static </td><td> <a href="#a3c4687b73d817a3e9e9b4fc3bb78aad7"><bold>isAPIactionEnabled</bold></a> ($api_version, $service_name, $action_name) </td></tr>
<tr><td> static </td><td> <a href="#afebe8b466ccaae5637747d7511fa806e"><bold>enableAPIaction</bold></a> ($api_version, $service_name, $action_name) </td></tr>
<tr><td> static </td><td> <a href="#aaf048ede10a1f046b4ce2113dd72420c"><bold>disableAPIaction</bold></a> ($api_version, $service_name, $action_name) </td></tr>
<tr><td> static </td><td> <a href="#a77d8ac28459de344f06eb751e71563f7"><bold>getStatistics</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#a462669d2cc070b4f13b350bc4de6663e"><bold>getSiteName</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#aaf24a2c54235215cefde38fd4cbf1190"><bold>getCodebaseHash</bold></a> ($long_hash=false) </td></tr>
<tr><td> static </td><td> <a href="#a0b4acd9b008755f46f178125eeee36ed"><bold>getCodebaseInfo</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#a70e0d94d76199f6ab1674e5586e3b12e"><bold>redirectTo</bold></a> ($resource) </td></tr>
<tr><td> static </td><td> <a href="#afacd478acbb8984d9c8009b4010fe2e7"><bold>throwError</bold></a> ($errorMsg) </td></tr>
<tr><td> static </td><td> <a href="#a73756076ef703ea5a8f9d7fea0e210e9"><bold>sendEMail</bold></a> ($to, $subject, $template, $replace, $replyTo=null) </td></tr>
<tr><td> static </td><td> <a href="#a80d7ab73f7d6c91867e7ed09b34637be"><bold>isAlphabetic</bold></a> ($string, $length=null) </td></tr>
<tr><td> static </td><td> <a href="#a9eeceb23ecd7fa27e5d5b2ff1f982f73"><bold>isNumeric</bold></a> ($string, $length=null) </td></tr>
<tr><td> static </td><td> <a href="#ab7b20754d8dd97a3c11a1cd4b63f3f01"><bold>isAlphaNumeric</bold></a> ($string, $length=null) </td></tr>
<tr><td> static </td><td> <a href="#aca4abe1bf05c678dba5b61a4c2d82cf7"><bold>isAvalidEmailAddress</bold></a> ($string, $length=null) </td></tr>
<tr><td> static </td><td> <a href="#a385c3b045755d41d2b84eaf1d5e05950"><bold>hash_password</bold></a> ($plain_password) </td></tr>
<tr><td> static </td><td> <a href="#abbbb28100a578ca75290da29bde9bf0b"><bold>collectErrorInformation</bold></a> ($errorData) </td></tr>
<tr><td> static </td><td> <a href="#ae75b49d06b180d7f80054c3ae869e3a5"><bold>generateRandomString</bold></a> ($length) </td></tr>
<tr><td> static </td><td> <a href="#a5af781588d163e7179b493c8d88185d4"><bold>verbose</bold></a> ($verbose_flag=True) </td></tr>
<tr><td> static </td><td> <a href="#a5441f92cab5a9db947b33a17bd3ea546"><bold>debug</bold></a> ($debug_flag=True) </td></tr>
<tr><td> static </td><td> <a href="#a544f5ed1833e75c0d8607b7b6b71388c"><bold>log</bold></a> ($type, $message,...$args) </td></tr>
<tr><td> static </td><td> <a href="#aa7d3a24081003fc28669524cd9397e86"><bold>_getGMTOffset</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#ac8d18f92bd655d027e2a954351895f13"><bold>_load_packages_settings</bold></a> () </td></tr>
<tr><td> static </td><td> <a href="#aff713bae5d015585d7303c4291ec1a9f"><bold>_load_API_setup</bold></a> () </td></tr>
</table>
## Member Function Documentation
<div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="aac7fa2d23f36b74bd0f281aa195d02bf">static <bold>initCore</bold> ()</h3>
					</div>
					<div class="panel-body">
						Initializes the Core module. It is the first function to call when using the Core module.
						
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ab7c27cbc3d8be5557f5e195248c4f229">static <bold>loadPackagesModules</bold> ($module_family=null, $pkg_id=null)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a44a2c92952563878d4e738413f161e7b">static <bold>getClasses</bold> ($parent_class=null)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a1d68d0d72e16e18dfe327f39868b8fb5">static <bold>close</bold> ()</h3>
					</div>
					<div class="panel-body">
						Terminates the Core module. It is responsible for committing unsaved changes to the disk or closing open connections (e.g., mySQL) before leaving.
						
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a1a6e6b48f5dee6d884f8909a27899ce0">static <bold>startSession</bold> ()</h3>
					</div>
					<div class="panel-body">
						Creates a new PHP Session and assigns a new randomly generated 16-digits authorization token to it.
						
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p><code>TRUE</code> if the function succeded, <code>FALSE</code> otherwise </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a9e62e0b0bf3193051fe19bb2d2aba97d">static <bold>logInUserWithGoogle</bold> ($id_token)</h3>
					</div>
					<div class="panel-body">
						Logs in a user using the Google Sign-In OAuth 2.0 authentication procedure.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$id_token</code></td>
				<td style="width:8px"></td><td><p>id_token returned by the Google Identity Sign-In tool, (for more info check: <ulink url="https://developers.google.com/identity/sign-in/web/reference#gapiauth2authresponse">https://developers.google.com/identity/sign-in/web/reference#gapiauth2authresponse</ulink>);</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a77ebd352c65e46c9add748a7f6d412cb">static <bold>createNewUserAccount</bold> ($user_id, &$user_info)</h3>
					</div>
					<div class="panel-body">
						Creates a new user account.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$user_id</code></td>
				<td style="width:8px"></td><td><p>string containing the (numeric) user id provided by Google Sign-In;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>array</bold></td>
				<td style="width:8px"></td>
				<td><code>$user_info</code></td>
				<td style="width:8px"></td><td><p>array containing information about the new user. This array has to contain at least all the keys defined in $USER_ACCOUNT_TEMPLATE;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a0431918e1ae0d222801fdc4bdf90ed18">static <bold>isUserLoggedIn</bold> ()</h3>
					</div>
					<div class="panel-body">
						Returns whether a user is currently logged in.
						
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p>whether a user is currently logged in; </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a66cb2a1b994e4a369592d88f1b619df7">static <bold>getUsersList</bold> ()</h3>
					</div>
					<div class="panel-body">
						Returns the list of users registered on the platform. A user is automatically registered when s/he logs in with google.
						
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>list of user ids. The user id of a user is the numeric user id assigned by Google; </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a19397300ef7f50f9eec0db0fc9899e8c">static <bold>logOutUser</bold> ()</h3>
					</div>
					<div class="panel-body">
						Logs out the user from the platform. If the user is not logged in yet, the function will return an error status.
						
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a379dadcba690f7abcd650e52887504af">static <bold>userExists</bold> ($user_id)</h3>
					</div>
					<div class="panel-body">
						Checks whether a user account exists.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$user_id</code></td>
				<td style="width:8px"></td><td><p>string containing the (numeric) user id provided by Google Sign-In;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p>whether a user account with the specified user id exists; </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a335ea07e24fd8d3590bfac9e3b8e7da4">static <bold>openUserInfo</bold> ($user_id)</h3>
					</div>
					<div class="panel-body">
						Opens the user account record for the user specified in write-mode. This function returns an instance of the class \system\classes\jsonDB\JsonDB containing the information about the user specified.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$user_id</code></td>
				<td style="width:8px"></td><td><p>string containing the (numeric) user id provided by Google Sign-In;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or instance of \system\classes\jsonDB\JsonDB
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>, otherwise it will contain an instance of the class \system\classes\jsonDB\JsonDB containing the information about the user specified. The JsonDB object will contain at least the keys specified in $USER_ACCOUNT_TEMPLATE. See the documentation for the class JsonDB to understand how to edit and commit information. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="adfa7812cff409fc6c19a6f1bfa688184">static <bold>getUserInfo</bold> ($user_id)</h3>
					</div>
					<div class="panel-body">
						Returns the user account record for the user specified. Unlike openUserInfo(), this function returns a read-only copy of the user account.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$user_id</code></td>
				<td style="width:8px"></td><td><p>string containing the (numeric) user id provided by Google Sign-In;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or associative array
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>, otherwise it will contain an associative array containing the information about the user specified. The associative array in <code>data</code> will contain at least the keys specified in $USER_ACCOUNT_TEMPLATE. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a0ba89ee474d44c0a0fe33983ec44221b">static <bold>getUserLogged</bold> ($field=null)</h3>
					</div>
					<div class="panel-body">
						Returns the user account record of the user currently logged in.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$field</code></td>
				<td style="width:8px"></td><td><p>(optional) name of the field to retrieve from the user account. It can be any of the keys specified in $USER_ACCOUNT_TEMPLATE;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>mixed</code></td>
				<td style="width:8px"></td><td><p>If no user is currently logged in, returns <code>NULL</code>; If <code>$field</code>=<code>NULL</code>, returns associative array containing the information about the user currently logged in (similar to <ref refid="classsystem_1_1classes_1_1_core_1adfa7812cff409fc6c19a6f1bfa688184" kindref="member">getUserInfo()</ref>); If a value for <code>$field</code> is passed, only the value of the field specified is returned (e.g., name). </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a5eea8459bc88c5b03bfdd278d05c4b2b">static <bold>getUserRole</bold> ()</h3>
					</div>
					<div class="panel-body">
						Returns the role of the user that is currently using the platform.
						
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>string</code></td>
				<td style="width:8px"></td><td><p>role of the user that is currently using the platform. It can be any of the default roles defined by <bold>\compose\</bold> or any other role registered by third-party packages. A list of all the user roles registered can be retrieved using the function <ref refid="classsystem_1_1classes_1_1_core_1ab70820f943fa705ef1d175808046e7b4" kindref="member">getUserTypesList()</ref>; </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="acf9c0e3b43df11dd723e3f74864f1083">static <bold>setUserRole</bold> ($user_role)</h3>
					</div>
					<div class="panel-body">
						Sets the user role of the user that is currently using the platform. NOTE: this function does not update the user account of the current user permanently. This change will be lost once the session is closed.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$user_role</code></td>
				<td style="width:8px"></td><td><p>role to assign to the current user;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>void</code></td>
				<td style="width:8px"></td><td><para/></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ab70820f943fa705ef1d175808046e7b4">static <bold>getUserTypesList</bold> ()</h3>
					</div>
					<div class="panel-body">
						Returns the list of all user roles known to the platform. It includes all the user roles defined by \compose\ plus all the user roles introduced by third-party packages.
						
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>list of unique strings. Each string represents a different user role; </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a73eba23a4633ea1031752d423ea0c2b5">static <bold>getPackagesList</bold> ()</h3>
					</div>
					<div class="panel-body">
						Returns the list of packages installed on the platform.
						
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>an associative array of the form <pre><code>[
    "package_id" =&gt; [
        "id" : string,                  // ID of the package (identical to package_id)
        "name" : string,                // name of the package
        "description" : string,         // brief description of the package
        "dependencies" : [
            "system-packages" : [],     // list of system packages required by the package
            "packages" : []             // list of \compose\ packages required by the package
        ],
        "url_rewrite" : [
            "rule_id" : [
                "pattern" : string,     // regex of the rule for the URI to be compared against
                "replace" : string      // replacement template using group-specific variables (e.g., $1)
            ],
            ...
        ]
        "enabled" : boolean             // whether the package is enabled
    ],
    ...                                 // other packages
]</code></pre></p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="aec538673fbbdf5ef7c4f3201930b9e5a">static <bold>packageExists</bold> ($package)</h3>
					</div>
					<div class="panel-body">
						Returns whether the package specified is installed on the platform.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package</code></td>
				<td style="width:8px"></td><td><p>the name of the package to check.</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p>whether the package exists. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a97a85856bc814a4f1383babeec522967">static <bold>isPackageEnabled</bold> ($package)</h3>
					</div>
					<div class="panel-body">
						Returns whether the specified package is enabled.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package</code></td>
				<td style="width:8px"></td><td><p>the name of the package to check.</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p>whether the package is enabled. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a331ea9f7473def92c0f636303280cc92">static <bold>enablePackage</bold> ($package)</h3>
					</div>
					<div class="panel-body">
						Enables a package installed on the platform.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package</code></td>
				<td style="width:8px"></td><td><p>the name of the package to enable.</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a82122568e61fa4b513dd44fa086def1f">static <bold>disablePackage</bold> ($package)</h3>
					</div>
					<div class="panel-body">
						Disables a package installed on the platform.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package</code></td>
				<td style="width:8px"></td><td><p>the name of the package to disable. </p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a3b887670d32301a83838299dbf6e56b9">static <bold>getPackageSettings</bold> ($package_name)</h3>
					</div>
					<div class="panel-body">
						Returns the settings for a given package as an instance of .
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package_name</code></td>
				<td style="width:8px"></td><td><p>the ID of the package to retrieve the settings for.</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>mixed</code></td>
				<td style="width:8px"></td><td><p>If the package is installed, it returns an associative array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the configuration was successfully loaded
    "data" =&gt; mixed         // instance of <ref refid="classsystem_1_1classes_1_1_editable_configuration" kindref="compound">EditableConfiguration</ref> or a string error message
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains a string with the error when <code>success</code> is <code>FALSE</code>. If the package is not installed, the function returns <code>NULL</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ade9eb8b4891953bd18e014385451a874">static <bold>getPackageSettingsAsArray</bold> ($package_name)</h3>
					</div>
					<div class="panel-body">
						Returns the settings for a given package as an associative array.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package_name</code></td>
				<td style="width:8px"></td><td><p>the ID of the package to retrieve the settings for.</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>mixed</code></td>
				<td style="width:8px"></td><td><p>If the function succeeds, it returns an associative array of the form <pre><code>[
    "key" =&gt; "value",
    ...                 // other entries
]</code></pre> where, <code>key</code> can ba any configuration key exported by the package and <code>value</code> its value. If the package is not installed, the function returns <code>NULL</code>. If an error occurred while reading the configuration of the given package, a <code>string</code> containing the error is returned. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="aafc1c79d1179abd8d5c906a51809344e">static <bold>getSetting</bold> ($key, $package_name='core', $default_value=null)</h3>
					</div>
					<div class="panel-body">
						Returns the value of the given setting key for the given package.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$key</code></td>
				<td style="width:8px"></td><td><p>the setting key to retrieve;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package_name</code></td>
				<td style="width:8px"></td><td><p>(optional) Name of the package the requested setting belongs to. Default is 'core';</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$default_value</code></td>
				<td style="width:8px"></td><td><p>the default value returned if the key does not exist. DEFAULT = null;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>mixed</code></td>
				<td style="width:8px"></td><td><p>If the function succeeds, it returns the value of the setting key specified. If the package is not installed or an error occurred while reading the configuration for the given package, <code>NULL</code> is returned. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a251526b0d90564e1b4be318dfe724b9d">static <bold>setSetting</bold> ($package_name, $key, $value)</h3>
					</div>
					<div class="panel-body">
						Sets the value for the given setting key of the given package.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package_name</code></td>
				<td style="width:8px"></td><td><p>the ID of the package the setting key belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$key</code></td>
				<td style="width:8px"></td><td><p>the setting key to set the value for;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$value</code></td>
				<td style="width:8px"></td><td><p>the new value to store in the package's settings;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>mixed</code></td>
				<td style="width:8px"></td><td><p>If the function succeeds, it returns <code>TRUE</code>. If the package is not installed, the function returns <code>NULL</code>. If an error occurred while writing the configuration of the given package, a <code>string</code> containing the error is returned. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a4b7bafdf7b847a3a0d6c3976c6680521">static <bold>getImageURL</bold> ($image_file_with_extension, $package_name="core")</h3>
					</div>
					<div class="panel-body">
						Returns the URL to a package-specific image. The image file must in the directory /images of the package.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$image_file_with_extension</code></td>
				<td style="width:8px"></td><td><p>Filename of the image (including extension);</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package_name</code></td>
				<td style="width:8px"></td><td><p>(optional) Name of the package the requested image belongs to. Default is 'core';</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>string</code></td>
				<td style="width:8px"></td><td><p>URL to the requested image. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="abf8818b9689322325d35a9a85debefda">static <bold>getJSscriptURL</bold> ($js_file_with_extension, $package_name="core")</h3>
					</div>
					<div class="panel-body">
						Returns the URL to a package-specific Java-Script file. The JS file must in the directory /js of the package.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$js_file_with_extension</code></td>
				<td style="width:8px"></td><td><p>Filename of the Java-Script file (including extension);</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package_name</code></td>
				<td style="width:8px"></td><td><p>(optional) Name of the package the requested Java-Script file belongs to. Default is 'core';</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>string</code></td>
				<td style="width:8px"></td><td><p>URL to the requested Java-Script file. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="aced2ad53122efd8874920fe01562557b">static <bold>getCSSstylesheetURL</bold> ($css_file_with_extension, $package_name="core")</h3>
					</div>
					<div class="panel-body">
						Returns the URL to a package-specific CSS file. The CSS file must in the directory /css of the package.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$css_file_with_extension</code></td>
				<td style="width:8px"></td><td><p>Filename of the CSS file (including extension);</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package_name</code></td>
				<td style="width:8px"></td><td><p>(optional) Name of the package the requested CSS file belongs to. Default is 'core';</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>string</code></td>
				<td style="width:8px"></td><td><p>URL to the requested CSS file. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ad15dd2d7aa16fcfe8709c3eca8d4bbd9">static <bold>getPagesList</bold> ($order=null)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a9e595d2a3312c762cf1e5c1dfd83114b">static <bold>getFilteredPagesList</bold> ($order='list', $enabledOnly=false, $accessibleBy=null)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a7d569e6ee5a63aee918fc28f8f737f7d">static <bold>getPageDetails</bold> ($page_id, $attribute=null)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a4c9eabe4876eb7647278162f783519b7">static <bold>pageExists</bold> ($package, $page)</h3>
					</div>
					<div class="panel-body">
						Returns whether the page specified is installed on the platform as part of the package specified.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package</code></td>
				<td style="width:8px"></td><td><p>the name of the package the page to check belongs to. </p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$page</code></td>
				<td style="width:8px"></td><td><p>the name of the page to check. </p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p>whether the page exists. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ab53c23c392ae4c5a0fe14cf25c01d7eb">static <bold>isPageEnabled</bold> ($package, $page)</h3>
					</div>
					<div class="panel-body">
						Returns whether the specified page is enabled.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package</code></td>
				<td style="width:8px"></td><td><p>the name of the package the page to check belongs to. </p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$page</code></td>
				<td style="width:8px"></td><td><p>the name of the page to check. </p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p>whether the page is enabled. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ad75cb2fbed784bed45a5906cc773fde3">static <bold>enablePage</bold> ($package, $page)</h3>
					</div>
					<div class="panel-body">
						Enables a page installed on the platform as part of the given package.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package</code></td>
				<td style="width:8px"></td><td><p>the name of the package the page to enable belongs to.. </p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$page</code></td>
				<td style="width:8px"></td><td><p>the name of the page to enable. </p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a9fd63f1e1073cbf165e80c2107766dd9">static <bold>disablePage</bold> ($package, $page)</h3>
					</div>
					<div class="panel-body">
						Disables a page installed on the platform as part of the given package.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$package</code></td>
				<td style="width:8px"></td><td><p>the name of the package the page to disable belongs to.. </p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$page</code></td>
				<td style="width:8px"></td><td><p>the name of the page to disable. </p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a2a98d9c3cb11ba272d485e7d66ee48de">static <bold>getFactoryDefaultPagePerRole</bold> ($user_role)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a76efa8913ffbc183cec04e053f6e8ab3">static <bold>getAPIsetup</bold> ()</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a7b0bb68896afceb0e9f86755eaed09a0">static <bold>APIserviceExists</bold> ($api_version, $service_name)</h3>
					</div>
					<div class="panel-body">
						Returns whether the given API service is installed on the platform.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$api_version</code></td>
				<td style="width:8px"></td><td><p>the version of the API the service to check belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$service_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API service to check;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p>whether the API service exists; </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a44265d026cf05ca1b5b34ce2c5012bbd">static <bold>isAPIserviceEnabled</bold> ($api_version, $service_name)</h3>
					</div>
					<div class="panel-body">
						Returns whether the specified API service is enabled.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$api_version</code></td>
				<td style="width:8px"></td><td><p>the version of the API the service to check belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$service_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API service to check;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p>whether the API service exists and is enabled; </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ab09ca54ba2aca018b29e1c0ec8be8823">static <bold>enableAPIservice</bold> ($api_version, $service_name)</h3>
					</div>
					<div class="panel-body">
						Enables an API service.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$api_version</code></td>
				<td style="width:8px"></td><td><p>the version of the API the service to enable belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$service_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API service to enable;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a611499a458cc6f6222013ac57a50bbdf">static <bold>disableAPIservice</bold> ($api_version, $service_name)</h3>
					</div>
					<div class="panel-body">
						Disables an API service.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$api_version</code></td>
				<td style="width:8px"></td><td><p>the version of the API the service to disable belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$service_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API service to disable;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ade0efafb1fabf9d2596423fc67b0bc9d">static <bold>APIactionExists</bold> ($api_version, $service_name, $action_name)</h3>
					</div>
					<div class="panel-body">
						Returns whether the given API action is installed on the platform.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$api_version</code></td>
				<td style="width:8px"></td><td><p>the version of the API the action to check belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$service_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API service the action to check belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$action_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API action to check;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p>whether the API action exists; </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a3c4687b73d817a3e9e9b4fc3bb78aad7">static <bold>isAPIactionEnabled</bold> ($api_version, $service_name, $action_name)</h3>
					</div>
					<div class="panel-body">
						Returns whether the specified API action is enabled.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$api_version</code></td>
				<td style="width:8px"></td><td><p>the version of the API the action to check belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$service_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API service the action to check belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$action_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API action to check;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>boolean</code></td>
				<td style="width:8px"></td><td><p>whether the API action exists and is enabled; </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="afebe8b466ccaae5637747d7511fa806e">static <bold>enableAPIaction</bold> ($api_version, $service_name, $action_name)</h3>
					</div>
					<div class="panel-body">
						Enables an API action.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$api_version</code></td>
				<td style="width:8px"></td><td><p>the version of the API the action to enable belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$service_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API service the action to enable belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$action_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API action to enable;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="aaf048ede10a1f046b4ce2113dd72420c">static <bold>disableAPIaction</bold> ($api_version, $service_name, $action_name)</h3>
					</div>
					<div class="panel-body">
						Disables an API action.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$api_version</code></td>
				<td style="width:8px"></td><td><p>the version of the API the action to disable belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$service_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API service the action to disable belongs to;</p></td>
			</tr><tr style="vertical-align:text-top;">
				<td><bold>string</bold></td>
				<td style="width:8px"></td>
				<td><code>$action_name</code></td>
				<td style="width:8px"></td><td><p>the name of the API action to disable;</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>a status array of the form <pre><code>[
    "success" =&gt; boolean,   // whether the function succeded
    "data" =&gt; mixed         // error message or NULL
]</code></pre> where, the <code>success</code> field indicates whether the function succeded. The <code>data</code> field contains an error string when <code>success</code> is <code>FALSE</code>. </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a77d8ac28459de344f06eb751e71563f7">static <bold>getStatistics</bold> ()</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a462669d2cc070b4f13b350bc4de6663e">static <bold>getSiteName</bold> ()</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="aaf24a2c54235215cefde38fd4cbf1190">static <bold>getCodebaseHash</bold> ($long_hash=false)</h3>
					</div>
					<div class="panel-body">
						Returns the hash identifying the version of the codebase. This corresponds to the commit ID on git.
						<br/><br/>
				<bold>Parameters</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><bold>boolean</bold></td>
				<td style="width:8px"></td>
				<td><code>$long_hash</code></td>
				<td style="width:8px"></td><td><p>whether to return the short hash (first 7 digits) or the long (full) commit hash. DEFAULT = false (7-digits commit hash).</p></td>
			</tr></tbody></table></dd>
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>string</code></td>
				<td style="width:8px"></td><td><p>alphanumeric hash of the commit currently fetched on the server </p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a0b4acd9b008755f46f178125eeee36ed">static <bold>getCodebaseInfo</bold> ()</h3>
					</div>
					<div class="panel-body">
						Returns information about the current codebase (e.g., git user, git repository, remote URL, etc.)
						
						<br/><br/>
				<bold>Return values</bold></br></br>
				<dd><table><tbody><tr style="vertical-align:text-top;">
				<td><code>array</code></td>
				<td style="width:8px"></td><td><p>An array containing info about the codebase with the following details: <pre><code>[
    "git_owner" =&gt; string,          // username of the owner of the git repository
    "git_repo" =&gt; string,           // name of the repository
    "git_host" =&gt; string,           // hostname of the remote git server
    "git_remote_url" =&gt; string,     // url to the remote repository
    "head_hash" =&gt; string,          // short commit hash of the head of the local repository
    "head_full_hash" =&gt; string,     // full commit hash of the head of the local repository
    "head_tag" =&gt; mixed             // tag associated to the head. null if no tag is found
    "latest_tag" =&gt; mixed           // latest tag (back in time) of codebase. null if no tag is found.
]</code></pre></p></td>
			</tr></tbody></table></dd>
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a70e0d94d76199f6ab1674e5586e3b12e">static <bold>redirectTo</bold> ($resource)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="afacd478acbb8984d9c8009b4010fe2e7">static <bold>throwError</bold> ($errorMsg)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a73756076ef703ea5a8f9d7fea0e210e9">static <bold>sendEMail</bold> ($to, $subject, $template, $replace, $replyTo=null)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a80d7ab73f7d6c91867e7ed09b34637be">static <bold>isAlphabetic</bold> ($string, $length=null)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a9eeceb23ecd7fa27e5d5b2ff1f982f73">static <bold>isNumeric</bold> ($string, $length=null)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ab7b20754d8dd97a3c11a1cd4b63f3f01">static <bold>isAlphaNumeric</bold> ($string, $length=null)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="aca4abe1bf05c678dba5b61a4c2d82cf7">static <bold>isAvalidEmailAddress</bold> ($string, $length=null)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a385c3b045755d41d2b84eaf1d5e05950">static <bold>hash_password</bold> ($plain_password)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="abbbb28100a578ca75290da29bde9bf0b">static <bold>collectErrorInformation</bold> ($errorData)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ae75b49d06b180d7f80054c3ae869e3a5">static <bold>generateRandomString</bold> ($length)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a5af781588d163e7179b493c8d88185d4">static <bold>verbose</bold> ($verbose_flag=True)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a5441f92cab5a9db947b33a17bd3ea546">static <bold>debug</bold> ($debug_flag=True)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="a544f5ed1833e75c0d8607b7b6b71388c">static <bold>log</bold> ($type, $message,...$args)</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="aa7d3a24081003fc28669524cd9397e86">static <bold>_getGMTOffset</bold> ()</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="ac8d18f92bd655d027e2a954351895f13">static <bold>_load_packages_settings</bold> ()</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div><div class="panel panel-primary reference-code-documentation-panel">
					<div class="panel-heading">
						<h3 class="panel-title" id="aff713bae5d015585d7303c4291ec1a9f">static <bold>_load_API_setup</bold> ()</h3>
					</div>
					<div class="panel-body">
						
						
						
					</div>
				</div>