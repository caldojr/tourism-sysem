<?php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();


// Get all posts for selection
$safaris_query = "SELECT * FROM admin_posts WHERE category = 'safari' AND deleted = 0 ORDER BY title";
$hotels_query = "SELECT * FROM admin_posts WHERE category = 'hotel' AND deleted = 0 ORDER BY title";
$transports_query = "SELECT * FROM admin_posts WHERE category = 'transport' AND deleted = 0 ORDER BY title";

$safaris_stmt = $db->prepare($safaris_query);
$hotels_stmt = $db->prepare($hotels_query);
$transports_stmt = $db->prepare($transports_query);

$safaris_stmt->execute();
$hotels_stmt->execute();
$transports_stmt->execute();

$safaris = $safaris_stmt->fetchAll(PDO::FETCH_ASSOC);
$hotels = $hotels_stmt->fetchAll(PDO::FETCH_ASSOC);
$transports = $transports_stmt->fetchAll(PDO::FETCH_ASSOC);

$country_phone_codes = [
    ['country' => 'Afghanistan', 'code' => '+93'],
    ['country' => 'Albania', 'code' => '+355'],
    ['country' => 'Algeria', 'code' => '+213'],
    ['country' => 'Andorra', 'code' => '+376'],
    ['country' => 'Angola', 'code' => '+244'],
    ['country' => 'Antigua and Barbuda', 'code' => '+1-268'],
    ['country' => 'Argentina', 'code' => '+54'],
    ['country' => 'Armenia', 'code' => '+374'],
    ['country' => 'Australia', 'code' => '+61'],
    ['country' => 'Austria', 'code' => '+43'],
    ['country' => 'Azerbaijan', 'code' => '+994'],
    ['country' => 'Bahamas', 'code' => '+1-242'],
    ['country' => 'Bahrain', 'code' => '+973'],
    ['country' => 'Bangladesh', 'code' => '+880'],
    ['country' => 'Barbados', 'code' => '+1-246'],
    ['country' => 'Belarus', 'code' => '+375'],
    ['country' => 'Belgium', 'code' => '+32'],
    ['country' => 'Belize', 'code' => '+501'],
    ['country' => 'Benin', 'code' => '+229'],
    ['country' => 'Bhutan', 'code' => '+975'],
    ['country' => 'Bolivia', 'code' => '+591'],
    ['country' => 'Bosnia and Herzegovina', 'code' => '+387'],
    ['country' => 'Botswana', 'code' => '+267'],
    ['country' => 'Brazil', 'code' => '+55'],
    ['country' => 'Brunei', 'code' => '+673'],
    ['country' => 'Bulgaria', 'code' => '+359'],
    ['country' => 'Burkina Faso', 'code' => '+226'],
    ['country' => 'Burundi', 'code' => '+257'],
    ['country' => 'Cabo Verde', 'code' => '+238'],
    ['country' => 'Cambodia', 'code' => '+855'],
    ['country' => 'Cameroon', 'code' => '+237'],
    ['country' => 'Canada', 'code' => '+1'],
    ['country' => 'Central African Republic', 'code' => '+236'],
    ['country' => 'Chad', 'code' => '+235'],
    ['country' => 'Chile', 'code' => '+56'],
    ['country' => 'China', 'code' => '+86'],
    ['country' => 'Colombia', 'code' => '+57'],
    ['country' => 'Comoros', 'code' => '+269'],
    ['country' => 'Congo (Congo-Brazzaville)', 'code' => '+242'],
    ['country' => 'Costa Rica', 'code' => '+506'],
    ['country' => 'Croatia', 'code' => '+385'],
    ['country' => 'Cuba', 'code' => '+53'],
    ['country' => 'Cyprus', 'code' => '+357'],
    ['country' => 'Czechia', 'code' => '+420'],
    ['country' => 'Democratic Republic of the Congo', 'code' => '+243'],
    ['country' => 'Denmark', 'code' => '+45'],
    ['country' => 'Djibouti', 'code' => '+253'],
    ['country' => 'Dominica', 'code' => '+1-767'],
    ['country' => 'Dominican Republic', 'code' => '+1-809'],
    ['country' => 'Ecuador', 'code' => '+593'],
    ['country' => 'Egypt', 'code' => '+20'],
    ['country' => 'El Salvador', 'code' => '+503'],
    ['country' => 'Equatorial Guinea', 'code' => '+240'],
    ['country' => 'Eritrea', 'code' => '+291'],
    ['country' => 'Estonia', 'code' => '+372'],
    ['country' => 'Eswatini', 'code' => '+268'],
    ['country' => 'Ethiopia', 'code' => '+251'],
    ['country' => 'Fiji', 'code' => '+679'],
    ['country' => 'Finland', 'code' => '+358'],
    ['country' => 'France', 'code' => '+33'],
    ['country' => 'Gabon', 'code' => '+241'],
    ['country' => 'Gambia', 'code' => '+220'],
    ['country' => 'Georgia', 'code' => '+995'],
    ['country' => 'Germany', 'code' => '+49'],
    ['country' => 'Ghana', 'code' => '+233'],
    ['country' => 'Greece', 'code' => '+30'],
    ['country' => 'Grenada', 'code' => '+1-473'],
    ['country' => 'Guatemala', 'code' => '+502'],
    ['country' => 'Guinea', 'code' => '+224'],
    ['country' => 'Guinea-Bissau', 'code' => '+245'],
    ['country' => 'Guyana', 'code' => '+592'],
    ['country' => 'Haiti', 'code' => '+509'],
    ['country' => 'Honduras', 'code' => '+504'],
    ['country' => 'Hungary', 'code' => '+36'],
    ['country' => 'Iceland', 'code' => '+354'],
    ['country' => 'India', 'code' => '+91'],
    ['country' => 'Indonesia', 'code' => '+62'],
    ['country' => 'Iran', 'code' => '+98'],
    ['country' => 'Iraq', 'code' => '+964'],
    ['country' => 'Ireland', 'code' => '+353'],
    ['country' => 'Israel', 'code' => '+972'],
    ['country' => 'Italy', 'code' => '+39'],
    ['country' => 'Jamaica', 'code' => '+1-876'],
    ['country' => 'Japan', 'code' => '+81'],
    ['country' => 'Jordan', 'code' => '+962'],
    ['country' => 'Kazakhstan', 'code' => '+7'],
    ['country' => 'Kenya', 'code' => '+254'],
    ['country' => 'Kiribati', 'code' => '+686'],
    ['country' => 'Kuwait', 'code' => '+965'],
    ['country' => 'Kyrgyzstan', 'code' => '+996'],
    ['country' => 'Laos', 'code' => '+856'],
    ['country' => 'Latvia', 'code' => '+371'],
    ['country' => 'Lebanon', 'code' => '+961'],
    ['country' => 'Lesotho', 'code' => '+266'],
    ['country' => 'Liberia', 'code' => '+231'],
    ['country' => 'Libya', 'code' => '+218'],
    ['country' => 'Liechtenstein', 'code' => '+423'],
    ['country' => 'Lithuania', 'code' => '+370'],
    ['country' => 'Luxembourg', 'code' => '+352'],
    ['country' => 'Madagascar', 'code' => '+261'],
    ['country' => 'Malawi', 'code' => '+265'],
    ['country' => 'Malaysia', 'code' => '+60'],
    ['country' => 'Maldives', 'code' => '+960'],
    ['country' => 'Mali', 'code' => '+223'],
    ['country' => 'Malta', 'code' => '+356'],
    ['country' => 'Marshall Islands', 'code' => '+692'],
    ['country' => 'Mauritania', 'code' => '+222'],
    ['country' => 'Mauritius', 'code' => '+230'],
    ['country' => 'Mexico', 'code' => '+52'],
    ['country' => 'Micronesia', 'code' => '+691'],
    ['country' => 'Moldova', 'code' => '+373'],
    ['country' => 'Monaco', 'code' => '+377'],
    ['country' => 'Mongolia', 'code' => '+976'],
    ['country' => 'Montenegro', 'code' => '+382'],
    ['country' => 'Morocco', 'code' => '+212'],
    ['country' => 'Mozambique', 'code' => '+258'],
    ['country' => 'Myanmar', 'code' => '+95'],
    ['country' => 'Namibia', 'code' => '+264'],
    ['country' => 'Nauru', 'code' => '+674'],
    ['country' => 'Nepal', 'code' => '+977'],
    ['country' => 'Netherlands', 'code' => '+31'],
    ['country' => 'New Zealand', 'code' => '+64'],
    ['country' => 'Nicaragua', 'code' => '+505'],
    ['country' => 'Niger', 'code' => '+227'],
    ['country' => 'Nigeria', 'code' => '+234'],
    ['country' => 'North Korea', 'code' => '+850'],
    ['country' => 'North Macedonia', 'code' => '+389'],
    ['country' => 'Norway', 'code' => '+47'],
    ['country' => 'Oman', 'code' => '+968'],
    ['country' => 'Pakistan', 'code' => '+92'],
    ['country' => 'Palau', 'code' => '+680'],
    ['country' => 'Palestine', 'code' => '+970'],
    ['country' => 'Panama', 'code' => '+507'],
    ['country' => 'Papua New Guinea', 'code' => '+675'],
    ['country' => 'Paraguay', 'code' => '+595'],
    ['country' => 'Peru', 'code' => '+51'],
    ['country' => 'Philippines', 'code' => '+63'],
    ['country' => 'Poland', 'code' => '+48'],
    ['country' => 'Portugal', 'code' => '+351'],
    ['country' => 'Qatar', 'code' => '+974'],
    ['country' => 'Romania', 'code' => '+40'],
    ['country' => 'Russia', 'code' => '+7'],
    ['country' => 'Rwanda', 'code' => '+250'],
    ['country' => 'Saint Kitts and Nevis', 'code' => '+1-869'],
    ['country' => 'Saint Lucia', 'code' => '+1-758'],
    ['country' => 'Saint Vincent and the Grenadines', 'code' => '+1-784'],
    ['country' => 'Samoa', 'code' => '+685'],
    ['country' => 'San Marino', 'code' => '+378'],
    ['country' => 'Sao Tome and Principe', 'code' => '+239'],
    ['country' => 'Saudi Arabia', 'code' => '+966'],
    ['country' => 'Senegal', 'code' => '+221'],
    ['country' => 'Serbia', 'code' => '+381'],
    ['country' => 'Seychelles', 'code' => '+248'],
    ['country' => 'Sierra Leone', 'code' => '+232'],
    ['country' => 'Singapore', 'code' => '+65'],
    ['country' => 'Slovakia', 'code' => '+421'],
    ['country' => 'Slovenia', 'code' => '+386'],
    ['country' => 'Solomon Islands', 'code' => '+677'],
    ['country' => 'Somalia', 'code' => '+252'],
    ['country' => 'South Africa', 'code' => '+27'],
    ['country' => 'South Korea', 'code' => '+82'],
    ['country' => 'South Sudan', 'code' => '+211'],
    ['country' => 'Spain', 'code' => '+34'],
    ['country' => 'Sri Lanka', 'code' => '+94'],
    ['country' => 'Sudan', 'code' => '+249'],
    ['country' => 'Suriname', 'code' => '+597'],
    ['country' => 'Sweden', 'code' => '+46'],
    ['country' => 'Switzerland', 'code' => '+41'],
    ['country' => 'Syria', 'code' => '+963'],
    ['country' => 'Taiwan', 'code' => '+886'],
    ['country' => 'Tajikistan', 'code' => '+992'],
    ['country' => 'Tanzania', 'code' => '+255'],
    ['country' => 'Thailand', 'code' => '+66'],
    ['country' => 'Timor-Leste', 'code' => '+670'],
    ['country' => 'Togo', 'code' => '+228'],
    ['country' => 'Tonga', 'code' => '+676'],
    ['country' => 'Trinidad and Tobago', 'code' => '+1-868'],
    ['country' => 'Tunisia', 'code' => '+216'],
    ['country' => 'Turkey', 'code' => '+90'],
    ['country' => 'Turkmenistan', 'code' => '+993'],
    ['country' => 'Tuvalu', 'code' => '+688'],
    ['country' => 'Uganda', 'code' => '+256'],
    ['country' => 'Ukraine', 'code' => '+380'],
    ['country' => 'United Arab Emirates', 'code' => '+971'],
    ['country' => 'United Kingdom', 'code' => '+44'],
    ['country' => 'United States', 'code' => '+1'],
    ['country' => 'Uruguay', 'code' => '+598'],
    ['country' => 'Uzbekistan', 'code' => '+998'],
    ['country' => 'Vanuatu', 'code' => '+678'],
    ['country' => 'Vatican City', 'code' => '+379'],
    ['country' => 'Venezuela', 'code' => '+58'],
    ['country' => 'Vietnam', 'code' => '+84'],
    ['country' => 'Yemen', 'code' => '+967'],
    ['country' => 'Zambia', 'code' => '+260'],
    ['country' => 'Zimbabwe', 'code' => '+263']
];

