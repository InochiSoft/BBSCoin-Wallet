<?php
/***************************************************************************
 *
 *   BBSCoin Wallet Plugin
 *	 Author: Novian Agung
 *   
 *   Website: https://bbscoin.xyz
 *
 *   Dependency: NewPoints Plugin
 *
 ***************************************************************************/
 
/****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("global_intermediate", "newpoints_bbswallet_nav", 10);
$plugins->add_hook("newpoints_start", "newpoints_bbswallet", 10);

$wallet_user_name = "";
$wallet_password = "";
$wallet_address = "";

function newpoints_bbswallet_info()
{
	/**
	 * Array of information about the plugin.
	 * name: The name of the plugin
	 * description: Description of what the plugin does
	 * website: The website the plugin is maintained at (Optional)
	 * author: The name of the author of the plugin
	 * authorsite: The URL to the website of the author (Optional)
	 * version: The version number of the plugin
	 * guid: Unique ID issued by the MyBB Mods site for version checking
	 * compatibility: A CSV list of MyBB versions supported. Ex, "121,123", "12*". Wildcards supported.
	 */
	return array(
		"name"			=> "BBSCoin Wallet",
		"description"	=> "Plugin to create BBSCoin wallet.",
		"website"		=> "https://bbscoin.xyz",
		"author"		=> "BBSCoin Foundation",
		"authorsite"	=> "https://bbscoin.xyz",
		"version"		=> "1.0",
		"guid" 			=> "",
		"compatibility" => "*"
	);
}

function newpoints_bbswallet_uninstall()
{
	global $db;
	$collation = $db->build_create_table_collation();

	newpoints_remove_templates("'newpoints_bbswallet_links','newpoints_bbswallet_main'");
	newpoints_remove_templates("'newpoints_bbswallet_links','newpoints_bbswallet_wallet'");

	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	find_replace_templatesets("header_welcomeblock_member", '#'.preg_quote('{$newpoints_bbswallet_links}').'#', '', 0);
}

