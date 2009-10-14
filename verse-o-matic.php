<?php
/*
Plugin Name: Verse-O-Matic
Plugin URI: http://butlerblog.com/verse-o-matic/
Description: Displays a single random verse. Verses can be added and edited through the Wordpress admin.  To manage your settings, there is a <a href="options-general.php?page=verse-o-matic.php">'Verse-O-Matic' tab</a> under the 'Settings' tab.  <a href="http://butlerblog.com/verse-o-matic/">Click here</a> for usage instructions.
Version: 4.1.0
Author: Chad Butler
Author URI: http://butlerblog.com/

    
	Copyright (c) 2009  Chad Butler (email : chad@butlerblog.com)

    
	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    For full details of the license, visit http://www.gnu.org/licenses/
*/



// NOT a good idea to change these.
global $wpdb;
define("WP_VOM_VERSES", $wpdb->prefix."vom");
define("WP_VOM_VERSION", "4.1.0");

// this is the actual verse-o-matic
function verse_o_matic()
{
	global $wpdb;
	
	//Get user defined variables
	$vom_options_arr   = get_option('vom_options'); //get the options array from settings table
	$turnOnAltVersions = $vom_options_arr[0]; //get the setting to turn on links to alternate versions
	$limitAltBarSize   = $vom_options_arr[1]; //get the setting to limit the alt bar to 4 or not
	$displayMethod     = $vom_options_arr[2]; //random, daily, daily specific, static specific	
	$vom_staticID      = $vom_options_arr[3]; //get the static id, if there is one
	
	$today = date('Y-m-d');
	
	//Get the verse based on display
	switch ($displayMethod) {
	
	case "daily":
		$sql = "select * from ".WP_VOM_VERSES." where visible='yes' and date='{$today}'";
		$verseArr = $wpdb->get_row($sql, ARRAY_N);
		
		if ( empty($verseArr) ) { 
			$sql = "select * from ".WP_VOM_VERSES." where visible='yes' order by rand() limit 1";
			$newVerseArr = $wpdb->get_row($sql, ARRAY_N);

			$sql = "update ".WP_VOM_VERSES." set date='{$today}' where vomID={$newVerseArr[0]}"; 
			$wpdb->query($sql);

			$verseArr = $newVerseArr;
		}	
		break;
	
	case "daily_specific":	
		$sql = "select * from ".WP_VOM_VERSES." where visible='yes' and date='{$today}'";
		$verseArr = $wpdb->get_row($sql, ARRAY_N);
		
		// This sets a random verse for the day if you are set for daily specific,
		// but if there is no verse set with today's date you get blank output.
		if ( empty($verseArr) ) { 
			$sql = "select * from ".WP_VOM_VERSES." where visible='yes' order by rand() limit 1";
			$newVerseArr = $wpdb->get_row($sql, ARRAY_N);

			$sql = "update ".WP_VOM_VERSES." set date='{$today}' where vomID={$newVerseArr[0]}"; 
			$wpdb->query($sql);

			$verseArr = $newVerseArr;
		}	
		break;
		
	case "static_specific":
		$sql = "select * from ".WP_VOM_VERSES." where visible='yes' and vomID='{$vom_staticID}'";
		$verseArr = $wpdb->get_row($sql, ARRAY_N);
		break;
		
	case "random": //this is the default
		$sql = "select * from ".WP_VOM_VERSES." where visible='yes' order by rand() limit 1";
		$verseArr = $wpdb->get_row($sql, ARRAY_N);
		break;
	
	// The feeds employ the same process, differentiated in the rss function
	case "esv_votd":
	case "bg_votd":
		$verseArr = vom_get_rss($displayMethod);
		break;
	
	} //end select display method
	
	//get the size of the array
	//$verseArrCount = count($verseArr);
	
	//put array contents into variables
	$verseVrsn = $verseArr[1]; //$verseVrsn => Translation version i.e. ESV or KJV
	$verseBook = $verseArr[2]; //$verseBook => The book i.e. Genesis, Matthew
	$verseChap = $verseArr[3]; //$verseChap => The chapter
	$verseVrse = $verseArr[4]; //$verseVrse => The verse
	$verseText = $verseArr[5]; //$verseText => The actual verse
	$verseLink = $verseArr[6]; //$verseLink => link for the verse, if any

	//build the full verse reference, i.e. John 3:16
	$verseRef = "$verseBook $verseChap:$verseVrse";
	
	//for the translation bar.  use caution if editing this
	$bibleGateway = "http://www.biblegateway.com/bible?passage=$verseRef&version=";
	
	//different output based on whether there is a link or not.
	if (empty($verseLink)) {
		$verseOutput = "\n$verseText - $verseRef $verseVrsn\n";
	} else {
		$verseOutput = "\n<a href=\"$verseLink\">\n$verseText - $verseRef $verseVrsn\n</a>\n";
	}

	//finally, give us nice, clean output
	$i=0;	
	echo "\n\n<!-- BEGIN Verse-O-Matic Plugin -->\n";
	echo "<!--       version ". WP_VOM_VERSION ."       -->\n";
	echo $verseOutput;
	if ($turnOnAltVersions == "true") {
	  echo "<br /><br />\n";
	  echo "<!-- alternate version lookup -->\n";
	  echo "<div align=\"center\">\n";
	  //build the alternate version bar
	  //based on whatever version the displayed verse is
	  if ($verseVrsn != ESV) {
        echo "  <a href=\"http://www.gnpcb.org/esv/search/?q=$verseRef\">ESV</a> | \n";
	    $i++;
	  }
	  if ($verseVrsn != NIV) {
        echo "  <a href=\"".$bibleGateway."31\">NIV</a> | \n";
	    $i++;
	  }
	  if ($verseVrsn != KJV) {
        echo "  <a href=\"".$bibleGateway."9\">KJV</a> | \n";
	    $i++;
	  }
	  if ($verseVrsn != AMP) {
        echo "  <a href=\"".$bibleGateway."45\">AMP</a>\n";
	    $i++;
	  }
	  if ($verseVrsn != NLT) {
	  	if ($limitAltBarSize == "true") {
	      if ($i < 4) {
            echo " | <a href=\"".$bibleGateway."51\">NLT</a>\n";
		  }
		} else {
		  echo " | <a href=\"".$bibleGateway."51\">NLT</a>\n";
		}
	  }
	  echo "<br /><br /><small>".WP_VOM_CREDIT."</small>\n";
	  echo "</div>\n";
	  echo "<!-- end alternate version lookup -->\n";
	}
	echo "\n<!-- /END Verse-O-Matic Plugin -->\n\n";

} // end of the verse-o-matic function


