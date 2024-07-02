<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['userid']) && isset($_SESSION['username'])) {
    // User is logged in
    include 'header.php'; // Include header.php for logged-in users
} else {
    // User is not logged in
    include 'common_header.php'; // Include common_header.php for non-logged-in users
}
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
        /* Your existing CSS styles */
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
        .home {
            background-color: #222;
        }
        .home .content {
            text-align: center;
            padding-top: 6rem;
            margin: 2rem auto;
            max-width: 70rem;
        }
        .home .content h3 {
            color: #fff;
            font-size: 4.5rem;
            text-transform: uppercase;
        }
        .home .content h3 span {
            color: var(--main-color);
            text-transform: uppercase;
        }
        .carousel-container {
            max-width: 600px;
            /* Adjust the width as needed */
            margin: auto;
            /* Center the container */
        }
        .carousel-inner img {
            width: 100%;
            /* Ensure the image takes the full width of the container */
            height: auto;
            /* Maintain the aspect ratio */
        }
        .fw-bolder {
            color: #fff;
        }
        .text-body-secondary {
            color: #fff;
        }
        .lead {
            background-color: #222;
        }
        .album {
            background-color: #222;
        }
        .card {
            background-color: #333;
            color: #fff;
        }
        .card-text {
            color: #ccc;
        }
        @media (max-width: 991px) {
            html {
                font-size: 55%;
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
    <section class="home" id="home">
        <div class="content">
            <h3>It's time to celebrate! The National level <span>Symposium</span></h3>
            <a href="#" class="btn">contact us</a>
        </div>

        <div class="container carousel-container">
            <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="./images/pic1.jpg" class="d-block w-100" alt="...">
                    </div>
                    <div class="carousel-item">
                        <img src="./images/pic2.jpg" class="d-block w-100" alt="...">
                    </div>
                    <div class="carousel-item">
                        <img src="Event\user\images\pic3.jpg" class="d-block w-100" alt="...">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>

    <section id="Gallery">
        <main>
            <section class="py-5 text-center container">
                <div class="row py-lg-5">
                    <div class="col-lg-6 col-md-8 mx-auto">
                        <h1 class="fw-bolder">Chief Guests</h1>
                    </div>
                </div>
            </section>
            <div class="album py-5">
                <div class="container">
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                        <div class="col">
                            <div class="card shadow-sm">
                                <img src="./images/pic6.jpg" class="card-img-top" alt="...">
                                <div class="card-body">
                                    <p class="card-text">This is a wider card with supporting text below as a natural
                                        lead-in to additional content. This content is a little bit longer.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-body-secondary">9 mins</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card shadow-sm">
                                <img src="./images/pic2.jpg" class="card-img-top" alt="...">
                                <div class="card-body">
                                    <p class="card-text">This is a wider card with supporting text below as a natural
                                        lead-in to additional content. This content is a little bit longer.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-body-secondary">9 mins</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card shadow-sm">
                                <img src="./images/pic3.jpg" class="card-img-top" alt="...">
                                <div class="card-body">
                                    <p class="card-text">This is a wider card with supporting text below as a natural
                                        lead-in to additional content. This content is a little bit longer.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-body-secondary">9 mins</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Add more cards as needed -->
                    </div>
                </div>
            </div>
        </main>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-pajq9al8mxaXc/WORVHHXZtGGWljFWnqsFBo6xFhOmGm8wxU9yyPhBr5PjnxKRvR" crossorigin="anonymous">
    </script>
    <script>
        // Initialize carousel
        var carousel = new bootstrap.Carousel(document.querySelector('#carouselExampleAutoplaying'), {
            interval: 2000, // Adjust the interval as needed (in milliseconds)
            wrap: true, // Enable looping of slides
            keyboard: true // Enable keyboard navigation
        });
    </script>
</body>
</html>
