<div id="main">
			
				<div id="rightside">
				</div>
				
				<div id="content">
				<h1>Join DINO SPACE!</h1>
				{error}
				<form action="authenticate/register" method="post"> 
 
<label for="register_user">Username</label><br /> 
<input type="text" id="register_user" name="register_user" value="{register_user}" /><br /> 
 
<label for="register_password">Password</label><br /> 
<input type="password" id="register_password" name="register_password" value="" /><br /> 
 
<label for="register_password_confirm">Confirm password</label><br /> 
<input type="password" id="register_password_confirm" name="register_password_confirm" value="" /><br /> 
 
<label for="register_email">Email</label><br /> 
<input type="text" id="register_email" name="register_email" value="{register_email}" /><br /> 

<label for="register_dino_name">Name of dinosaur</label><br /> 
<input type="text" id="register_dino_name" name="register_dino_name" value="{register_dino_name}" /><br /> 

<label for="register_dino_breed">Breed of dinosaur</label><br /> 
<input type="text" id="register_dino_breed" name="register_dino_breed" value="{register_dino_breed}" /><br /> 

<label for="register_dino_gender">Gender of dinosaur</label><br /> 
<select id="register_dino_gender" name="register_dino_gender">
<option value="male">male</option>
<option value="female">female</option>
</select><br />

<label for="register_dino_dob">Dinosaurs Date of Birth (dd/mm/yy)</label><br /> 
<input type="text" id="register_dino_dob" name="register_dino_dob" value="{register_dino_dob}" /><br /> 
 
 
 
<label for="">Do you accept our terms and conditions?</label><br /> 
<input type="checkbox" id="register_terms" name="register_terms" value="1" /> <br />

<input type="submit" id="process_registration" name="process_registration" value="Create an account" /> 
</form> 
				
				
				</div>
			
			</div>