//DO NOT EDIT below this line


// the hooks...
add_action('admin_menu', 'vom_admin_menu');
register_activation_hook(__FILE__, 'vom_install');



// function to put the Verse-O-Matic tab in the Manage submenu
function vom_admin_menu()
{
	add_options_page('Verse-O-Matic', 'Verse-O-Matic', 8, basename(__FILE__), 'vom_admin');
}


//functions to widgetize the verse-o-matic
// if you don't have a widget compatible theme... 
// don't worry about this, vom is backward compatible
function widget_vomwidget_init() {
	
	function widget_vomwidget($args) {
		extract($args);
		
		$options = get_option('widget_vomwidget');
		$title = $options['title'];
			
		echo $before_widget;
			// Widget Title
			echo $before_title . $title . $after_title;
			// The Widget
			verse_o_matic();
		echo $after_widget;
	}
	
	function widget_vomwidget_control() {
	
		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_vomwidget');
		if ( !is_array($options) )
			$options = array('title'=>'', 'buttontext'=>__('Verse-O-Matic', 'widgets'));
		if ( $_POST['vomwidget-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['vomwidget-title']));
			update_option('widget_vomwidget', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		
		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.
		echo '<p style="text-align:right;"><label for="vomwidget-title">' . __('Title:') . ' <input style="width: 200px;" id="vomwidget-title" name="vomwidget-title" type="text" value="'.$title.'" /></label></p>';
		echo '<input type="hidden" id="vomwidget-submit" name="vomwidget-submit" value="1" />';
	}

	register_sidebar_widget('Verse-O-Matic', 'widget_vomwidget');
	register_widget_control('Verse-O-Matic', 'widget_vomwidget_control');
}

add_action('widgets_init', 'widget_vomwidget_init');
// end of widgetization



/********************************
ADMIN FUNCTIONS 
*********************************/

function vom_admin()
{
		global $wpdb;
		
		require_once('admin.php');
		$parent_file = 'options-general.php';
		
		// clear all globals. 
		$edit = $create = $save = $delete = false;
		
		// Request necessary variables, etc...
		$action     = !empty($_REQUEST['action'])        ? $_REQUEST['action'] : '';
		$display    = !empty($_REQUEST['display'])       ? $_REQUEST['display'] : '';
		$vomID      = !empty($_REQUEST['vomID'])         ? $_REQUEST['vomID'] : '';
		$version    = !empty($_REQUEST['vom_version'])   ? $_REQUEST['vom_version'] : '';
		$book       = !empty($_REQUEST['vom_book'])      ? $_REQUEST['vom_book'] : '';
		$chapter    = !empty($_REQUEST['vom_chapter'])   ? $_REQUEST['vom_chapter'] : '';
		$verse      = !empty($_REQUEST['vom_verse'])     ? $_REQUEST['vom_verse'] : '';
		$verseText  = !empty($_REQUEST['vom_verseText']) ? $_REQUEST['vom_verseText'] : '';
		$link       = !empty($_REQUEST['vom_link'])      ? $_REQUEST['vom_link'] : '';
		$visible    = !empty($_REQUEST['vom_visible'])   ? $_REQUEST['vom_visible'] : '';
		$date       = !empty($_REQUEST['vom_date'])      ? $_REQUEST['vom_date'] : '';
		$altSwitch  = !empty($_REQUEST['vom_switch'])    ? $_REQUEST['vom_switch'] : '';
		$altLimit   = !empty($_REQUEST['vom_limit'])     ? $_REQUEST['vom_limit'] : '';
		$vomDisplay = !empty($_REQUEST['vom_display'])   ? $_REQUEST['vom_display'] : '';
		$staticID   = !empty($_REQUEST['vom_staticID'])  ? $_REQUEST['vom_staticID'] : '';
		
		$sortby = $_REQUEST['sortby'];
		
		if (ini_get('magic_quotes_gpc')) {
			if($version)  {$version   = stripslashes($version);}
			if($book)     {$book      = stripslashes($book);}
			if($chapter)  {$chapter   = stripslashes($chapter);}
			if($verse)    {$verse     = stripslashes($verse);}
			if($verseText){$verseText = stripslashes($verseText);}
			if($link)     {$link      = stripslashes($link);}
			if($visible)  {$visible   = stripslashes($visible);}	
		}
		
		require_once('admin-header.php');
		
		
		/* Handle any data based on 'action':
		    * add
		    * update
		    * delete
		    * update_settings
		    * reset_daily
		*/
		
		switch ($action) {
		
		
		case "add":	
			$wpdb->insert(
  				WP_VOM_VERSES,
  				array( 'version'   => $version,
					   'book'      => $book,
					   'chapter'   => $chapter,
					   'verse'     => $verse,
					   'verseText' => $verseText,
					   'link'      => $link,
					   'visible'   => $visible,
					   'date'      => $date )
				);
				
			$result = $wpdb->get_results("select vomID from ".WP_VOM_VERSES."
				where verseText='" . mysql_escape_string($verseText)."' 
				and book='".mysql_escape_string($book)."' 
				and visible='".mysql_escape_string($visible)."' 
				limit 1");
			
			if (empty($result) || empty($result[0]->vomID)) {?>
				<div class="error"><p><strong><?php _e('Failure:'); ?></strong> Verse-O-Matic <?php _e('experienced and error and nothing was inserted.'); ?></p></div>
				<?php
			} else {?>
				<div id="message" class="updated fade"><p>Verse-O-Matic <?php _e('successfully added'); echo $book." ".$chapter.":".$verse; _e('to the database.'); ?></p></div>
				<?php
			}
			break;
		  
		
		case "update":	
			
			if (empty($vomID)) {?>
				<div class="error"><p><strong><?php _e('Failure:');?></strong> <?php _e('No verse ID.  Giving up...'); ?></p></div>
				<?php		
			} else {
					
				$wpdb->update(
  				WP_VOM_VERSES,
  				array( 'version'   => $version,
					   'book'      => $book,
					   'chapter'   => $chapter,
					   'verse'     => $verse,
					   'verseText' => $verseText,
					   'link'      => $link,
					   'visible'   => $visible,
					   'date'      => $date ),
				array( 'vomID' => $vomID )
				);
					
				$result = $wpdb->get_results("select vomID from ".WP_VOM_VERSES."
					where verseText='" . mysql_escape_string($verseText)."' 
					and book='".mysql_escape_string($book)."' 
					and visible='".mysql_escape_string($visible)."' 
					limit 1");
				
				if (empty($result) || empty($result[0]->vomID)) {
					?>
					<div class="error"><p><strong><?php _e('Failure:');?></strong> Verse-O-Matic <?php _e('was unable to edit the verse.  Try again?');?></p></div>
					<?php
				} else {
					?>
					<div id="message" class="updated fade"><p>Verse-O-Matic <?php _e('updated'); echo $book." ".$chapter.":".$verse." "; _e('successfully!');?></p></div>
					<?php
				}		
			}
			break;
		  
		
		case "delete":
		
			if (empty($vomID)) {
				?>
				<div class="error"><p><strong><?php _e('Failure:');?></strong> <?php _e('No verse ID given. Nothing was deleted.');?></p></div>
				<?php			
			} else {
				$sql = "delete from ".WP_VOM_VERSES." where vomID = '".mysql_escape_string($vomID)."'";
				$wpdb->get_results($sql);
				
				$sql = "select vomID from ".WP_VOM_VERSES." where vomID = '".mysql_escape_string($vomID)."'";
				$result = $wpdb->get_results($sql);
				
				if (empty($result) || empty($result[0]->vomID)) {
					?>
					<div id="message" class="updated fade"><p><strong><?php echo $book." ".$chapter.":".$verse;?></strong> <?php _e('deleted successfully');?></p></div>
					<?php
				} else {
					?>
					<div class="error"><p><strong><?php _e('Failure:');?></strong> <?php _e('Nothing was successfully deletd.');?></p></div>
					<?php
				}		
			}
			break;
		
		  
		case("update_settings"):
			
			$vom_options_arr = array($altSwitch,$altLimit,$vomDisplay,$staticID);
			update_option('vom_options',$vom_options_arr,'','yes');
			
			
			?>
			<div id="message" class="updated fade"><p>Verse-O-Matic <?php _e('settings updated!');?></p></div>
			<?php	
			break;
			
		
		case("reset_daily"):
		
			$sql = "update ".WP_VOM_VERSES." set date=null";
			$wpdb->query($sql);?>
			
			<div id="message" class="updated fade"><p><?php _e('Daily random verse successfully reset!');?></p></div>
			<?php
		
		} // end of handling data
		
		
		
		// Begin functions
		
		
		//function to display the edit form
		function vom_edit_form($mode='add', $vomID=false)
		{
			global $wpdb;
			$data = false;
			
			if ($vomID !== false) {
				$data = $wpdb->get_results("select * from ".WP_VOM_VERSES." where vomID='".mysql_escape_string($vomID)."' limit 1");
				if (empty($data)) {
					echo "<div class=\"error\"><p>No verse was found with that id. <a href=\"options-general.php?page=verse-o-matic.php\">Go back</a> and try again?</p></div>";
					return;
				}
				$data = $data[0];
			}
			
			if ($mode=="update") {
				$buttonText = "Edit Verse &raquo;";
			} else {
				$buttonText = "Add Verse &raquo;";
			}
			?>
			<form name="quoteform" id="quoteform" method="post" action="<?php echo $_SERVER['PHP_SELF']?>?page=verse-o-matic.php">
				<input type="hidden" name="action" value="<?php echo $mode?>">
				<input type="hidden" name="vomID" value="<?php echo $vomID?>">
			
				<div id="item_manager">
				
				<table class="optiontable">
					<tbody>
						<tr valign="top">
							<th scope="row" align="right"><?php _e('Verse'); ?></th>
							<td><textarea name="vom_verseText" class="input" cols=80 rows=5><?php if ( !empty($data) ) echo htmlspecialchars($data->verseText); ?></textarea></td>
						</tr>
						<tr valign="top">
							<th scope="row" align="right"><?php _e('Book'); ?></th>
							<td><input type="text" name="vom_book" class="regular-text" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->book); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row" align="right"><?php _e('Chapter'); ?></th>
							<td><input type="text" name="vom_chapter" class="regular-text" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->chapter); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row" align="right"><?php _e('Verse'); ?></th>
							<td><input type="text" name="vom_verse" class="regular-text" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->verse); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row" align="right"><?php _e('Version'); ?></th>
							<td><input type="text" name="vom_version" class="regular-text" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->version); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row" align="right"><?php _e('Link'); ?></th>
							<td><input type="text" name="vom_link" class="regular-text" size=60 value="<?php if ( !empty($data) ) echo htmlspecialchars($data->link); ?>" />
							Not Required: leave blank if none.</td>
						</tr>
						<tr valign="top">
							<th scope="row" align="right"><?php _e('Date'); ?></th>
							<td><input type="text" name="vom_date" class="regular-text" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->date); ?>" />
							Use to set VOTD order (format: yyyy-mm-dd). Leave blank if not using VOTD</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Visible'); ?></th>
							<td><input type="radio" name="vom_visible" class="input" value="yes" 
								<?php if ( empty($data) || $data->visible=='yes' ) echo "checked" ?>/> Yes
								<br />
								<input type="radio" name="vom_visible" class="input" value="no" 
								<?php if ( !empty($data) && $data->visible=='no' ) echo "checked" ?>/> No
							</td>
						</tr>
						<tr valign="top">
							<th scopt="row">&nbsp;</th>
							<td><input type="submit" name="save" value="<?php echo $buttonText;?>" style="font-weight: bold;" tabindex="4" class="button" /></td>
						</tr>
					</tbody>
				</table>			
				</div>
			</form>
			<?php
		} //end of the edit form function
		
		
		//The list of verses
		function vom_display_list()
		{
			global $wpdb;
			
			$sortby = $_GET['sortby'];
			if (!$sortby) { $sortby = "vomID"; }
			$verses = $wpdb->get_results("SELECT * FROM " . WP_VOM_VERSES . " order by $sortby");
			if (!empty($verses)) {
				//coming soon!?>
				<!--<input type="submit" name="reset" class="button" value="Reset daily random verse &raquo;" onclick="javascript:document.location.href='options-general.php?page=vom-admin.php&action=reset'" />
				<br /><br />-->
				<h3>Verses: (<a href="options-general.php?page=verse-o-matic.php#add")>Add New &raquo;</a>)</h3>
				<table class="widefat">
					<thead><tr class="head">
						<th scope="col"><a href="options-general.php?page=verse-o-matic.php&amp;sortby=vomID"><?php _e('ID') ?></a></th>
						<th scope="col"><a href="options-general.php?page=verse-o-matic.php&amp;sortby=book"><?php _e('Reference') ?></a></th>
						<th scope="col"><a href="options-general.php?page=verse-o-matic.php&amp;sortby=version"><?php _e('Version') ?></a></th>
						<th scope="col"><?php _e('Verse') ?></th>
						<th scope="col"><?php _e('Link') ?></th>
						<th scope="col"><?php _e('Visible?') ?></th>
						<th scope="col"><a href="options-general.php?page=verse-o-matic.php&amp;sortby=date"><?php _e('Date') ?></a></th>
						<th scope="col"><?php _e('Edit') ?></th>
						<th scope="col"><?php _e('Delete') ?></th>
					</tr></thead>
				<?php
				$class = '';
				foreach ($verses as $verse) {
					$class = ($class == 'alternate') ? '' : 'alternate';
					$today = date('Y-m-d');
					$class = $verse->date == $today ? 'active' : $class;
					?>
					<tr class="<?php echo $class; ?>" valign="top">
						<th scope="row"><?php echo $verse->vomID; ?></th>
						<td nowrap><?php echo $verse->book." ".$verse->chapter.":".$verse->verse; ?></td>
						<td nowrap><?php echo $verse->version; ?></td>
						<td><?php echo $verse->verseText; ?></td>
						<td><?php
						if ($verse->link){
							echo "<a href=\"".$verse->link."\">Link"; 
						}?></td>
						<td><?php echo $verse->visible=='yes' ? 'Yes' : 'No'; ?></td>
						<td><?php echo $verse->date; ?></td>
						<td><a href="options-general.php?page=verse-o-matic.php&action=edit&amp;vomID=<?php echo $verse->vomID;?>" class='edit'><?php echo __('Edit'); ?></a></td>
						<td><a href="options-general.php?page=verse-o-matic.php&action=delete&amp;vomID=<?php echo $verse->vomID."&amp;vom_book=".$verse->book."&amp;vom_chapter=".$verse->chapter."&amp;vom_verse=".$verse->verse;?>" class="delete" onclick="return confirm('Are you sure you want to delete this quote?')"><?php echo __('Delete'); ?></a></td>
					</tr>
					<?php
				}
				?>
				</table>
				<?php
			} else {
				?>
				<p><?php _e("You haven't entered any verses yet.") ?></p>
				<?php	
			}
		} // end of the vom_display_list function
		
		
		//  End functions
		
		
		//  Display the user interface
		
		if ($action == 'edit') {?>
			<div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>
				<h2>Manage Verse-O-Matic</h2>
				<h3><?php _e('Edit Verse'); ?></h3>
				<?php
				if (empty($vomID)) {
					echo "<div class=\"error\"><p>Verse ID not received, Cannot edit. <a href=\"options-general.php?page=verse-o-matic.php\">Go back</a> and try again?</p></div>";
				} else {
					vom_edit_form('update', $vomID);
				}?>
			</div>
				
		<?php } else {
		
			$vom_options_arr = get_option('vom_options'); //get the options array from settings table
			$altVersion      = $vom_options_arr[0]; //get the setting to turn on links to alternate versions
			$altBarSize      = $vom_options_arr[1]; //get the setting to limit the alt bar to 4 or not
			$displayMethod   = $vom_options_arr[2]; //random, daily, daily specific, static specific	
			$staticSpecific  = $vom_options_arr[3]; //get the static id, if there is one
			
			?>
			<div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>
				<h2>Manage Verse-O-Matic</h2>
				<h3><?php _e('Settings');?></h3>
				<form name="settings" id="settings" method="post" action="<?php echo $_SERVER['PHP_SELF']?>?page=verse-o-matic.php">
                    <table width="100%" cellpadding="3" cellspacing="3">
                      <tr> 
                        <td width="100" nowrap><a href="http://butlerblog.com/verse-o-matic/"><?php _e('Verse-O-Matic'); ?></a></td>
                        <td align="right" nowrap>Display Method</td>
                        <td align="left"> <select name="vom_display">
                            <option value="random" <?php if ($displayMethod=="random") {echo "selected";}?>>Random</option>
                            <option value="daily"  <?php if ($displayMethod=="daily") {echo "selected";}?>>Daily Random</option>
                            <option value="daily_specific"  <?php if ($displayMethod=="daily_specific") {echo "selected";}?>>Daily Specific</option>
                            <option value="static_specific" <?php if ($displayMethod=="static_specific") {echo "selected";}?>>Static Specific</option>               
                            <option value="esv_votd" <?php if ($displayMethod=="esv_votd") {echo "selected";}?>>ESV VOTD</option>
                            <option value="bg_votd"  <?php if ($displayMethod=="bg_votd") {echo "selected";}?>>Bible Gateway VOTD</option>
                          </select> </td>
                        <td align="right">Turn on alternate version links?</td>
                        <td> <select name="vom_switch">
                            <option value="true"  <?php if ($altVersion=="true") {echo "selected";}?>>Yes&nbsp;&nbsp;</option>
                            <option value="false" <?php if ($altVersion=="false"){echo "selected";}?>>No</option>
                          </select> </td>
                      </tr>
                      <tr> 
                        <td nowrap><p> <?php _e('version: '); echo WP_VOM_VERSION; ?></p></td>
                        <td align="right" nowrap>Static ID</td>
                        <td align="left"><input name="vom_staticID" type="text" size="3" maxlength="10" value="<?php echo $staticSpecific ?>"></td>
                        <td align="right" nowrap>Limit number of alternate version links?</td>
                        <td> <select name="vom_limit">
                            <option value="true"  <?php if ($altBarSize=="true")  {echo "selected";}?>>Yes&nbsp;&nbsp;</option>
                            <option value="false" <?php if ($altBarSize=="false") {echo "selected";}?>>No</option>
                          </select> </td>
                      </tr>
                      <tr> 
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="right">
                            </td>
                        <td align="left"> <input type="hidden" name="action" value="update_settings"> 
                          <input type="submit" name="EditSettings" value="Edit Settings &raquo;" style="font-weight: bold;" tabindex="4" class="button" /> 
                        </td>
                      </tr>
                    </table>
				</form>
				<?php vom_display_list();?>
            	<p>&nbsp;</p>
				<h3>Reset Daily Random Verse:</h3>
				<p>
				<input type="submit" 
					name="reset_daily" 
					class="button" 
					value="Reset Daily Random Verse &raquo;" 
					onclick="javascript:document.location.href='options-general.php?page=verse-o-matic.php&action=reset_daily'" />
					<br /><?php _e('Important Note: if you are using "Daily Specific", this clears ALL dates
					before reseting a random date.');?></p>
			</div>
            <p>&nbsp;</p>
			<div class="wrap"><a name="add"></a>
				<h3><?php _e('Add Verse'); ?></h3>
				<?php vom_edit_form(); ?>
			</div>
		<?php }?>
		
<?php }

