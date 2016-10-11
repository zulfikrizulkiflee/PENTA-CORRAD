<?php
/*DAL*/
class DAL extends dbQuery
{
	//attribute
	public $table;
	public $field;
	public $where;
	
	//get parameter
	public function getParameter($array)
	{
		if(is_array($array))
		{
			$arrayCount=count($array);		//count array
			$fieldCount=0;					//count field
			
			for($x=0;$x<$arrayCount;$x++)
			{
				if($x==0)
					$this->table=$array[$x];
				else
					if($array[$x][0]=='?')
					{
						$tempWhere=explode('?',$array[$x],2);
						$this->where=$tempWhere[1];
					}//eof if
					else
						$this->field[$fieldCount++]=$array[$x];
			}//eof for
		}//eof if
	}//eof function
	
	//select
	public function select($sql,$returnType='INDEX')
	{
		return $this->query($sql,'SELECT',$returnType);
	}//eof function
	
	//insert
	public function insert()
	{
		//call getParameter function
		$this->getParameter(func_get_args());
		
		$fieldCount=count($this->field);
		
		//loop on count field
		for($x=0;$x<$fieldCount;$x++)
		{
			$temp=explode('=',$this->field[$x],2);
			$tempField[$x]=$temp[0];	//field
			$tempValue[$x]=$temp[1];	//value
		}//eof for
		
		//generate insert statement
		$sql="insert into ".$this->table." (".implode(',',$tempField).") values (".implode(',',$tempValue).")";
		
		return $this->query($sql,'RUN');
	}//eof function
	
	//update
	public function update()
	{
		//call getParameter function
		$this->getParameter(func_get_args());
		
		//generate update statement
		$sql="update ".$this->table." set ".implode(',',$this->field)."";
		
		//if have where clause
		if($this->where)
			$sql.=" where ".$this->where;
		
		return $this->query($sql,'RUN');
	}//eof function
	
	//delete
	public function delete()
	{
		//call getParameter function
		$this->getParameter(func_get_args());
		
		//generate delete statement
		$sql="delete from ".$this->table;
		
		//if have where clause
		if($this->where)
			$sql.=" where ".$this->where;
		
		return $this->query($sql,'RUN');
	}//eof function
}//eof class
?>