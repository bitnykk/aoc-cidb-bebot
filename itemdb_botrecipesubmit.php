<?php

/**
 * Page Setup items
 *     MUST be at the top of this page!
 */
$page_title = "ItemDB Recipe Submit";
$body_class = "aoc";

require_once "init.php";

$username		= request_var("username", "");
$botname		= request_var("botname", "");
$guildname		= request_var("guildname", "");
$server			= request_var("server", "UNKNOWN");

$recipelowid	= request_var("recipelowid", "");
$recipehighid	= request_var("recipehighid", "");
$recipeqty		= request_var("recipeqty", "");
$itemlowid		= request_var("itemlowid", "");
$itemhighid		= request_var("itemhighid", "");
$itemqty		= request_var("itemqty", "");

$checksum	= request_var("checksum", "");

if ($username == "" || $botname == "" || $guildname == "" || $recipelowid == "" || $recipehighid == "" || $recipeqty == "" || $itemlowid == "" || $itemhighid == "" || $itemqty == "" || $checksum == "")
{
	echo "Invalid Parameters Specified";
}
else 
{
	$sql = "SELECT Passkey FROM itemdb_bots WHERE BotName = ?";
	$result = $mm_db->sql_query_parms($sql, array($botname));
	if(count($result) > 0) { $passkey = $result[0]->Passkey; }

	$salt  = $recipelowid . "_" . $recipehighid . "_" . $recipeqty . "_" . $itemlowid . "_" . $itemhighid . "_" . $itemqty . "_" . $botname . "_" . $username . "_" . $passkey;
	$new_checksum = md5('aocrecipe' . $salt );

	if ($checksum != $new_checksum)
	{
		echo "Checksum Failed, unable to process the recipie submittion.";
	}
	else
	{
		$sql =	'INSERT INTO itemdb_recipe_crafters (ToonName, BotName, GuildName, LowID, HighID, InsertOn) VALUES (?, ?, ?, ?, ?, ?)';
		$mm_db->sql_query_parms($sql, array($username, $botname, $guildname, $recipelowid, $recipehighid, date('Y-m-d H:i:s')));

		$sql =	'SELECT InsertOn FROM itemdb_recipes WHERE LowID = ? and HighID = ? and Item_LowID = ? and Item_HighID = ?';
		$result = $mm_db->sql_query_parms($sql, array($recipelowid, $recipehighid, $itemlowid, $itemhighid));
		if(count($result) > 0) 
		{ 
			echo "1";
		}
		else 
		{
			$sql =	'INSERT INTO itemdb_recipes (LowID, HighID, Qty, Item_LowID, Item_HighID, Item_Qty, InsertOn) VALUES (?, ?, ?, ?, ?, ?, ?)';
			$mm_db->sql_query_parms($sql, array($recipelowid, $recipehighid, $recipeqty, $itemlowid, $itemhighid, $itemqty, date('Y-m-d H:i:s')));

			$sql =	'SELECT InsertOn FROM itemdb_recipes WHERE LowID = ? and HighID = ? and Item_LowID = ? and Item_HighID = ?';
			$result = $mm_db->sql_query_parms($sql, array($recipelowid, $recipehighid, $itemlowid, $itemhighid));
			if(count($result) > 0) 
			{ 
				echo "2";
			}
			else 
			{
				echo "Recipe Submit: Insert Failed.";
			}
		}
	}
}

?>
