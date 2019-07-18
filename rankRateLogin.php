<?php session_start();?>
<!--
Stripped for release; you'll have to figure out the authentication yourself.
-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="utf-8">

	<script src= "https://unpkg.com/jquery@3.3.1/dist/jquery.min.js" ></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
	<script src="https://unpkg.com/jquery-ui-css@1.11.5/jquery-ui.min.js" ></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css" />
    <style type="text/css">
    @media screen and (min-width: 1400px) {
        html,
        body {
            height: 100%;
        }

        /* Neatly fades the placeholders upon user focus to prevent confusion.
           Thank you to James Wilson on SO
        */

        :-ms-input-placeholder { opacity: 1; -ms-transition: opacity .5s; transition: opacity .5s; } /* IE 10+ */
        ::placeholder { opacity: 1; transition: opacity .5s; } /* Modern Browsers */
        *:focus:-ms-input-placeholder { opacity: 0; } /* IE 10+ */
        *:focus::placeholder { opacity: 0; } /* Modern Browsers */

        .container {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .form-control {
          width: 800px;
          max-width: 100%;
          max-height: 100%;
        }
    }
    </style>
	<title>Login</title>
</head>
<body>
<div class="container">
  <form method="post" action="login.php" role="form">
      <div class="form-group">
        <h1>CLNS Rank/Rate</h1>
        <label for="ghr">GHR ID</label>
        <input type="text" class="form-control" id="ghr" name="ghr" placeholder="31415926" >
        <small id="passwordHelpBlock" class="form-text text-muted">
            Your GHR ID must be 8 characters long and only contain numbers.
        </small>
    </div>
    <div class="form-group">
        <label for="pwd">Password</label>
        <input type="password" class="form-control" id="pwd" name ="pwd" placeholder="hunter2">
        <small id="passwordHelpBlock" class="form-text text-muted">
            Your password must be 8-20 characters long, contain letters and numbers, and must not contain spaces, special characters, or emoji.
        </small>
    </div>
    <div>
        <button id = "loginButton" type="submit" class="btn btn-primary mb-2">Log In</button>
        </form>
    </div>
</div>
</body>
</body>
</html>
