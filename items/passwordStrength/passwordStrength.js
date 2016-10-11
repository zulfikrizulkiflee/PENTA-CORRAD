//show password strength
function showPasswordStrengthExt(password)
{
	var pwdStrength = checkPasswordStrength(password);

	document.getElementById('passwordStrength').innerHTML = pwdStrength;
	document.getElementById('passwordStrength').className = 'passwordStrength'+pwdStrength;
}//eof function
