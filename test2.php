<?php
echo urlencode("https://egpa.treasury.gov.my/egpa/index.php?page=page_wrapper&menuID=xxxxxxxx&randomNo=1811397718105275996021766113001397946229");

//http://<uapdomain>/uap/jsp/regpasword.jsp?email=<email>&userID=<userid>&return=<URL>
//https://egpa.treasury.gov.my/egpa/index.php?page=page_wrapper&menuID=148&randomNo=1234567891
//https://uap.treasury.gov.my/uap/register/password.jsp?email=faisal.imran@nc.com.my&userid=850108035311&language=my&return=https://egpa.treasury.gov.my/egpa/index.php?randomNo=XXXXXX

//https://egpa.treasury.gov.my/egpa/index.php?randomNo=1234567890123456789012345678901234567890

/*
	$randomNo = generateUAPRandomNo();
	$returnURL = urlencode(UAP_GATEWAY.'/'.UAP_FOLDER_NAME."index.php?randomNo=".$randomNo);


	$catatan = "Dato'/Datin, Datuk, Tuan/Puan,"."<br><br>".
				"Sila masukkan pautan di bawah bagi mengaktifkan Pendaftaran Profile anda :"."<br><br>". 
				"<a href='https://uap.treasury.gov.my/uap/register/password.jsp?email=$email&userid=$username&language=my&return=".$returnURL."'>Klik Disini</a>";
	$catatan  .= "<br/><br/>Sekian. Terima Kasih.";
*/

//ADLY	830308085673

//if first time login
		if(isset($_GET['randomNo']))
		{
			//check for the random no
			$checkRandomNo = "select a.USERID
								from 
									PRUSER a, FLC_EXTENDED_ATTR b, FLC_EXTENDED_ATTR_VAL c 
								where 
									a.USERID = c.ATTR_PARENT_ID 
									and b.ATTR_ID = c.ATTR_ID 
									and b.ATTR_PARENT_TABLE = 'PRUSER'
									and b.ATTR_NAME = 'PID'
									and c.ATTR_VALUE = '".$_GET['randomNo']."'";
			$checkRandomNoRs = $myQuery->query($checkRandomNo,'SELECT','NAME');
			
			if(count($checkRandomNoRs) > 0)
			{
				//update randomno to pid
				$update = "update FLC_EXTENDED_ATTR_VAL 
							set ATTR_VALUE = '".$_SERVER['HTTP_REMOTE_USER']."'
							where ATTR_ID = 1 
							and ATTR_PARENT_ID = ".$checkRandomNoRs[0]['USERID'];
				$updateRs = $myQuery->query($update,'RUN');
			}
		}
?>
