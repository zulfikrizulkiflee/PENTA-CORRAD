<!doctype html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title><?php echo APP_FULL_NAME;?></title>
	<link rel="shortcut icon" type="image/x-icon" href="themes/modern_v2/images/favicon.png">
	<style type="text/css">
		html, body {
            margin: 0;
            font-family: Verdana;
            font-size: 16px;
            color: white;
            width: 100%;
            height: 100%;
        }
        body {
            background: url('themes/modern_v2/images/background01.jpg') no-repeat center center fixed; 
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
        }
        body:after {
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            display: block;
            content: '';
            background: url('themes/modern_v2/images/background00.png') no-repeat center center fixed; 
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
        }
        .container {
            z-index: 2;
        }
        .container input {
            width: 350px;
            text-align: center;
            font-size: 16px;
            border: 0;
            background-color: transparent;
            padding: 15px;
            color: white;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }
        .container .logo {
            width: 500px;
            margin-bottom: 70px;
        }
        .container .username, .container .password {
            border-bottom: 2px solid white;
        }
        .container .button {
            background-color: #FA4115;
            box-shadow: 0px 2px 5px 0px rgba(0,0,0,0.5);
            border-radius: 4px;
            cursor: pointer;
        }
        .container div {
            position: relative;
            text-align: center;
            margin-bottom: 30px;
        }
        input+label {
            top: 13px;
            z-index: -1;
            transition: all .3s ease-in-out;
        }
        input:focus+label, input.filled+label {
            top: -4px;
            font-size: 12px;
            color: #FA4115;
        }
        .footer {
            bottom: 0;
            padding: 15px;
        }
        .hidden {
            display: none;
        }
        .centerX { position: absolute; left: 50%; transform: translateX(-50%); }
        .centerY { position: absolute; top: 50%; transform: translateY(-50%); }
        .center { position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%); }
        textarea, input { outline: none; }
	</style>
	<script src="tools/jquery.js"></script>
</head>

<body>
    <form id="form1" name="form1" method="post" autocomplete="off">
        <div class="container center">
            <img class="logo" src="themes/modern_v2/images/logo.svg">
            <input type="text" class="hidden" id="userID">
            <div>
                <input type="text" class="username" id="userID" name="userID" onkeyup="monitorInput(this)">
                <label class="centerX">Username</label>
            </div>
            <div>
                <input type="password" class="password" id="userPassword" name="userPassword" onkeyup="monitorInput(this)">
                <label class="centerX">Password</label>
            </div>
            <div><input type="submit" class="button" name="login" id="login" value="LOGIN" /></div>
            <div class="error centerX"><?php if($error) echo '<div id=txtInvalid>Invalid username or password!</div>'; ?></div>
        </div>
    </form>
    <div class="footer centerX">Developed by Encoral Digital Solutions Sdn Bhd | Powered by Corrad v2.14</div>
    
    <script>       
        function monitorInput(e) {
            $(".error").css("opacity","0");
            if($(e).val()!="") $(e).addClass("filled");
            else $(e).removeClass("filled");
        }
    </script>
</body>
</html>