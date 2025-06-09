<?php 
session_start();
include_once('view/header.php'); 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phúc Long Coffee & Tea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <style>
        .banner-slider {
            width: 100%;
            height: 400px;
            margin: 0 auto;
        }
        .swiper {
            width: 100%;
            height: 100%;
        }
        .swiper-slide {
            text-align: center;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit:cover;
        }
        .swiper-button-next,
        .swiper-button-prev {
            color: #fff;
            background: rgba(0, 0, 0, 0.3);
            padding: 30px 20px;
            border-radius: 5px;
        }
        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: rgba(0, 0, 0, 0.5);
        }
        .swiper-pagination-bullet {
            background: #fff;
        }
        .swiper-pagination-bullet-active {
            background: #007a33;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Banner Slider -->
    <div class="banner-slider">
        <div class="swiper">
        <div class="swiper-wrapper">
    <div class="swiper-slide">
        <img src="uploads/banner/banner1.jpg" alt="Banner 1">
    </div>
    <div class="swiper-slide">
        <img src="uploads/banner/banner2.jpg" alt="Banner 2">
    </div>
    <div class="swiper-slide">
        <img src="uploads/banner/banner3.png" alt="Banner 3">
    </div>
</div> 
            <!-- Add Navigation -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <!-- Rest of your content -->
    <div class="container my-5">
        <!-- Your existing content -->
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        const swiper = new Swiper('.swiper', {
            // Optional parameters
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            
            // Navigation arrows
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            
            // Pagination
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            
            // Responsive breakpoints
            breakpoints: {
                // when window width is >= 320px
                320: {
                    slidesPerView: 1,
                    spaceBetween: 20
                },
                // when window width is >= 768px
                768: {
                    slidesPerView: 1,
                    spaceBetween: 30
                },
                // when window width is >= 1024px
                1024: {
                    slidesPerView: 1,
                    spaceBetween: 40
                }
            }
        });
    </script>
</body>
</html> 
<?php include_once('view/footer.php'); ?>
</body>
</html>
