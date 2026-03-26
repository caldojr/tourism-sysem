<?php
// index.php
// Display welcome message for 2 seconds then redirect to home.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Nakupenda Tours & Safaris</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body, html {
            height: 100%;
            font-family: 'Lato', Arial, sans-serif;
            overflow: hidden;
        }
        
        .welcome-container {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('photos/nakupenda.jpg') center/cover;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 2rem;
        }
        
        .welcome-logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid #ffb300;
            margin-bottom: 2rem;
            object-fit: cover;
            box-shadow: 0 8px 25px rgba(255, 179, 0, 0.3);
        }
        
        .welcome-title {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
            color: #ffb300;
        }
        
        .welcome-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #ffb300;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-top: 2rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .redirect-text {
            margin-top: 1rem;
            font-size: 1rem;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .welcome-title {
                font-size: 2.5rem;
            }
            
            .welcome-subtitle {
                font-size: 1.2rem;
            }
            
            .welcome-logo {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <img src="photos/download.jpg" alt="Nakupenda Tours & Safaris" class="welcome-logo">
        <h1 class="welcome-title">Welcome to Nakupenda Tours & Safaris</h1>
        <p class="welcome-subtitle">Crafting Unforgettable Journeys Across Tanzania</p>
        <div class="loading-spinner"></div>
        <p class="redirect-text">Redirecting to homepage...</p>
    </div>

    <script>
        // Redirect to home.php after 2 seconds
        setTimeout(function() {
            window.location.href = 'home.php';
        }, 2000);
    </script>
</body>
</html>