<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kredensial Pendaftaran PPDB</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <h2>Selamat, Pendaftaran Santri Baru Berhasil!</h2>
    <p>Halo {{ $name ?? 'Calon Santri' }},</p>
    <p>Terima kasih telah mendaftar di PPTQ Muhammadiyah Ibnu Juraimi. Berikut adalah informasi akun Anda untuk masuk ke sistem:</p>
    <div style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <p><strong>Email:</strong> {{ $email }}</p>
        <p><strong>Password:</strong> {{ $password }}</p>
    </div>
    <p>Silakan login ke portal santri melalui tautan berikut: <a href="{{ route('login') }}">{{ route('login') }}</a></p>
    <p>Simpan kredensial ini dan segera ganti kata sandi setelah login pertama kali demi keamanan akun Anda.</p>
</body>
</html>
