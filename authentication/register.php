<div class="container">
	<div class="row mt-4">
		<div class="col-md-3"></div>
		<div class="col-md-6">
		<?php
			if (!empty($_SESSION['errors'])) {
				?><p> <?php print_r($_SESSION['errors']);?></p><?php
				unset($_SESSION['errors']);
				//session_destroy();
			}	?>
			<form method="POST" action="addUser.php">
				<input type="email" class="form-control" name="email" placeholder="Votre email" required="required"><br>
				<input type="text" class="form-control" name="firstname" placeholder="Votre prénom"><br>
				<input type="text" class="form-control" name="lastname" placeholder="Votre nom"><br>
				<input type="text" class="form-control" name="pseudo" placeholder="Votre pseudo" required="required"><br>
				<input type="date" class="form-control" name="birthday" placeholder="Votre date de naissance"><br>
				<input type="password" class="form-control" name="password" placeholder="Votre mot de passe" required="required"><br>
				<input type="password" class="form-control" name="passwordConfirm" placeholder="confirmation" required="required"><br>
				<select name="country" class="form-control">
					<option value="fr">France</option>
					<option value="pl">Pologne</option>
					<option value="ml">Mali</option>
				</select>
				<input type="checkbox" id="cguCheckbox" name="cgu" class=" mt-4" required="required"> <label for="cguCheckbox">CGU</label> <br>
				<input type="submit"  class="btn btn-outline-light mb-4 mt-4 submitButton" value="S'inscrire">
			</form>
		</div>
	</div>
</div>