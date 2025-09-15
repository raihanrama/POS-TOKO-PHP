<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - POS Toko Sembako</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/modern-style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        }
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: 0 20px 60px rgba(26, 31, 54, 0.3);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
        }
        .error-icon {
            color: var(--danger);
            margin-bottom: 2rem;
        }
        .error-title {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .error-message {
            color: var(--dark-gray);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card fade-in">
            <i class="fas fa-ban fa-5x error-icon"></i>
            <h2 class="error-title">Akses Ditolak</h2>
            <p class="error-message">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>Kembali ke Login
            </a>
        </div>
    </div>
</body>
</html>
