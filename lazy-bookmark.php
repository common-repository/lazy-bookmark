<?php
/*
Plugin Name: Lazy Bookmark
Plugin URI: http://wordpress.org/extend/plugins/lazy-bookmark/
Description: This plugin collects the meta data of any desired sites based on submitted URL. These metadata including URL, IP Address, Title, Author(s), Keywords, and Description.
Author: Freelynx
Version: 0.7
Author URI: http://codeindesign.com/id/about/
*/

/*  

Copyright 2010  Freelynx (email : freelynx@codeindesign.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

global $lazbook_domain_version;
global $lazbook_page_version;
$lazbook_domain_version = "0.5";  /* database domain version */
$lazbook_page_version = "0.5";    /* database page version */

if(is_admin()) {
  $webregurl = get_bloginfo('url').'/wp-admin/options-general.php?page=lazy-bookmark/lazy-bookmark.php';
} else {
  $dummy = explode('?',lbm_curPageURL());
  $webregurl = $dummy[0].'?dum=dum';
}

define('LAZBOOK_URL',$webregurl);

//include_once(dirname(__FILE__).'/validation.php');
require_once(ABSPATH . 'wp-content/plugins/lazy-bookmark/validation.php');

/* Plugin activation */
register_activation_hook(__FILE__,'lbm_install');
function lbm_install() {
  lbm_create_table('lazbook_domain');
  lbm_create_table('lazbook_page');
  
  add_option("lazbook_metadata","title:::::");
  add_option("lazbook_record","domain");
  add_option("lazbook_nourl","10");
  
}

