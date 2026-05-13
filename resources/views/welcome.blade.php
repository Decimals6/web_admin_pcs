<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            overflow: hidden;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Floating shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
        }

        .shape1 {
            width: 300px;
            height: 300px;
            background: #ff6ec4;
            top: -100px;
            left: -100px;
        }

        .shape2 {
            width: 400px;
            height: 400px;
            background: #42e695;
            bottom: -150px;
            right: -100px;
        }

        .shape3 {
            width: 250px;
            height: 250px;
            background: #fddb92;
            top: 50%;
            left: 60%;
        }

        /* Glass card */
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            color: white;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }

        .btn-custom {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
        }

        .btn-login {
            background: white;
            color: #764ba2;
        }

        .btn-login:hover {
            background: #f1f1f1;
        }

        .btn-register {
            border: 2px solid white;
            color: white;
        }

        .btn-register:hover {
            background: white;
            color: #764ba2;
        }
    </style>
</head>
<body>

    <!-- Shapes -->
    <div class="shape shape1"></div>
    <div class="shape shape2"></div>
    <div class="shape shape3"></div>

    <!-- Center Content -->
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="glass-card">
            <h2 class="mb-4 fw-bold">Admin PCS</h2>
            <p class="mb-4">Silakan masuk atau buat akun untuk melanjutkan</p>

            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ route('login') }}" class="btn btn-custom btn-login">
                    Login
                </a>

                <a href="{{ route('register') }}" class="btn btn-custom btn-register">
                    Register
                </a>
            </div>
        </div>
    </div>

</body>
</html>
