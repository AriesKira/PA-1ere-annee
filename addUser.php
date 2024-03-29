<?php
session_start();

require "functions.php";



//Est-ce que je recois ce que j'ai demandé

if (
	empty($_POST["email"]) ||
	!isset($_POST["firstname"]) ||
	!isset($_POST["lastname"]) ||
	empty($_POST["pseudo"]) ||
	empty($_POST["password"]) ||
	empty($_POST["passwordConfirm"]) ||
	empty($_POST["country"]) ||
	empty($_POST["cgu"]) ||
	!isset($_POST["birthday"]) ||
	count($_POST) != 9
) {
	echo ($_POST["email"] + $_POST["firstname"] + " " +  $_POST["lastname"] + " " +  $_POST["pseudo"] + " " +  $_POST["password"] + " " +  $_POST["passwordConfirm"] + " " +  $_POST["country"] + " " +  $_POST["cgu"] + " " +  $_POST["birthday"]);
	die("Tentative de Hack ... ");
}




//récupérer les données du formulaire
$email = $_POST["email"];
$firstname = $_POST["firstname"];
$lastname = $_POST["lastname"];
$pseudo = $_POST["pseudo"];
$pwd = $_POST["password"];
$pwdConfirm = $_POST["passwordConfirm"];
$birthday = $_POST["birthday"];
$country = $_POST["country"];
$cgu = $_POST["cgu"];



//nettoyer les données

$email = htmlspecialchars(strtolower(trim($email)));
$firstname = htmlspecialchars(ucwords(strtolower(trim($firstname))));
$lastname = htmlspecialchars(strtoupper(trim($lastname)));
$pseudo = htmlspecialchars(ucwords(strtolower(trim($pseudo))));


//vérifier les données
$errors = [];

//Email OK
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$errors[] = "Email incorrect";
} else {

	//Vérification l'unicité de l'email
	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("SELECT idUser from AROOTS_USERS WHERE email=:email");

	$queryPrepared->execute(["email" => $email]);

	if (!empty($queryPrepared->fetch())) {
		$errors[] = "L'email existe déjà";
	}
}

//prénom : Min 2, Max 45 ou empty
if (strlen($firstname) == 1 || strlen($firstname) > 40) {
	$errors[] = "Votre prénom doit faire plus de 2 caractères et moins de 40";
}

//nom : Min 2, Max 100 ou empty
if (strlen($lastname) == 1 || strlen($lastname) > 100) {
	$errors[] = "Votre nom doit faire plus de 2 caractères";
}

//pseudo : Min 4 Max 60 et uicité
if (strlen($pseudo) < 4 || strlen($pseudo) > 40) {
	$errors[] = "Votre pseudo doit faire entre 4 et 40 caractères";
} else {
	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("SELECT pseudo from AROOTS_USERS WHERE pseudo=:pseudo");

	$queryPrepared->execute(["pseudo" => $pseudo]);

	if (!empty($queryPrepared->fetch())) {
		$errors[] = "Ce pseudo est déjà pris";
	}
}

//Date anniversaire : YYYY-mm-dd
//entre 16 et 100 ans
$birthdayExploded = explode("-", $birthday);

if (count($birthdayExploded) != 3 || !checkdate($birthdayExploded[1], $birthdayExploded[2], $birthdayExploded[0])) {
	$errors[] = "date incorrecte";
} else {
	$age = (time() - strtotime($birthday)) / 60 / 60 / 24 / 365.2425;
	if ($age < 16) {
		$errors[] = "Vous devez avoir plus de 16 ans pour vous inscrire";
	}
}


//Mot de passe : Min 8, Maj, Min et chiffre
if (
	strlen($pwd) < 8 ||
	preg_match("#\d#", $pwd) == 0 ||
	preg_match("#[a-z]#", $pwd) == 0 ||
	preg_match("#[A-Z]#", $pwd) == 0
) {
	$errors[] = "Votre mot de passe doit faire plus de 8 caractères avec une minuscule, une majuscule et un chiffre";
}


//Confirmation : égalité
if ($pwd != $pwdConfirm) {
	$errors[] = "Votre mot de passe de confirmation ne correspond pas";
}

//Pays
$countryAuthorized = ["fr", "ml", "pl"];
if (!in_array($country, $countryAuthorized)) {
	$errors[] = "Votre pays n'existe pas";
}

$key = rdmKeyValues();

if (count($errors) == 0) {


	$queryPrepared = $pdo->prepare("INSERT INTO AROOTS_USERS (email, firstname, lastname, pseudo, country, birthday, pwd, mailKey) 
		VALUES ( :email , :firstname, :lastname, :pseudo, :country, :birthday, :pwd, :mailKey );");


	$pwd = password_hash($pwd, PASSWORD_DEFAULT);

	$queryPrepared->execute([
		"email" => $email,
		"firstname" => $firstname,
		"lastname" => $lastname,
		"pseudo" => $pseudo,
		"country" => $country,
		"birthday" => $birthday,
		"pwd" => $pwd,
		"mailKey" => $key,
	]);

	$queryPrepared2 = $pdo->prepare("SELECT idUser FROM AROOTS_USERS WHERE email = :email");
	$queryPrepared2-> execute(["email"=> $email]);
	$result = $queryPrepared2->fetch();
	
	$queryPrepared3 = $pdo->prepare("INSERT INTO AVATARS (userId) VALUES (:userId)");
	$queryPrepared3->execute(["userId"=> $result['idUser']]);


	sendVerifyMail($email,$pseudo,$key);

	header("Location: ./index.php");
} else {

	$_SESSION['errors'] = $errors;
	header("Location: ./index.php");
}


//Si aucune erreur insérer l'utilisateur en base de données puis rediriger sur la page de connexion


//Si il y a des erreurs rediriger sur la page d'inscription et afficher les erreurs
