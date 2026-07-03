<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barbearia Elite</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    background:#111;
    color:#fff;
}

header{
    min-height:100vh;
    background:
    linear-gradient(rgba(0,0,0,.75),rgba(0,0,0,.85)),
    url('https://images.unsplash.com/photo-1503951914875-452162b0f3f1');
    background-size:cover;
    background-position:center;
    display:flex;
    flex-direction:column;
}

nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:25px 8%;
}

.logo{
    color:#d4af37;
    font-size:28px;
    font-weight:bold;
}

nav a{
    color:white;
    text-decoration:none;
    margin-left:20px;
}

.hero{
    flex:1;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    padding:30px;
}

.hero h1{
    font-size:50px;
    color:#d4af37;
}

.hero p{
    font-size:20px;
    margin:20px 0;
}

.btn{
    display:inline-block;
    background:#d4af37;
    color:#111;
    padding:15px 35px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
}

section{
    padding:60px 10%;
    background:#fff;
    color:#222;
    text-align:center;
}

.cards{
    display:flex;
    gap:20px;
    justify-content:center;
    flex-wrap:wrap;
    margin-top:30px;
}

.card{
    width:260px;
    padding:25px;
    border-radius:15px;
    box-shadow:0 5px 20px #ccc;
}

.card h3{
    color:#b8860b;
    margin-bottom:15px;
}

footer{
    background:#000;
    padding:20px;
    text-align:center;
}
</style>
</head>

<body>

<header>

<nav>
<div class="logo">💈 Barbearia Elite</div>

<div>
<a href="login.php">Login</a>
<a href="cadastro.php">Cadastrar</a>
</div>
</nav>


<div class="hero">

<div>
<h1>Seu estilo, nossa tradição</h1>

<p>
Cortes modernos, barba profissional e atendimento premium.
</p>

<a class="btn" href="login.php">
Agendar horário
</a>

</div>

</div>

</header>


<section>

<h2>Nossos Serviços</h2>

<div class="cards">

<div class="card">
<h3>💇 Corte</h3>
<p>Cortes masculinos modernos e personalizados.</p>
</div>

<div class="card">
<h3>🧔 Barba</h3>
<p>Modelagem e acabamento profissional.</p>
</div>

<div class="card">
<h3>⭐ Combo Premium</h3>
<p>Corte + barba com experiência completa.</p>
</div>

</div>

</section>


<section>

<h2>Por que escolher a Elite?</h2>

<p>
Ambiente confortável, profissionais preparados e sistema online
para facilitar seu agendamento.
</p>

</section>


<footer>
© Barbearia Elite - Todos os direitos reservados
</footer>

</body>
</html>