//installation function
function vom_install() 
{
   global $wpdb;

   require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

   $table_name = WP_VOM_VERSES;
   if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
	$sql = "CREATE TABLE `".WP_VOM_VERSES."` (
		`vomID` INT(11) NOT NULL AUTO_INCREMENT ,
		`version` VARCHAR( 20 ) NOT NULL ,
		`book` VARCHAR( 30 ) NOT NULL ,
		`chapter` VARCHAR( 10 ) NOT NULL ,
		`verse` VARCHAR( 10 ) NOT NULL ,
		`verseText` TEXT NOT NULL ,
		`link` TEXT DEFAULT NULL ,
		`visible` ENUM( 'yes', 'no' ) NOT NULL ,
  		`date` DATE DEFAULT NULL, 
  		PRIMARY KEY  (`vomID`),
  		KEY `date` (`date`)	)";	
      dbDelta($sql);

      $insert  = "INSERT INTO `".WP_VOM_VERSES."` (version, book, chapter, verse, verseText, link, visible) values "
	     . "('ESV','Romans','8','1','There is therefore now no condemnation for those who are in Christ Jesus.', 'http://www.gnpcb.org/esv/search/?q=romans+8%3A1', 'yes'), "
	     . "('ESV','John','11','25-26','Jesus said to her, I am the resurrection and the life. Whoever believes in me, though he die, yet shall he live, and everyone who lives and believes in me shall never die. Do you believe this?', '', 'yes'), "
	     . "('NLT','Colossians','1','15','Christ is the visible image of the invisible God. He existed before God made anything at all and is supreme over all creation.', '', 'yes'), "
	     . "('The Message','Matthew','5','19','Trivialize even the smallest item in God&acute;s Law and you will only have trivialized yourself. But take it seriously, show the way for others, and you will find honor in the kingdom.', '', 'yes')";
      $results = $wpdb->query( $insert );

	// sets default options
	$vom_options_arr = array('true','true','random','');
	add_option('vom_options',$vom_options_arr,'','yes');

   }
} // end of the install function