function lbm_create_table($table) {
  global $wpdb;
  global $lazbook_domain_version;
  global $lazbook_page_version;
  
  $table_name = $wpdb->prefix . $table;
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
     
    $sql = "CREATE TABLE `".$table_name."` (
      `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
      `url` VARCHAR( 255 ) NOT NULL ,
      `ip` VARCHAR( 15 ) NOT NULL ,
      `title` VARCHAR( 255 ) NOT NULL ,
      `author` VARCHAR( 255 ) NOT NULL ,
      `keywords` VARCHAR( 255 ) NOT NULL ,
      `description` TEXT NOT NULL,
      `date` TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
      `confirmed` ENUM('0','1') NOT NULL
    )";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    add_option($table."_version", (($table=='lazbook_domain')?$lazbook_domain_version:$lazbook_page_version));
   }
}

add_action("admin_menu","lbm_add_option_pages");
add_action("wp_head","lbm_js_frontend");
function lbm_add_option_pages() {
	if (function_exists('lbm_option_page')) {
		$plugin_page = add_options_page('Web Reg Setting', 'Lazy Bookmark', 8, __FILE__, 'lbm_option_page');
		add_action( 'admin_head-'.$plugin_page, 'lbm_admin_header' );
		if($_COOKIE['lbm_widgets'] == '') setcookie('lbm_widgets','dum:donate:faq:frontend:setting:stat:filter:');
	}		
}

function lbm_admin_header() {?>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
  <script type="text/javascript" src="<?=get_bloginfo('url');?>/wp-content/plugins/lazy-bookmark/cookie.js"></script>
  <script type="text/javascript">
    $(document).ready(function(){
      $('h3.hndle').click(function(){
        $(this).toggleClass('on').next().slideToggle();
        var cookie = 'dum:';
        $('h3.on').each(function(){
          if($('h3.hndle').hasClass('on')) cookie += $(this).attr('id')+':';
          else cookie += ':'; 
        });
        setCookie('lbm_widgets',cookie);
      });
      $('form[name="lbm_the_table"]').submit(function() {
        var param = $(this).serialize();
        var arrpar = param.split('&');
        var wrAction;
        var wrEntries = new Array();
        var arrUrl = new Array();
        var j = 0;
        for(var i=0;i<arrpar.length;i++) {
          var variable = arrpar[i].split('=')[0];
          var value = arrpar[i].split('=')[1];
          if(variable == 'lbm_action') { 
            wrAction = value;
          }
          else if(variable.split('cb_').length == 2){
            var id = variable.split('cb_')[1];
            if(id != 'all') {
              wrEntries[j] = id;
              arrUrl[j] = $('a#lbm_urlid_'+id).attr('href');
              j++;
            }
          }
        } 
        var checkbox = false;
        $('input.lbm_chkbx:checked').each(function(){
          checkbox = true;
        });     
        if(checkbox) {
          var msg = 'You are about to '+((wrAction=='del')?'remove':'confirm')+' the following URL(s):\n'+arrUrl.join(',\n');
          var cfm = confirm(msg);
          if(cfm) {
            window.location = '<?=LAZBOOK_URL;?>'+'&id='+wrEntries.join(',')+'&'+wrAction+'=true';
          } else return false;
        } else {
          alert('Please checked or tick one or more URL(s) first.');
        }
      });
      $('input[name="lbm_cb_all"]').click(function(){
        var chkbx = '.lbm_display_list tbody input[type="checkbox"]';
        if($(this).attr('checked')) {
          $(chkbx).each(function(){
            $(this).attr('checked','checked');
          });
        } else {
          $(chkbx).each(function(){
            $(this).removeAttr('checked');
          });
        }
      });
      $('select[name="lbm_action"]').change(function(){
        var value = $(this).val();
        $('option[value="'+value+'"]').attr('selected','selected');
      });
    });
  </script>
  <?lbm_js_frontend();?>
  <link rel='stylesheet' href='<?=get_bloginfo('url');?>/wp-content/plugins/lazy-bookmark/style.css' type='text/css' media='all' />

<?}

/* Display form backend */

function lbm_option_page() {
  global $update_alert;
  if($_GET['submit'] == 'Save Settings') {
    $metadata  = $_GET['lbm_title'] . ':';
    $metadata .= $_GET['lbm_ip'] . ':';
    $metadata .= $_GET['lbm_authors'] . ':';
    $metadata .= $_GET['lbm_keywords'] . ':';
    $metadata .= $_GET['lbm_description'] . ':';
    $metadata .= $_GET['lbm_date'];
    update_option("lazbook_metadata",$metadata);
    update_option("lazbook_record",$_GET['lbm_record']);
    update_option("lazbook_nourl",$_GET['lbm_nourl']);
  }
  if($_GET['del'] || $_GET['confirm']) {
    $arrid = explode(',',$_GET['id']);
    for($i=0;$i<count($arrid);$i++) {
      if($_GET['del']) $status = lbm_delete_url($arrid[$i]);
      if($_GET['confirm']) $status = lbm_confirm_url($arrid[$i]);
    }
  } else $status = lbm_submitting();
  
  /* cookie handler */
  $arron = explode(':',$_COOKIE['lbm_widgets']);
  $donate = false;
  $faq = false;
  $frontend = false;
  $setting = false;
  $stat = false;
  for($i=0;$i<=count($arron);$i++) {
    if($arron[$i] == 'donate') $donate = true;
    if($arron[$i] == 'faq') $faq = true;
    if($arron[$i] == 'frontend') $frontend = true;
    if($arron[$i] == 'setting') $setting = true;
    if($arron[$i] == 'stat') $stat = true;
    if($arron[$i] == 'filter') $filter = true;
  }
?>

  <div class="wrap" >
    <div class="icon32" id="icon-options-general"><br></div>
    <h2><?php _e('Lazy Bookmark Setting Page'); ?></h2>
    <div class="metabox-holder">
      <div style="float: right;">
        <div class="postbox">
          <h3 class="hndle <?=($donate)?'on':'';?>" id="donate">Like The Plugin? You Know What To Do</h3>
          <div class="inside" style="text-align: center; padding: 10px 0px;<?=(!$donate)?'display:none;':'';?>">
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
              <input type="hidden" name="cmd" value="_s-xclick">
              <input type="hidden" name="hosted_button_id" value="LTUB9HAHBXTHJ">
              <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
              <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          </div>
        </div>
        <div class="postbox" style="max-width: 330px;">
          <h3 class="hndle <?=($faq)?'on':'';?>" id="faq">FAQ</h3>
          <div class="inside" style="max-height: 400px; overflow: auto;<?=(!$faq)?'display:none;':'';?>">
            <ul style="margin: 10px 15px; list-style-type: square; padding: 0px 10px;">
              <li><p><b>What does this plugin do?</b></p>
                  <p>In a simple term: Records IP Address, Title, Authors, Keywords, and Description of a website / webpage.</p></li>
              <li><p><b>How can I use it?</b></p>
                  <p>Just type a URL address of a website or webpage, i.e. <code>http://yahoo.com</code> in the form next to this <b>faq</b> then press Submit. You'll find what this plugin actually do.</p></li>
              <li><p><b>Why on some website it didn't give the meta data information? they just go blank.</b></p>
                  <p>This is because the website you are trying to get the info is not allowing the plugin to get the content. Note that this is not a problem with the SEO of a given webpage, instead it's just the policy of the website that does not allow a php script to open the page directly.</p></li>
              <li><p><b>Can I use this form and the list on my frontend page?</b></p>
                  <p>Yes you can. See the left widget next to Donate widget for instruction how to embed it on your pages.</p></li>
              <li><p><b>Is it the same between</b> <code>http://example.com</code><b> and </b><code>http://example.com/abc</code><b>?</b></p>
                  <p>It depends. If you choose '<b>A domain</b>' on the Plugin Settings widget then the plugin will record the the frontpage of given url. So, if you inserted <code>http://example.com/abc</code>, it will store metadata of <code>http://example.com</code>. But If you choose '<b>A page</b>' then it will record the page's metadata of a given URL.</p></li>  
              <li><p><b>How about URL written in IP address, such as</b> <code>http://74.125.235.16</code> <b>?</b></p>
                  <p>The plugin will treat them differently. So if you insert <code>http://74.125.235.16</code> and <code>http://google.com</code> (both are the same host for Google Search Engine) to the form then the plugin will consider it as a different domain. It's not a bug. Just a limitation of the current version.</p></li>
              <li><p><b>How can I insert styles for the list?</b></p>
                  <p>Simply put, the listing table by default has embeded classes <code>widefat</code> and <code>lbm_display_list</code>, so you can add your css as you like in references to those classes, accordingly, and include them in your css file.</p></li>
              <li><p><b>What's the different between #C and #NC?</b></p>
                  <p>This plugin allow public user to participate to add the list (if you set the form available to public). However, all URL that have been submitted through frontend form will need to be confirmed by owner of the site via admin page. So. both #C and #NC are information how many confirmed and unconfirmed URL, respectively.</p></li>
              <li><p><b>Do I always need to write the protocol like</b> <code>http://</code><b>?</b></p>
                  <p>Absolutely! it's part of the valid URL.</p></li> 
              <li><p><b>In that case what kind of protocol that is allowed?</b></p>
                  <p><code>http</code>, <code>https</code>, and <code>ftp</code></p></li> 
              <li><p><b>I got a strange behaviour when I use this plugin on my frontend page. What's wrong?</b></p>
                  <p>It depends. But the first thing that you need to check is that the permalinks should not be set as a 'Default'. If this does not fix the issues then please <a href="http://codeindesign.com/id/about/">email</a> and let me know.</p></li> 
              <li><p><b>How about port number like</b> <code>http://example.com:8080</code><b>?</b></p>
                  <p>Forgive this plugin but it's not allowed. If you specified the port number then it will mark as invalid URL.</p></li> 
              <li><p><b>Can I choose which metadata to be displayed?</b></p>
                  <p>Yes. But keep in mind that this will only be affecting both of the table lists appearance on your frontend, synchronously.</p></li> 
              <li><p><b>Can I edit the url and its metadata?</b></p>
                  <p>Sorry, no.</p></li>
              <li><p><b>Can I filter or search the result?</b></p>
                  <p>Yes, finally!</p></li>
              <li><p><b>Is this plugin a crawler?</b></p>
                  <p>Nope. Not even remotely.</p></li>
              <li><p><b>Can I export the result as an XML or JSon?</b></p>
                  <p>No. But soon in the next release.</p></li>
              <li><p><b>This plugin is so lame!</b></p>
                  <p>Thank you and so are you, so please <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LTUB9HAHBXTHJ">donate</a>.</p></li>
              <li><p><b>I'm kidding. This plugin is awesome!</b></p>
                  <p>You know, you've just brighten up my day. Thanx You!</p></li>
            </ul>
          </div>
        </div>
        <div class="postbox">
          <h3 class="hndle <?=($stat)?'on':'';?>" id="stat">Statistics</h3>
          <div class="inside" style="text-align: center; padding: 10px;<?=(!$stat)?'display:none;':'';?>">
            <table class="widefat">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Table</th>
                  <th>#URL</th>
                  <th>#C</th>
                  <th>#NC</th>
                </tr>
              </thead>
              <tbody>
              <tr>
                <td><p>1</p></td>
                <td style="text-align: left;"><p>Domain</p></td>
                <td><p><b><?=lbm_statistics('domain');?></b></p></td>
                <td><p><b><?=lbm_statistics('domain',true);?></b></p></td>
                <td><p><b><?=lbm_statistics('domain',false);?></b></p></td>
              </tr>
              <tr>
                <td><p>2</p></td>
                <td style="text-align: left;"><p>Page</p></td>
                <td><p><b><?=lbm_statistics('page');?></b></p></td>
                <td><p><b><?=lbm_statistics('page',true);?></b></p></td>
                <td><p><b><?=lbm_statistics('page',false);?></b></p></td>
              </tr>
              </tbody>
            </table>
            <ul class="lbm_legend">
              <li><b>#C</b> : The number of confirmed URL</li>
              <li><b>#NC</b> : The number of unconfirmed URL</li>
            </ul>
          </div>
        </div>
        <div class="postbox">
          <h3 class="hndle <?=($filter)?'on':'';?>" id="filter">Filter & Search</h3>
          <div class="inside" style="text-align: center; padding: 10px;<?=(!$filter)?'display:none;':'';?>">
            <form action="options-general.php" method="GET">
            <input type="hidden" name="page" value="<?=$_GET["page"];?>" />
            <table class="widefat">
              <tbody>
              <tr>
                <th>Search</th>
                <td style="text-align: left;">
                  <?php lbm_filter_search();?>
                </td>
              </tr>
              <tr>
                <th>Filter</th>
                <td style="text-align: left;">
                  <select name="lbm_filter">
                    <option value=""  <?=(($_GET['lbm_filter'] == '')?'selected':'');?>>All</option>
                    <option value="1" <?=(($_GET['lbm_filter'] == '1')?'selected':'');?>>Confirmed</option>
                    <option value="0" <?=(($_GET['lbm_filter'] == '0')?'selected':'');?>>Unconfirmed</option>
                  </select>
                </td>
              </tr>
              </tbody>
              <tfoot>
                <td><input type="submit" value="Go!" class="button" /></td>
              </tfoot>
            </table>
            </form>
          </div>
        </div>      
      </div>
      <div style="margin-right: 350px;">
        <div class="postbox">
          <h3 class="hndle <?=($frontend)?'on':'';?>" id="frontend">Setting for Frontend Page</h3>
          <div class="inside" style="margin: 10px;<?=(!$frontend)?'display:none;':'';?>">
            <p style="line-height: 1.5em;">If you wish to use this <b>URL Submitted form</b> and <b>Listing Submitted URL</b> table to be visible to public users then use the following shortcodes in your page/post:</p>
            <table class="widefat lbm_table" style="clear: left;">
              <tbody>
                <tr>
                  <td><code>[lbm_form]</code></td><td>Displaying the form and processing the submitted URL based on table where you specify the <i><b>Collection of URL of?</b></i>.</td>
                </tr>
                <tr>
                  <td><code>[lbm_form_domain]</code></td><td>Displaying the form and processing the URL for domain.</td>
                </tr>
                <tr>
                  <td><code>[lbm_form_page]</code></td><td>Displaying the form and processing the URL for pages.</td>
                </tr>
                <tr>  
                  <td><code>[lbm_list]</code></td><td>Displaying the submitted URL based on table where you specify the <i><b>Collection of URL of?</b></i>.</td>
                </tr>
                <tr>
                  <td><code>[lbm_list_domain]</code></td><td>Displaying the submitted domain.</td>
                </tr>
                <tr>
                  <td><code>[lbm_list_page]</code></td><td>Displaying the submitted webpage.</td>
                </tr>
                <tr>
                  <td><code>[lbm_search]</code></td><td>Search the contents</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="postbox">
          <h3 class="hndle <?=($setting)?'on':'';?>" id="setting">Plugin Settings</h3>
          <div class="inside" style="margin: 10px;<?=(!$setting)?'display:none;':'';?>">
            <form action="options-general.php" method="GET">
              <input type="hidden" name="page" value="<?=$_GET["page"]?>" />  
              <table class="widefat" style="clear: left;">
                <head></thead>
                <tbody>
                <tr>
                  <th>1</th>
                  <th style="width: 300px;">Collection URL of?</th>
                  <td style="vertical-align: middle; padding: 10px;">
                    <select name="lbm_record">
                      <option value="domain" <?=(get_option('lazbook_record')=='domain')?'selected':'';?>>A Domain&nbsp;&nbsp;&nbsp;</option>
                      <option value="page" <?=(get_option('lazbook_record')=='page')?'selected':'';?>>A Page</option>
                    </select>
                  </td>
                  <td style="vertical-align: middle;">
                    <a href="javascript: void(0);" onclick="$(this).parent().parent().next().toggle()">What's this?</a>
                  </td>
                </tr>
                <tr style="display: none;">
                  <td colspan="4"><p style="color: grey;">Which option would you like to choose? 'A domain' would only record the hostname and it's metadata. 'A Page' would record the page and its metadata. This also means which table would you like to use for the storing?</p></td>
                </tr>
                <tr>
                  <th>2</th>
                  <th style="">Metadata to be displayed?</th>
                  <td style="vertical-align: middle; padding: 10px;">
                    <?php
                      $getmeta = explode(':',get_option('lazbook_metadata')); 
                    ?>
                    <ul>
                      <li><input type="checkbox" name="lbm_title" value="title" <?=($getmeta[0]=='title')?'checked':'';?> />&nbsp;Title</li>
                      <li><input type="checkbox" name="lbm_ip" value="ip" <?=($getmeta[1]=='ip')?'checked':'';?> />&nbsp;IP Address</li>
                      <li><input type="checkbox" name="lbm_authors" value="authors" <?=($getmeta[2]=='authors')?'checked':'';?> />&nbsp;Author(s)</li>
                      <li><input type="checkbox" name="lbm_keywords" value="keywords" <?=($getmeta[3]=='keywords')?'checked':'';?> />&nbsp;Keywords</li>
                      <li><input type="checkbox" name="lbm_description" value="description" <?=($getmeta[4]=='description')?'checked':'';?> />&nbsp;Description</li>
                      <li><input type="checkbox" name="lbm_date" value="date" <?=($getmeta[5]=='date')?'checked':'';?> />&nbsp;Date</li>
                    </ul>
                  </td>
                  <td style="vertical-align: middle;">
                    <a href="javascript: void(0);" onclick="$(this).parent().parent().next().toggle()">What's this?</a>
                  </td>
                </tr>
                <tr style="display: none;">
                  <td colspan="4">
                    <p style="color: grey;">These metadata only affecting the frontend table in which the table is presented. These selection of metadata are applied for both collection of domain and page.</p></td>
                  </td>
                </tr>
                <tr>
                  <th>3</th>
                  <th style="">No of URL displayed on each page?</th>
                  <td style="vertical-align: middle;">
                    <div style="padding: 5px 0px;"><input type="text" value="<?=get_option('lazbook_nourl');?>" name="lbm_nourl" /></div>
                  </td>
                  <td style="vertical-align: middle;">
                    <a href="javascript: void(0);" onclick="$(this).parent().parent().next().toggle()">What's this?</a>
                  </td>
                </tr>
                <tr style="display: none;">
                  <td colspan="4"><p style="color: grey;">The number of URL that will appear in the table list.</p></td>
                </tr>
                </tbody>
              </table>
              <p><input type="submit" name="submit" class="button" value="Save Settings" /></p>
            </form>
          </div>
        </div>
        <div class="postbox">
          <h3 class="hndle">Submit url</h3>
          <div class="inside">
            <div style="text-align: center; margin-top: 20px;"><b><?=$status;?></b></div>
            <form action="options-general.php" method="GET">
              <?php lbm_get_form();?>
              <input type="hidden" name="page" value="<?=$_GET["page"]?>" />
            </form>
          </div>
        </div>
      </div>
    </div>
  <div style="clear: both;">
    <form action="javascript: void(0);" name="lbm_the_table">
      <?php lbm_display_list(get_option('lazbook_record')); ?>
    </form>
  </div>
  </div>
<?}

function lbm_get_form() {?>
  <div style="margin: 0px 10px; 0px 0px;">
    <p style="text-align: center;"><b>URL Address:</b></p>
    <p style="text-align: center;"><input type="text" name="u" value="<?=$GET['u'];?>" style="width: 100%; padding: 5px; text-align: center;" /></p>
    <p class="submit" style="text-align: center;"><input type="submit" name="submit" id="submit_comment" value="Submit &rsaquo;&rsaquo;" /></p>
  </div>
<?}

/* Display for frontend page */

function lbm_get_fp_form() {
  $status = lbm_submitting();
?>
  <div style="text-align: center;"><?=$status;?></div>
  <form action="<?=lbm_curPageURL(); ?>" method="GET">
    <?php lbm_get_form();?>
  </form>
<?}

add_shortcode('lbm_form','lbm_get_fp_form');

function lbm_get_fp_form_domain() {
  $status = lbm_submitting('domain');
?>
  <div style="text-align: center;"><?=$status;?></div>
  <form action="<?=lbm_curPageURL(); ?>" method="GET">
    <?php lbm_get_form();?>
  </form>
<?}

add_shortcode('lbm_form_domain','lbm_get_fp_form_domain');

function lbm_get_fp_form_page() {
  $status = lbm_submitting('page');
?>
  <div style="text-align: center;"><?=$status;?></div>
  <form action="<?=lbm_curPageURL(); ?>" method="GET">
    <?php lbm_get_form();?>
  </form>
<?}

add_shortcode('lbm_form_page','lbm_get_fp_form_page');

function lbm_filter_search() {?>
  <input type="text" value="<?=$_GET['wrq'];?>" name="wrq" style="width: 100%;" />
<?}

function lbm_get_filter_search() {?>
  <form action="<?=lbm_curPageURL(); ?>" method="GET">
    <?php lbm_filter_search();?>
    <input type="submit" value="Go!" class="button" />
  </form>
<?}

add_shortcode('lbm_search', 'lbm_get_filter_search');

function lbm_submitting($table = '') {
  global $wpdb;
  
  $url = trim($_GET["u"]);
  if($url == '') return '';
  else {
    $arrurl = explode('//',$url);
    $arrurl2 = explode('/',$arrurl[1]);
    $cleanurl = $arrurl[0].'//'.$arrurl2[0];
    $valid = new validation();
    $valid->setValue($cleanurl);
    if($valid->url()) {  /* check if the given url is valid */
      $ip = gethostbyname($arrurl2[0]);
      if(!$ip || ($ip == '') || ($valid->ipv4($ip) <> '1')) {
        return "<span style='color: red;'>Domain or URL <b><span style='color: grey;'>".$cleanurl."</span></b> might not active or you are working offline</span>";
      }
    } else {
      if($valid->ipv4($arrurl2[0])) $ip = $arrurl2[0];
      else return "<span style='color: red;'>Invalid URL!</span>";
    }
    
    if((get_option('lazbook_record') == 'page') || ($table == 'page')) {
      $cleanurl = $url;
      $orquery = '';
    } else {
      //$orquery = "OR `ip`='".$ip."'";
      $orquery = '';
    }
    $table = $wpdb->prefix."lazbook_".(($table == '')?get_option('lazbook_record'):$table);
    /* check if url exists */
    $select = "SELECT COUNT(*) FROM `".$table."` WHERE `url` LIKE '".$cleanurl."%' ".$orquery.";";
    $exists = $wpdb->get_var($select);
    if($exists <> 0) return "<span style='color: red;'>URL or domain <b><span style='color: grey;'>".$cleanurl."</span></b> exsits!</span>";
    else {
      $page = @file_get_contents($cleanurl);
      $arrtitle  = explode("</title>",$page);
      $arrtitle1 = explode('<title',$arrtitle[0]);
      $arrtitle2 = explode('>',$arrtitle1[1]);
      $metapage = @get_meta_tags($cleanurl);
      $insert = "INSERT INTO `".$table."` (id, url, ip, title, author, keywords, description, confirmed) " .
                "VALUES (
                  NULL, 
                  '".$cleanurl."',
                  '".$ip."',
                  '".htmlspecialchars(addslashes(trim($arrtitle2[1])))."',
                  '".htmlspecialchars(addslashes(trim($metapage['author'])))."',
                  '".htmlspecialchars(addslashes(trim($metapage['keywords'])))."',
                  '".htmlspecialchars(addslashes(trim($metapage['description'])))."',
                  '".((is_admin())?'1':'0')."');";

      $results = $wpdb->query( $insert );
      return "<span style='color: blue;'>Submitting URL <b><span style='color: grey;'>".$cleanurl."</span></b> Successfull</span>";
    }    
  }
}

function lbm_display_list($table = '') {
  global $wpdb;
  $therow = '';
  
  /* Get Content */
  $seq = $_GET['seq'];
  if($seq != '') {
    if(($_GET['sort'] == '') || ($_GET['sort'] == 'asc')) $sort = 'ASC';
    else $sort = 'DESC';
    $order = 'ORDER BY `'.$seq.'` '.$sort;
    $sort = ($sort == 'ASC')? 'desc' : 'asc';
  } else $order = 'ORDER BY `date` DESC';
  $nourl = get_option('lazbook_nourl');
  $page = $_GET['wrp'];
  $fpage = $page*get_option('lazbook_nourl') - get_option('lazbook_nourl');
  switch($page) {
    case '':
    case '1':
      $limit = 'LIMIT 0,'.$nourl;
    break;
    default:
      $limit = 'LIMIT '.$fpage.','.$nourl;
    break;
  }
  
  $where = lbm_get_where();
  $table_name = $wpdb->prefix."lazbook_".(($table == '')?get_option('lazbook_record'):$table);
  $select = "SELECT * FROM `".$table_name."` ".$where." ".$order." ".$limit.";";
  $result = $wpdb->get_results($select,ARRAY_A);
  $meta = explode(':',get_option('lazbook_metadata'));
  $i = ($fpage <= 0) ? 1 : ($fpage + 1);
  foreach ($result as $row) {
    $therow .= '<tr class="'.(($row['confirmed'] == '0')?'unconf':'').'">';
    $therow .= (is_admin())?'<td style="text-align: center;"><input type="checkbox" class="lbm_chkbx" name="lbm_cb_'.$row['id'].'" style="margin-top: 4px;" /></td>':'';
    $therow .= '<td style="text-align: center;">'.$i.'</td>';
    $therow .= '<td><a href="'.$row['url'].'" id="lbm_urlid_'.$row['id'].'">'.$row['url'].'</a></td>';
    
    $therow .= ((!$meta[0]=='') || is_admin())?'<td>'.htmlspecialchars_decode($row['title']).'</td>':'';
    $therow .= ((!$meta[1]=='') || is_admin())?'<td>'.$row['ip'].'</td>':'';
    $therow .= ((!$meta[2]=='') || is_admin())?'<td>'.htmlspecialchars_decode($row['author']).'</td>':'';
    $therow .= ((!$meta[3]=='') || is_admin())?'<td>'.htmlspecialchars_decode($row['keywords']).'</td>':'';
    $therow .= ((!$meta[4]=='') || is_admin())?'<td>'.htmlspecialchars_decode($row['description']).'</td>':'';
    $therow .= ((!$meta[5]=='') || is_admin())?"<td>".$row['date']."</td>":'';
    
    $therow .= (is_admin())?'<td style="text-align: center;"><a href="javascript: if(confirm(\'Delete '.$row['url'].'?\')) window.location = \''.LAZBOOK_URL.'&id='.$row['id'].'&del=true\'; else void(0);" style="color: red;">Remove</a><br />'.(($row['confirmed'] == '1')?"Confirmed":"<a href=\"javascript: if(confirm('Confirm ".$row['url']."?')) window.location = '".LAZBOOK_URL."&id=".$row['id']."&confirm=true'; else void(0);\">Confirm?</a>").'</td>':'';
    $therow .= '</tr>';
    $i++;
  }
  
  $thetable .= '<table class="widefat lbm_display_list" id="all-plugins-table">';
  $thetable .= '<thead><tr>';
  $thetable .= (is_admin())?'<th style="text-align: center;"><input type="checkbox" class="lbm_chkbx" name="lbm_cb_all" style="margin: 0;" /></th>':'';
  $thetable .= '<th><a href="'.LAZBOOK_URL.'&seq=id&sort='.$sort.'">No</a></th>';
  $thetable .= '<th><a href="'.LAZBOOK_URL.'&seq=url&sort='.$sort.'">URL</a></th>';
  $thetable .= ((!$meta[0]=='') || is_admin())?'<th><a href="'.LAZBOOK_URL.'&seq=Title&sort='.$sort.'">Title</a></th>':'';
  $thetable .= ((!$meta[1]=='') || is_admin())?'<th><a href="'.LAZBOOK_URL.'&seq=ip&sort='.$sort.'">IP</a></th>':'';
  $thetable .= ((!$meta[2]=='') || is_admin())?'<th><a href="'.LAZBOOK_URL.'&seq=author&sort='.$sort.'">Author</a></th>':'';
  $thetable .= ((!$meta[3]=='') || is_admin())?'<th><a href="'.LAZBOOK_URL.'&seq=keywords&sort='.$sort.'">Keywords</a></th>':'';
  $thetable .= ((!$meta[4]=='') || is_admin())?'<th><a href="'.LAZBOOK_URL.'&seq=description&sort='.$sort.'">Description</a></th>':'';
  $thetable .= ((!$meta[5]=='') || is_admin())?'<th><a href="'.LAZBOOK_URL.'&seq=date&sort='.$sort.'">Submission Date</a></th>':'';
  $thetable .= (is_admin())?'<th style="text-align: center;">Action</th>':'';
  $thetable .= '</tr>';
  $thetable .= '</thead>';
  $thetable .= '<tbody>'.$therow.'</tbody>';
  $thetable .= '</table>';
  
  echo '<div style="line-height: 3em;">';
  echo '<div style="float: left;">';
        (is_admin())?lbm_add_action():false;
  echo '</div>';
  echo '<div style="text-align: right; margin-left: 300px;">'.lbm_pagination((($table == '')?get_option('lazbook_record'):$table)).'</div>';
  echo '</div>';
  echo $thetable;
  echo '<div style="line-height: 3em;">';
  echo '<div style="float: left;">';
        (is_admin())?lbm_add_action():false;
  echo '</div>';
  echo '<div style="float: right; margin-left: 300px;">'.lbm_pagination((($table == '')?get_option('lazbook_record'):$table)).'</div>';
  echo '</div>';
  
}

add_shortcode('lbm_list','lbm_display_list');

function lbm_add_action() {?>
  <select style="width: 150px;" name="lbm_action">
    <option value="">Select Action</option>
    <option value="confirm">Confirm</option>
    <option value="del">Remove</option>
  </select>
  <input type="submit" class="button" value="Apply" />
<?}

function lbm_get_where() {
  if(is_admin()) {
    $filter = $_GET['lbm_filter'];
    switch($filter) {
      case '':
        $conf = '';
      break;
      case '1':
        $conf = "`confirmed`='1'";
      break;
      case '0':
        $conf = "`confirmed`='0'";
      break;
    }
  } else $conf = "`confirmed`='1'";
  
  $q = htmlspecialchars(addslashes(trim($_GET['wrq'])));
  if($q != ''){
    $q = "(`url` LIKE '%".$q."%' OR 
          `title` LIKE '%".$q."%' OR 
          `ip` LIKE '%".$q."%' OR 
          `author` LIKE '%".$q."%' OR 
          `keywords` LIKE '%".$q."%' OR
          `description` LIKE '%".$q."%' OR 
          `date` LIKE '%".$q."%')";
  } else $q = '';
  
  if($q != '' && $conf != '') return "WHERE ".$q." AND ".$conf;
  else if($q != '' && $conf == '') return "WHERE ".$q;
  else if($q == '' && $conf != '') return "WHERE ".$conf;
  else return "";
}

function lbm_display_domain() {
  lbm_display_list('domain');
} 

add_shortcode('lbm_list_domain','lbm_display_domain');

function lbm_display_page() {
  lbm_display_list('page');
} 

add_shortcode('lbm_list_page','lbm_display_page');

function lbm_pagination($table) {
  global $wpdb;
  //$where  = (!is_admin())?"WHERE `confirmed` = '1'":"";
  $where = lbm_get_where();
  $select = "SELECT COUNT(*) FROM `".$wpdb->prefix."lazbook_".$table."` ".$where.";";
  $result = $wpdb->get_var($select);
  if(($result <= get_option('lazbook_nourl')) || ($result == '')) $page = 1;
  else $page = $result/get_option('lazbook_nourl'); /* to get total page */
  
  $pages = 'Total URL: <b>'.$result.'</b>&nbsp;|&nbsp;';
  $pages .= 'Page: ';
  $pages .= '<select onchange="lbmGotoPage(this.value);">';
  
  $totalpage = ceil($page);
  for($i=1;$i<=$totalpage;$i++) {
    if($_GET['wrp'] == '') {
      if($i == 1) {
        //$pages .= '&nbsp;<b>1</b>&nbsp;';
        $pages .= '<option value="1" selected>1</option>';
      }
      else {
        //$pages .= '&nbsp;<a href="'.$pageurl[0].'&wrp='.$i.'">'.$i.'</a>&nbsp;';
        $pages .= '<option value="'.$i.'">'.$i.'</option>';
      }
    }
    else {   
      if($i == $_GET['wrp']) {
        //$pages .= '&nbsp;<b>'.$i.'</b>&nbsp;';
        $pages .= '<option value="'.$i.'" selected>'.$i.'</option>';
      }
      else {
        //$pages .= '&nbsp;<a href="'.$pageurl[0].'&wrp='.$i.'">'.$i.'</a>&nbsp;';
        $pages .= '<option value="'.$i.'">'.$i.'</option>';
      }
    }
  }
  $pages .= '</select>';
  return $pages;
}

function lbm_delete_url($id='') {
  global $wpdb;
  $id = ($id=='') ? htmlspecialchars($_GET['id']):$id;
  $delete = "DELETE FROM `".$wpdb->prefix."lazbook_".get_option('lazbook_record')."` WHERE id='".$id."'";
  $result = $wpdb->query($delete);
  if(!$result) return "<span style='color: red;'>Deleting URL Failed!</span>";
  else return "<span style='color: blue;'>Deleting URL Success!</span>";
}

function lbm_confirm_url($id='') {
  global $wpdb;
  $id = ($id=='')?htmlspecialchars($_GET['id']):$id;
  $update = "UPDATE  `".$wpdb->prefix."lazbook_".get_option('lazbook_record')."` SET  `confirmed` =  '1' WHERE  `id` ='".$id."';";
  $result = $wpdb->query($update);
  if(!$result) return "<span style='color: red;'>Confirming URL failed!</span>";
  else return "<span style='color: blue;'>URL has been confirmed successfully.</span>";
}

function lbm_statistics($table,$confirm = '') {
  global $wpdb;
  switch($confirm) {
    case '1':
      $where = ' WHERE `confirmed` = "1";';
    break;
    case '0':
      $where = ' WHERE `confirmed` = "0";';
    break;
    case '':
    default:
      $where = '';
    break;
  }
  $select = "SELECT COUNT(*) FROM `".$wpdb->prefix."lazbook_".$table."` ".$where.";";
  return $result = $wpdb->get_var($select);
}

/* Deactivate Plugin */
register_deactivation_hook(__FILE__, 'lbm_uninstall' );
function lbm_uninstall() {
  global $wpdb;
  
  $delete = "DROP TABLE `".$wpdb->prefix."lazbook_domain` ;";
  $results = $wpdb->query( $delete );
  $delete = "DROP TABLE `".$wpdb->prefix."lazbook_page` ;";
  $results = $wpdb->query( $delete );
  
  delete_option("lazbook_domain_version");
  delete_option("lazbook_page_version");
  delete_option("lazbook_metadata");
  delete_option("lazbook_record");
  delete_option("lazbook_nourl");
  setcookie ("lbm_widgets", "", time() - 3600);
}

function lbm_curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function lbm_js_frontend() {
  $pageurl = explode('?',lbm_curPageURL());
  if(count($pageurl) < 2) $pageurl[0] .= '?';
  else {
    $var = explode('wrp',$pageurl[1]);
    $pageurl[0] .= '?'.$var[0]. ((count($var) == 2) ? '' : '&');
  }
?>
  <script type="text/javascript">
    function lbmGotoPage(id) {
      window.location = '<?=$pageurl[0];?>wrp='+id;
    }
  </script>
<?}
?>