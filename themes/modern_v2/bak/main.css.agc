/*
* Name           : Modern
* Author         : Encoral Digital Solutions Sdn Bhd
* Version        : 2.0
*/

/*--GENERAL--*/
body{
	/*font-family: "Open Sans", Lucida Grande ,Tahoma,sans-serif !important;*/
	font-family: Arial, Lucida Grande ,Tahoma,sans-serif !important;*/
	font-size: 8pt;
	background-color: white;
}
table{
	border-collapse: collapse;
}
a{ color: blue; }
select{ border: 1px solid #B9B9B9; }
input,textarea,select { font-family: inherit;color:#5A5A5A; font-size:10pt; border: 1px solid #d3d3d3; padding:3px 5px 3px 5px; 

 box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}
input {
 padding:4px 5px 4px 5px; 
}

/*--HEADER--*/
*{
    -webkit-transition: all 0.25s ease-in-out;
-moz-transition: all 0.25s ease-in-out;
-ms-transition: all 0.25s ease-in-out;
-o-transition: all 0.25s ease-in-out;
transition: all 0.25s ease-in-out;
}

#header{
	background-color: white;
	height: 50px !important; /*overwrite layout properties*/
    weight: 1300px;
	border-bottom: 4px solid #303398;
    background-image: url(images/bg.png);
    background-repeat: no-repeat;
    
}
#hide-sidebar,
#hide-topbar{
	position: absolute;
	top: 0;
	left: 0;
	padding: 0px 20px;
	line-height: 54px;
	height: 50px;
	background: white url('images/hide_sidebar_icon.png') no-repeat center center;
	cursor: pointer;
	border-bottom: 4px solid #303398;
}
#hide-sidebar:hover{
    background-color: #303398;
	border-bottom: 4px solid white;
    background: #303398 url('images/hide_sidebar_icon-hover.png') no-repeat center center;
}
.topMenuDiv:hover > .topIcon {
    background: url('images/top_icons-hover.png');
}



.topMenuDiv:hover{
	background-color: #303398;
	border-bottom: 4px solid white;
    color: white;
}
#logo{
	background: url('images/logo.png') no-repeat;
	width: 273px;
	height: 36px;
	position: absolute;
	top: 8px;
	left: 43px;
}
.topMenuDiv{
	text-align: center;
	position: absolute;
	background-color: white;
	height: 38px;
	padding: 6px 10px;
	top: 0;
	border-bottom: 4px solid #303398;
    color: #303398;
}
.topIcon{
	background-image: url('images/top_icons.png');
	background-repeat: no-repeat;
	width: 37px;
	height:38px;
}
#topLogoutLinkIcon{ background-position: -74px 0px; }
#topHomeLinkIcon  { background-position: 0px 0px; }
#topDateIcon      { background-position: -37px 0px; float:right;}
#topLogoutLink{
	right: 0;
	background-position: center right;
}
#topHomeLink{
	right: 57px;
	border-right: 1px solid #3B7692;
}
#topTime{
	right: 115px;
	border-right: 1px solid #3B7692;
}

/*--SIDEBAR--*/
#sidebar{
	background-color: #f5f5f5;
	max-width: 250px;
}

/*Profile Block*/
#profileBlock{
	padding: 8px;
	background-color: #fff;
	float: left;
	width: 234px;
	color: #303398;
    background-image: url(images/bg1.PNG);
    background-repeat: no-repeat;
}
#sidebar .profileImageBlock{
	float: left;
	margin-right: 5px;
}

#sidebar .profileImageBlock img{
    border-radius: 50%;
}

/*search*/
#sidebar .searchMenuBlock{
	clear: both; /*IE fix*/
	padding: 9px;
	background-color: #dbdbdb;
	text-align: ;
	border-bottom: 0px solid #bdbdbd;
}
#sidebar .searchMenuBlock input{
	border: none;
	padding: 5px;
	width: 120px;
	/*font-family: Open Sans;*/
	font-family: Arial;
	background: #ffffff url('images/search-icon.png') no-repeat right center;
	font-size:12px;
	border-right: 3px solid #ffffff;
    
    

}
#sidebar .searchMenuBlock input:hover,
#sidebar .searchMenuBlock input:active,
#sidebar .searchMenuBlock input:focus {
	border: none;
	padding: 5px !important;
	width: 219px !important;
	/*font-family: Open Sans;*/
	font-family: Arial;
	
	background: #ffffff url('images/search-icon.png') no-repeat right center;
	font-size:12px;
	border-right: 3px solid #ffffff;
    -webkit-transition: width 0.25s ease-out;
    -moz-transition: width 0.25s ease-out;
    -ms-transition: width 0.25s ease-out;
    -o-transition: width 0.25s ease-out;
    transition: width 0.25s ease-out;
}

