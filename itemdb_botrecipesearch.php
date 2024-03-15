<?php
/**
 * Page Setup items
 *     MUST be at the top of this page!
 */
$page_title = "ItemDB Recipe Search";
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
	$new_checksum = md5('aocrecipe' . $salt );
}

if ($search != "" && $pre != "" && $botname != "" && $checksum == $new_checksum)
{
	if (is_numeric($search))
	{
		//Search by ID
		$sql = 'SELECT 	DISTINCT a.Qty, b.ItemName, b.LowID, b.HighID, b.Color
				FROM 	itemdb_recipes a
						JOIN itemdb_items b ON b.LowID = a.LowID AND b.HighID = a.HighID
				WHERE 	b.HighID = ?
				ORDER 	BY b.ItemName';
		$result_recipe = $mm_db->sql_query_parms($sql, array($search));

		if(count($result_recipe) > 0)
		{
			$output = FormatSingleRecipie($result_recipe, $mm_db, $botname);
		}
		else 
		{
			$output = "Item Search: No Rows Found for the supplied Item ID";
		}
	}
	else 
	{
		//Search By Text
		if ($search == "***all***")
		{
			$sql = 'SELECT 	DISTINCT a.Qty, b.ItemName, b.LowID, b.HighID, b.Color
					FROM 	itemdb_recipes a
							JOIN itemdb_items b ON b.LowID = a.LowID AND b.HighID = a.HighID
					ORDER 	BY b.ItemName
					LIMIT	30';
		}
		else 
		{
			$sql = 'SELECT 	DISTINCT a.Qty, b.ItemName, b.LowID, b.HighID, b.Color
					FROM 	itemdb_recipes a
							JOIN itemdb_items b ON b.LowID = a.LowID AND b.HighID = a.HighID
					WHERE 	b.ItemName LIKE ("%' . $search . '%")
					ORDER 	BY b.ItemName
					LIMIT 30';
		}
		$result_recipe = $mm_db->sql_query($sql);

		if(count($result_recipe) == 1)
		{
			$output = FormatSingleRecipie($result_recipe, $mm_db, $botname);
		}
		elseif(count($result_recipe) > 1)
		{
			$output = "<a href=\"text://<center><font color='#6d6dff' face='hyborianlarge'><b>Recipe Item Search Results<br />using the parameters '" . $search . "'</b></font><br /><font color='#6d6dff' face='normal'>from the MeatHooks Minions Central Items Database</font></center><br /><br />";

			for($r=0; $r<count($result_recipe); $r++) 
			{
				$recipe = $result_recipe[$r];

				$output .= "<font color='#000000'>__</font><font color='#ff9900'>Recipe </font>";
				$output .= "<a style='text-decoration:none' href='chatcmd:///tell " . $botname . " itemrecipes " . $recipe->HighID . "'><font color=#" . $recipe->Color . ">[" . $recipe->ItemName . "]</font> <font color='#ff9900'>(makes " . $recipe->Qty . " per)</font></a><br /><br />";
			}

			$output .= "<br /><br /><br /><font color='#c0c0c0'>Results are limited to 30 recipe items.</font>";

			$output .= "\" style=text-decoration:none><font color='#FF8000'>[Recipe Item Search Results using the parameters '" . $search . "']</font></a>";
		}
		else 
		{
			$output = "Item Search: No Rows Found for the supplied search word(s)";
		}
	}
}
else 
{
	$output = "Item Search: Error with parameters or checksum";
}

echo $output;

function FormatSingleRecipie($result_recipe, $mm_db, $botname)
{
	$output = "<a href=\"text://<center><font color='#6d6dff' face='hyborianlarge'><b>Recipe Item Search Results</b></font><br /><font color='#6d6dff' face='normal'>from the MeatHooks Minions Central Items Database</font></center><br /><br />";

	$output .= "<br /><font face='large' color='#ff9900'>Recipe </font>";
	$output .= "<a style='text-decoration:none' href='chatcmd:///tell " . $botname . " items " . $result_recipe[0]->HighID . "'><font face='large' color=#" . $result_recipe[0]->Color . ">[" . $result_recipe[0]->ItemName . "]</font> <font face='large' color='#ff9900'>(makes " . $result_recipe[0]->Qty . " per)</font></a><br />";
	$output .= "<font face='large' color='#ff9900'>" . str_repeat("_", strlen($result_recipe[0]->ItemName) + strlen($result_recipe[0]->Qty) + 20) . "</font><br /><br />";

	$sql = 'SELECT 	DISTINCT a.Item_Qty Qty, b.ItemName, b.LowID, b.HighID, b.Color, CASE WHEN c.LowID IS NULL AND c.HighID IS NULL THEN "N" ELSE "Y" END IsRecipe
			FROM 	itemdb_recipes a
					JOIN itemdb_items b ON b.LowID = a.Item_LowID AND b.HighID = a.Item_HighID 
					LEFT OUTER JOIN itemdb_recipes c ON c.LowID = a.Item_LowID AND c.HighID = a.Item_HighID
			WHERE 	a.LowID = ? AND a.HighID = ?
			ORDER 	BY b.ItemName';
	$result = $mm_db->sql_query_parms($sql, array($result_recipe[0]->LowID, $result_recipe[0]->HighID));
	if(count($result) > 0)
	{
		for($r=0; $r<count($result); $r++) 
		{
			$item = $result[$r];

			$output .= "<font color='#000000'>" . str_repeat("_", 3 - strlen($item->Qty)) . "</font><font color='#ff9900'>" . $item->Qty . "x </font>";
			if ($item->IsRecipe == "Y")
			{
				$output .= "<a style='text-decoration:none' href='chatcmd:///tell " . $botname . " itemrecipes " . $item->HighID . "'><font color=#" . $item->Color . ">[" . $item->ItemName . "]</font></a>";
			}
			else 
			{
				$output .= "<a style='text-decoration:none' href='chatcmd:///tell " . $botname . " items " . $item->HighID . "'><font color=#" . $item->Color . ">[" . $item->ItemName . "]</font></a>";
			}
			$output .= "<br /><br />";
		}
	}

	$sql = "SELECT 	ToonName, GuildName
			FROM 	itemdb_recipe_crafters
			WHERE 	LowID = ? AND HighID = ?
			ORDER	BY 
					CASE WHEN GuildName = 'MeatHooks Minions' THEN '_MeatHooks Minions' ELSE GuildName END";
	$result_crafters = $mm_db->sql_query_parms($sql, array($result_recipe[0]->LowID, $result_recipe[0]->HighID));
	if(count($result_crafters) > 0)
	{
		$output .= "<br /><br /><br /><font face='large' color='#ff9900'>Craftable by:</font><br />";
		$output .= "<font face='large' color='#ff9900'>" . str_repeat("_", 20) . "</font><br /><br />";

		for($r=0; $r<count($result_crafters); $r++) 
		{
			$crafter = $result_crafters[$r];

			$output .= $crafter->ToonName . " of the guild " . $crafter->GuildName . "<br />";
		}
	}

	$output .= "\" style=text-decoration:none><font color='#FF8000'>[Recipe Item Search Results - " . $result_recipe[0]->ItemName . "]</font></a>";

	return $output;
}

?>
