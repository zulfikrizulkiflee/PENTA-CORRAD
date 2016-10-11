<?php
//validate that user have session
require_once('func_common.php');
validateUserSession();
?>
<div id="breadcrumbs">System Administrator / Configuration / Development Log</div>
<h1>Development Log</h1>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent flcEditorList">
	<tr>
		<th colspan="7">Log List</th>
	</tr>
	<tr>
		<th class="listingHead">Date</th>
		<th class="listingHead">Version</th>
		<th class="listingHead">Build</th>
		<th class="listingHead">Description</th>
		<th class="listingHead">Type</th>
		<th class="listingHead">Status</th>
		<th class="listingHead">Patch File</th>
	</tr>
	<tr>
		<td class="listingContent">27/09/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">270914</td>
		<td class="listingContent">Repaired flc_required_caller to support tabular / report component</td>
		<td class="listingContent">Bugs</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">Patch 1</td>
	</tr>
	<tr>
		<td class="listingContent">27/09/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">270914</td>
		<td class="listingContent">Bugs found for REPORT component - running number for ID and NAME is not correct for radiobutton</td>
		<td class="listingContent">Bugs</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">Patch 1</td>
	</tr>
	<tr>
		<td class="listingContent">27/09/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">270914</td>
		<td class="listingContent">Bugs found for TABULAR component - radio button id and name is not incremented on ADD RECORD BUTTON</td>
		<td class="listingContent">Bugs</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">Patch 1</td>
	</tr>
	<tr>
		<td class="listingContent">27/09/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">270914</td>
		<td class="listingContent">Bugs found for REPORT component - radio button name is not incremented on ADD RECORD BUTTON</td>
		<td class="listingContent">Bugs</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">Patch 1</td>
	</tr>
	<tr>
		<td class="listingContent">27/09/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">270914</td>
		<td class="listingContent">REPORT component - checkbox not showing all items (in this case using predefined, other case not tested)</td>
		<td class="listingContent">Bugs</td>
		<td class="listingContent" style="color:red">NOT SOLVED</td>
		<td class="listingContent">&nbsp;</td>
	</tr>
	<tr>
		<td class="listingContent">27/09/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">270914</td>
		<td class="listingContent">Bugs found for TABULAR component - checkbox is not working (infinite loop)</td>
		<td class="listingContent">Bugs</td>
		<td class="listingContent">Solved</td>
		<td class="listingContent">By Fais</td>
	</tr>
	<tr>
		<td class="listingContent">21/10/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">211014</td>
		<td class="listingContent">Bugs found when using texteditor in multi component tab - Uncaught The editor instance "TEXTEDITOR" is already attached to the provided element.</td>
		<td class="listingContent">Bugs</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">engine_elem_builder.php, page_wrapper.php - Tested @ MOF</td>
	</tr>
	<tr>
		<td class="listingContent">21/10/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">211014</td>
		<td class="listingContent">Required items not working properly</td>
		<td class="listingContent">Bugs</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">&nbsp;</td>
	</tr>
	<tr>
		<td class="listingContent">22/10/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">211014</td>
		<td class="listingContent">Tab in tabs features is now available</td>
		<td class="listingContent">Features</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">&nbsp;</td>
	</tr>
	<tr>
		<td class="listingContent">02/12/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">051214</td>
		<td class="listingContent">Added disconnect function at bottom of the page to auto close connection</td>
		<td class="listingContent">Features</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">&nbsp;</td>
	</tr>
	<tr>
		<td class="listingContent">04/12/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">051214</td>
		<td class="listingContent">Added convertQryToPost function for usage in BL</td>
		<td class="listingContent">Features</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">&nbsp;</td>
	</tr>
	<tr>
		<td class="listingContent">04/12/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">051214</td>
		<td class="listingContent">Added sticky support for tabs (cookie based)</td>
		<td class="listingContent">Features</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">&nbsp;</td>
	</tr>
	<tr>
		<td class="listingContent">05/12/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">051214</td>
		<td class="listingContent">Added translation support for LOV</td>
		<td class="listingContent">Features</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">&nbsp;</td>
	</tr>
	<tr>
		<td class="listingContent">05/12/2014</td>
		<td class="listingContent">2.13</td>
		<td class="listingContent">051214</td>
		<td class="listingContent">Added configuration for user group management listing max records fetched</td>
		<td class="listingContent">Features</td>
		<td class="listingContent">Done</td>
		<td class="listingContent">&nbsp;</td>
	</tr>
</table>
