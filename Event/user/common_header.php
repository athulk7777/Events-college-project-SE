<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap');
        :root {
            --main-color: #3867d6;
        }
        * {
            font-family: "Nunito", sans-serif;
            margin: 0;
            box-sizing: border-box;
            outline: none;
            border: none;
            text-decoration: none;
            text-transform: capitalize;
            transition: .2s linear;
        }
        html {
            font-size: 62.5%;
            overflow-x: hidden;
            scroll-padding-top: 7rem;
            scroll-behavior: smooth;
        }
        html::-webkit-scrollbar {
            width: 1rem;
        }
        html::-webkit-scrollbar-track {
            background: #444;
        }
        html::-webkit-scrollbar-thumb {
            background: var(--main-color);
            border-radius: 5rem;
        }
        body {
            background: #222;
        }
        section {
            padding: 2rem 9%;
        }
        .btn {
            margin-top: 1rem;
            display: inline-block;
            padding: .8rem 3rem;
            font-size: 1.7rem;
            border-radius: .5rem;
            background: #666;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }
        .btn:hover {
            background: var(--main-color);
        }
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10000;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 9%;
        }
        .header .logo {
            font-weight: bolder;
            color: #fff;
            font-size: 2.5rem;
            text-decoration: none;
        }
        .header .logo span {
            color: var(--main-color);
        }
        .header .navbar a {
            font-size: 1.7rem;
            color: #fff;
            margin-left: 2rem;
            text-decoration: none;
        }
        .header .navbar a:hover {
            color: var(--main-color);
        }
        #menu-bars {
            font-size: 3rem;
            color: #fff;
            cursor: pointer;
            display: none;
        }
        @media (max-width: 991px) {
            html {
                font-size: 55%;
            }
            .header {
                padding: 1.5rem 2rem;
            }
        }
        @media (max-width: 768px) {
            #menu-bars {
                display: initial;
            }
            .header .navbar {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                border-top: .1rem solid #222;
                background: #333;
                clip-path: polygon(0 0, 100% 0, 100% 0, 0 0);
            }
            .header .navbar.active {
                clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
            }
            .fa-times {
                transform: rotate(180deg);
            }
            .header .navbar a {
                display: flex;
                background: #222;
                border-radius: .5rem;
                padding: 1.3rem;
                margin: 1.3rem;
                font-size: 2rem;
            }
        }
        @media (max-width: 450px) {
            html {
                font-size: 50%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="#" class="logo"><span>N</span>eura</a>
        <nav class="navbar">
            <a href="#">Home</a>
            <a href="event.php">Events</a>
            <a href="#Registration">Registration</a>
            <a href="#Gallery">Gallery</a>
            <a href="#Contact">Contact</a>
            <a href="user_login.php">Login/sign up</a>

        </nav>
        <div id="menu-bars" class="fas fa-bars"></div>
    </header>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-pajq9al8mxaXc/WORVHHXZtGGWljFWnqsFBo6xFhOmGm8wxU9yyPhBr5PjnxKRvR" crossorigin="anonymous">
    </script>
    <script>
        let menu = document.querySelector('#menu-bars');
        let header = document.querySelector('.header');

        menu.onclick = () => {
            menu.classList.toggle('fa-times');
            header.classList.toggle('active');
        }

        window.onscroll = () => {
            menu.classList.remove('fa-times');
            header.classList.remove('active');
        }
    </script>
</body>
</html>