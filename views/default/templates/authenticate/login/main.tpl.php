
<div id="main">
			
				<div id="rightside">
				</div>
				
				<div id="content">
				<h1>Login to DINO SPACE!</h1>
				{error}
				<form action="authenticate/login" method="post">
				<label for="sn_auth_user">Username</label><br />
				<input type="text" id="sn_auth_user" name="sn_auth_user" /><br />
				<label for="sn_auth_pass">Password</label><br />
				<input type="password" id="sn_auth_pass" name="sn_auth_pass" /><br />
				<input type="hidden" id="referer" name="referer" value="{referer}" />
				<input type="submit" id="login" name="login" value="Login" />
				</form>			
				</div>
			
			</div>