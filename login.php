<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Kue Jeprianto Zai</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            /* Background menggunakan gambar yang sama agar konsisten */
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('Roti.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Kartu Login Transparan */
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 25px;
            /* Efek Glassmorphism */
            background: rgba(255, 255, 255, 0.15); 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            color: white;
        }

        .logo-img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            padding: 5px;
        }

        /* Styling Input agar selaras dengan tema kaca */
        .form-control {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
            padding: 12px 15px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.3);
            border-color: white;
            color: white;
            box-shadow: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
        }

        .btn-login {
            background: #ffffff;
            color: #333;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .text-muted-glass {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        a.glass-link {
            color: rgba(255, 255, 255, 0.8);
            transition: 0.3s;
        }

        a.glass-link:hover {
            color: white;
        }
    </style>
</head>
<body>

    <div class="login-card shadow-lg text-center">
        <div class="login-header">
            <img src="Roti.jpg" alt="Logo Toko" class="logo-img">
            <h3 class="fw-bold m-0">Toko Kue</h3>
            <p class="text-muted-glass small">Jeprianto Zai - Admin System</p>
        </div>

        <form action="proses_login.php" method="POST" class="text-start">
            <div class="mb-3">
                <label class="form-label small fw-bold">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="passInput" class="form-control" placeholder="Password" required>
                    <button class="btn btn-outline-light border-0 px-3" type="button" onclick="togglePass()" style="background: rgba(255,255,255,0.1)">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-login w-100 mb-3">MASUK SEKARANG</button>
            
            <div class="text-center">
                <a href="lupa_password.php" class="text-decoration-none small glass-link">Lupa Password?</a>
            </div>
        </form>
    </div>

    <script>
        function togglePass() {
            const passInput = document.getElementById('passInput');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passInput.type = 'password';
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>
</body>
</html>