<?php

require ('../connection.php'); // Include your database connection file

// Handle login form submission
if (isset($_POST['btnPostMe1'])) {
    $Email = htmlspecialchars(trim($_POST['user_name']));
    $Password = htmlspecialchars(trim($_POST['password']));

    if (empty($Email) || empty($Password)) {
        $_SESSION['status'] = "Please enter both email and password.";
        header("location: signuptest1.php");
        exit();
    }

    try {

        $db = new PDO("mysql:host=localhost;dbname=password_vault", "root", "");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $db->prepare("SELECT uid, full_name, email, user_name, is_admin, disable FROM user WHERE user_name=:Email AND password=:Password");
        $stmt->bindParam(':Email', $Email);
        $stmt->bindParam(':Password', $Password);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "User found!<br>";
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row['disable'] == 1) {
                $_SESSION['status'] = "Your account has been disabled. Please contact the administrator for assistance.";
                header("location: signuptest1.php");
                exit();
            }

            $_SESSION['ID'] = $row['uid'];
            $_SESSION['Email'] = $row['email'];
            $_SESSION['Name'] = $row['full_name'];
            $_SESSION["username"] = $row['user_name'];
            $_SESSION["loggedIn"] = true;
            $_SESSION["role"] = $row["is_admin"];
            $_SESSION["client"] = $row["client_name"];

            if ($_SESSION["role"] == 1) {
                header('location: ../dashboard.php');
                exit();
            } else {
                header("location: ../passwordgenerator.php");
                exit();
            }
        } else {
            echo "User not found!<br>";
            $_SESSION['status'] = "Incorrect email or password.";
            header("location: signuptest1.php");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


?>
<?php if (isset($_POST['btnPostMe2'])) {
    $Name = $_POST['full_name'];
    $User_name = $_POST['user_name'];
    $address = $_POST['designation'];
    $Email = $_POST['email'];
    $Password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $is_admin = 0;
    $disable = 1; // Validate password length if (strlen($Password)> 16) {
    $_SESSION['status'] = "Password must be 16 characters or less.";
}

// Check if passwords match
if ($Password !== $cpassword) {
    $_SESSION['status'] = "Passwords do not match.";
}

$uploadDir = 'C:/xampp/htdocs/passwordvault/uploadimage/';
$uploadFile = $uploadDir . basename($_FILES['user_image']['name']);

// Handle file upload
if (!move_uploaded_file($_FILES['user_image']['tmp_name'], $uploadFile)) {
    $_SESSION['status'] = "Sorry, there was an error uploading your file.";
}

$dsn = "mysql:host=localhost;dbname=password_vault";
$username = "root";
$password = "";
$options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    $_SESSION['status'] = "Could not connect to the database: " . $e->getMessage();

}

// Check for existing username and email
$check_username_query = $pdo->prepare("SELECT * FROM user WHERE user_name = :user_name");
$check_username_query->execute(array(':user_name' => $User_name));
$check_email_query = $pdo->prepare("SELECT * FROM user WHERE email = :email");
$check_email_query->execute(array(':email' => $Email));

$emailcount = $check_email_query->rowCount();
$usernamecount = $check_username_query->rowCount();

if ($emailcount > 0 && $usernamecount > 0) {
    $_SESSION['status'] = "Email and Username already exist. Please use different credentials.";
} elseif ($emailcount > 0) {
    $_SESSION['status'] = "Email already registered. Please use a different email ID.";
} elseif ($usernamecount > 0) {
    $_SESSION['status'] = "Username already exists.";

} else {
    // Insert new user
    $query = $pdo->prepare("INSERT INTO user (full_name, user_name, designation, cpassword, email, password, is_admin,
    user_image, disable) VALUES (:Name, :User_name, :address, :cpassword, :Email, :Password, :is_admin, :user_image,
    :disable)");
    $query->execute(
        array(
            ':Name' => $Name,
            ':User_name' => $User_name,
            ':address' => $address,
            ':cpassword' => $cpassword,
            ':Email' => $Email,
            ':Password' => $Password,
            ':is_admin' => $is_admin,
            ':user_image' => $uploadFile,
            ':disable' => $disable
        )
    );

    if ($query) {
        $_SESSION['signup_success'] = true;
        $_SESSION['status'] = "You are successfully registered with ENCS Networks, please login with your credentials.";

    }
}

