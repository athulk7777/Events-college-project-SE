<?php
session_start();

// Check if the user is logged in and if the designation is ADMIN
if (!isset($_SESSION['userid']) || !isset($_SESSION['designation']) || $_SESSION['designation'] !== 'ADMIN') {
    header('Location: login.php'); // Redirect to login page if not logged in or not an admin
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            overflow: hidden;
        }
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-color: #000;
        }
        .container {
            text-align: center;
            padding: 50px;
            position: relative;
            z-index: 1;
        }
        .container h1 {
            color: #fff;
            margin-bottom: 40px;
        }
        .btn {
            background-color: white;
            border: none;
            color: #000;
            padding: 15px 30px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px 5px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #000;
            color: white;
            transition-duration: 0.5s;
            box-shadow: 0 4px 8px 0 white, 0 6px 20px 0 white;   
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>Admin Panel</h1>
        <button class="btn" onclick="window.location.href='eventupd.php'">Update Event</button>
        <button class="btn" onclick="window.location.href='ad_exp.php'">Manage Expense</button>
        <button class="btn" onclick="window.location.href='CO_SPON.php'">Sponsors</button>
        <button class="btn" onclick="window.location.href='ad_profit.php'">Check Profit</button>
        <button class="btn" onclick="window.location.href='check_co_ord.php'">Check Co-ordinator</button>
        <button class="btn" onclick="window.location.href='CO-ORD_LOGIN.PHP'">Logout</button>
    </div>

    <script src="js/particles.min.js"></script>
    <script>
        particlesJS('particles-js', {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": "#ffffff"
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    },
                    "polygon": {
                        "nb_sides": 5
                    }
                },
                "opacity": {
                    "value": 0.5,
                    "random": false
                },
                "size": {
                    "value": 3,
                    "random": true
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#ffffff",
                    "opacity": 0.4,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 6,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "repulse"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 400,
                        "line_linked": {
                            "opacity": 1
                        }
                    },
                    "bubble": {
                        "distance": 400,
                        "size": 40,
                        "duration": 2,
                        "opacity": 8,
                        "speed": 3
                    },
                    "repulse": {
                        "distance": 200,
                        "duration": 0.4
                    },
                    "push": {
                        "particles_nb": 4
                    },
                    "remove": {
                        "particles_nb": 2
                    }
                }
            },
            "retina_detect": true
        });
    </script>
</body>
</html>
