<?php

/**
 * Page Setup items
 *     MUST be at the top of this page!
 */
$page_title = "ItemDB Submit";
$body_class = "aoc";

require_once "init.php";

$username	= request_var("username", "");
$botname	= request_var("botname", "");
$guildname	= request_var("guildname", "");
$server		= request_var("server", "UNKNOWN");

$name		= request_var("name", "");
$lowid		= request_var("lowid", "");
$highid		= request_var("highid", "");
$lowlvl		= request_var("lowlvl", "");
$highlvl	= request_var("highlvl", "");
$lowcrc		= request_var("lowcrc", "");
$midcrc		= request_var("midcrc", "");
$highcrc	= request_var("highcrc", "");
$color		= request_var("color", "");

$checksum	= request_var("checksum", "");

if ($username == "" || $botname == "" || $name == "" || $lowid == "" || $highid == "" || $lowlvl == "" || $highlvl == "" || $lowcrc == "" || $midcrc == "" || $highcrc == "" || $color == "" || $checksum == "")
{
	echo "Invalid Parameters Specified";
}
else 
{
	$sql = "SELECT Passkey FROM itemdb_bots WHERE BotName = ?";
	$result = $mm_db->sql_query_parms($sql, array($botname));
	if(count($result) > 0) { $passkey = $result[0]->Passkey; }

	$salt = $lowid . "_" . $highid . "_" . $lowlvl . "_" . $highlvl . "_" . $lowcrc . "_" . $midcrc . "_" . $highcrc."_" . 
			$color . "_" . $name . "_" . $botname . "_" . $username . "_" . $passkey;
	$new_checksum = md5('aocitems' . $salt );

	if ($checksum != $new_checksum)
	{
		echo "Checksum Failed, unable to process the item submission.";
	}
	else
	{
		$sql =	'SELECT InsertOn FROM itemdb_items WHERE ItemName = ? and LowID = ? and HighID = ? and LowLVL = ? and HighLVL = ? and Color = ?';
		$result = $mm_db->sql_query_parms($sql, array($name, $lowid, $highid, $lowlvl, $highlvl, $color));
		if(count($result) > 0) 
		{ 
			echo "1";
		}
		else 
		{
			$sql =	'INSERT INTO itemdb_items (ToonName, BotName, GuildName, ServerName, ItemName, LowID, HighID, LowLVL, HighLVL, LowCRC, MidCRC, HighCRC, Color, InsertOn) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
			$mm_db->sql_query_parms($sql, array($username, $botname, $guildname, $server, $name, $lowid, $highid, $lowlvl, $highlvl, $lowcrc, $midcrc, $highcrc, $color, date('Y-m-d H:i:s')));

			$sql =	'SELECT InsertOn FROM itemdb_items WHERE ItemName = ? and LowID = ? and HighID = ? and LowLVL = ? and HighLVL = ? and Color = ?';
			$result = $mm_db->sql_query_parms($sql, array($name, $lowid, $highid, $lowlvl, $highlvl, $color));

			if(count($result) > 0) 
			{ 
				echo "2";
			}
			else 
			{
				echo "Item Submit: Insert Failed.";
			}
		}
	}
}
?>
