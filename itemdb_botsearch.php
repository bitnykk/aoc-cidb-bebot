<?php
/**
 * Page Setup items
 *     MUST be at the top of this page!
 */
$page_title = "ItemDB Search";
$body_class = "aoc";

require_once "init.php";

$search = urldecode(request_var("search", ""));
$pre = request_var("pre", "");

$botname = request_var("botname", "");
$checksum = urldecode(request_var("checksum", ""));

$passkey = "**INVALID**";
$new_checksum = "**NOTHING**";

if ($botname != "")
{
	$sql = "SELECT Passkey FROM itemdb_bots WHERE BotName = ?";
	$result = $mm_db->sql_query_parms($sql, array($botname));
	if(count($result) > 0) { $passkey = $result[0]->Passkey; }

	$salt = $passkey . "_" . $botname;
	$new_checksum = md5('aocitems' . $salt );
}

if ($search != "" && $pre != "" && $botname != "" && $checksum == $new_checksum)
{
	if (is_numeric($search))
	{
		//Search By ID
		$sql = "SELECT ItemName, LowID, HighID, LowLVL, HighLVL, LowCRC, MidCRC, HighCRC, Color FROM itemdb_items WHERE HighID = ? LIMIT 1";
		$result = $mm_db->sql_query_parms($sql, array($search));
	}
	else 
	{
		//Search By Text
		$sql = 'SELECT 	DISTINCT a.ItemName, a.LowID, a.HighID, a.LowLVL, a.HighLVL, a.LowCRC, a.MidCRC, a.HighCRC, a.Color,
						CASE WHEN b.LowID IS NULL AND b.HighID IS NULL THEN "N" ELSE "Y" END IsRecipe
				FROM 	itemdb_items a
						LEFT OUTER JOIN itemdb_recipes b ON b.LowID = a.LowID AND b.HighID = a.HighID
				WHERE 	a.ItemName LIKE ("%' . $search .'%")
				ORDER	BY a.ItemName
				LIMIT	50';
		$result = $mm_db->sql_query($sql);
	}

	if (count($result) == 1)
	{
		$item = $result[0];

		$output = "<a style='text-decoration:none' href='itemref://" . $item->LowID . "/" . $item->HighID . "/" . $item->LowLVL . "/" . $item->HighLVL . "/" . $item->LowCRC . "/" . $item->MidCRC . "/" . $item->HighCRC . "'><font color=#" . $item->Color . ">[" . $item->ItemName . "]</font></a>";
	}
	elseif(count($result) > 1)
	{
		$output = "<a href=\"text://<center><font color='#6d6dff' face='hyborianlarge'><b>Item Search Results<br />using the parameters '" . $search . "'</b></font><br /><font color='#6d6dff' face='normal'>from the MeatHooks Minions Central Items Database</font></center><br /><br />";

		for($r=0; $r<count($result); $r++) 
		{
			$item = $result[$r];
			
			if ($item->IsRecipe == "Y")
			{
				$output .= "<font color='#000000'>__</font><font color='#ff9900'>Recipe </font>";
				$output .= "<a style='text-decoration:none' href='chatcmd:///tell " . $botname . " itemrecipes " . $item->HighID . "'><font color=#" . $item->Color . ">[" . $item->ItemName . "]</font></a><br /><br />";
			}
			else 
			{
				$output .= "<font color='#000000'>__</font><font color='#ff9900'>Item </font>";
				$output .= "<a style='text-decoration:none' href='chatcmd:///tell " . $botname . " items " . $item->HighID . "'><font color=#" . $item->Color . ">[" . $item->ItemName . "]</font></a><br /><br />";
			}
		}

		$output .= "<br /><br /><br /><font color='#c0c0c0'>Results are limited to 50 items.</font>";

		$output .= "\" style=text-decoration:none><font color='#FF8000'>[Item Search Results using the parameters '" . $search . "']</font></a>";
	}
	else 
	{
		$output = "Item Search: No Rows Found for the supplied search word(s)";
	}
}
else 
{
	$output = "Item Search: Error with parameters or checksum";
}

echo $output;
?>