// This new function pulls in a VOTD feed from Good News Publishers (ESV) or Bible Gateway.
function vom_get_rss($whichFeed)
{
	switch($whichFeed) {
	
	case "esv_votd":
		$vom_feed_arr = array(
			'feed' => "http://www.gnpcb.org/esv/share/rss2.0/daily/",
			'item' => "item",
			'desc' => "description",
			'vrsn' => "ESV"
			);
		break;
		
	case "bg_votd";
		$vom_feed_arr = array(
			'feed' => "http://www.biblegateway.com/votd/get/?format=atom",
			'item' => "entry",
			'desc' => "content",
			'vrsn' => "NIV"
			);
		break;
	}

	$doc = new DOMDocument();
	$doc->load($vom_feed_arr['feed']);
		
	foreach ($doc->getElementsByTagName($vom_feed_arr['item']) as $node) {
	
		$verseRef = $node->getElementsByTagName('title')->item(0)->nodeValue;
		$verseExp = explode(":",$verseRef);
		$verseVerse = $verseExp[1];
		$verseExp = explode(" ",$verseExp[0]);
		$verseBook = $verseExp[0];
		$verseChap = $verseExp[1];
		
		$vom_verse_arr = array ( 
			1 => $vom_feed_arr['vrsn'],
			2 => $verseBook,
			3 => $verseChap,
			4 => $verseVerse,
			5 => $node->getElementsByTagName($vom_feed_arr['desc'])->item(0)->nodeValue,
			6 => $node->getElementsByTagName('link')->item(0)->nodeValue,
			);
		array_push($vom_verse_arr, $itemRSS);
	}
	return $vom_verse_arr;
}

define("WP_VOM_CREDIT", "powered by <a href=\"http://butlerblog.com/verse-o-matic\">verse-o-matic</a>");
?>