// Handle form submission
if ($_POST && isset($_POST['full_name'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_country_code = preg_replace('/[^0-9+]/', '', $_POST['phone_country_code'] ?? '');
    $phone_local_number = preg_replace('/\D+/', '', $_POST['phone_local_number'] ?? '');
    $phone_number = trim($phone_country_code . $phone_local_number);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $region = $_POST['region'];
    $total_travelers = $_POST['total_travelers'];
    $special_requests = $_POST['special_requests'] ?? '';
    
    // Handle multiple selections
    $selected_safaris = isset($_POST['selected_safaris']) ? json_encode($_POST['selected_safaris']) : json_encode([]);
    $selected_hotels = isset($_POST['selected_hotels']) ? json_encode($_POST['selected_hotels']) : json_encode([]);
    $selected_transports = isset($_POST['selected_transports']) ? json_encode($_POST['selected_transports']) : json_encode([]);
    
    $query = "INSERT INTO bookings (full_name, email, phone_number, start_date, end_date, region, selected_safaris, selected_hotels, selected_transports, total_travelers, special_requests) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    
    if (empty($phone_country_code) || empty($phone_local_number)) {
        $error = "Please select a country code and enter a valid phone number.";
    } elseif ($stmt->execute([$full_name, $email, $phone_number, $start_date, $end_date, $region, $selected_safaris, $selected_hotels, $selected_transports, $total_travelers, $special_requests])) {
        $success = "Booking submitted successfully! You can view your booking status in 'My Bookings' section.";
    } else {
        $error = "Failed to submit booking. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Adventure - Nakupenda Tours & Safaris</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: 'Lato', Arial, sans-serif; }
        
        /* Navigation Styles */
        .responsive-navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #ffb300;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .navbar-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.8rem 1.5rem;
        }
        
        .navbar-logo-img {
            height: 50px;
            width: 50px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #ff8800;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            object-fit: cover;
        }
        
        .navbar-toggle {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 44px;
            height: 44px;
            background: none;
            border: none;
            cursor: pointer;
        }
        
        .navbar-toggle .bar {
            display: block;
            width: 28px;
            height: 3px;
            margin: 3px 0;
            background: #fff;
            border-radius: 2px;
            transition: 0.3s;
        }
        
        .navbar-menu {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .navbar-menu li {
            position: relative;
            margin: 0 0.3rem;
        }
        
        .navbar-menu a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 0.7rem 1rem;
            border-radius: 6px;
            transition: background 0.3s;
            display: block;
        }
        
        .navbar-menu a:hover,
        .navbar-menu a.active {
            background: #ff8800;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #ffb300;
            min-width: 180px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 0 0 8px 8px;
            z-index: 10;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        .dropdown-content a {
            padding: 0.7rem 1rem;
            border-radius: 0;
        }
        
        /* Mobile Styles */
        @media (max-width: 768px) {
            .navbar-container {
                padding: 0.8rem 1rem;
            }
            
            .navbar-toggle {
                display: flex;
                position: absolute;
                right: 1rem;
                top: 50%;
                transform: translateY(-50%);
            }
            
            .navbar-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 280px;
                height: calc(100vh - 70px);
                flex-direction: column;
                background: #ffb300;
                transition: left 0.3s ease;
                padding: 1rem 0;
                box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            }
            
            .navbar-menu.open {
                left: 0;
            }
            
            .navbar-menu li {
                margin: 0;
                width: 100%;
            }
            
            .navbar-menu a {
                padding: 1rem 1.5rem;
                border-radius: 0;
            }
            
            .dropdown-content {
                position: static;
                box-shadow: none;
                background: #ff8800;
                display: none;
            }
            
            .dropdown.open .dropdown-content {
                display: block;
            }
        }
        
          /* Hero Section */
.hero-section {
    margin-top: 80px;
    min-height: 100vh;
    background: linear-gradient(135deg, rgba(20, 83, 45, 0.7), rgba(255, 179, 0, 0.6)), url('photos/download.jpg') center/cover fixed;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    padding: 4rem 2rem;
    position: relative;
    overflow: hidden;
}

.hero-content {
    max-width: 900px;
    z-index: 2;
}

.hero-content h1 {
    font-size: 4.5rem;
    font-weight: 900;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
    line-height: 1.1;
}

.hero-content p {
    font-size: 1.4rem;
    margin-bottom: 3rem;
    opacity: 0.95;
    line-height: 1.6;
}

.cta-buttons {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-button {
    background: #ffb300;
    color: white;
    border: none;
    padding: 1.2rem 2.5rem;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
    box-shadow: 0 8px 25px rgba(255, 179, 0, 0.3);
}

.cta-button.secondary {
    background: transparent;
    border: 3px solid white;
}

.cta-button:hover {
    background: #ff8800;
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(255, 179, 0, 0.4);
}

.cta-button.secondary:hover {
    background: white;
    color: #ff8800;
}
        
        .btn-primary {
            background: #ffb300;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.3s;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background: #ff8800;
        }
        
        .btn-secondary {
            background: transparent;
            color: white;
            padding: 1rem 2rem;
            border: 2px solid white;
            border-radius: 25px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .btn-secondary:hover {
            background: white;
            color: #ffb300;
        }
        
        /* Booking Form Section */
        .booking-section {
            padding: 4rem 1.5rem;
            background: #f8f9fa;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            color: #14532d;
            margin-bottom: 3rem;
        }
        
        .booking-form-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .booking-form {
            padding: 3rem;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            flex: 1 1 calc(50% - 1.5rem);
            min-width: 300px;
        }
        
        .form-group-full {
            flex: 1 1 100%;
        }

        .phone-input-group {
            display: flex;
            gap: 0.75rem;
        }

        .phone-input-group select {
            max-width: 220px;
            flex: 0 0 220px;
        }

        .phone-input-group input {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ffb300;
            box-shadow: 0 0 0 3px rgba(255, 179, 0, 0.2);
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .checkbox-group {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
        }
        
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 1rem;
            background: white;
            border-radius: 8px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        
        .checkbox-item label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
        }
        
        .submit-btn {
            width: 100%;
            background: #ffb300;
            color: white;
            border: none;
            padding: 1.2rem 2rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 2rem;
        }
        
        .submit-btn:hover {
            background: #ff8800;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
        }
        
        .alert-success {
            background: #e6f7e6;
            color: #27ae60;
            border: 1px solid #58d68d;
        }
        
        .alert-error {
            background: #ffe6e6;
            color: #d63031;
            border: 1px solid #ff7675;
        }
        
        /* Footer Styles */
        .safari-footer {
            position: relative;
            color: #fff;
            font-family: 'Lato', Arial, sans-serif;
            background: #ffb300;
            overflow: hidden;
            margin-top: 3rem;
        }

        .safari-footer .footer-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('photos/nakupenda.jpg') center center/cover no-repeat;
            opacity: 0.18;
            z-index: 1;
        }

        .safari-footer .footer-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 1.2rem 1.5rem;
            background: linear-gradient(90deg, #ffb300 80%, #ff8800 100%);
            border-radius: 16px 16px 0 0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }

        .safari-footer .footer-col {
            flex: 1 1 220px;
            margin: 0 1.2rem 1.5rem 0;
            min-width: 180px;
        }

        .safari-footer .footer-col h3, .safari-footer .footer-col h4 {
            margin-bottom: 1rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .safari-footer .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .safari-footer .footer-col ul li {
            margin-bottom: 0.7rem;
            font-size: 1rem;
        }

        .safari-footer .footer-col.quick-links ul li a {
            color: #fff;
            text-decoration: none;
            transition: color 0.18s, background 0.18s;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }

        .safari-footer .footer-col.quick-links ul li a:hover {
            background: #fff;
            color: #ff8800;
        }

        .safari-footer .footer-col.contact-info ul li i {
            margin-right: 0.7rem;
            color: #fff;
        }

        .safari-footer .tagline {
            font-size: 1.05rem;
            margin-bottom: 0.7rem;
            color: #fff;
            font-style: italic;
        }

        .safari-footer .naac {
            font-size: 0.98rem;
            color: #fff;
            opacity: 0.85;
        }

        .safari-footer .footer-bar {
            position: relative;
            z-index: 2;
            background: #ff8800;
            text-align: center;
            padding: 0.9rem 1rem;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.2px;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.04);
            margin-top: -0.5rem;
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .form-group {
                flex: 1 1 100%;
            }
            
            .booking-form {
                padding: 2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .safari-footer .footer-content {
                flex-wrap: wrap;
                padding: 2rem 1rem 1rem 1rem;
            }
            
            .safari-footer .footer-col {
                margin: 0 0.7rem 1.2rem 0;
            }
        }

        @media (max-width: 600px) {
            .safari-footer .footer-content {
                flex-direction: column;
                padding: 1.5rem 0.7rem 0.7rem 0.7rem;
            }
            
            .safari-footer .footer-col {
                margin: 0 0 1.2rem 0;
                min-width: 0;
            }
            
            .safari-footer .footer-bar {
                font-size: 0.95rem;
                padding: 0.7rem 0.5rem;
            }
        }



    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="responsive-navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <img src="photos/download.jpg" alt="Nakupenda Tours" class="navbar-logo-img">
            </div>
            
            <button class="navbar-toggle" aria-label="Toggle menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            
            <ul class="navbar-menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="safari.php">Safari Tours</a></li>
                <li><a href="transport.php">Transport</a></li>
                <li><a href="hotel.php">Hotel</a></li>
                <li><a href="book.php" class="active">Book</a></li>
                <li><a href="order.php">My Bookings</a></li>
                <li><a href="aboutzanzibar.php">Zanzibar</a></li>
                <li><a href="gallery.html">Gallery</a></li>

            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Book Your Adventure</h1>
            <p>Reserve your spot for an unforgettable experience in Tanzania & Zanzibar</p>
            <div class="hero-buttons">
                <a href="#booking-form" class="btn-primary">Book Now</a>
                <a href="order.php" class="btn-secondary">View My Bookings</a>
            </div>
        </div>
    </section>

    <!-- Booking Form Section -->
    <section class="booking-section" id="booking-form">
        <h2 class="section-title">Booking Form</h2>
        
        <div class="booking-form-container">
            <form class="booking-form" method="POST">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone_local_number">Phone Number *</label>
                        <div class="phone-input-group">
                            <select id="phone_country_code" name="phone_country_code" required>
                                <option value="">Code</option>
                                <?php foreach ($country_phone_codes as $entry): ?>
                                    <?php $option_value = $entry['code']; ?>
                                    <option
                                        value="<?php echo htmlspecialchars($option_value, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php echo (isset($_POST['phone_country_code']) && $_POST['phone_country_code'] === $option_value) ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($entry['country'] . ' (' . $entry['code'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input
                                type="tel"
                                id="phone_local_number"
                                name="phone_local_number"
                                placeholder="Phone number"
                                inputmode="numeric"
                                pattern="[0-9]{6,15}"
                                maxlength="15"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                value="<?php echo isset($_POST['phone_local_number']) ? htmlspecialchars(preg_replace('/\D+/', '', $_POST['phone_local_number']), ENT_QUOTES, 'UTF-8') : ''; ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="total_travelers">Total Number of Travelers *</label>
                        <input type="number" id="total_travelers" name="total_travelers" min="1" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date *</label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>
                </div>
                
            <div class="form-row">
    <div class="form-group">
        <label for="region">Select Region *</label>
        <select id="region" name="region" required onchange="filterOptions()">
            <option value="">Choose Region</option>
            <option value="tanzania">Tanzania Mainland</option>
            <option value="zanzibar">Zanzibar</option>
        </select>
    </div>
</div>

<!-- Safari Tours Selection -->
<div class="form-group-full">
    <label>Safari Tours (You Select multiple)</label>
    <div class="checkbox-group">
        <div class="checkbox-grid" id="safari-checkboxes">
            <?php foreach ($safaris as $safari): ?>
                <div class="checkbox-item" data-region="<?php echo $safari['region']; ?>">
                    <input type="checkbox" id="safari_<?php echo $safari['id']; ?>" 
                           name="selected_safaris[]" value="<?php echo $safari['id']; ?>"
                           data-region="<?php echo $safari['region']; ?>">
                    <label for="safari_<?php echo $safari['id']; ?>">
                        <?php echo $safari['title']; ?> 
                        <small style="color: #666;">(<?php echo ucfirst($safari['region']); ?>)</small>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Hotels Selection -->
<div class="form-group-full">
    <label>Hotels </label>
    <div class="checkbox-group">
        <div class="checkbox-grid" id="hotel-checkboxes">
            <?php foreach ($hotels as $hotel): ?>
                <div class="checkbox-item" data-region="<?php echo $hotel['region']; ?>">
                    <input type="checkbox" id="hotel_<?php echo $hotel['id']; ?>" 
                           name="selected_hotels[]" value="<?php echo $hotel['id']; ?>"
                           data-region="<?php echo $hotel['region']; ?>">
                    <label for="hotel_<?php echo $hotel['id']; ?>">
                        <?php echo $hotel['title']; ?> 
                        <small style="color: #666;">(<?php echo ucfirst($hotel['region']); ?>)</small>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Transport Selection -->
<div class="form-group-full">
    <label>Transport</label>
    <div class="checkbox-group">
        <div class="checkbox-grid" id="transport-checkboxes">
            <?php foreach ($transports as $transport): ?>
                <div class="checkbox-item" data-region="<?php echo $transport['region']; ?>">
                    <input type="checkbox" id="transport_<?php echo $transport['id']; ?>" 
                           name="selected_transports[]" value="<?php echo $transport['id']; ?>"
                           data-region="<?php echo $transport['region']; ?>">
                    <label for="transport_<?php echo $transport['id']; ?>">
                        <?php echo $transport['title']; ?> 
                        <small style="color: #666;">(<?php echo ucfirst($transport['region']); ?>)</small>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="form-group-full">
    <label for="special_requests">Special Requests (Optional)</label><br>
    <textarea id="special_requests" name="special_requests" placeholder="Any special requirements or requests..." style="width:90%; height: 50px; border-color: orange; border-radius: 6px;"></textarea>
</div>

<button type="submit" class="submit-btn">Submit Booking</button>
</form>
</div>
</section>

    <!-- Footer -->
    <footer class="safari-footer">
        <div class="footer-bg"></div>
        <div class="footer-content">
            <div class="footer-col company-info">
                <h3>Nakupenda Tours & Safaris</h3>
                <p class="tagline">Crafting Unforgettable Journeys Across Tanzania.</p>
                <p class="naac">Part of the NAAC Group.</p>
            </div>
            <div class="footer-col quick-links">
                <h4>Explore</h4>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="aboutus.php">About Us</a></li>
                    <li><a href="safari.php">Safari Packages</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="gallery.html">Gallery</a></li>

                </ul>
            </div>
            <div class="footer-col destinations">
                <h4>Popular Destinations</h4>
                <ul>
                    <li>Serengeti</li>
                    <li>Ngorongoro</li>
                    <li>Zanzibar</li>
                    <li>Kilimanjaro</li>
                    <li>Tarangire</li>
                </ul>
            </div>
            <div class="footer-col contact-info">
                <h4>Get In Touch</h4>
                <ul>
                    <li><i class="fas fa-phone"></i> +255 620144829</li>
                    <li><i class="fas fa-envelope"></i> info@nakupendatours.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> Dar es Salaam, Tanzania</li>
                </ul>
            </div>
        </div>
        <div class="footer-bar">
            © <?php echo date('Y'); ?> Nakupenda Tours & Safaris. All rights reserved. A proud member of NAAC.
        </div>
    </footer>

    <script>
        // Mobile Navigation
        const navbarToggle = document.querySelector('.navbar-toggle');
        const navbarMenu = document.querySelector('.navbar-menu');
        const dropdowns = document.querySelectorAll('.dropdown');
        
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('open');
        });
        
        // Close menu when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768) {
                if (!navbarMenu.contains(e.target) && !navbarToggle.contains(e.target)) {
                    navbarMenu.classList.remove('open');
                    dropdowns.forEach(dropdown => dropdown.classList.remove('open'));
                }
            }
        });
        
        // Mobile dropdown functionality
        dropdowns.forEach(dropdown => {
            const dropBtn = dropdown.querySelector('a');
            dropBtn.addEventListener('click', function(e) {
                if (window.innerWidth < 768) {
                    e.preventDefault();
                    dropdown.classList.toggle('open');
                }
            });
        });
        
       // Function to filter options based on selected region