?>






<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="images/icons/favicon.ico" />

    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">

    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">

    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">


    <link rel="stylesheet" type="text/css" href="signuptest.css">

    <title>Modern Login Page | ENCS</title>


</head>

<body>

    <!--
    <div class="limiter">
        <div class="container-login100">

            <div class="container" id="container">

                <div class="form-container sign-in">
                    <form class="login100-form validate-form" method='post' action="login1.php"
                        onsubmit="saveUsername()">
                        <span class="login100-form-title">
                            ENCS Networks <br> Password Wallet Login
                        </span>
                        <?php echo (isset($_GET['msg'])) ? "<div id='alertDiv' class='alert alert-success'>" . $_GET['msg'] . "</div>" : "";
                        ?>
                        <?php echo (isset($_SESSION['success'])) ? "<div id='alertDiv' class='alert alert-success'>" . $_SESSION['success'] . "</div>" : "";
                        ?>

                        <?php

                        if (isset($_SESSION['status'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" id="alertDiv" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <?= htmlspecialchars($_SESSION['status']) ?>
                            </div>
                            <?php unset($_SESSION['status']); ?>
                        <?php endif; ?>

                        <div class="wrap-input100 validate-input" data-validate="User name is required">
                            <input class="input100" type="text" name="user_name" placeholder="User name" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </span>
                        </div>

                        <div class="wrap-input100 validate-input" data-validate="Password is required">
                            <input class="input100" type="password" name="password" placeholder="Password" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>
                        </div>

                        <div class="container-login100-form-btn">
                            <ul>
                                <<button type="submit" value="Save" name="login" class=" btn btn-md pr-3 pl-3 btn-info">
                                    Login</button>

                                    <button id="register" name="signup"
                                        class="btn btn-md pr-3 pl-3 btn-info">SignUp</button>
                            </ul>
                        </div>

                        <div class="text-center p-t-12">
                            <a class="btn btn-md pr-3 pl-3 btn-info" onclick="checkUsernameAndRedirect()">Forgot
                                Password?</a>
                        </div>
                    </form>
                </div>


                <div class="form-container sign-up">
                    <span class="login100-form-title">
                        ENCS Member signup
                    </span>
                    <form id="signupForm" class="login100-form validate-form" action="signup1.php" method='post'
                        onsubmit="saveUsername()">
                        <?php
                        // print_r($_SESSION);
                        if (isset($_SESSION['status'])) {
                            ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">

                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <strong> <?php echo $_SESSION['status']; ?></strong>
                            </div>



                            <?php
                            unset($_SESSION['status']);
                        }

                        ?>
                        <div class="wrap-input100 validate-input">
                            <input class="input100" type="text" name="full_name" placeholder="Enter your name" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="User name is required">
                            <input class="input100" type="text" name="user_name" placeholder="Enter your user name"
                                required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="Designation is required">
                            <input class="input100" type="text" name="designation" placeholder="Enter your designation"
                                required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-address-book" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="Valid email is required: ex@abc.xyz">
                            <input class="input100" type="text" name="email" placeholder="Enter your email" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="Password is required">
                            <input class="input100" type="password" id="passt" name="password"
                                placeholder="Enter your password" required maxlength="16" minlength="16">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="Password is required">
                            <input class="input100" type="password" id="passwordt" name="cpassword"
                                placeholder="Confirm your password" required maxlength="16" minlength="16">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>

                        </div>
                        <div class="chooseimg validate-input" data-validate="Image is required">
                            <input type="file" name="user_image" required>
                            <span class="focus-input100"></span>

                        </div>

                        <div class="container-login100-form-btn">
                            <ul>


                                <button type="submit" id="login" name="Signup" value="Save"
                                    class=" btn btn-md pr-3 pl-3 btn-info">signup</button>

                                <button id="cancelButton" name="cancel"
                                    class=" btn btn-md pr-3 pl-3 btn-info">Cancel</button>
                            </ul>

                        </div>
                    </form>
                </div>



                <div class="toggle-container">
                    <div class="toggle">
                        <div class="toggle-panel toggle-left">
                            <div class="login100-pic js-tilt" style=" width: 400px; height: auto;" data-tilt>
                                <img src="../dist/img/vault.png" alt="IMG">
                            </div>

                        </div>
                        <div class="toggle-panel toggle-right">
                            <div class="login100-pic js-tilt" style=" width: 400px; height: auto; " data-tilt>
                                <img src="../dist/img/vault.png" alt="IMG">
                            </div>


                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
                    -->

    <div class="limiter">
        <div class="container-login100">
            <div class="container" id="container"
                class="<?php echo isset($_SESSION['signup_success']) ? '' : 'active'; ?> ">
                <!-- Sign In Form -->
                <div class="form-container sign-in">

                    <form method='post' action="signuptest1.php" onsubmit="saveUsername()">
                        <span id="formTitle" class="login100-form-title">
                            <span id="letter-orange" class="word">E</span>
                            <span id="letter-lightblue" class="word">N</span>
                            <span id="letter-yellow" class="word">C</span>
                            <span id="letter-green" class="word">S</span>
                            <span class="word">Networks</span><br>
                            <span class="word">Password</span>
                            <span class="word">Wallet</span>
                            <span class="word">Login</span>

                        </span>
                        <?php if (isset($_GET['msg'])): ?>
                            <div id="alertDiv" class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div id="alertDiv" class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['status'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" id="alertDiv" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <?= htmlspecialchars($_SESSION['status']) ?>
                            </div>
                            <?php unset($_SESSION['status']); ?>
                        <?php endif; ?>


                        <div class="wrap-input100 validate-input" data-validate="User name is required">
                            <input class="input100" type="text" name="user_name" placeholder="User name" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="Password is required">
                            <input class="input100" type="password" name="password" placeholder="Password" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="cnt">
                            <div class="checkbox">
                                <input type="checkbox" id="remember-me">
                                <label for="remember-me">Remember me</label>
                            </div>
                            <div class="pass-link">
                                <a href="#" onclick="checkUsernameAndRedirect()">Forgot password?</a>
                            </div>
                        </div>
                        <div class="container-signup100-form-btn">


                            <button type="submit" id="loginupButton" name="btnPostMe1" value="Confirm"
                                class="btn btn-md pr-3 pl-3 btn-success">Login</button>

                        </div>
                        <div class="signup-link">
                            Not a member? <a href="#" id="register">Signup now</a>
                        </div>

                    </form>
                </div>

                <!-- Sign Up Form -->
                <div class="form-container sign-up">

                    <form id="signupForm" action="signuptest1.php" method="post" enctype="multipart/form-data">
                        <span id="formTitle" class="signup-form-title">
                            <span id="letter-orange" class="word">E</span>
                            <span id="letter-lightblue" class="word">N</span>
                            <span id="letter-yellow" class="word">C</span>
                            <span id="letter-green" class="word">S</span>
                            <span class="word">Networks</span><br>
                            <span class="word">Password</span>
                            <span class="word">Wallet</span>
                            <span class="word">Login</span>

                        </span>
                        <?php if (isset($_SESSION['status'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                style="margin-top: 10px;">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <strong><?= htmlspecialchars($_SESSION['status']) ?></strong>
                            </div>
                            <?php unset($_SESSION['status']); ?>
                        <?php endif; ?>
                        <div class="wrap-input100 validate-input">
                            <input class="input100" type="text" name="full_name" placeholder="Enter your name" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="User name is required">
                            <input class="input100" type="text" name="user_name" placeholder="Enter your user name"
                                required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="Designation is required">
                            <input class="input100" type="text" name="designation" placeholder="Enter your designation"
                                required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-address-book" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="Valid email is required: ex@abc.xyz">
                            <input class="input100" type="email" name="email" placeholder="Enter your email" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="Password is required">
                            <input class="input100" type="password" id="passt" name="password"
                                placeholder="Enter your password" required maxlength="16" minlength="16">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="Password is required">
                            <input class="input100" type="password" id="passwordt" name="cpassword"
                                placeholder="Confirm your password" required maxlength="16" minlength="16">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="chooseimg validate-input" data-validate="Image is required">
                            <input type="file" name="user_image" required>
                            <span class="focus-input100"></span>
                        </div>
                        <div class="container-signup100-form-btn">

                            <button type="submit" id="signupButton" name="btnPostMe2" value="Confirm"
                                class="btn btn-md pr-3 pl-3 btn-success">Signup</button>
                            <button id="cancelButton" type="button"
                                class="btn btn-md pr-3 pl-3 btn-info">Cancel</button>

                        </div>
                    </form>
                </div>

                <!-- Toggle Container -->
                <div class="toggle-container">
                    <div class="toggle">
                        <div class="toggle-panel toggle-left">
                            <div class="login100-pic js-tilt" data-tilt>
                                <img src="../dist/img/vault.png" alt="IMG">
                            </div>
                        </div>
                        <div class="toggle-panel toggle-right">
                            <div class="login100-pic js-tilt" data-tilt>
                                <img src="../dist/img/vault.png" alt="IMG">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--

    <script>
        // Your JavaScript code for toggling between signup and login forms
        const container = document.getElementById('container');
        const registerBtn = document.getElementById('register');
        const loginBtn = document.getElementById('login');

        registerBtn.addEventListener('click', () => {
            container.classList.add("active");
        });


        const signupMessage = document.querySelector('.success-message'); // Adjust selector based on your message element

        if (signupMessage) {
            // Signup successful, disable signup button
            const signupButton = document.getElementById('login'); // Assuming signup button has id "login"
            signupButton.disabled = true;
        }

    </script>

    <script>



        document.getElementById('cancelButton').addEventListener('click', function (event) {
            event.preventDefault(); // Prevent the default link behavior
            document.querySelector('.container').classList.remove('active'); // Remove the active class from the container
        });

    </script>
    -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('container');
            const registerBtn = document.getElementById('register');
            const cancelBtn = document.getElementById('cancelButton');
            const signupButton = document.getElementById('signupButton');

            registerBtn.addEventListener('click', () => {
                container.classList.add('active');
            });

            cancelBtn.addEventListener('click', () => {
                container.classList.remove('active');
            });

            // Check if signup was successful
            const signupSuccess = <?php echo isset($_SESSION['signup_success']) ? 'true' : 'false'; ?>;
            if (!signupSuccess) {
                container.classList.add('active');
            }

            // Check for error message
            const signupMessage = document.querySelector('.alert-danger');
            if (signupMessage) {
                container.classList.add('active');
            }
        });


        document.addEventListener('DOMContentLoaded', function () {
            var formTitle = document.getElementById('formTitle');
            var words = formTitle.querySelectorAll('.word');

            // Trigger animation for each word with a delay
            words.forEach(function (word, index) {
                setTimeout(function () {
                    word.style.opacity = 1;
                    word.classList.add('animate-fade-in');
                }, index * 500);
            });
        });




    </script>



    <script>


        function saveUsername() {
            var username = document.getElementsByName("user_name")[0].value;
            localStorage.setItem("user_name", username);
        }


        function checkUsernameAndRedirect() {
            var username = document.getElementsByName("user_name")[0].value;
            if (username) {
                window.location.href = "validateuser.php?username=" + encodeURIComponent(username);
            } else {
                alert("Please enter your username first.");
            }
        }

    </script>





    <!--===============================================================================================-->
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <!--===============================================================================================-->
    <script src="vendor/bootstrap/js/popper.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <!--===============================================================================================-->
    <script src="vendor/select2/select2.min.js"></script>
    <!--===============================================================================================-->
    <script src="vendor/tilt/tilt.jquery.min.js"></script>
    <script>
        $('.js-tilt').tilt({
            scale: 1.1
        })

        setTimeout(function () {
            document.getElementById('alertDiv').style.display = 'none';
            history.replaceState(null, null, window.location.pathname);
        }, 5000);
    </script>
    <!--===============================================================================================-->
    <script src="js/main.js"></script>
</body>

</html>