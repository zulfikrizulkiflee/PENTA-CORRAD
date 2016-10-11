//check strength of password
function checkPasswordStrength(password)
{
	//strength
	var desc = new Array();
	desc[0] = "";
	desc[1] = "Weak";
	desc[2] = "Better";
	desc[3] = "Medium";
	desc[4] = "Strong";
	desc[5] = "Strongest";
	
	var score = 0;
	
	//if password bigger than 6 give 1 point
	if(password.length>6) score++;
	
	//if password bigger than 12 give another 1 point
	if(password.length>12) score++;
	
	//if password has both lower and uppercase characters give 1 point      
	if((password.match(/[a-z]/)) && (password.match(/[A-Z]/))) score++;
	
	//if password has at least one number give 1 point
	if(password.match(/\d+/)) score++;
	
	//if password has at least one special character give 1 point
	if(password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/)) score++;
	
	return desc[score];
}//eof function

//validate email address
function validateEmail(email) 
{
    var regExp = /\S+@\S+\.\S+/;
    return regExp.test(email);
}//eof function

//validate alphabet character
function validateAlpha(char)
{
	var regExp = /^[a-zA-Z]+$/;
    return regExp.test(char);
}//eof function

//validate numeric character
function validateNumeric(char)
{
	var regExp = /^[0-9]+$/;
    return regExp.test(char);
}//eof function

function validateValue(itemName)
{
	var currentItem = document.getElementsByName(itemName);
	var currentItemCount = currentItem.length;
	
	//loop on count of item
	for(x=0; x<currentItemCount; x++)
	{
		//reset by item type
		if(currentItem[x].tagName == 'select')
		{
			if(currentItem[x].selectedIndex == 0)
				return false;
		}//eof if
		else if(currentItem[x].type == 'checkbox' || currentItem[x].type == 'radio')
		{
			if(currentItem[x].checked == false)
				return false;
		}//eof elseif
		else
		{
			if(currentItem[x].value == '')
				return false;
		}//eof else
	}//eof for
	
	return true;
}//eof function

//capitalize initial letter
function initialCap(str)
{
	return str.substr(0,1).toUpperCase()+str.substr(1).toLowerCase()
}//eof function
