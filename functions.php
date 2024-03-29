<?php


require "config.inc.php";

function connectDB()
{
	//création d'une nouvelle connexion à notre bdd
	try {

		$pdo = new PDO(DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT, DB_USER, DB_PWD);

		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (Exception $e) {
		die("Erreur SQL " . $e->getMessage());
	}


	return $pdo;
}


function createToken()
{
	$token = sha1(md5(rand(0, 100) . "gdgfm432") . uniqid());
	return $token;
}


function updateToken($userId, $token)
{

	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("UPDATE AROOTS_USERS SET token=:token WHERE idUser=:idUser");
	$queryPrepared->execute(["token" => $token, "idUser" => $userId]);
}


function isConnected()
{

	if (!isset($_SESSION["email"]) || !isset($_SESSION["token"])) {
		return false;
	}

	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("SELECT idUser FROM AROOTS_USERS WHERE email=:email AND token=:token");
	$queryPrepared->execute(["email" => $_SESSION["email"], "token" => $_SESSION["token"]]);

	return $queryPrepared->fetch();
}



function displayCountryFlag($results)
{

	if ($results["country"] == "fr") {
		$countryFlag = 'france';
	} elseif ($results["country"] == "pl") {
		$countryFlag = 'pologne';
	} elseif ($results["country"] == "ml") {
		$countryFlag = 'mali';
	}

	$countryDisplay = '<img src="stylesheet/images/flags/' . $countryFlag . '.png">';
	echo '' . $countryDisplay . '';
}


function sendVerifyMail($email, $pseudo, $key)
{

	$to = $email;
	$subject = 'Confirmation de mail';
	$message = 'Bienvenue sur ARoots' . "\r\n" . 'Veuillez cliquez sur le lien suivant pour valider votre email : http://141.94.251.167/authentication/verifyMail.php?key=' . $key . '&pseudo=' . $pseudo . "\r\n" . 'Cordalement,' . "\r\n" . "L'équipe AROOTS";
	$headers = 'From: <arootsverify@gmail.com>'       . "\r\n" .
		'Reply-To: <arootsverify@gmail.com>' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	mail($to, $subject, $message, $headers);
}

function rdmKeyValues()
{
	return mt_rand();
}

function isValidated($idUser) {

	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("SELECT validated FROM AROOTS_USERS where idUser= :idUser");
	$queryPrepared->execute(["idUser" => $idUser]);
	$results = $queryPrepared->fetch();

	$validated = $results[0];

	if ($validated == 1) {
		return true;
	}

	return false;
}


function isWebmaster($idUser)
{
	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("SELECT userRole FROM AROOTS_USERS where idUser = :idUser");
	$queryPrepared->execute(["idUser" => $idUser]);
	$results = $queryPrepared->fetch();

	$webmaster = $results['userRole'];

	if ($webmaster == 2) {
		return true;
	}

	return false;
}


function isAdmin($idUser){
	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("SELECT userRole FROM AROOTS_USERS where idUser = :idUser");
	$queryPrepared->execute(["idUser" => $idUser]);
	$results = $queryPrepared->fetch();

	$admin = $results['userRole'];

	if ($admin == 3) {
		return true;
	}

	return false;
}

function getThreadAuthor($authorID) {

	$pdo = connectDB();
	$getAuthorName = $pdo -> prepare("SELECT pseudo FROM AROOTS_USERS WHERE idUser =:idUser");
	$getAuthorName ->execute(["idUser" => $authorID]);
	$authorName = $getAuthorName -> fetch();

	return $authorName[0];
}

function getThreadCommentAuthor($authorID) {

	$pdo = connectDB();
	$getAuthorName = $pdo -> prepare("SELECT pseudo FROM AROOTS_USERS WHERE idUser =:idUser");
	$getAuthorName ->execute(["idUser" => $authorID]);
	$authorName = $getAuthorName -> fetch();

	return $authorName[0];
}

function hasLiked($userId,$therdId) {
	
	$pdo = connectDB();
	$verifyLike = $pdo ->prepare("SELECT count(idThread) FROM THREAD_LIKE WHERE idThread = :idThread AND idUser=:idUser");
	$verifyLike->execute(['idThread'=>$therdId,'idUser'=>$userId]);
	$isLiked = $verifyLike ->fetch();

	if ($isLiked[0] != 0) {
		return true;
	}
	return false;
}
function hasLikedThreadComment($userId,$commentId) {
	
	$pdo = connectDB();
	$verifyLike = $pdo ->prepare("SELECT count(commentId) FROM THREAD_COMMENT_LIKE WHERE commentId = :commentId AND userId=:userId");
	$verifyLike->execute(['commentId'=>$commentId,'userId'=>$userId]);
	$isLiked = $verifyLike ->fetch();

	if ($isLiked[0] != 0) {
		return true;
	}
	return false;
}

function likeThread($userId,$threadId) {

	$pdo = connectDB();
	$setLike = $pdo->prepare("INSERT INTO THREAD_LIKE (idUser,idThread) VALUES (:idUser,:idThread)");
	$setLike -> execute(['idUser'=>$userId,'idThread'=>$threadId]);

}
function likeThreadComment($userId,$commentId) {

	$pdo = connectDB();
	$setLike = $pdo->prepare("INSERT INTO THREAD_COMMENT_LIKE (userId,commentId) VALUES (:userId,:commentId)");
	$setLike -> execute(['userId'=>$userId,'commentId'=>$commentId]);

}

function unLikeThread($userId,$threadId) {

	$pdo = connectDB();
	$unsetLike = $pdo->prepare("DELETE FROM THREAD_LIKE WHERE idUser=:idUser AND idThread=:idThread");
	$unsetLike->execute(['idUser'=>$userId,'idThread'=>$threadId]);

}
function unLikeThreadComment($userId,$commentId) {

	$pdo = connectDB();
	$unsetLike = $pdo->prepare("DELETE FROM THREAD_COMMENT_LIKE WHERE userId=:userId AND commentId=:commentId");
	$unsetLike->execute(['userId'=>$userId,'commentId'=>$commentId]);

}

function setLike($userId,$contentId) {
	if(hasLiked($userId,$contentId)) {
		unLikeThread($userId,$contentId);
	}else{
		likeThread($userId,$contentId);
	}
}

function setLikeComment($userId,$contentId) {
	if(hasLikedThreadComment($userId,$contentId)) {
		unLikeThreadComment($userId,$contentId);
	}else{
		likeThreadComment($userId,$contentId);
	}
}

function hasImage($threadId) {
	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("SELECT picture FROM AROOTS_THREAD WHERE idThread=:idThread");
	$queryPrepared ->execute(['idThread'=> $threadId]);
	$results = $queryPrepared->fetch();

	if ($results[0]== null) {
		return false;
	}
	return true;
}
function commentHasImage($idThreadComment) {
	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("SELECT picture FROM THREAD_COMMENT WHERE idThreadComment=:idThreadComment");
	$queryPrepared ->execute(['idThreadComment'=> $idThreadComment]);
	$results = $queryPrepared->fetch();

	if ($results[0]== null) {
		return false;
	}
	return true;
}

function threadLikes($threadId) {
	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("SELECT count(idThread) FROM THREAD_LIKE WHERE idThread = :idThread");
	$queryPrepared ->execute(['idThread'=> $threadId]);
	$likes = $queryPrepared ->fetch();
	return $likes[0];
}
function threadCommentLikes($commentId) {
	$pdo = connectDB();
	$queryPrepared = $pdo->prepare("SELECT count(commentId) FROM THREAD_COMMENT_LIKE WHERE commentId = :commentId");
	$queryPrepared ->execute(['commentId'=> $commentId]);
	$likes = $queryPrepared ->fetch();
	return $likes[0];
}


 /* Fonctions de redimension des images */

 function resizeImageJpeg($source, $dst, $width, $height, $quality) {
	$imageSize = getimagesize($source);
	$imageRessource = imagecreatefromjpeg($source);
	$imageFinal = imagecreatetruecolor($width, $height);
	$final = imagecopyresampled($imageFinal, $imageRessource, 0, 0, 0, 0, $width, $height, $imageSize[0], $imageSize[1]);

	imagejpeg($imageFinal, $dst, $quality);
}


function resizeImagePng($source, $dst, $width, $height, $quality) {
	$imageSize = getimagesize($source);
	$imageRessource = imagecreatefrompng($source);
	$imageFinal = imagecreatetruecolor($width, $height);
	$final = imagecopyresampled($imageFinal, $imageRessource, 0, 0, 0, 0, $width, $height, $imageSize[0], $imageSize[1]);

	imagepng($imageFinal, $dst, $quality);
}

function resizeImageGif($source, $dst, $width, $height, $quality) {
	$imageSize = getimagesize($source);
	$imageRessource = imagecreatefromgif($source);
	$imageFinal = imagecreatetruecolor($width, $height);
	$final = imagecopyresampled($imageFinal, $imageRessource, 0, 0, 0, 0, $width, $height, $imageSize[0], $imageSize[1]);

	imagegif($imageFinal, $dst, $quality);
}



function validateUser($user) {

	$pdo = connectDB();
    $queryPrepared = $pdo->prepare("UPDATE AROOTS_USERS SET validated = 1 WHERE idUser = :idUser");
    $queryPrepared -> execute(['idUser'=> $user]);

}
//DELETE USER
function deleteAvatar($user) {
	$pdo = connectDB();
    $queryPrepared = $pdo->prepare("DELETE FROM AVATARS WHERE userId = :userId");
    $queryPrepared -> execute(['userId'=> $user]);
}

function deleteUserThreads($user) {
	$pdo = connectDB();
    $queryPrepared = $pdo->prepare("DELETE FROM AROOTS_THREAD WHERE author = :author");
    $queryPrepared -> execute(['author'=> $user]);
}

function deleteLikedUserThread($user) {
	$pdo = connectDB();
	$getUserThreads = $pdo -> prepare("SELECT idThread FROM AROOTS_THREAD WHERE author= :author");
	$getUserThreads -> execute(['author'=>$user]);
	$threads = $getUserThreads->fetchAll();

	foreach ($threads as $thread) {
		$deleteLikes = $pdo ->prepare("DELETE FROM THREAD_LIKE WHERE idThread = :idThread ");
		$deleteLikes ->execute(['idThread'=> $thread['idThread']]);
	}

}

function deleteUserLikes($user) {
	$pdo = connectDB();
    $queryPrepared = $pdo->prepare("DELETE FROM THREAD_LIKE WHERE idUser = :idUser");
    $queryPrepared -> execute(['idUser'=> $user]);
}

function deleteUserComments($user) {
	$pdo = connectDB();
    $queryPrepared = $pdo->prepare("DELETE FROM THREAD_COMMENT WHERE author = :author");
    $queryPrepared -> execute(['author'=> $user]);
}

function deleteUserLikedComments($user) {
	$pdo = connectDB();
    $queryPrepared = $pdo->prepare("DELETE FROM THREAD_COMMENT_LIKE WHERE userId = :userId");
    $queryPrepared -> execute(['userId'=> $user]);
}
function deleteUser($user) {

	deleteAvatar($user);
	deleteUserLikes($user);
	deleteUserLikedComments($user);
	deleteUserComments($user);
	deleteLikedUserThread($user);
	deleteUserThreads($user);
	
	
	$pdo = connectDB();
    $queryPrepared = $pdo->prepare("DELETE FROM AROOTS_USERS WHERE idUser = :idUser");
    $queryPrepared -> execute(['idUser'=> $user]);
}
//DELET THREAD
function deleteThreadLikes($threadId)  {
	$pdo = connectDB();
	$deleteThread = $pdo -> prepare("DELETE FROM THREAD_LIKE WHERE idThread =:idThread");
	$deleteThread -> execute(['idThread'=>$threadId]);
}

function deleteThreadComments($threadId) {
	$pdo = connectDB();
	$deleteThread = $pdo -> prepare("DELETE FROM THREAD_COMMENT WHERE idThread =:idThread");
	$deleteThread -> execute(['idThread'=>$threadId]);
}

function deleteThreadCommentsLikes($commentId) {
	$pdo = connectDB();
	$deleteThread = $pdo -> prepare("DELETE FROM THREAD_COMMENT_LIKE WHERE commentId =:commentId");
	$deleteThread -> execute(['commentId'=>$commentId]);
}
function deleteThread($threadId) {
	$pdo = connectDB();
	

	$getCommentsId = $pdo -> prepare("SELECT idThreadComment FROM THREAD_COMMENT WHERE idThread = :idThread");
	$getCommentsId -> execute(['idThread'=>$threadId]);
	$commentsId = $getCommentsId->fetchAll(PDO::FETCH_COLUMN, 0);

	foreach($commentsId as $commentId) {
		$commentId = intval($commentId);
		deleteThreadCommentsLikes($commentId);
	}
	deleteThreadComments($threadId);
	deleteThreadLikes($threadId);

	$deleteThread = $pdo -> prepare("DELETE FROM AROOTS_THREAD WHERE idThread =:idThread");
	$deleteThread -> execute(['idThread'=>$threadId]);
}


//ROLE
function setNormalUser($user) {
	$pdo = connectDB();
	$queryPrepared = $pdo ->prepare("UPDATE AROOTS_USERS SET userRole = 1 WHERE idUser =:idUser");
	$queryPrepared -> execute(['idUser'=>$user]);
}
function setWebmasterUser($user) {
	$pdo = connectDB();
	$queryPrepared = $pdo ->prepare("UPDATE AROOTS_USERS SET userRole = 2 WHERE idUser =:idUser");
	$queryPrepared -> execute(['idUser'=>$user]);
}

function setAdminUser($user) {
	$pdo = connectDB();
	$queryPrepared = $pdo ->prepare("UPDATE AROOTS_USERS SET userRole = 3 WHERE idUser =:idUser");
	$queryPrepared -> execute(['idUser'=>$user]);
}

//MESSAGES
function convExists($user1,$user2) {

	$pdo = connectDB();
	$findConv = $pdo->prepare("SELECT idConversation FROM CONV WHERE (user1 = :user1 AND user2 = :user2) OR (user1 = :user2 AND user2 = :user1)");
	$findConv->execute(['user2'=>$user2,'user1'=>$user1]);
	$result = $findConv->fetch();

	if (!empty($result)) {
		return true;
	}

	return false;
}

function createConv($user1,$user2) {

	$pdo = connectDB();
	$createConv = $pdo -> prepare("INSERT INTO CONV (user1,user2) VALUES (:user1,:user2)");
	$createConv -> execute(['user2'=>$user2,'user1'=>$user1]);
}

function getConv($user1,$user2) {
	$pdo = connectDB();
	$findConv = $pdo->prepare("SELECT idConversation FROM CONV WHERE (user1 = :user1 AND user2 = :user2) OR (user1 = :user2 AND user2 = :user1)");
	$findConv->execute(['user2'=>$user2,'user1'=>$user1]);
	$result = $findConv->fetch();
	
	return $result[0];
}


function getUserId($pseudo) {
	$pdo = connectDB();
	$getUserId = $pdo -> prepare("SELECT idUser FROM AROOTS_USERS WHERE pseudo = :pseudo");
	$getUserId-> execute(['pseudo'=>$pseudo]);
	$userId = $getUserId -> fetch();

	return intval($userId[0]);
}

function getUsersFromConv($conv) {

	$pdo = connectDB();
	$getUserId = $pdo -> prepare("SELECT user1,user2 FROM CONV WHERE idConversation = :idConversation");
	$getUserId-> execute(['idConversation'=>$conv]);
	$userIds = $getUserId -> fetch();

	return $userIds;

}

