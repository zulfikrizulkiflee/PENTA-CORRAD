<?php
/*============== PAGE POST SCRIPT ==============*/
//if have post value
if($_POST)
{
	
		//print_r($_POST);
	//=============== POST SECURITY ===============
	//html special chars
	if(STRIP_TAGS_ENABLED)
		$_POST = stripTags($_POST);
	
	//echo '<br>';
//	print_r($_POST);
	
	//strip tags
	if(HTML_SPECIAL_CHARS_ENABLED)
		$_POST = convertHTMLSpecialChars($_POST);
	//============= EOF POST SECURITY =============
	
	//default referer
	$previousURL = $_SERVER['HTTP_REFERER'];
	
	//get previous url (decode if encoded url)
	if(URL_SECURITY)
	{
		//decode previous URL
		$tempDecodeURL = flc_url_decode(substrByChar($_SERVER['HTTP_REFERER'], 'index.php?a='));
		
		//url after decode
		if($tempDecodeURL)
			$previousURL = $tempDecodeURL;
	}//eof if
	
	//if previous page has menuid
	if(strpos($previousURL, 'menuID=') !== false)
	{
		//get post (previous) menuid
		$postMenu = substrByChar($previousURL, 'menuID=', '&');
		
		//if have post (previous) menuid
		if($postMenu)
		{			
			//get post (previous) page
			$getPostPage = "select PAGEID from FLC_PAGE where MENUID = ".$postMenu;
			$getPostPageRs = $myQuery->query($getPostPage, 'SELECT','NAME');
			
			//if have pageid
			if($getPostPageRs)
			{
				//=============== PAGE POST-PROCESS ===============
				executePhpEvent($myQuery, 'postprocess', 'page', $getPostPageRs[0]['PAGEID']);
				//============= EOF PAGE POST-PROCESS =============
				
				//get post (previous) component
				$getPostComponent = "select COMPONENTID from FLC_PAGE_COMPONENT 
										where COMPONENTSTATUS = 1 ".$componentPermissionSQL." and PAGEID = ".$getPostPageRs[0]['PAGEID'];
				$getPostComponentRs = $myQuery->query($getPostComponent, 'SELECT','NAME');
				$getPostComponentRsCount = count($getPostComponentRs);
				
				//loop on count of component
				for($x=0; $x<$getPostComponentRsCount; $x++)
				{
					//============= COMPONENT POST-PROCESS ============
					executePhpEvent($myQuery, 'postprocess', 'component', $getPostComponentRs[$x]['COMPONENTID']);
					//=========== EOF COMPONENT POST-PROCESS ==========
				}//eof for
				
				//get post (previous) item
				$getPostItem = "select ITEMID, ITEMNAME from FLC_PAGE_COMPONENT_ITEMS a, FLC_PAGE_COMPONENT b
										where ITEMSTATUS = 1 ".$itemPermissionSQL." 
											and a.COMPONENTID = b.COMPONENTID and PAGEID = ".$getPostPageRs[0]['PAGEID']." 
										order by COMPONENTORDER, ITEMORDER";
				$getPostItemRs = $myQuery->query($getPostItem, 'SELECT','NAME');
				$getPostItemRsCount = count($getPostItemRs);
				
				//loop on count of item
				for($x=0; $x<$getPostItemRsCount; $x++)
				{
					//if item post-ed
					if(isset($_POST[$getPostItemRs[$x]['ITEMNAME']]))
					{
						//============= ITEM POST-PROCESS ============
						executePhpEvent($myQuery, 'postprocess', 'item', $getPostItemRs[$x]['ITEMID']);
						//=========== EOF ITEM POST-PROCESS ==========
					}//eof if
				}//eof for
				
				//get post (previous) control
				$getPostControl = "select CONTROLID, CONTROLNAME from FLC_PAGE_CONTROL 
										where PAGEID = ".$getPostPageRs[0]['PAGEID'].$controlPermissionSQL;
				$getPostControlRs = $myQuery->query($getPostControl, 'SELECT','NAME');
				$getPostControlRsCount = count($getPostControlRs);
				
				//loop on count of control
				for($x=0; $x<$getPostControlRsCount; $x++)
				{
					//if control post-ed
					if(isset($_POST[$getPostControlRs[$x]['CONTROLNAME']]))
					{
						//============= CONTROL POST-PROCESS ============
						executePhpEvent($myQuery, 'postprocess', 'control', $getPostControlRs[$x]['CONTROLID']);
						//=========== EOF CONTROL POST-PROCESS ==========
						
						//get control type
						$getControlType = "select CONTROLTYPE from FLC_PAGE_CONTROL where CONTROLID = ".$getPostControlRs[$x]['CONTROLID'];
						$getControlTypeRs = $myQuery->query($getControlType,'SELECT', 'NAME');
						
						//switch control type
						switch($getControlTypeRs[0]['CONTROLTYPE'])
						{
							//save
							case '1':
								include('page_wrapper_post_save.php');
							break;
							
							//delete
							case '8':
								include('page_wrapper_post_delete.php');
							break;
						}//eof switch
					}//eof if
				}//eof for
			}//eof if
		}//eof if
	}//eof if
}//eof if
/*============ EOF PAGE POST SCRIPT ============*/
?>
