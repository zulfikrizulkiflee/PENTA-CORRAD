<?php
/**
* class HTML
*/
class HTML
{
	//=====class members declaration================
	//attribute
	protected $align;				//alignment
	protected $height;				//height
	protected $htmlclass;			//class
	protected $id;					//id
	protected $javascript;			//javascript
	protected $style;				//css
	protected $width;				//width
	
	//javascript attribute
	protected $onblur;				//onblur
	protected $onchange;			//onchange
	protected $onclick;				//onclick
	protected $ondblclick;			//ondblclick
	protected $onfocus;				//onfocus
	protected $onselect;			//onselect
	protected $onkeydown;			//onkeydown
	protected $onkeypress;			//onkeypress
	protected $onkeyup;				//onkeyup
	protected $onmousedown;			//onmousedown
	protected $onmousemove;			//onmousemove
	protected $onmouseout;			//onmouseout
	protected $onmouseover;			//onmouseover
	protected $onmouseup;			//onmouseup
	//=====class functions =========================
	
	//set html's attribute
	public function setAttribute($attribute,$value)
	{
		switch(strtolower($attribute))
		{
			case 'align'			: $this->align = $value;
				break;
			case 'class'			: $this->htmlclass = $value;
				break;
			case 'height'			: $this->height = $value;
				break;
			case 'id'				: $this->id = $value;
				break;
			case 'onblur'			: $this->onblur = $value;
				break;
			case 'onchange'			: $this->onchange = $value;
				break;
			case 'onclick'			: $this->onclick = $value;
				break;
			case 'ondblclick'		: $this->ondblclick = $value;
				break;
			case 'onfocus'			: $this->onfocus = $value;
				break;
			case 'onselect'			: $this->onselect = $value;
				break;
			case 'onkeydown'		: $this->onkeydown = $value;
				break;
			case 'onkeypress'		: $this->onkeypress = $value;
				break;
			case 'onkeyup'			: $this->onkeyup = $value;
				break;
			case 'onmousedown'		: $this->onmousedown = $value;
				break;
			case 'onmousemove'		: $this->onmousemove = $value;
				break;
			case 'onmouseout'		: $this->onmouseout = $value;
				break;
			case 'onmouseover'		: $this->onmouseover = $value;
				break;
			case 'onmouseup'		: $this->onmouseup = $value;
				break;
			case 'style'			: $this->style = $value;
				break;
			case 'width'			: $this->width = $value;
				break;
		}//eof switch
	}//eof function
	
	//return html's attribute
	public function getAttribute($att='')
	{
		//if attribute not given, return all
		if($att=='')
		{
			if(isset($this->align))
				$value.=' align="'.$this->align.'"';
					
			if(isset($this->htmlclass))
				$value.=' class="'.$this->htmlclass.'"';
						
			if(isset($this->height))
				$value.=' height="'.$this->height.'"';
							
			if(isset($this->id))
				$value.=' id="'.$this->id.'"';
							
			if(isset($this->onblur))
				$value.=' onblur="'.$this->onblur.'"';
				
			if(isset($this->onchange))
				$value.=' onchange="'.$this->onchange.'"';
				
			if(isset($this->onclick))
				$value.=' onclick="'.$this->onclick.'"';
				
			if(isset($this->ondblclick))
				$value.=' ondblclick="'.$this->ondblclick.'"';
				
			if(isset($this->onfocus))
				$value.=' onfocus="'.$this->onfocus.'"';
				
			if(isset($this->onselect))
				$value.=' onselect="'.$this->onselect.'"';
				
			if(isset($this->onkeydown))
				$value.=' onkeydown="'.$this->onkeydown.'"';
				
			if(isset($this->onkeypress))
				$value.=' onkeypress="'.$this->onkeypress.'"';
				
			if(isset($this->onkeyup))
				$value.=' onkeyup="'.$this->onkeyup.'"';
				
			if(isset($this->onmousedown))
				$value.=' onmousedown="'.$this->onmousedown.'"';
				
			if(isset($this->onmousemove))
				$value.=' onmousemove="'.$this->onmousemove.'"';
				
			if(isset($this->onmouseout))
				$value.=' onmouseout="'.$this->onmouseout.'"';
				
			if(isset($this->onmouseover))
				$value.=' onmouseover="'.$this->onmouseover.'"';
				
			if(isset($this->onmouseup))
				$value.=' onmouseup="'.$this->onmouseup.'"';
						
			if(isset($this->style))
				$value.=' style="'.$this->style.'"';
						
			if(isset($this->width))
				$value.=' width="'.$this->width.'"';	
		}//eof if				
		//else, return by attribute given
		else
		{
			//switch / select attribute type
			switch(strtolower($att))
			{
				case 'align'		: $value=$this->align;
					break;
				case 'class'		: $value=$this->htmlclass;
					break;
				case 'height'		: $value=$this->height;
					break;	
				case 'id'			: $value=$this->id;
					break;	
				case 'onblur'		: $value=$this->onblur;
					break;
				case 'onchange'		: $value=$this->onchange;
					break;
				case 'onclick'		: $value=$this->onclick;
					break;
				case 'ondblclick'	: $value=$this->ondblclick;
					break;
				case 'onfocus'		: $value=$this->onfocus;
					break;
				case 'onselect'		: $value=$this->onselect;
					break;
				case 'onkeydown'	: $value=$this->onkeydown;
					break;
				case 'onkeypress'	: $value=$this->onkeypress;
					break;
				case 'onkeyup'		: $value=$this->onkeyup;
					break;
				case 'onmousedown'	: $value=$this->onmousedown;
					break;
				case 'onmousemove'	: $value=$this->onmousemove;
					break;
				case 'onmouseout'	: $value=$this->onmouseout;
					break;
				case 'onmouseover'	: $value=$this->onmouseover;
					break;
				case 'onmouseup'	: $value=$this->onmouseup;
					break;
				case 'style'		: $value=$this->style;
					break;	
				case 'width'		: $value=$this->width;					
					break;
			}//eof switch
		}//eof else
		
		return $value;
	}//eof function
	
	//add html's javascript
	public function addJavascript($event,$script)
	{
		//if already have given event
		if($this->getAttribute($event))
			$script=';'.$script;
		
		switch(strtolower($event))
		{
			case 'onblur'			: $this->onblur .= $script;
				break;
			case 'onchange'			: $this->onchange .= $script;
				break;
			case 'onclick'			: $this->onclick .= $script;
				break;
			case 'ondblclick'		: $this->ondblclick .= $script;
				break;
			case 'onfocus'			: $this->onfocus .= $script;
				break;
			case 'onselect'			: $this->onselect .= $script;
				break;
			case 'onkeydown'		: $this->onkeydown .= $script;
				break;
			case 'onkeypress'		: $this->onkeypress .= $script;
				break;
			case 'onkeyup'			: $this->onkeyup .= $script;
				break;
			case 'onmousedown'		: $this->onmousedown .= $script;
				break;
			case 'onmousemove'		: $this->onmousemove .= $script;
				break;
			case 'onmouseout'		: $this->onmouseout .= $script;
				break;
			case 'onmouseover'		: $this->onmouseover .= $script;
				break;
			case 'onmouseup'		: $this->onmouseup .= $script;
				break;
		}//eof switch
	}//eof function
	
	//add html's css
	public function addStyle($style)
	{
		//if already have style
		if($this->style)
			$this->style.=';';
			
		$this->style.=$style;
	}//eof function
}//eof class
?>