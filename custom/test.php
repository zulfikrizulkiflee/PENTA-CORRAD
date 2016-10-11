<?php
$qry = "select * from PRUSER";
$qryRs = $myQuery->query($qry,'SELECT','NAME');



?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent">
	<tbody><tr>
		<th colspan="2">
		test include	</th>
	</tr>
	
	<tr id="column_45_226">
		<td width="150" class="inputLabel"><div id="input_label_45_226"> ID Pelanggan : </div></td>
		<td class="inputArea"><select name="Userid" tabindex="" id="Userid" class="inputInput" onchange="FLC_AJAX_BL_TEXT_CALLER('BL_TEST','pelanggan_id='+this.value+'&amp;type=alamat_rumah','alamat_rumah');FLC_AJAX_BL_TEXT_CALLER('BL_TEST','pelanggan_id='+this.value+'&amp;type=tarikh_lahir','tarikh_lahir');FLC_AJAX_BL_TEXT_CALLER('BL_TEST','pelanggan_id='+this.value+'&amp;type=nama_penuh','nama_penuh');FLC_AJAX_BL_TEXT_CALLER('BL_TEST','pelanggan_id='+this.value+'&amp;type=no_kad_pengenalan','no_kad_pengenalan');" title=""><option></option><option value="1">1 - Mohd Karim bin Ahmad Dhani</option><option value="2">2 - Nur Syazana binti Abu Bakar</option><option value="3">3 - Nur Waheeda binti Faisal</option><option value="4">4 - Nor Sufinah binti Ahmad Muhsin</option><option value="5">5 - Laila Lily binti Gaffar</option><option value="6">6 - Mohd Anas Hasan</option><option value="7">7 - Kamarul Aifin bin Kamarudzzaman</option><option value="8">8 - ALI BIN ABU</option></select> </td>
	</tr>
	
	<tr id="column_45_227">
		<td width="150" class="inputLabel"><div id="input_label_45_227"> Nama Penuh : </div></td>
		<td class="inputArea"><input name="nama_penuh" id="nama_penuh" type="text" tabindex="" maxlength="" class="inputInput" value="" size="38" style="text-align:left;" title="" placeholder=""> </td>
	</tr>
	
	<tr id="column_45_228">
		<td width="150" class="inputLabel"><div id="input_label_45_228"> No KP : </div></td>
		<td class="inputArea"><input name="no_kad_pengenalan" id="no_kad_pengenalan" type="text" tabindex="" maxlength="" class="inputInput" value="" size="14" style="text-align:left;" title="" placeholder=""> </td>
	</tr>
	
	<tr id="column_45_417">
		<td width="150" class="inputLabel"><div id="input_label_45_417"> Alamat Rumah : </div></td>
		<td class="inputArea"><textarea name="alamat_rumah" id="alamat_rumah" tabindex="" class="inputInput" cols="30" rows="3" style="text-align:left;" title="" placeholder=""></textarea> </td>
	</tr>
	
	<tr id="column_45_418">
		<td width="150" class="inputLabel"><div id="input_label_45_418"> Tarikh Lahir : </div></td>
		<td class="inputArea"><input name="tarikh_lahir" id="tarikh_lahir" type="text" tabindex="" class="inputInput" value="" size="10" title="" placeholder="" style="text-align:left;"><a href="#tarikh_lahir" class="date-picker-control" title="Open Calendar" id="fd-but-tarikh_lahir" role="button" aria-haspopup="true" tabindex="0"><span>&nbsp;</span><span class="fd-screen-reader">Open Calendar</span></a><script>var opts = {formElements:{"tarikh_lahir":"Y-ds-m-ds-d"}, showWeeks:true, statusFormat:"l-cc-sp-d-sp-F-sp-Y"}; datePickerController.createDatePicker(opts);</script> </td>
	</tr>
		</tbody></table>
		
		
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tbody><tr>
    <th colspan="9">Control List </th>
  </tr>
    <tr>
    <th width="10" class="listingHead">#</th>
    <th width="15" class="listingHead">ID</th>
    <th width="100" class="listingHead">Component</th>
    <th width="100" class="listingHead">Name</th>
    <th class="listingHead">Title</th>
    <th width="100" class="listingHead">Type</th>
    <th width="30" class="listingHead">Order</th>
    <th width="30" class="listingHead">Trigger</th>
    <th width="100" class="listingHead">Action</th>
  </tr>
  <?php for($x=0; $x < count($qryRs); $x++) { ?>
  
    <tr onmouseover="this.style.background = '#FFFFCC'" onmouseout="this.style.background = '#ffffff'" style="background: rgb(255, 255, 255);">
    <td class="listingContent">1.</td>
    <td class="listingContent">41</td>
    <td class="listingContent">-</td>
    <td class="listingContent">zxczxc</td>
    <td class="listingContent">zcxczxc</td>
    <td class="listingContent">Back Button</td>
    <td class="listingContent">1</td>
    <td class="listingContent">No</td>
    <td nowrap="nowrap" class="listingContentRight"><form id="formReference" name="formReference" method="post" action="">
        <input name="editReference" type="submit" class="inputButton" id="editReference" value="Update">
        <input name="deleteReference" type="submit" class="inputButton" id="deleteReference" value="Delete" onclick="if(window.confirm('Are you sure you want to DELETE this control?')) {return true} else {return false}">
        <input name="hiddenCode" type="hidden" id="hiddenCode" value="41">
        <input name="code" type="hidden" id="code" value="23">
      </form></td>
  </tr>
      <?php } ?>
      
      <tr>
    <td colspan="9" class="contentButtonFooter">
        <form id="form2" name="form2" method="post" action="">
          <input name="code" type="hidden" id="code" value="23">
		  <input name="resetControlOrder" type="submit" class="inputButton" id="resetControlOrder" value="Optimize Order" onclick="if(window.confirm('Are you sure you want to reset the control order?')) {return true} else {return false}">
          <input name="newReference" type="submit" class="inputButton" id="newReference" value="New Control">
        </form>
    </td>
  </tr>
</tbody></table>
