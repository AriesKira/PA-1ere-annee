<div>
    <div class="container">
        <div class="row pt-4" id="loginBorder">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <?php
                if (!empty($_POST['email']) &&  !empty($_POST['pwd']) && count($_POST) == 2) {

                    //Récupérer en bdd le mot de passe hashé pour l'email provenant du formulaire

                    $pdo = connectDB();
                    $queryPrepared = $pdo->prepare("SELECT * FROM AROOTS_USERS WHERE email=:email");
                    $queryPrepared->execute(["email" => $_POST['email']]);
                    $results = $queryPrepared->fetch();
                    if (!empty($results) && password_verify($_POST['pwd'], $results['pwd'])) {
                        $token = createToken();
                        updateToken($results["idUser"], $token);
                        //Insertion dans la session du token
                        $_SESSION['pseudo'] = $results['pseudo'];
                        $_SESSION['email'] = $_POST['email'];
                        $_SESSION['idUser'] = $results["idUser"];
                        $_SESSION['token'] = $token;
                        ?>
                        <script>
                            if (window.localStorage) {
                                if (!localStorage.getItem('reload')) {
                                    localStorage['reload'] = true;
                                    window.location.reload();
                                }else{
                                localStorage.removeItem('reload');
                                }
                            }
                        </script>
                        <?php
                    } else {
                        echo '<div class="alert alert-danger mt-4 pb-1 " role="alert"><h5 class="fw-bold">Identifiants Incorrects</h5></div>';
                    }
                }
                ?>
                <form method="POST" action="">
                    <input type="email" class="form-control" name="email" placeholder="Votre email" required="required"><br>
                    <input type="password" class="form-control" name="pwd" placeholder="Votre mot de passe" required="required"><br>
                    <input type="submit" class="btn btn-outline-light mb-4 submitButton" value="Se connecter">
                </form>
            </div>
        </div>
    </div>
</div>