function filterOptions() {
    const selectedRegion = document.getElementById('region').value;
    
    // Filter Safari options
    document.querySelectorAll('#safari-checkboxes .checkbox-item').forEach(item => {
        const itemRegion = item.getAttribute('data-region');
        if (!selectedRegion || itemRegion === selectedRegion) {
            item.style.display = 'flex';
            item.style.opacity = '1';
        } else {
            item.style.display = 'none';
            item.style.opacity = '0.5';
        }
    });
    
    // Filter Hotel options
    document.querySelectorAll('#hotel-checkboxes .checkbox-item').forEach(item => {
        const itemRegion = item.getAttribute('data-region');
        if (!selectedRegion || itemRegion === selectedRegion) {
            item.style.display = 'flex';
            item.style.opacity = '1';
        } else {
            item.style.display = 'none';
            item.style.opacity = '0.5';
        }
    });
    
    // Filter Transport options
    document.querySelectorAll('#transport-checkboxes .checkbox-item').forEach(item => {
        const itemRegion = item.getAttribute('data-region');
        if (!selectedRegion || itemRegion === selectedRegion) {
            item.style.display = 'flex';
            item.style.opacity = '1';
        } else {
            item.style.display = 'none';
            item.style.opacity = '0.5';
        }
    });
    
    // Clear selections when region changes
    if (selectedRegion) {
        clearSelectionsFromOtherRegions(selectedRegion);
    }
}

