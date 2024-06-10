<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .Hbody {
            margin: 0;
            font-family: 'Tahoma', sans-serif;
        }
        .header {
            background-color: #333;
            color: #fff;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
        }
        .nav-links {
            display: flex;
            align-items: center;
        }
        .nav-links a, .dropdown .dropbtn, .logout-btn {
            padding: 10px 20px;
            margin: 0 10px;
            font-size: 16px;
            color: #fff;
            background-color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }
        .nav-links a:hover, .dropdown .dropbtn:hover, .logout-btn:hover {
            background-color: #fff;
            color: #000;
        }
        .user-icon img {
            width: 50px;
            height: 50px;
            margin-left: 10px;
            border-radius: 10px;
            cursor: pointer;
        }
        .dropdown {
            position: relative;
            display: inline-block;
            margin-right: 10px;
            z-index: 2;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #333;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            padding-top: 10px; /* Added padding to keep the dropdown open */
        }
        .dropdown-content a {
            color: #fff;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1;
            color: #000;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .logout-btn {
            padding: 10px 20px;
            margin-right: 10px;
            font-size: 16px;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }
        .logout-btn:hover {
            background-color: #000;
            color: #fff;
        }
    </style>
</head>
<body class="Hbody">
    <div class="header">
        <div class="user-icon">
            <a href="MANAGE_ACC.php"><img src="user.gif" alt="User Icon"></a>
        </div>
        <div class="title">NEURA</div>
        <div class="nav-links">
            <a href="co_ord_main.php">Home</a>
            <div class="dropdown">
                <button class="dropbtn">Manage</button>
                <div class="dropdown-content">
                    <a href="CO_ORD_EVENT.php" class="button">Manage Event</a>
                    <a href="MANAGE_ACC.php" class="button">Manage Account</a>
                    <a href="MANAGE_VOL.php" class="button">Manage Volunteer</a>
                    <a href="ADD_EXPENSE.php" class="button">Add Expense</a>
                    <a href="CHECK_PROFIT.php" class="button">Check Profit</a>
                    <a href="CO_SPON.php" class="button">Add Sponsors</a>
                    <a href="ADD_FILTERS.php" class="button">Add Filters</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="dropbtn">Registration</button>
                <div class="dropdown-content">
                    <a href="ONSPOT_REG.php">ON-SPOT REGISTRATION</a>
                    <a href="CHECK_REG.php">CHECK REGISTRATION</a>
                </div>
            </div>
            <button onclick="location.href='CO-ORD_LOGIN.PHP'" class="logout-btn">Logout</button>
        </div>
    </div>
</body>
</html>
