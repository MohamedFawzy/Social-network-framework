			<div id="main">
			
				<div id="rightside">
				</div>
				
				<div id="content">
					<h1>DINO SPACE! Members List</h1>
					<p>Search results for "{public_name}"</p>
					<!-- START members -->
					<p><strong>{name}</strong></p>
					<p>Keeper of <strong>{dino_name}</strong> a <strong>{dino_gender} {dino_breed}</strong></p>
					<hr />
					<!-- END members -->
					<p>Viewing page {page_number} of {num_pages}</p>
					<p>{first} {previous} {next} {last}</p>
					
					<form action="members/search" method="post">
					<h2>Search for another member?</h2>
					<label for="name">Their name</label><br />
					<input type="text" id="name" name="name" value="" /><br />
					<input type="submit" id="search" name="search" value="Search" />
					</form>
				</div>
			
			</div>