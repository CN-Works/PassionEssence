<?php

namespace Controller;

use App\Session;
use App\AbstractController;
use App\ControllerInterface;
use Model\Managers\UserManager;

class SecurityController extends AbstractController implements ControllerInterface {

    public function index(){

    }

    public function ConnectUserForm() {
        // Par défaut on présente la page de connection, mais on peut être redirigé vers la page de création d'utilisateur et vice versa.
        return [
            "view" => VIEW_DIR."security/connectUserForm.php"
        ];
    }

    public function RegisterUserForm() {

        return [
            "view" => VIEW_DIR."security/registerUserForm.php"
        ];

    }

    public function RegisterUser() {
        $userManager = new UserManager();

        // On vérifie que les mots de passes & nom d'utilisateur sont sains.
        $password = filter_input(INPUT_POST, "newuser-password", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $passwordRepeat = filter_input(INPUT_POST, "newuser-password-repeat", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $username = filter_input(INPUT_POST, "newuser-username", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // On vérifie que le nom d'utilisateur n'est pas déjà pris
        $doesExist = $userManager->findAllByTableAndId($username, "username");

        if ($doesExist) {
            // on redirige vers la page de création de compte
            header("location: index.php?ctrl=security&action=RegisterUserForm");
        } else {
            // Dans un cas réel, le minimun recommandé serait 12 caractères.
            $passwordMinChars = 5;

            // on vérifie que le mot de passe et le mot de passe répété est le même,est que la longueur est suffisante
            if ($password == $passwordRepeat && strlen($password) > $passwordMinChars) {

                // On récupère les données
                $newUser = array(
                    "username" => $username,
                    "profileimage" => filter_input(INPUT_POST,"newuser-image",FILTER_VALIDATE_URL),
                    "creationdate" => date('Y-m-d H:i:s'),
                    // Par défaut
                    "role" => "user",
                    // Ici on va utiliser la technologie Bcrypt pour pouvoir stocker l'empreinte numérique du mot de passe dans la Base de donnée.
                    "password" => password_hash($password, PASSWORD_BCRYPT),
                );
            }
        }
    }
}