<?php
session_start();
if (isset($_SESSION['employee_id'])) {
  header("Location: pos.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Minute Burger | Login</title>
  <link rel="stylesheet" href="css_frontend.css">
  <style>
    .login-wrap{
      min-height: calc(100vh - 60px);
      display:flex; align-items:center; justify-content:center;
      padding: 30px;
    }
    .login-card{
      width: 420px; max-width: 95vw;
      background: var(--mb-grey);
      border: 1px solid #333;
      border-radius: 10px;
      padding: 26px;
    }
    .login-title{ color: var(--mb-yellow); margin:0 0 6px; font-size:1.6rem; font-weight:900; }
    .login-sub{ margin:0 0 18px; color:#cfcfcf; }
    .login-label{ display:block; margin:10px 0 6px; color:#ddd; font-weight:700; }
    .login-input{
      width:100%; padding:12px; border-radius:6px;
      border:1px solid #444; background:#333; color:#fff; outline:none;
    }
    .login-input:focus{ border-color: var(--mb-orange); box-shadow:0 0 0 3px rgba(255,140,0,.15); }
    .login-actions{ display:flex; gap:10px; margin-top:16px; }
    .btn-login{
      flex:1; background: var(--mb-orange); color:#000;
      border:none; padding:12px; border-radius:6px; font-weight:900; cursor:pointer;
    }
    .btn-clear{
      width:120px; background:#333; color:#fff;
      border:1px solid #444; padding:12px; border-radius:6px; font-weight:800; cursor:pointer;
    }
    .error-box{
      margin-top:14px; padding:10px 12px; border-radius:6px;
      background: rgba(220,53,69,.12);
      border: 1px solid rgba(220,53,69,.35);
      color:#ffb3ba; font-weight:700; display:none;
    }
    .hint{ margin-top:16px; padding-top:12px; border-top:1px dashed #444; color:#bdbdbd; font-size:.9rem; }
    .chip{ display:inline-block; background:#222; border:1px solid #444; color:#eee; padding:6px 10px; border-radius:20px; margin:6px 6px 0 0; font-weight:800; }
    .chip b{ color: var(--mb-yellow); }
  </style>
</head>
<body>

<header class="mb-navbar">
  <div class="brand">
    <div class="logo-text">MINUTE BURGER</div>
  </div>
  <div class="staff-status">LOGIN</div>
</header>

<div class="login-wrap">
  <div class="login-card">
    <h1 class="login-title">Sign in</h1>
    <p class="login-sub">Use your fixed credentials to access the POS.</p>

    <form id="loginForm">
      <label class="login-label" for="username">Username</label>
      <input class="login-input" id="username" name="username" type="text" required>

      <label class="login-label" for="password">Password</label>
      <input class="login-input" id="password" name="password" type="password" required>

      <div class="login-actions">
        <button class="btn-login" type="submit">LOGIN</button>
        <button class="btn-clear" type="button" onclick="document.getElementById('loginForm').reset()">CLEAR</button>
      </div>

      <div id="err" class="error-box"></div>

    </form>
  </div>
</div>

<script>
  const form = document.getElementById('loginForm');
  const errBox = document.getElementById('err');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    errBox.style.display = 'none';
    errBox.textContent = '';

    const fd = new FormData(form);
    const res = await fetch('../backend/login_auth.php', { method: 'POST', body: fd });
    const data = await res.json().catch(() => ({}));

    if (res.ok && data.success) {
      window.location.href = 'pos.php';
      return;
    }

    errBox.textContent = data.message || 'Login failed.';
    errBox.style.display = 'block';
  });
</script>

</body>
</html>