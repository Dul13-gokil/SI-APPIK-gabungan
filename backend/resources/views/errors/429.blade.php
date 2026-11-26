<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terlalu Banyak Percobaan — SI-APPIK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #0F172A; --muted: #475569; --line: #E2E8F0; --brand: #1E3A8A; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center;
            background: #F8FAFC; color: var(--ink);
            font-family: 'Inter', system-ui, sans-serif; padding: 24px;
        }
        .kartu {
            max-width: 460px; width: 100%; background: #fff;
            border: 1px solid var(--line); border-radius: 14px; padding: 28px;
        }
        h1 { font-family: 'Outfit', sans-serif; font-size: 20px; margin: 0 0 8px; letter-spacing: -0.01em; }
        p { font-size: 14px; line-height: 1.6; color: var(--muted); margin: 0 0 12px; }
        .kode { font-size: 12px; color: var(--muted); margin: 0 0 18px; }
        a.tombol {
            display: inline-block; background: var(--brand); color: #fff; text-decoration: none;
            font-size: 13px; font-weight: 600; padding: 10px 16px; border-radius: 10px;
        }
    </style>
</head>
<body>
    <main class="kartu">
        <h1>Terlalu banyak percobaan masuk</h1>
        <p>
            Demi keamanan akun, sistem membatasi jumlah percobaan login dalam satu menit.
            Tunggu sekitar satu menit, lalu coba lagi dengan kata sandi yang benar.
        </p>
        <p class="kode">Kode kesalahan: 429</p>
        <a class="tombol" href="{{ url('/login') }}">Kembali ke halaman masuk</a>
    </main>
</body>
</html>