function newpoints_bbswallet_activate()
{
	global $db, $mybb;
	// add settings
	// take a look at inc/plugins/newpoints.php to know exactly what each parameter means
    
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
	newpoints_add_setting('newpoints_bbswallet_daemon_path', 'newpoints_bbswallet', 'BBSCoin Daemon Path', 'Your BBSCoin Daemon Path', 'text', $rootPath, 1);
	newpoints_add_setting('newpoints_bbswallet_wallet_path', 'newpoints_bbswallet', 'Wallet Container Path', 'Your Wallet Container Path', 'text', $rootPath, 2);
    
	rebuild_settings();
    
	newpoints_add_template('newpoints_bbswallet_links', '<li><a href="{$mybb->settings[\'bburl\']}/newpoints.php?action=bbswallet">{$lang->newpoints_bbswallet_usercp_nav_name}</a></li>');
	newpoints_add_template('newpoints_bbswallet_main', '<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->newpoints_bank}</title>
{$headerinclude}
{$javascript}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
<td valign="top">

<form action="newpoints.php" method="POST">
<input type="hidden" name="postcode" value="{$mybb->post_code}" />
<input type="hidden" name="action" value="bbswallet_create_wallet" />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" width="100%">
<tr>
<td class="thead" colspan="2"><strong>{$lang->newpoints_bbswallet_wallet}</strong></td>
</tr>
<tr>
<td class="trow1" width="100%" colspan="2"><strong>{$lang->newpoints_bbswallet_wallet_desc}</td>
</tr>
<tr>
<td class="trow2" width="50%"><strong>{$lang->newpoints_bbswallet_user_name}:</strong></td>
<td class="trow2" width="50%"><input type="text" name="wallet_user_name" id="wallet_user_name" value="{$mybb->user[\'username\']}" class="textbox" size="20" readonly /></td>
</tr>
<tr>
<td class="trow1" width="50%"><strong>{$lang->newpoints_bbswallet_new_password}:</strong><br /><span class="smalltext">{$lang->newpoints_bbswallet_new_password_desc}</span></td>
<td class="trow1" width="50%"><input type="text" name="wallet_password" value="" class="textbox" size="20" /></td>
</tr>
<tr>
<td class="tfoot" width="100%" colspan="2" align="center"><input type="submit" name="submit" value="{$lang->newpoints_submit}" class="button" /></td>
</tr>
</table>
</form>

<br />

</td>
</tr>
</table>
{$footer}
</body>
</html>');
    
    /* Novian Agung 02/17/2018 */
    newpoints_add_template('newpoints_bbswallet_wallet', '<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->newpoints_bank}</title>
{$headerinclude}
{$javascript}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
<td valign="top">

<form action="newpoints.php" method="POST">
<input type="hidden" name="postcode" value="{$mybb->post_code}" />
<input type="hidden" name="action" value="bbswallet_download_wallet" />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" width="100%">
<tr>
<td class="thead" colspan="2"><strong>{$lang->newpoints_bbswallet_wallet_info}</strong></td>
</tr>
<tr>
<td class="trow2" width="50%"><strong>{$lang->newpoints_bbswallet_user_name}:</strong></td>
<td class="trow2" width="50%"><input type="text" name="wallet_user_name" id="wallet_user_name" value="{$wallet_user_name}" class="textbox" size="20" readonly /></td>
</tr>
<tr>
<td class="trow1" width="50%"><strong>{$lang->newpoints_bbswallet_password}:</strong></td>
<td class="trow1" width="50%"><input type="text" name="wallet_password" value="{$wallet_password}" class="textbox" size="20" readonly /></td>
</tr>
<tr>
<td class="trow1" width="50%"><strong>{$lang->newpoints_bbswallet_wallet_address}:</strong></td>
<td class="trow1" width="50%"><textarea name="wallet_address" rows="2" cols="100" class="textarea" readonly >{$wallet_address}</textarea></td>
</tr>
<tr>
<td class="tfoot" width="100%" colspan="2" align="center"><input type="submit" name="submit" value="Download" class="button" /></td>
</tr>
</table>
</form>

<br />

</td>
</tr>
</table>
{$footer}
</body>
</html>');

	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	find_replace_templatesets("header_welcomeblock_member", '#'.preg_quote('{$searchlink}').'#', '{$newpoints_bbswallet_links}'.'{$searchlink}');

}

function newpoints_bbswallet_deactivate()
{
	global $db, $mybb;
	// delete settings
	newpoints_remove_settings("'newpoints_bbswallet_daemon_path','newpoints_bbswallet_wallet_path'");
	rebuild_settings();
}

function newpoints_bbswallet_nav()
{
    global $templates, $mybb, $newpoints_bbswallet_links, $lang;
	newpoints_lang_load('newpoints_bbswallet');
    eval("\$newpoints_bbswallet_links = \"".$templates->get('newpoints_bbswallet_links')."\";"); 
}

function newpoints_bbswallet($page)
{
	global $mybb, $db, $lang, $cache, $bbswallet_withdraw, $theme, $header, $templates, $plugins, $headerinclude, $footer, $options;

    global $wallet_user_name, $wallet_password, $wallet_address;
        
	if (!$mybb->user['uid']) {
		return;	
	}

	if ($mybb->input['action'] == "bbswallet")
	{
        $plugins->run_hooks("newpoints_bbswallet_page_start");

        eval("\$page = \"".$templates->get('newpoints_bbswallet_main')."\";");

        $plugins->run_hooks("newpoints_bbswallet_page_end");

    	output_page($page);
    } elseif ($mybb->input['action'] == "bbswallet_create_wallet") {
        verify_post_check($mybb->input['postcode']);
    	// load language files
    	newpoints_lang_load('newpoints_bbswallet');

        $plugins->run_hooks("newpoints_bbswallet_page_start");
        
        $old_user_name = $mybb->input['wallet_user_name'];
        $old_password = $mybb->input['wallet_password'];
        
        if (!empty($old_user_name) && !empty($old_password)){
            $wallet_user_name = getSlug($old_user_name);
            $wallet_password = getSlug($old_password);
            $wallet_address = createWallet($wallet_user_name, $wallet_password);
        }
        
        //$wallet_user_name, $wallet_password, $wallet_address
        eval("\$wallet_user_name = \"".$wallet_user_name."\";"); 
        eval("\$wallet_password = \"".$wallet_password."\";"); 
        eval("\$wallet_address = \"".$wallet_address."\";"); 
        eval("\$page = \"".$templates->get('newpoints_bbswallet_wallet')."\";");
        
        $plugins->run_hooks("newpoints_bbswallet_page_end");
        
    	output_page($page);
    } elseif ($mybb->input['action'] == "bbswallet_download_wallet") {
        verify_post_check($mybb->input['postcode']);
        
        $wallet_dir = $mybb->settings['newpoints_bbswallet_wallet_path'];
        $wallet_user_name = $mybb->input['wallet_user_name'];
        
        $wallet_name = $wallet_user_name . '.wallet';
        $address_name = $wallet_user_name . '.address';
        
        $wallet_file = $wallet_dir . "/" . $wallet_user_name . '.wallet';
        $address_file = $wallet_dir . "/" . $wallet_user_name . '.address';
        
        $zip_name = $wallet_user_name. ".zip";
        $zip_path = $wallet_dir . "/" . $zip_name;
        
        $zip = new ZipArchive();
        
        if(true === ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE))){
            $zip->open($zip_path, ZipArchive::CREATE);
            $zip->addFile($wallet_file, $wallet_name);
            $zip->addFile($address_file, $address_name);
            
            $zip->close();
        }
        
        header("Content-type: application/zip"); 
        header("Content-Disposition: attachment; filename=\"$zip_name\""); 
        header("Pragma: no-cache"); 
        header("Expires: 0"); 
        readfile($zip_path);
        
        exit;
    }
}

