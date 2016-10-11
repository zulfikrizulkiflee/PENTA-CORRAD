<?php
//validate that user have session
require_once('func_common.php');
validateUserSession();

//TODO - KEMASKAN PAGE NIH, BR TAMBAH CODEMIRROR

//if button save clicked
if($_POST['saveButton'])
{
	//backup current config file to cfg_bak folder
	if(!is_dir('conf_bak'))
	{
		mkdir('conf_bak',0777);
	}

	//if copy / backup success
	if(copy('conf.php','conf_bak/conf_'.date(YmdHis).'.php'))
	{
		//
		$_POST['configText']=str_replace('\'\'','\\\'',$_POST['configText']);

		//create new config file
		$bytesWritten = file_put_contents('conf.php',stripcslashes($_POST['configText']));
	}
}
?>
<script src="tools/codemirror/lib/codemirror.js"></script>
<script src="tools/codemirror/lib/util/searchcursor.js"></script>
<script src="tools/codemirror/lib/util/match-highlighter.js"></script>
<script src="tools/codemirror/lib/util/foldcode.js"></script>
<script src="tools/codemirror/mode/php/php.js"></script>
<script src="tools/codemirror/mode/clike/clike.js"></script>


<link rel="stylesheet" href="tools/codemirror/lib/codemirror.css">
<style type="text/css">
.CodeMirror {border: 1px solid #F0F0F0;}
.CodeMirror-scroll {height: 400px;}
.CodeMirror-focused span.CodeMirror-matchhighlight {background: #e7e4ff; !important}
.CodeMirror-fullscreen {display: block; position: absolute; top: 0; left: 0; width: 100%; z-index: 9999; background-color:white;}
.activeline {background: #e8f2ff !important;}
</style>





<div id="breadcrumbs">System Administrator / Configuration / System Configuration</div>
<h1>System Configuration
  <?php if($_POST['editConfig']) echo ' - Edit Mode'?>
</h1>
<?php
//if button save clicked
if($_POST['saveButton'])
{
	//notification
	showNotificationInfo('Configuration file has been updated. '.$bytesWritten.' bytes written.');
}
?>
<form name="form1" method="post" action="" style="margin:0; padding:0;">
  <?php if($_POST['editConfig']) { ?>
  <textarea name="configText" rows="32" id="configText" style="margin:0px;width:99%; font-size:11px; color:#333333; margin-left:0px; margin-bottom:10px; border:1px solid #00CCCC"><?php echo file_get_contents('conf.php'); ?></textarea>
  <script>//CKEDITOR.replace('configText');</script>
  <?php } else { ?>
  <div style="overflow:scroll; width:99%; margin-bottom:10px;padding-bottom:10px; margin-left:0px;" >
    <?php highlight_string(file_get_contents('conf.php')); ?>
  </div>
  <?php } ?>
  <div align="right" style="width:98%; <?php if($_POST['editConfig']) { ?>padding-top:10px;<?php } ?>">
    <?php if($_POST['editConfig']) { ?>
    <input name="cancel" type="submit" class="inputButton" id="cancel" value="Cancel" />
    <input name="saveButton" type="submit" class="inputButton" id="saveButton" value="Save Changes" onClick="if(window.confirm('Are you sure you want to SAVE changes?')) {return true} else {return false}" />
    <?php } ?>
    <?php if(!isset($_POST['editConfig'])) { ?>
    <input name="editConfig" type="submit" class="inputButton" id="editConfig" value="Edit Configuration" />
    <?php } ?>
  </div>
</form>



<script>
function isFullScreen(cm) {
  return /\bCodeMirror-fullscreen\b/.test(cm.getWrapperElement().className);
}
function winHeight() {
  return window.innerHeight || (document.documentElement || document.body).clientHeight;
}
function setFullScreen(cm, full) {
  var wrap = cm.getWrapperElement(), scroll = cm.getScrollerElement();
  if (full) {
	wrap.className += " CodeMirror-fullscreen";
	scroll.style.height = winHeight() + "px";
	document.documentElement.style.overflow = "hidden";
  } else {
	wrap.className = wrap.className.replace(" CodeMirror-fullscreen", "");
	scroll.style.height = "";
	document.documentElement.style.overflow = "";

	//to make window jump to detail section after close 'Full Screen'
	window.location.hash="detail_section";
	cm.focus();
  }
  cm.refresh();
}
CodeMirror.connect(window, "resize", function() {
  var showing = document.body.getElementsByClassName("CodeMirror-fullscreen")[0];
  if (!showing) return;
  showing.CodeMirror.getScrollerElement().style.height = winHeight() + "px";
});

var editor = CodeMirror.fromTextArea(document.getElementById("configText"), {
	lineNumbers: true,
	lineWrapping: false,
	matchBrackets: true,
	mode: "text/x-php",
	indentUnit: 4,
	indentWithTabs: true,
	enterMode: "keep",
	tabMode: "shift",
	autoClearEmptyLines: true,
	onCursorActivity: function() {
    editor.setLineClass(hlLine, null, null);
    hlLine = editor.setLineClass(editor.getCursor().line, null, "activeline");
	editor.matchHighlight("CodeMirror-matchhighlight");
	},
	extraKeys: {
        "F11": function(cm) {setFullScreen(cm, !isFullScreen(cm));},
        "Esc": function(cm) {if (isFullScreen(cm)) setFullScreen(cm, false);}
	}
});

var hlLine = editor.setLineClass(0, "activeline");
</script>


