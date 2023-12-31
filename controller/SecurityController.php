<?php

    namespace Controller;

    use App\Session;
    use App\AbstractController;
    use App\ControllerInterface;
    use Model\Managers\UserManager;
    use Model\Managers\TopicManager;
    use Model\Managers\PostManager;
    use Model\Managers\CategoryManager;
    use App\DAO;

    class SecurityController extends AbstractController implements ControllerInterface {

        public function index(){

        }

        public function disconnect() {
            if ($_SESSION["user"]) {
                // On retire l'objet user de la session
                $_SESSION["user"] = null;

                header("location: index.php?ctrl=security&action=ConnectUserForm");
                exit;
            } else {
                // On redirige vers la page de connection
                header("location: index.php?ctrl=security&action=ConnectUserForm");
                exit;
            }
        }

        public function connectUserForm() {
            // Par défaut on présente la page de connection, mais on peut être redirigé vers la page de création d'utilisateur et vice versa.
            return [
                "view" => VIEW_DIR."security/connectUserForm.php"
            ];
        }

        public function connectUser() {
            $session = new Session();
            $userManager = new UserManager();

            // Si l'utilisateur est connecté, il ne peut pas se connecter à un autre compte
            if (isset($_SESSION["user"])) {
                header("location: index.php?ctrl=security&action=ConnectUserForm");
                exit;
            }

            // On filtre les entrées
            $password = filter_input(INPUT_POST,"connect-password",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $username = filter_input(INPUT_POST,"connect-username",FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // On vérifie que l'utilisateur existe pour ensuite aller chercher son objet
            $doesExist = $userManager->findUserByUsername($username);

            // On vérifie en plus que le nom d'utilisateur correspond.
            if ($doesExist && $password) {
                // Cette variable correspond à l'utilisateur voulu
                $selectedUser = $doesExist;

                if (password_verify($password, $selectedUser->getPassword())) {
                    // On sauvegarde l'objet de l'utilisateur dans la session pour pouvoir manipuler le site par la suite
                    $_SESSION["user"] = $selectedUser;

                    header("location: index.php?ctrl=security&action=ConnectUserForm");
                    exit;
                } else {
                    // Ici le mot de passe est incorrecte
                    // On redirige l'utilisateur vers la page de connection pour une nouvelle chance
                    header("location: index.php?ctrl=security&action=ConnectUserForm");
                    exit;
                }
            } else {
                // Dans le cas où le nom d'utilisateur n'est pas reconnu
                // On redirige l'utilisateur vers la page de connection pour une nouvelle chance
                header("location: index.php?ctrl=security&action=ConnectUserForm");
                exit;
            }
        }

        public function registerUserForm() {

            return [
                "view" => VIEW_DIR."security/registerUserForm.php"
            ];

        }

        public function registerUser() {
            $userManager = new UserManager();

            // Si l'utilisateur est connecté, il ne peut pas créer un autre compte
            if (isset($_SESSION["user"])) {
                header("location: index.php?ctrl=security&action=ConnectUserForm");
                exit;
            }

            // On vérifie que les mots de passes & nom d'utilisateur sont sains.
            $password = filter_input(INPUT_POST, "newuser-password", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $passwordRepeat = filter_input(INPUT_POST, "newuser-password-repeat", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $username = filter_input(INPUT_POST, "newuser-username", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $image = filter_input(INPUT_POST,"newuser-image",FILTER_VALIDATE_URL);

            // On vérifie que le nom d'utilisateur n'est pas déjà pris
            $doesExist = $userManager->findUserByUsername($username);

            if ($doesExist) {
                // on redirige vers la page de création de compte
                header("location: index.php?ctrl=security&action=RegisterUserForm");
                exit;
            } else {
                // Dans un cas réel, le minimun recommandé serait 12 caractères.
                $passwordMinChars = 5;


                // on vérifie que le mot de passe et le mot de passe répété est le même,est que la longueur est suffisante
                if ($password == $passwordRepeat && strlen($password) > $passwordMinChars && $password) {

                    // On récupère les données
                    $newUser = array(
                        "username" => $username,
                        // Ici on va utiliser la technologie Bcrypt pour pouvoir stocker l'empreinte numérique du mot de passe dans la Base de donnée.
                        "password" => password_hash($password, PASSWORD_BCRYPT),
                        "description" => "Pas de description.",
                        // Par défaut
                        "role" => "user",
                        "profileimage" => $image,
                    );

                    // On crée un nouveau utilisateur
                    $creatingUser = $userManager->add($newUser);

                    // On redirige l'utilisateur vers la page de connection
                    header("location: index.php?ctrl=security&action=ConnectUserForm");
                    exit;
                }
            }
        }

        /* Admin panel */

        public function listUsers() {
            $userManager = new UserManager();
            $topicManager = new TopicManager();
            $postManager = new PostManager();

            if (isset($_SESSION["user"])) {
                // C'est une fonctionnalité admin donc on vérifie le role
                if ($_SESSION["user"]->getRole() == "admin") {
                    // On récupère les utilisateurs du site
                    return [
                        "view" => VIEW_DIR."security/listUsers.php",
                        "data" => [
                            "AllUsers" => $userManager->findAll(["creationdate", "ASC"]),
                            "AllTopics" => $topicManager->findAll(["creationdate", "DESC"]),
                            "AllPosts" => $postManager->findAll(["creationdate", "DESC"]),
                        ]
                    ];
                } else {
                    // On redirige l'utilisateur non-admin
                    header("Location: index.php");
                    exit;
                }

            } else {
                // On redirige l'utilisateur non-connecté sur la page d'acceuil
                header("Location: index.php");
                exit;
            }
        }
    }