function getUrlContent($url, $data_string) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'bbscoin');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $data;
}

function getSlug($str, $delimiter = '_', $options = array()){
    $str = str_replace('"', "", $str);
    $str = str_replace('\"', "", $str);
    $str = str_replace("'", "", $str);
    $str = str_replace("039", "", $str);
    $str = str_replace("&", "", $str);
    $str = htmlentities($str); 
    
    $str = filter_var($str, FILTER_SANITIZE_STRING);
        
    $defaults = array(
        'delimiter' => $delimiter,
        'limit' => null,
        'lowercase' => true,
        'replacements' => array(),
    );
        
    $options = array_merge($defaults, $options);
    $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
    $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
    $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
    $str = substr($str, 0, ($options['limit'] ? $options['limit'] : strlen($str)) );
    $str = trim($str, $options['delimiter']);
    $str = str_ireplace("-amp-", "-", $str);
    
    return $options['lowercase'] ? strtolower($str) : $str;
}

function execInBackground($cmd) {
    $output = "";
    if (substr(php_uname(), 0, 7) == "Windows"){
        pclose(popen("start /B ". $cmd, "r")); 
    } else {
        $output = exec($cmd . " > /dev/null &");  
    }
    return $output;
}

function createWallet($name, $pass){
	global $mybb;
    $wallet_dir = $mybb->settings['newpoints_bbswallet_wallet_path'];
    $app_dir = $mybb->settings['newpoints_bbswallet_daemon_path'];
    $output = "";
    
    if (!empty($app_dir) && !empty($wallet_dir)){
        if (is_dir($app_dir)) {
            if (!file_exists($wallet_dir) && !is_dir($wallet_dir)) {
                mkdir($wallet_dir);
            }
            
            $wallet_file = $wallet_dir . "/" . $name . '.wallet';
            $address_file = $wallet_dir . "/" . $name . '.address';
            
            $command = $app_dir . "/" . "simplewallet --generate-new-wallet '" . $wallet_dir . "/" . $name . "' --password '" . $pass . "'";
            
            if (!file_exists($wallet_file) && !file_exists($address_file)){
                //$command = $app_dir . "/" .  "simplewallet --generate-new-wallet '" . $wallet_dir . $name . "' --password '" . $pass . "'";
                $output = execInBackground($command);
                
                sleep(3);
                
                if (file_exists($address_file)){
                    $output = file_get_contents($address_file, false);
                }
            }
            
            if (file_exists($address_file)){
                $output = file_get_contents($address_file, false);
            }
            
        }
    }
    
    return $output;
}
    