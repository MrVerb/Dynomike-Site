				<h3>Do you have full documentation?</h3>
				<p>Yes. You can find full online documentation here: <a href="https://layeredpopups.com/documentation/" target="_blank">https://layeredpopups.com/documentation/</a></p>
				<h3>How can I create my own popup?</h3>
				<ol>
					<li>
						Go to <a href="admin.php?page=ulp-add">Create Popup</a> and enter the size of basic popup frame.
						This frame will be centered and all layers will be placed relative to the top-left corner of this frame.
					</li>
					<li>
						Click button <code>Add New Layer</code> and configure your first layer.
					</li>
					<li>
						Create as many layers as you need.
					</li>
					<li>
						Save popup details.
					</li>
				</ol>
				<h3>How can I raise a popup?</h3>
				<p>There are several ways to raise popup: by clicking certain element, on every page load, on scrolling down, on exit intent, on user inactivity.</p>
				<ol>
					<li>
						If you want to raise popup by clicking certain element, add the following <code>onclick</code> handler to the element:
						<br /><code>onclick="return ulp_open('OBJECT_ID');"</code>
						<br /><code>OBJECT_ID</code> is a popup ID taken form relevant column on <a href="admin.php?page=ulp">this page</a>, or A/B campaign ID taken form relevant column on <a href="admin.php?page=ulp-campaigns">this page</a>.
						<br />Example: <code>&lt;a href="#" onclick="return ulp_open('OBJECT_ID');"&gt;Raise the popup&lt;/a&gt;</code>
					</li>
					<li>
						If you want to raise popup by clicking link just use the following kind of links:
						<br /><code>&lt;a href="#ulp-OBJECT_ID"&gt;Raise the popup&lt;/a&gt;</code>
						<br /><code>OBJECT_ID</code> is a popup ID taken form relevant column on <a href="admin.php?page=ulp">this page</a>, or A/B campaign ID taken form relevant column on <a href="admin.php?page=ulp-campaigns">this page</a>.
					</li>
					<li>
						To raise popup on page load, go to <a href="admin.php?page=ulp-settings">Settings</a> page and set OnLoad parameters.
					</li>
					<li>
						To raise popup on scrolling down, go to <a href="admin.php?page=ulp-settings">Settings</a> page and set OnScroll parameters.
					</li>
					<li>
						To raise popup on exit intent, go to <a href="admin.php?page=ulp-settings">Settings</a> page and set OnExit parameters.
					</li>
					<li>
						To raise popup on user inactivity, go to <a href="admin.php?page=ulp-settings">Settings</a> page and set OnInactivity parameters.
					</li>
				</ol>
				<h3>How can I raise a different popups on different pages?</h3>
				<p>Go to post/page editor and play with "Layered Popups" meta box. You can set different popups for different posts/pages.</p>
				<h3>How can I attach a popup (or A/B campaign) to certain menu item?</h3>
				<p>
					Create custom link menu item with URL like that: <code>#ulp-OBJECT_ID</code>
					<br /><code>OBJECT_ID</code> is a popup ID taken form relevant column on <a href="admin.php?page=ulp">this page</a>, or A/B campaign ID taken form relevant column on <a href="admin.php?page=ulp-campaigns">this page</a>.
					<br />Look at video tutorial: <a target="_blank" href="http://www.youtube.com/watch?v=fSUpOVzMjT0">http://www.youtube.com/watch?v=fSUpOVzMjT0</a>.
				</p>
				<h3>How can I use popups "inline"?</h3>
				<p>
					It is easy, just insert the following shortcode into post/page content: <code>[ulp id="POPUP_ID"]</code>
					<br /><code>POPUP_ID</code> is a popup ID taken form relevant column on <a href="admin.php?page=ulp">this page</a>.
				</p>
				<h3>How can I add subscription form to popup?</h3>
				<p>
					First of all, please remember that subscription form consists of 2 mandatory elements ("e-mail" input field and "submit" button) and 3 optional elements ("name", "phone number" input fields and "message" text area).
					Each element has relevant shortcode:
					<br /><code>{subscription-email}</code> - mandatory
					<br /><code>{subscription-submit}</code> - mandatory
					<br /><code>{subscription-name}</code> - optional
					<br /><code>{subscription-phone}</code> - optional
					<br /><code>{subscription-message}</code> - optional
					<br />All you have to do is to insert shortcodes into popup layers.
				</p>
				<h3>Can I use custom fields on my subscription/contact form?</h3>
				<p>You can create custom fields (text fields, text areas and drop-down lists) and use them with your subscription/contact form.
				<ol>
					<li>Go to <a href="admin.php?page=ulp-settings&mode=ext">Advanced Settings</a> page and activate "Custom Fields" module.</li>
					<li>On popup editor page you can create your own custom fields and add them as shortcode into popup's layers.</li>
				</ol>
				</p>
				<h3>Can I use reCAPTCHA with my form?</h3>
				<p>You can use reCAPTCHA v.2.0 with your forms.
				<ol>
					<li>Go to <a href="admin.php?page=ulp-settings">Settings</a> page and set required parameters under "reCAPTCHA Settings" section.</li>
					<li>Set "reCAPTCHA is mandatory" checkbox on popup editor page.</li>
					<li>Create layer and insert shortcode <code>{recaptcha}</code> into its content.</li>
				</ol>
				Please remember, reCAPTCHA has fixed size () and is not resized automatically.
				</p>
				<h3>Can I submit user's data as a part of 3rd party form?</h3>
				<p>You can do that.
				<ol>
					<li>Go to <a href="admin.php?page=ulp-settings&mode=ext">Advanced Settings</a> page and activate HTML Form Integration module.</li>
					<li>At the bottom of popup editor page paste your HTML-form code and press "Connect Form" button.</li>
					<li>Associate popup's fields with 3rd party form fields.</li>
				</ol>
				</p>
				<h3>How can I organize 2-step opt-in process?</h3>
				<p>You can do it by creating 2 separate popups and open one popup from another.
					<br />Create POPUP1 and add link/button/image/etc. with <code>onclick</code> handler:
					<br /><code>onclick="return ulp_open('OBJECT2_ID');"</code>
					<br /><code>OBJECT2_ID</code> is a popup ID taken form relevant column on <a href="admin.php?page=ulp">this page</a>, or A/B campaign ID taken form relevant column on <a href="admin.php?page=ulp-campaigns">this page</a>.
					<br />Example: <code>&lt;a href="#" onclick="return ulp_open('OBJECT2_ID');"&gt;Yes! I want to subscribe!&lt;/a&gt;</code>
					<br />Click on this element will close POPUP1 and open OBJECT2.
				</p>
				<h3>How can I add "close" icon to popup?</h3>
				<p>
					You can add and customize "close" icon as you wish. Create new layer with content like that:
					<br /><code>&lt;a href="#" onclick="return ulp_self_close();"&gt;&lt;img src="http://url-to-my-wonderful-close-icon" alt=""&gt;&lt;/a&gt;</code>
					<br />The important part of this string is <code>onclick</code> handler: <code>onclick="return ulp_self_close();"</code>. It runs JavaScript
					function called <code>ulp_self_close()</code>, which closes popup.
				</p>
				<h3>How can I close event popup forever?</h3>
				<p>
					You can add the following link into layer content:
					<br /><code>&lt;a href="#" onclick="return ulp_close_forever();"&gt;Close Forever&lt;/a&gt;</code>
					<br />The important part of the this string is <code>onclick</code> handler: <code>onclick="return ulp_close_forever();"</code>. It runs JavaScript
					function called <code>ulp_close_forever()</code>, which closes popup and set cookie to prevent displaying popup on page load, on scrolling down and on exit intent.
				</p>
				<h3>How can I display link button?</h3>
				<p>
					The plugin has basic set of link buttons which can be used with popups. Example of the button:
					<span style="display: block; text-align: center; width: 180px; height: 40px;"><a href="#" class="ulp-link-button ulp-link-button-orange" style="color: #FFF; font-size: 14px;">Hi, I'm orange button.</a></span>
					Add the following HTML-code into layer content to display relevant link button:
					<br /><code>&lt;a href="#" class="ulp-link-button ulp-link-button-red"&gt;Hi, I'm red button.&lt;/a&gt;</code>
					<br /><code>&lt;a href="#" class="ulp-link-button ulp-link-button-blue"&gt;Hi, I'm blue button.&lt;/a&gt;</code>
					<br /><code>&lt;a href="#" class="ulp-link-button ulp-link-button-green"&gt;Hi, I'm green button.&lt;/a&gt;</code>
					<br /><code>&lt;a href="#" class="ulp-link-button ulp-link-button-yellow"&gt;Hi, I'm yellow button.&lt;/a&gt;</code>
					<br /><code>&lt;a href="#" class="ulp-link-button ulp-link-button-orange"&gt;Hi, I'm orange button.&lt;/a&gt;</code>
					<br /><code>&lt;a href="#" class="ulp-link-button ulp-link-button-pink"&gt;Hi, I'm pink button.&lt;/a&gt;</code>
					<br /><code>&lt;a href="#" class="ulp-link-button ulp-link-button-black"&gt;Hi, I'm black button.&lt;/a&gt;</code>
					<br /><strong>Important:</strong> link button inherits layer size and font settings.
				</p>
				<h3>I inserted &lt;IMG&gt; tag, but image is not responsive.</h3>
				<p>Make sure that image tag does not have <code>width</code> and <code>height</code> attributes.</p>
				<h3>Can I use image as submit button?</h3>
				<p>Yes, of course: <code>&lt;a href="#" onclick="return ulp_subscribe(this);">&lt;img src="..." />&lt;/a></code></p>