// Function to clear selections from other regions
function clearSelectionsFromOtherRegions(selectedRegion) {
    // Clear safari selections from other regions
    document.querySelectorAll('#safari-checkboxes input[type="checkbox"]').forEach(checkbox => {
        const itemRegion = checkbox.getAttribute('data-region');
        if (itemRegion !== selectedRegion && checkbox.checked) {
            checkbox.checked = false;
        }
    });
    
    // Clear hotel selections from other regions
    document.querySelectorAll('#hotel-checkboxes input[type="checkbox"]').forEach(checkbox => {
        const itemRegion = checkbox.getAttribute('data-region');
        if (itemRegion !== selectedRegion && checkbox.checked) {
            checkbox.checked = false;
        }
    });
    
    // Clear transport selections from other regions
    document.querySelectorAll('#transport-checkboxes input[type="checkbox"]').forEach(checkbox => {
        const itemRegion = checkbox.getAttribute('data-region');
        if (itemRegion !== selectedRegion && checkbox.checked) {
            checkbox.checked = false;
        }
    });
}

// Add event listener for region change
document.getElementById('region').addEventListener('change', filterOptions);

// Initial filter on page load
document.addEventListener('DOMContentLoaded', function() {
    filterOptions();
    
    // Add visual feedback for region-based filtering
    const regionSelect = document.getElementById('region');
    const allCheckboxGroups = [
        '#safari-checkboxes',
        '#hotel-checkboxes', 
        '#transport-checkboxes'
    ];
    
    allCheckboxGroups.forEach(selector => {
        const container = document.querySelector(selector);
        if (container) {
            // Add a message about filtering
            const filterMessage = document.createElement('div');
            filterMessage.id = selector + '-message';
            filterMessage.style.cssText = 'padding: 0.5rem; background: #e6f7e6; border-radius: 5px; margin-bottom: 0.5rem; font-size: 0.9rem; color: #27ae60;';
            filterMessage.innerHTML = '<strong>Tip:</strong> Select a region above to see relevant options';
            container.parentNode.insertBefore(filterMessage, container);
        }
    });
    
    // Update filter message based on selection
    function updateFilterMessage() {
        const selectedRegion = regionSelect.value;
        allCheckboxGroups.forEach(selector => {
            const messageElement = document.querySelector(selector + '-message');
            if (messageElement) {
                if (selectedRegion) {
                    messageElement.innerHTML = `<strong>Showing:</strong> ${selectedRegion.charAt(0).toUpperCase() + selectedRegion.slice(1)} options only`;
                    messageElement.style.background = '#fff3cd';
                    messageElement.style.color = '#856404';
                } else {
                    messageElement.innerHTML = '<strong>Tip:</strong> Select a region above to filter options';
                    messageElement.style.background = '#e6f7e6';
                    messageElement.style.color = '#27ae60';
                }
            }
        });
    }
    
    regionSelect.addEventListener('change', updateFilterMessage);
    updateFilterMessage(); // Initial message
});