#menu_suggestions{
	max-width: 400px !important;
	margin-left: 0 !important;
	border: 3px solid #B6BABE;
	box-shadow: #888 0px 3px 4px !important;
	-webkit-box-shadow: #888 0px 3px 4px !important;
	-moz-box-shadow: #888 0px 3px 4px !important;
	z-index: 99;
}
#menu_suggestions table{
	width: 100%;
	text-align: left !important;
}
#menu_suggestions table th,
#menu_suggestions table td{
	padding: 5px;
}
#menu_suggestions table th.listingHead{
	background-color: #E7E7E7;
}
#menu_suggestions a{
	font-size: 11px;
	text-decoration: none;
	color: #333;
	display: block;
	border-bottom: 1px dotted #DADADA;
}
#menu_suggestions a:hover{
	color: #FFF;
	background-color: #428BCE;
}

/*side menu list*/
ul.sideMenuList{
	padding: 0;
	list-style-type: none;
	margin: 0;
}
ul.sideMenuList li a{
	display: block;
	padding: 8px;
	color: white;
	text-decoration: none;
}
ul.sideMenuList li a.noChild.menuActive{
	font-weight: normal;
    color: #FFFFFF;
    background-color: #303398;
}
ul.sideMenuList li a.hasChild{
	font-weight: 350;
    /*font-family: Open Sans;*/
    font-family: Arial;
    font-size: 13px;
    background-color: #575757;
    color: white;
}
ul.sideMenuList li a.hasChild:hover{
	background-color: #d3d3d3;
    color: #303398;
    background-image: url(images/icon_default_folder.png)
}
ul.sideMenuList.menuLevel1 > li > a.hasChild{
	background-color: #4c4b4c;
    color: white;
    
}

ul.sideMenuList.menuLevel1 > li > a.hasChild:hover{
	background-color: #d3d3d3;
    color:#303398;
    background-image: url(images/icon_default_folder.png)
}



ul.sideMenuList.menuLevel1{
	max-width: 250px;
    background-color: #7c7c7c;
}
ul.sideMenuList.menuLevel1 a:hover{
	background-color: #d3d3d3;
    color: #303398;
    background-image: url(images/icon_default_file.png)
}
ul.sideMenuList li a.noChild:hover{
	
}

/* side menu icons*/
ul.sideMenuList.menuLevel1 li > a{ padding-left: 30px; background-position: 8px 7px; }
ul.sideMenuList.menuLevel2 li > a{ padding-left: 51px; background-position: 30px 7px; }
ul.sideMenuList.menuLevel3 li > a{ padding-left: 71px; background-position: 50px 7px; }
ul.sideMenuList.menuLevel4 li > a{ padding-left: 91px; background-position: 70px 7px; }
ul.sideMenuList.menuLevel5 li > a{ padding-left: 111px; background-position: 90px 7px; }
ul.sideMenuList.menuLevel6 li > a{ padding-left: 131px; background-position: 110px 7px; }
ul.sideMenuList.menuLevel7 li > a{ padding-left: 151px; background-position: 130px 7px; }
ul.sideMenuList.menuLevel8 li > a{ padding-left: 171px; background-position: 150px 7px; }
ul.sideMenuList.menuLevel9 li > a{ padding-left: 191px; background-position: 170px 7px; }
ul.sideMenuList.menuLevel10 li > a{ padding-left: 211px; background-position: 190px 7px; }

/*--TOPMENU--*/
#topMenuBar{
	background-color: #DFDFDF;
	height: 35px;
}
/*search*/
#topMenuBar .searchMenuBlock{
	float: right;
	padding: 4px;
	position: relative;
}
#topMenuBar .searchMenuBlock input{
	padding: 5px;
	font-family: inherit;
	background: #ffffff url('images/search-icon.png') no-repeat right center;
	border: none;
	width: 200px;
	font-size:11px;
	border-right: 3px solid #ffffff;
}
#topMenuBar .searchMenuBlock #menu_suggestions{
	height: 300px;
	width: 250px;
}
/*profile*/
#topMenuBar #profileName{
	height: 35px;
	line-height: 35px;
	padding: 0 10px 0 26px;
	float:right;
	background: #CACACA url('images/usericon.png') no-repeat 5px center;
	cursor: pointer;
}
#topMenuBar #profileBlock{
	position: absolute;
	z-index: 100;
	right: 0;
	top: 89px;
}
ul.topMenuList ul{display: none;}
ul.topMenuList li:hover > ul{display: block;}
ul.topMenuList ul{position: absolute; left:-1px; top:98%; background-color: #DFDFDF; z-index: 1000; border: 1px solid #b4b4b4; box-shadow:0px 0px 2px #b4b4b4;}
ul.topMenuList ul ul{position: absolute;left:98%;top:0;}

ul.topMenuList,
ul.topMenuList ul {
	margin: 0;
	list-style:none;
	padding: 0;
	float: left;
}
ul.topMenuList ul{
	min-width: 180px;
}
ul.topMenuList li{
	display:block;
	float: left;
}
ul.topMenuList.menuLevel1 > li > a{
	border-right: 1px solid #CACACA;
	line-height: 35px;
	padding: 11px 8px 11px 28px;
}
ul.topMenuList li a{
	color: #333333;
	text-decoration: none;
	white-space: nowrap;
	padding: 7px 8px 7px 28px;
}
ul.topMenuList ul li{
	float: none;
	/*border-bottom: 1px dotted #33618B;*/
}
ul.topMenuList li:hover{
	position:relative;
}
ul.topMenuList li a:hover{
	position:relative;
	background-color: #D3D3D3;
}
ul.topMenuList ul li a{
	display: block;
}

/* top menu icons*/
ul.topMenuList.menuLevel1 > li > a{
	background-position: 8px 9px;
}
ul.topMenuList ul a{
	background-position: 8px 6px;
}



/*--CONTENT--*/
#content.withSideBarLeft{
	margin-left: 250px;
	padding: 15px;
}
#content.withSideBarRight{
	margin-right: 250px;
	padding: 15px;
}
#content.withTopMenu{
	padding: 15px;
}
#content.pageBorderEnabled{
	border: 4px solid #fff;
	background-color: #FDFDFD;
}
#content.withSideBarLeft.pageBorderEnabled{
	margin: 10px 10px 10px 260px;
}
#content.withSideBarRight.pageBorderEnabled{
	margin: 10px 260px 10px 10px;
}
#content.withTopMenu.pageBorderEnabled{
	margin: 10px 10px 10px 10px;
	padding: 10px;
}
#breadcrumbs{
	background-color: #F9F9F9;
    padding: 10px;
    /* text-shadow: 1px 1px 1px #FFF; */
    color: #8E8E8E;
}
#sideLeftContent{
	width:235px;
	margin: 10px auto 0 auto;
	overflow: auto;
}

#content > h1{
	/*font-family: Open Sans, Arial , sans-serif ;*/
	font-family: Arial , sans-serif ;
	font-weight: lighter;
	font-size: 25px;
}
#content .tableContent, #sideLeftContent .tableContent{
	width: 100%;
	border: 0px solid #080a4b !important;
	background-color: #f2f2f2;
}
#content .tableContent th, #sideLeftContent .tableContent th{
	background-color: #303398;
	padding: 8px;
	text-align: left;
	color: #EEE;
	font-size: 13px;
    /*font-family: Open Sans, Arial, sans-serif;*/
    font-family: Arial, sans-serif;
    font-weight: 300;

}
#content .tableContent th.listingHead, #sideLeftContent .tableContent th.listingHead{
	background-color: #4d50bc;
	
}
#content .tableContent td, #sideLeftContent .tableContent td{
	padding: 4px;
    padding-left: 10px;
    padding-right: 10px;
    padding-bottom: 10px;
    padding-top: 10px;

}
#content .tableContent td.inputLabel{
	/*background-color:#F7F7F7;*/
	font-weight:bold;
	
	max-width:160px;
	width:150px;

}
#content .tab
{
	/*border: 1px solid #215B88 !important;*/

}


#content .tab td
{
	padding: 4px;
	border: 1px solid #cccccc;
	background-color: #4681AA;
	padding:8px;



	color:#fff;
}
#content .tab .componentArea
{

	border: 1px solid #cccccc;
	background-color: #ffffff;
	padding:10px ;


	color:#fff;
}
#content .tab .componentArea td
{
	padding:4px;
	color:#000;
	background-color: #ffffff;

}

#content .tab td.active
{
font-weight:bold;
}

.contentButtonFooter{
	text-align: right;
}

#content iframe.sysadmin{
	border:1px solid #CCCCCC;
}


/*--OTHERS--*/
.inputButton{
	border: none;
	background-color: #4d50bc;
	padding: 5px 10px;
	color: #FFF;
	font-family: inherit;
	font-size: 11px;
    border-radius: 2px; 
}
.inputButton:hover{
	background-color: #303398;
}
#content.lov{
	position: static !important;
}
.notification{
	position: fixed;
	right: 15px;
}
.notificationInfo{
   padding: 10px;
   background-color: #C9FFC7;
   border-bottom: 2px solid #A6D8A1;
}
.notificationError{
	padding: 10px;
	background-color: #FFC7C7;
	border-bottom: 2px solid #DA9A9A;
}
#bottom{
	clear: both;
}
#footer{
	width: 100%;
	text-align: center;
	color:#8D8D8D;
	margin: 10px 0;
	font-size:10px;
	padding-bottom:20px;

}
#pageProperties{
	border-bottom: 1px dotted #888;
	padding: 5px;
	text-align: right;
	color: #888;
}
#hide-header{
	display: inline-block;
	vertical-align: middle;
	cursor: pointer;
}
#languageSelector{
	display: inline-block;
	padding: 0 10px;
}
#languageName{
	font-size: 7pt;
	margin-right: 5px;
}
#languageName:hover{
	cursor: pointer;
	text-decoration: underline;
}
#languageSelector img{
	vertical-align: middle;
}
#zoomSelector{
	display: inline-block;
	padding: 0 10px;
}