// Prevent selecting items from wrong region
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('click', function(e) {
        const selectedRegion = document.getElementById('region').value;
        const itemRegion = this.getAttribute('data-region');
        
        if (selectedRegion && itemRegion !== selectedRegion) {
            e.preventDefault();
            alert(`This option is for ${itemRegion} region. Please select ${selectedRegion} region options only.`);
            this.checked = false;
        }
    });
});
        
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').min = today;
        document.getElementById('end_date').min = today;
        
        // End date validation
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="assets/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = Array.from(document.querySelectorAll('.alert'));
            if (!alerts.length || typeof Swal === 'undefined') {
                return;
            }

            const queue = alerts
                .map(function (alertEl) {
                    alertEl.style.display = 'none';
                    const message = (alertEl.textContent || '').trim();
                    if (!message) {
                        return null;
                    }
                    const isError = alertEl.classList.contains('alert-error');
                    return {
                        icon: isError ? 'error' : 'success',
                        title: isError ? 'Error' : 'Success',
                        text: message
                    };
                })
                .filter(Boolean);

            (async function showAlertsSequentially() {
                for (const item of queue) {
                    await Swal.fire({
                        icon: item.icon,
                        title: item.title,
                        text: item.text,
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                }
            })();
        });
    </script>
</body>
</html>
